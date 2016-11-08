<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Keywords;
use App\Search;
use Carbon\Carbon;

class UpdateDaily extends Command
{

    /** @var string YouTube API key */
    protected $api_key = 'AIzaSyB0aC7v_GYru8bY0_N4ARHi_sKmA_4ZAU8';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upd_daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily update';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $keywords = Keywords::all();
        foreach ($keywords as $keyword){

            $kword = $keyword->keyword;

            $today = Search::where([
                ['created_at', '>=' ,Carbon::today()],
                ['keyword', $keyword->id]
            ])->get();
            if($today->count() != 0){
                continue;
            }
            $this->info("keyword:  ".$kword);

            $lang = $keyword->lang;
            $preferred = $keyword->preferred;
            $channel_id = $keyword->channel_id;
            $videos = $this->getVideos($kword, 100, $lang);
            $i = 0;
            $insert_array = [];
            if (count($videos) > 0){
                $rank = 1;
                foreach($videos as $key => $video){
                    if($i>=5){
                        break;
                    }
                    if($preferred == $video['id']['videoId'] &&
                        $video['snippet']['channelId'] == $channel_id){
                        $insert_array[]=array(
                            'keyword'=>$keyword->id,
                            'video_name'=>$video['snippet']['title'],
                            'video_id'=>$video['id']['videoId'],
                            'rating'=>$rank,
                            'preferred'=>1,
                            'high'=>1,
                            'created_at'=>Carbon::now(),
                        );
                        $i++;
                        $rank++;
                        $preferred = null;
                        continue;
                    }
                    else{
                        if ($video['snippet']['channelId'] == $channel_id) {
                            $high = 0;
                            if($i == 0 && empty($preferred)){
                                $high = 1;
                            }
                            $insert_array[]=array(
                                'keyword'=>$keyword->id,
                                'video_name'=>$video['snippet']['title'],
                                'video_id'=>$video['id']['videoId'],
                                'rating'=>$rank,
                                'preferred'=>0,
                                'high'=>$high,
                                'created_at'=>Carbon::now(),
                            );
                            $i++;
                        };
                    }
                    $rank++;
                }
            }
            if($i == 0){
                $insert_array[] = [
                    'keyword'=>$keyword->id,
                    'video_name'=>'(nothing found in top results)',
                    'video_id'=>'',
                    'rating'=>0,
                    'preferred'=>0,
                    'high'=>1,
                    'created_at'=>Carbon::now(),
                ];
            }
            else if(!empty($preferred)){
                array_unshift($insert_array,[
                    'keyword'=>$keyword->id,
                    'video_name'=>'(preferred video not found in top results)',
                    'video_id'=>'',
                    'rating'=>0,
                    'preferred'=>1,
                    'high'=>1,
                    'created_at'=>Carbon::now(),
                ]);
            }
            Search::insert($insert_array);
        }
    }

    /** Search for videos in YouTube */
    protected function getVideos($term, $max_res=100, $lang='us')
    {
        $i = 1;
        $videos = [];
        $term = urlencode($term);
        if($max_res > 50){
            $i = $max_res / 50;
            if($max_res % 50 != 0){
                $i = (int)($max_res / 50 + 1);
            }
        }
        $nextPage = '';
        $root = 'https://www.googleapis.com/youtube/v3/search?';
        for($i; $i>0; $i--){
            $params = [
                'part' => 'snippet',
                'maxResults' => '50',
                'order' => 'relevance',
                'q' => $term,
                'regionCode' => $lang,
                'type' => 'video',
                'key' => $this->api_key,
            ];
            if(!empty($nextPage)){
                $params['pageToken'] = $nextPage;
            }
            $params = http_build_query($params);
            $request = $root.$params;
            $res = $this->requestAPI($request);
            $res = json_decode($res, true);
            $res = $this->arrayCastRecursive($res);
            if (isset($res['nextPageToken'])){
                $nextPage = $res['nextPageToken'];
            }
            $res = $res['items'];
            foreach ($res as $rkey => $rval){
                $vid = $rval['id']['videoId'];
                $videos[$vid] = $rval;
            }
        }
        return $videos;
    }

    function requestAPI($url){
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
}
