<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use DateInterval;

class AnalysisController extends Controller
{
    protected $api_key = 'AIzaSyB0aC7v_GYru8bY0_N4ARHi_sKmA_4ZAU8';
    protected $country = array(
        'All',
        'USA'=>'USA',
        'Germany'=>'Germany',
        'UK'=>'UK',
        'Japan'=>'Japan',
        'India'=>'India',
        'Denmark'=>'Denmark',
        'Canada'=>'Canada',
        'France'=>'France',
        'South Korea'=>'South Korea',
        'Russia'=>'Russia',
        'Brazil'=>'Brazil',
        'Mexico'=>'Mexico'
    );
    protected $views=0;
    protected $totalWatchTime=0;
    protected $videoLengths=0;
    protected $videosAge=0;
    protected $uploadedLast30Days=0;
    protected $likes=0;
    protected $disLikes=0;
    protected $comments=0;
    protected $subtitles=0;
    protected $channelAge=0;
    protected $channelSubscribers=0;
    protected $channelVideos=0;
    protected $searchResult = [];

    public function __construct(Request $laravel_request){
        if(!$laravel_request->session()->has('user_id')){
            return redirect('login');
        }
    }

    public function index(Request $laravel_request){

        if($laravel_request->session()->has('user_id')){
            $data = $laravel_request->session()->all();
            return view('analysis',['data'=>$data]);
        }else{
            return redirect('login');
        }
    }

    /** Search for videos in YouTube */
    protected function getVideos(Request $laravel_request)
    {
        $regionCode = $laravel_request->input('country');
        $term = trim($laravel_request->input('term_1'));
        $tags = preg_split('/\n|\r\n?/',$term);
        $videos = [];
        $videosInfo = [];
        $totalCount = 0;
        $root = 'https://www.googleapis.com/youtube/v3/search?';
        foreach($tags as $tag){
            $params = [
                'part' => 'snippet',
                'maxResults' => '10',
                'order' => 'relevance',
                'q' => $tag,
                'regionCode' => $regionCode,
                'type' => 'video',
                'key' => $this->api_key,
            ];
            $params = http_build_query($params);
            $request = $root.$params;
            $res = $this->requestAPI($request);
            $res = json_decode($res, true);
//            $res = $this->arrayCastRecursive($res);
//            if (isset($res['nextPageToken'])){
//                $nextPage = $res['nextPageToken'];
//            }
            $res = $res['items'];
            foreach ($res as $video){
                array_push($videos,$video['id']['videoId']);
            }
        }
        $totalCount+=count($videos);
        //var_dump($totalCount);
        //dd($videos);
        for($i=0;$i<count($videos);$i++){
            $videoInfo = $this->videoInfoGetter($videos[$i]);
            if($videoInfo){
                array_push($videosInfo,$videoInfo['items']);
                $this->views+=(int)$videoInfo['items'][0]['statistics']['viewCount'];

                $interval = new DateInterval($videoInfo['items'][0]['contentDetails']['duration']);
                $this->videoLengths+=$interval->s+($interval->i*60)+($interval->h*360);
//var_dump($this->videoLengths);
                $this->videosAge += (int)((strtotime(date(DATE_ATOM))-strtotime($videoInfo['items'][0]['snippet']['publishedAt'])) / 86400);
                if($this->videosAge<31){
                    $this->uploadedLast30Days++;
                }
                if(isset($videoInfo['items'][0]['statistics']['likeCount'])) {
                    $this->likes += (int)$videoInfo['items'][0]['statistics']['likeCount'];
                }

                if(isset($videoInfo['items'][0]['statistics']['dislikeCount'])){
                    $this->disLikes += (int)$videoInfo['items'][0]['statistics']['dislikeCount'];
                }

                if(isset($videoInfo['items'][0]['statistics']['commentCount'])){
                    $this->comments += (int)$videoInfo['items'][0]['statistics']['commentCount'];
                }

                $this->channelAge += (int)((strtotime(date(DATE_ATOM))-strtotime($videoInfo['items'][0]['channelPublishedAt'])) / 86400);
                $this->channelSubscribers += (int)$videoInfo['items'][0]['channelSubscriberCount'];
                $this->channelVideos += (int)$videoInfo['items'][0]['channelVideoCount'];
                $subtitle=$this->simpleCurl("http://video.google.com/timedtext?type=list&v=".$videoInfo['items'][0]['id']);
                if(strlen($subtitle)>110){
                    $this->subtitles++;
                }
            }

        }

        //dd($videos);
        $this->searchResult['views'] = (int)floor($this->views/$totalCount);
        $this->searchResult['videoLength'] = number_format((int)floor($this->videoLengths/$totalCount/60));
        $this->searchResult['videoAge'] = number_format((int)floor($this->videosAge/$totalCount));
        $this->searchResult['uploadedLast30Days'] = $this->uploadedLast30Days;
        $this->searchResult['like'] = number_format((int)floor($this->likes/$totalCount));
        $this->searchResult['disLike'] = number_format((int)floor($this->disLikes/$totalCount));
        $this->searchResult['comment'] = number_format((int)floor($this->comments/$totalCount));
        $this->searchResult['subtitle'] = intval($this->subtitles*100/$totalCount);
        $this->searchResult['totalEngagement'] = number_format((int)floor(($this->likes+$this->disLikes+$this->comments)/$totalCount));
        $this->searchResult['totalEngagementPerView'] = round(($this->likes+$this->disLikes+$this->comments)/$totalCount/$this->searchResult['views'],4);
        $this->searchResult['channelAge'] = number_format((int)floor($this->channelAge/$totalCount));
        $this->searchResult['channelSubscriber'] = number_format((int)floor($this->channelSubscribers/$totalCount));
        $this->searchResult['channelVideo'] = number_format((int)floor($this->channelVideos/$totalCount));
        $this->searchResult['views'] = number_format((int)floor($this->views/$totalCount));
        //dd($this->searchResult);
        $laravel_request->session()->put('csv', $this->searchResult);
        return redirect('analysis')->with($this->searchResult);
       // echo json_encode($this->searchResult);
    }

    function simpleCurl($url){
        set_time_limit(0);

        $ch = curl_init();
        $curlConfig = array(
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => array()
        );
        curl_setopt_array($ch, $curlConfig);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    function requestAPI($url){
        set_time_limit(0);
        $ch = curl_init();
        curl_setopt_array(
            $ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            //todo remove
            CURLOPT_SSL_VERIFYHOST=> 0,
            CURLOPT_SSL_VERIFYPEER=> 0,
        ));
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }

    public function arrayCastRecursive($array){
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = $this->arrayCastRecursive($value);
                }
                if ($value instanceof stdClass) {
                    $array[$key] = $this->arrayCastRecursive((array)$value);
                }
            }
        }
        if ($array instanceof stdClass) {
            return $this->arrayCastRecursive((array)$array);
        }
        return $array;
    }

    public function videoInfoGetter($videoId){
        //https://www.googleapis.com/youtube/v3/videos?id=qZKvZzRynLE&key=AIzaSyB0aC7v_GYru8bY0_N4ARHi_sKmA_4ZAU8&fields=items(id,snippet(channelId,title,categoryId,publishedAt),statistics(viewCount,likeCount,commentCount,dislikeCount),contentDetails(duration))&part=snippet,contentDetails,statistics,status
        $root = 'https://www.googleapis.com/youtube/v3/videos?';
        $params = [
            'id' => $videoId,
            'key' => $this->api_key,
            'fields' => 'items(id,snippet(channelId,publishedAt),statistics(viewCount,likeCount,commentCount,dislikeCount),contentDetails(duration))',
            'part' => 'snippet,contentDetails,statistics,status',
//            'maxResults' => '10',
//            'order' => 'relevance',
//            'q' => $term,
//            'regionCode' => $lang,
//            'type' => 'video',

        ];
        $params = http_build_query($params);
        $request = $root.$params;
        $videoRes = $this->requestAPI($request);
        $videoRes = json_decode($videoRes, true);
        if(isset($videoRes)){
            $channelRes = $this->channelInfoGetter($videoRes['items'][0]['snippet']['channelId']);

            $videoRes['items'][0]['channelPublishedAt'] = $channelRes['items'][0]['snippet']['publishedAt'];
            $videoRes['items'][0]['channelSubscriberCount'] = $channelRes['items'][0]['statistics']['subscriberCount'];
            $videoRes['items'][0]['channelVideoCount'] = $channelRes['items'][0]['statistics']['videoCount'];
            return $videoRes;
        }else{
            return false;
        }

//            $res = $this->arrayCastRecursive($res);
//            if (isset($res['nextPageToken'])){
//                $nextPage = $res['nextPageToken'];
//            }
    }

    public function channelInfoGetter($channelId){
        //https://www.googleapis.com/youtube/v3/videos?id=qZKvZzRynLE&key=AIzaSyB0aC7v_GYru8bY0_N4ARHi_sKmA_4ZAU8&fields=items(id,snippet(channelId,title,categoryId,publishedAt),statistics(viewCount,likeCount,commentCount,dislikeCount),contentDetails(duration))&part=snippet,contentDetails,statistics,status
        $root = 'https://www.googleapis.com/youtube/v3/channels?';
        $params = [
            'id' => $channelId,
            'key' => $this->api_key,
            'fields' => 'items(id,snippet(publishedAt),statistics(subscriberCount,videoCount))',
            'part' => 'snippet,statistics,status',
//            'maxResults' => '10',
//            'order' => 'relevance',
//            'q' => $term,
//            'regionCode' => $lang,
//            'type' => 'video',

        ];
        $params = http_build_query($params);
        $request = $root.$params;
        $res = $this->requestAPI($request);
        $res = json_decode($res, true);

        return $res;
    }

    public function csvDownload(Request $laravel_request){
        $fills = $laravel_request->session()->get('csv');
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="rankings.csv";');
        $fp = fopen('php://output', 'w');
        fputcsv($fp, array('Views','Video Length','Video Age','Uploaded last 30 days','Likes','Dislikes','Comments','With Subtitles','Total Engagement','Total Engagement Per View','Channel Age','Channel Subscribers','Channel Videos'));
        fputcsv($fp, $fills);
        fclose($fp);

        return;
    }
}