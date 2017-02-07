<?php

namespace App\Http\Controllers;

use App\Channels;
use App\Clickability;
use App\Keywords;
use App\Top;
use Illuminate\Http\Request;
use DB;
use DateTime;
use Carbon\Carbon;
use Goutte\Client;
use App\User;
use App\Search as Search;
use League\Flysystem\Exception;

class DashboardController extends Controller
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
    public function __construct(Request $laravel_request){
        if(!$laravel_request->session()->has('user_id')){
            return redirect('login');
        }
    }

    public $youtube;
    public function index(Request $laravel_request){
        if($laravel_request->session()->has('user_id')){
            return view('dashboard');
        }else{
            return redirect('login');
        }
    }

    public function rankingsJson(Request $request)
    {
        $array = [];
        $special = false;
        $where = [
            ['high', 1],
//            ['rating', '!=', 0],
        ];
        if($request->input('groupAll')){
            $k = Keywords::where([
                ['user_id', session('user_id')],
                ['channel_id', session('default_channel')->channelid]
            ])->get()->toArray();
            foreach ($k as $key=>$val){
                if(!empty($val['group'])){
                    $ob = json_decode($val['group']);
                    if($ob !== null) {
                        if(in_array($request->input('groupAll'),$ob)){
                            $k[$key]  = (object)$val;
                        }
                        else{
                            unset($k[$key]);
                        }
                    }else{
                        if($request->input('groupAll') == $val['group'] ){
                            $k[$key] = (object)$val;
                        }else{
                            unset($k[$key]);
                        }
                    }
                }else{
                    unset($k[$key]);
                };

            }
            $k = (object)$k;
        }else{
            $k = Keywords::where([
                ['user_id', session('user_id')],
                ['channel_id', session('default_channel')->channelid]
            ])->get();
        }

        $keys = [];
        foreach($k as $v){
            $keys[] = $v->id;
        }

        if(is_array($request->input('keyword'))){
            $search = Search::orderBy('created_at', 'asc')
                ->where($where)
                ->whereIn('keyword', $request->input('keyword'))
                ->get()
                ->groupBy(function($search) {
                    return Carbon::parse($search->created_at)->format('Y-m-d');
                });
				
            $keyCount=0;
            foreach ($search as $date => $items){
                $d = sizeof($items);
                $a = 0;
                foreach ($items as $item){
                    $a += $item->rating;
                }
                $array[$keyCount] = [strtotime($date)*1000, $a/$d];
				if(!isset($array[1])){
					$array[1] = [strtotime($date)*1000, $a/$d];
				}
                $keyCount++;
            }

            return response()->json($array);
        }
        elseif($request->input('keyword')){
                $keyword = Keywords::where([
                    ['keyword', $request->input('keyword')],
                    ['user_id', session('user_id')],
                    ['channel_id', session('default_channel')->channelid],
                ])->first()->id;
                $where[] = ['keyword', $keyword];
                $special = true;
        }

        $search = Search::orderBy('created_at', 'asc')
            ->where($where)
            ->where('rating','!=',0)
            ->whereIn('keyword', $keys)
            ->get()
            ->groupBy(function($search) {
                return Carbon::parse($search->created_at)->format('Y-m-d');
            });

        if($special){
            $keyCount = 0;
            foreach ($search as $date => $items){
                $array[$keyCount] = [strtotime($date)*1000, (int)$items[0]->rating];
				if(!isset($array[1])){
                    $array[1] = [strtotime($date)*1000, (int)$items[0]->rating];
				}
		        $keyCount++ ;
            }
             
        }
        else {
            $keyCount = 0;
            if(!isset($search) || empty($search)){
                response()->json('no property');
            }
            foreach ($search as $date => $items){
                $d = sizeof($items);
                $a = 0;
                foreach ($items as $item){
                    $a += $item->rating;
                }
                $array[$keyCount] = [strtotime($date)*1000, $a/$d];
				if(!isset($array[1])){
					$array[1] = [strtotime($date)*1000, $a/$d];
				}
				$keyCount++;
            }
            
        }
        return response()->json($array);
    }

    public function rankingsJsonOption(Request $request)
    {
        $array = [];
        $where = [
            ['high', 1],
//            ['rating', '!=', 0],
        ];
        $k = Keywords::where([
            ['user_id', session('user_id')],
            ['channel_id', session('default_channel')->channelid]
        ])->get();
        $keys = [];
        foreach($k as $v){
            $keys[] = $v->id;
        }
        foreach ($request->input('keyword') as $keyword){
            $where[1] = ['keyword', $keyword];
            $search[] = Search::orderBy('created_at', 'asc')
                ->where($where)
                ->get()
                ->groupBy(function($search) {
                    return Carbon::parse($search->created_at)->format('Y-m-d');
                });
        }
        foreach ($search as $date => $items) {
            foreach ($items as $dat=>$item){
                $d = sizeof($item);
                $a = 0;
                foreach ($item as $it) {
                    $a += $it->rating;
                }
                if($dat==0){
                    dd($dat);
                }
                $array[$date][] = [strtotime($dat) * 1000, $a / $d];
				if(!isset($array[$date][1])){
                       $array[$date][1] = [strtotime($dat) * 1000, $a / $d];
                }

            }                   
        }

        return response()->json($array);
    }

    public function rankings(Request $laravel_request,$csv=false){
        if(!$laravel_request->session()->has('default_channel')){
            $laravel_request->session()->flash('error', 'Add a channel first');
            return redirect()->back();
        }
        $this->updateTop();
        $user_id = session('user_id');
        $channel_session = session('default_channel');
        $alldata = [];
        $keywords = Keywords::where('user_id', $user_id)
            ->where('channel_id', $channel_session->channelid)
            ->get();
        $totalCount = $keywords->count();
        foreach ($keywords as $keyword){
            $searches = Search::where('keyword', $keyword->id)
                ->where('created_at', '>=', Carbon::today())
                ->get();
            $count = $searches->count();
            foreach ($searches as $search){
                if($search->high){
                    $alldata[] = [
                        'count'=>$count,
                        'title'=>$search->video_name,
                        'keyword'=>$keyword->keyword,
                        'keyword_id'=>$keyword->id,
                        'rank'=>/*$search->rating,*/($search->rating==0)?'N/A':$search->rating,
                        'high'=>$search->high,
                        'country'=>$keyword->country,
                        'group'=>$keyword->group,
                        'url'=>$search->video_id,
                        'preferred'=>$search->preferred,
                        'others'=>$searches->toArray(),
                    ];
                }
            }
        }

        $alldata = array_values(array_sort($alldata, function ($value) {
            return $value['rank'];
        }));

        $maxNum = [];
        foreach ($alldata as $key=>$alldat){
            if($alldat['rank']=="N/A"){
                $alldata[]=$alldat;
                unset($alldata[$key]);
            }else{
                $maxNum[]=$alldat['rank'];
            }
        }

        $prevData = [];
        $dates = [
            'day'=>[
                '-1 day',
                '-0 day'
            ],
            'week'=>[
                '-7 day',
                '-6 day'
            ],
            'month'=>[
                '-30 day',
                '-29 day'
            ],
        ];


        foreach ($dates as $key => $value){
            $dateSeach = Search::orderBy('rating', 'desc')
                ->groupBy('keyword')
                ->where('created_at', '>=', date('Y-m-d',(strtotime ($value[0]))).' 00:00:00')
                ->where('created_at', '<=', date('Y-m-d',(strtotime ($value[1]))).' 00:00:00')
                ->get();

            foreach ($dateSeach as $dataKey => $search){
                $searchKey = Keywords::where('id', $search->keyword)->get()->first();
                 if($searchKey){
                     $keyword =  $searchKey->keyword;
                 }
                $prevData[$keyword][$key] = [
                    'rating'=>$search->rating,
                ];
            }
        }
        foreach ($alldata as $data){
            foreach ($dates as $key => $value){
                if(isset($prevData[$data['keyword']][$key])){
                    $prevData[$data['keyword']][$key]['rating'] = -$prevData[$data['keyword']][$key]['rating'] + $data['rank'];
                }
            }
        }
        $topData['total'] = $totalCount;
        $topData['today'] = [];
        $topData['tops'] = [];
        $topData['yesterday'] = [];
        
        $topSearch = Top::where([
            ['user_id',session('user_id')],
            ['channel_id',session('default_channel')->channelid],
            ['created_at', '>=', Carbon::today()],
        ])
            ->orderBy('top', 'asc')
            ->get()
            ->toArray();
        foreach ($topSearch as $top){
            $topData['today'][$top['top']] = $top['quantity'];
            $topData['tops'][] = $top['top'];
        }
        $topSearch = Top::where('created_at', '>=', Carbon::yesterday())
            ->where('channel_id', $channel_session->channelid)
            ->where('user_id', session('user_id'))
            ->where('created_at', '<=', Carbon::today())
            ->orderBy('top', 'asc')
            ->get()
            ->toArray();
        foreach ($topSearch as $top){
            if(isset($topData['today'][$top['top']])){
                 $topData['yesterday'][$top['top']] = $topData['today'][$top['top']] - $top['quantity'];
            }else{
                 $topData['yesterday'][$top['top']] = 0;
            }
        }

        $countKeyword = Keywords::where('user_id', $user_id)->get()->count();

        $country = collect($this->country);
        $sortedCountry = $country->sort();
        $country->shift();
        $sortedCountryAddKeyword =$country->all();

        if($csv=='csv'){
            $stepForCsv = 0;
            if(!isset($alldata) || empty($alldata)){
                $laravel_request->session()->flash('error', 'Not a information');
                return redirect()->back();
            }
            foreach ($alldata as $alldat){
                $csvFile[$stepForCsv]['Video'] = $alldat['title'];
                $csvFile[$stepForCsv]['Keyword'] = $alldat['keyword'];
                $csvFile[$stepForCsv]['Rank'] = $alldat['rank'];

                if(isset($prevData[$alldat['keyword']]['day'])){
                    $csvFile[$stepForCsv]['Day'] = $prevData[$alldat['keyword']]['day']['rating'];
                }else{
                    $csvFile[$stepForCsv]['Day'] = '-';
                }
                if(isset($prevData[$alldat['keyword']]['week'])){
                    $csvFile[$stepForCsv]['Week'] = $prevData[$alldat['keyword']]['week']['rating'];
                }else{
                    $csvFile[$stepForCsv]['Week'] = '-';
                }
                if(isset($prevData[$alldat['keyword']]['month'])){
                    $csvFile[$stepForCsv]['Month'] = $prevData[$alldat['keyword']]['month']['rating'];
                }else{
                    $csvFile[$stepForCsv]['Month'] = '-';
                }

                $csvFile[$stepForCsv]['Country'] = $alldat['country'];
                $csvFile[$stepForCsv]['URL'] = (!empty($alldat['url']))?"https://www.youtube.com/watch?v=".$alldat['url']:'';


                $stepForCsv++;
            }

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="rankings.csv";');
            $fp = fopen('php://output', 'w');
            fputcsv($fp, array('Video','Keyword','Rank','Day','Week','Month','Country','URL'));
            foreach ($csvFile as $csvFil){
                fputcsv($fp, $csvFil);
            }
            fclose($fp);

            return;

        }
        $groupsQuery = Keywords::select('group')->where('channel_id', $channel_session->channelid)->distinct()->get()->toArray();
        $group=['First','Second','Third'];
        foreach ($groupsQuery as $groupQuery){
            if($groupQuery['group'] != ""){
                $ob = json_decode($groupQuery['group']);
                if($ob === null) {
                    $group[$groupQuery['group']]=$groupQuery['group'];
                }else{
                    $newGroupArr[] = $ob;
                }
            };
        };

        if(isset($newGroupArr) && !empty($newGroupArr)){
            foreach ($newGroupArr as $newGroupAr){
                foreach ($newGroupAr as $newGr) {
                    $group[] = $newGr;
                }
            }
        }

        $group = array_unique($group);
        foreach ($group as $ke=>$grou){
            $groups[$grou]=$grou;
        }
        unset($group);

        $groupSlectPage = $groups;
        array_unshift($groupSlectPage,'All');
        return view('rankings', [
            'alldata' => $alldata,
            'prevData' => $prevData,
            'topData' => $topData,
            'countKeyword' => $countKeyword,
            'country' => $sortedCountry,
            'countryKeyword' => $sortedCountryAddKeyword,
            'group' => $groups,
            'groupSelect' => $groupSlectPage,
            'maxRank' => (!empty($maxNum))?max($maxNum)+1:0,
        ]);


    }

    public function clickability(Request $laravel_request){

        if($laravel_request->session()->has('user_id')){
            $channel_session = session('default_channel');
            if(!$laravel_request->session()->has('default_channel')){
                $laravel_request->session()->flash('error', 'Add a channel first');
                return redirect()->back();
            }
            $groupsQuery =
                Keywords:://select('group')->
                      where('channel_id', $channel_session->channelid)
                    ->where('user_id',$channel_session->user_id)
                    ->where('group', '!=' , '')
                    ->distinct()
                    ->get()
                    ->toArray();
            $group=[];

            if(empty($groupsQuery)){
                $laravel_request->session()->flash('error', 'Please add keyword in group');
                return redirect()->back();
            }
            foreach ($groupsQuery as $groupQuery){
                if($groupQuery['group'] != ""){
                    $ob = json_decode($groupQuery['group']);
                    if($ob === null) {
                        $group[$groupQuery['group']]=$groupQuery['group'];
                    }else{
                        $newGroupArr[] = $ob;
                    }
                };
            };

            if(isset($newGroupArr) && !empty($newGroupArr)){
                foreach ($newGroupArr as $newGroupAr){
                    foreach ($newGroupAr as $newGr) {
                        $group[] = $newGr;
                    }
                }
            }
            $group = array_unique($group);

            foreach ($group as $ke=>$grou){
                $grouKey = str_replace(' ','____',$grou);
                $groupsCharts[$grouKey]=$grou;
            }
            unset($group);
            if($laravel_request->ajax()){
                $getEndResult = Clickability::
                    select('data')
                    ->where('channel_id', $channel_session->channelid)
                    ->where('user_id',$channel_session->user_id)
                    ->orderBy('created_at','desc')
                    ->get()->toArray();
                if(!empty($getEndResult)){
                    $data = json_decode($getEndResult[0]['data']);
                    return response()->json($data);
                }else{
                    return response()->json('empty');
                }
            }
            return view('clickability',compact('groupsCharts'));
        }else{
            return redirect('login');
        }
    }
    public function clickabilityJson(Request $request){
        $channel_session = session('default_channel');
        $getDataCharts= Clickability::
            select('data_chart')
                ->where('channel_id', $channel_session->channelid)
                ->where('user_id',$channel_session->user_id)
                ->get()->toArray();
        $groupKey = str_replace(' ','____',$request->input('groupAll'));
        foreach ($getDataCharts as $key=>$getDataChart){
            $ob = json_decode($getDataChart['data_chart']);
            if(is_object($ob)) {
                $arrayOb = get_object_vars($ob);
                foreach ($arrayOb as $keyOb=>$arrayO){
                    if($groupKey == $keyOb){
                        $data['data'][$key] = floatval(number_format($arrayO, 2));
                    }
                }
            }else{
                $data['data'][$key] = floatval(number_format($getDataChart['data_chart'], 2));
            }
        }
        if(!isset($data['data'][0])){
            $data['data'][0] = 0;
            $data['data'][1] = 0;
        }elseif(!isset($data['data'][1])){
            $data['data'][1] = $data['data'][0];
        }
        $data['name'] = $channel_session->channelname;
        return response()->json($data);
    }

    public function changeKeywordGroup(Request $request){
        $postKeywords = $request->input('keyword');
        $postGroup = $request->input('group');
        if($request->input('removeKeywordFromGroups') == '1'){
            foreach ($postKeywords as $postKeyword){
                Keywords::where('id', $postKeyword)
                    ->where('user_id', session('user_id'))
                    ->where('channel_id', session('default_channel')->channelid)
                    ->where('group',$postGroup)
                    ->update((['group' => '']))
                ;
            }
        }else{
            foreach ($postKeywords as $postKeyword){
                $res = Keywords::where('id', $postKeyword)
                    ->where('user_id', session('user_id'))
                    ->where('channel_id', session('default_channel')->channelid);
                $groupArr = (json_decode($res->first()->group)===null)?$res->first()->group:json_decode($res->first()->group);
                $groupArray = array();
                if(!empty($groupArr) && !is_string($groupArr)){
                    foreach ($groupArr as $k=>$groupAr){
                        $groupAr = str_replace('"', "", $groupAr);
                        $groupArray[str_replace('"', "", $k)] = $groupAr;
                    }
                }else{
                    $groupArray[] = $groupArr;
                }
                if(!in_array($postGroup,$groupArray)){
                    $groupArray[] = $postGroup;
                }

                $res->update((['group' => json_encode($groupArray)]));
                unset($groupArray);
            }
        }
        echo 'success';
        return;

    }
    public function setPreferred(Request $request)
    {
        $term = $request->input('keyword');
        $keyword_id = $request->input('keyword_id');
        $videos = $this->getVideos($term);
        $removePerfered = $request->input('removePerfered');
        if($removePerfered == 1){
            $keyword = Keywords::where('id', $keyword_id)
                ->where('user_id', session('user_id'))
                ->where('channel_id', session('default_channel')->channelid);

            $keyword->update((['preferred' => '']));

            $getSearch = Search::where('keyword', $keyword_id)
                ->where('preferred',1);
            if(!empty($getSearch->first()->video_id)){
                $video_name = $videos[$getSearch->first()->video_id]['snippet']['title'];
            }else{
                $video_name = "(nothing found in top results)";
            }
            $getSearch->update((['preferred' => '','video_name'=>$video_name]));
            return redirect('rankings');
        }
        $pref_url = $request->input('video_url');
        $url = parse_url($pref_url);
        if(!isset($url['query'])){
            $request->session()->flash('error', 'Invalid preferred url');
            return redirect('rankings');
        }
        parse_str($url['query'],$array);
        $pref_id = $array['v'];
        $keyword = Keywords::where('keyword', $term)
            ->where('user_id', session('user_id'))
            ->where('channel_id', session('default_channel')->channelid)
            ->get()->first()->id;
        $insert_array =[];
        $i = 0;
        if (count($videos) > 0){
            $rank = 1;
            foreach($videos as $key => $video){
                if($i>=5){
                    break;
                }
                $term = strtolower(urldecode($term));
                if(!empty($pref_url)){
                    if($pref_id == $video['id']['videoId'] &&
                    $video['snippet']['channelId'] == session('default_channel')->channelid){
                        $insert_array[]=array(
                            'keyword'=>$keyword,
                            'video_name'=>$video['snippet']['title'],
                            'video_id'=>$video['id']['videoId'],
                            'rating'=>$rank,
                            'preferred'=>1,
                            'high'=>1,
                            'created_at'=>Carbon::now(),
                        );
                        $pref_url = '';
                        $i++;
                        $rank++;
                        continue;
                    }
                }
                if ($video['snippet']['channelId'] == session('default_channel')->channelid) {
                    $high = 0;

                    $insert_array[]=array(
                        'keyword'=>$keyword,
                        'video_name'=>$video['snippet']['title'],
                        'video_id'=>$video['id']['videoId'],
                        'rating'=>$rank,
                        'preferred'=>0,
                        'high'=>$high,
                        'created_at'=>Carbon::now(),
                    );
                    $i++;
                };
                $rank++;
            }
        }
        if(!empty($pref_url)){
            array_unshift($insert_array,[
                'keyword'=>$keyword,
                'video_name'=>'(preferred video not found in top results)',
                'video_id'=>'',
                'rating'=>0,
                'preferred'=>1,
                'high'=>1,
                'created_at'=>Carbon::now(),
            ]);
        }
//        if($i > 0){
//            Search::where('keyword',$keyword)
//                ->where('created_at', '>=', Carbon::today())
//                ->delete();
//        }
        Keywords::where('keyword', $term)
            ->where('user_id', session('user_id'))
            ->where('channel_id', session('default_channel')->channelid)
            ->update((['preferred' => $pref_id]))
        ;

        Search::where('keyword', $keyword_id)
            ->where('created_at', '>=', Carbon::today())
            ->where('high','1')
            ->update($insert_array[0]);

        return redirect('rankings');
    }

    public function updateTop()
    {
        $k = Keywords::select('id')->where([
            ['user_id', session('user_id')],
            ['channel_id', session('default_channel')->channelid]
        ])->get();
        $keys = [];
        foreach($k as $v){
          $keys[] = $v->id;
        }
        
        $tops = ['5','10','25','50','100'];
        $where = [
            ['rating', '!=', 0],
            ['created_at', '>=', Carbon::today()],
        ];
        $todaySearch = Search::orderBy('id', 'desc')
            ->groupBy('keyword')
            ->where($where)
            ->whereIn('keyword', $keys)
            ->get()
            ->toArray();
        $results = [];
        foreach ($todaySearch as $item){
            foreach ($tops as $i => $top){
                if($item['rating'] <= $top){
                    $results[$i]['top'] = $top;
                    $results[$i]['user_id'] = session('user_id');
                    $results[$i]['channel_id'] = session('default_channel')->channelid;
                    if(!isset($results[$i]['quantity'])){
                        $results[$i]['quantity'] = 0;
                    }
                    $results[$i]['quantity']++;
                    $results[$i]['created_at'] = Carbon::now();
                }
            }
        }
        foreach ($tops as $i => $top){
        	if(!isset($results[$i])){
        	    $results[$i] = [];
        	    $results[$i]['top'] = $top;
                    $results[$i]['user_id'] = session('user_id');
                    $results[$i]['channel_id'] = session('default_channel')->channelid;
                    $results[$i]['quantity'] = 0;
                    $results[$i]['created_at'] = Carbon::now();
        	}
        }
        
        foreach($results as $uptData){
        	$setTop = Top::where([
        	    ['user_id',session('user_id')],
                ['channel_id',session('default_channel')->channelid],
                ['top',$uptData['top']],
                ['created_at', '>=', Carbon::today()],
            ])
                ->first();
        	if(is_null($setTop) || empty($setTop)){
        		Top::insert($uptData);
        	}else{
        		$setTop->quantity = $uptData['quantity'];
        		$setTop->save();
        	}
        }
        //exit;
        //if(!empty($results)){
	        //Top::where('created_at', '>=', Carbon::today())->delete();
	        
	        //Top::insert($results);
        //}
    }

    public function getChannelInfo($channel_id)
    {
        $root = 'https://www.googleapis.com/youtube/v3/channels?';
        $params = [
            'part' => 'brandingSettings',
            'id' => $channel_id,
            'key' => $this->api_key,
        ];
        $params = http_build_query($params);
        $data = file_get_contents($root.$params);
        if(empty(\GuzzleHttp\json_decode($data)->items)) return false;
        return \GuzzleHttp\json_decode($data)->items[0];
    }

    public function getProfileImage($channelID)
    {
        $client = new Client();
        $guzzleClient = new \GuzzleHttp\Client(array(
            'verify' => false,
        ));
        $client->setClient($guzzleClient);
        $crawler = $client->request('GET', 'https://www.youtube.com/channel/'.$channelID);
        return $crawler->filter('.channel-header-profile-image')->each(function ($node) {
            return $node->attr('src');
        })[0];
    }

    function requestAPI($url){
        try {
            $ch = curl_init();
            if (FALSE === $ch)
                throw new Exception('failed to initialize');

            curl_setopt_array(
                $ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 0,
                //todo remove
//            CURLOPT_SSL_VERIFYHOST=> 0,
//            CURLOPT_SSL_VERIFYPEER=> 0,
            ));
            $resp = curl_exec($ch);

            if (FALSE === $resp)
                throw new Exception(curl_error($ch), curl_errno($ch));
            curl_close($ch);
        } catch(Exception $e) {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
        }
        return $resp;
    }

    protected function getVideos($term, $max_res=100, $lang='us',$max_res_new=50)
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
              'maxResults' => $max_res_new,
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

    public function getInsertArray($items,$options=null)
    {
        $preferred = null;
        $max_res = 100;
        $lang = 'us';
        $pref_id = '';
        if($options != null){
            if(isset($options['preferred']) && !empty($options['preferred'])){
                $preferred = $options['preferred'];
                $url = parse_url($preferred);
                if(!isset($url['query'])){
                    return false;
                }
                parse_str($url['query'],$array);
                $pref_id = $array['v'];
            }
            if(isset($options['max_res'])){
                $max_res = $options['max_res'];
            }
            if(isset($options['lang'])){
                $lang = $options['lang'];
            }
        }
        $insert_array = [];

        foreach ($items as $term){
            $keyword = Keywords::where('keyword', $term)
                ->where('user_id', session('user_id'))
                ->where('channel_id', session('default_channel')->channelid)
                ->where('country',$options['country'])
                ->get()->first();
            $videos = $this->getVideos($term,$max_res,$lang);
            $i = 0;
            if (count($videos) > 0){
                $rank = 1;
                foreach($videos as $key => $video){
                    if($i>=5){
                        break;
                    }
                    $term = strtolower(urldecode($term));
                    if($pref_id == $video['id']['videoId'] &&
                    $video['snippet']['channelId'] == session('default_channel')->channelid){
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
                        if ($video['snippet']['channelId'] == session('default_channel')->channelid) {
                            $high = 0;
                            if($i == 0 && empty($pref_id)){
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
            else if($preferred != null){
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
        }
        return $insert_array;
    }
    
    public function startSearch(Request $laravel_request){
        if($laravel_request->input('term_1')){
            $group = $laravel_request->input('group');
            $country = $laravel_request->input('country');
            $langs = array("usa"=>"us", "uk"=>"gb", "denmark"=>"dk", "japan"=>"jp");
            $lang = "us";
            if ($laravel_request->input('lang') && $langs[$laravel_request->input('lang')]){
                $lang = $langs[$laravel_request->input('lang')];
            }
            /*$api_key = "AIzaSyB0aC7v_GYru8bY0_N4ARHi_sKmA_4ZAU8";
            $client_id = "116557245044-p6084545s459inpuuvk6nkgfun06lhff.apps.googleusercontent.com";
            $client_secret = "WSq-mKIX6tyhhgOpaenBXYoZ";*/
            $max_res = 100;
            $items = array();

            $item = preg_split('/[|\n]+/',$laravel_request->input('term_1'));
            $keywords_insert = [];
            $preferred = '';
            if(!empty($laravel_request->input('video_url'))){
                $preferred = $laravel_request->input('video_url');
            }

            foreach($item as $key => $video){

                if(empty($video)){
                    unset($item[$key]);
                    continue;
                }
                $video = trim((preg_replace('/\s\s+/', ' ', $video)));
                $kw = Keywords::where('keyword', $video)
                    ->where('user_id', session('user_id'))
                    ->where('channel_id', session('default_channel')->channelid)
                    ->where('country', $country)
                    ->get()->toArray();
                if(empty($kw)){
                    $url = parse_url($preferred);
                    if(!isset($url['query'])){
                        //$laravel_request->session()->flash('error', 'Invalid preferred url');
                        //return redirect('rankings');
                        $url['query'] = 'v';
                    }
                    parse_str($url['query'],$array);
                    $pref_id = $array['v'];
                    $keywords_insert[]=[
                        'keyword' => $video,
                        'user_id' => session('user_id'),
                        'channel_id' => session('default_channel')->channelid,
                        'preferred' => $pref_id,
                        'group' => $group,
                        'country' => $country,
                        'lang' => $lang,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                else {
                    $laravel_request->session()->flash('error','"'.$video.'" keyword already exists! Some keywords were already tracked for this country and channel, and have not been added');
                    return redirect('rankings');
                }
                $items[] = $video;
            }
            if (count($items) > 0 && !$laravel_request->input('_token')){
                exit();
            }
            $options = [
                'preferred' => $preferred,
                'max_res' => $max_res,
                'lang' => $lang,
                'group' => $group,
                'country' => $country,
            ];
            if(count($items)>100){
                $laravel_request->session()->flash('error','You can add only 100 keywords at a time');
                return redirect('rankings');
            }

            ini_set("max_execution_time","0");
            /*set limit 500 */
//            set_time_limit(0);
            /*end*/

            $user_id = $laravel_request->session()->get('user_id');
            $chanelLongId = $laravel_request->session()->get('default_channel')->channelid;

            $countKeywordForUser = Keywords::where('channel_id',$chanelLongId)->where('user_id', $user_id)->get()->count();
            if($countKeywordForUser > 1000){
                $laravel_request->session()->flash('error','You already have 1000 keyword, max size keyword for you 1000');
                return redirect('rankings');
            }

            Keywords::insert($keywords_insert);

            $insert_array = $this->getInsertArray($items,$options);
            if($insert_array != false){
                Search::insert($insert_array);
            }
            else {
               $laravel_request->session()->flash('error', 'Something went wrong');
            }

            /*set limit 500 */
//            $stepCount = count($keywords_insert)/100;
//            $start = 0;
//
//            for($i=0;$i<$stepCount;$i++){
//                $insert_array = $this->getInsertArray(array_slice($items,$start,100),$options);
//                if($insert_array != false){
//                    Search::insert($insert_array);
//                }
//                else {
//                    $laravel_request->session()->flash('error', 'Something went wrong');
//                }
//                $start += 100;
//            }
            /*end*/

        }
        return redirect('rankings');
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
    
    public function editProfile(Request $laravel_request){
        
        if($laravel_request->input('firstname')){
            $user_id = $laravel_request->session()->get('user_id');
            $firstname = $laravel_request->input('firstname');
            $lastname = $laravel_request->input('lastname');
            $email = $laravel_request->input('email');
            $password = $laravel_request->input('password');
            $confirm_password = $laravel_request->input('confirm_password');
            if(!empty($password) || !empty($confirm_password)){
                if($password == $confirm_password){
                    $new_password = $password;
                    
                    $updateData = array(
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'email' => $email,
                        'password' => $new_password,
                        'updated_at' => Carbon::now()
                    );
                }
                else {
                    dd("Passwords doesn't match");
                }
            } 
            else {
                $updateData = array(
                        'firstname' => $firstname,
                        'lastname' => $lastname,
                        'email' => $email,
                        'updated_at' => Carbon::now()
                    );
            }
            $res = DB::table('users')->where('id', $user_id)->update($updateData);
            if ($res){
                $result = DB::table('users')->where('id',$user_id)->first(); 
                session(['profile_data' => $result]);
                return redirect()->back();
            }            
        }
    }
    
    public function addChannel(Request $laravel_request){
        $user_id = $laravel_request->session()->get('user_id');
        if($laravel_request->input('channelid')){
            $channelid = $laravel_request->input('channelid');
            $channel_info = $this->getChannelInfo($channelid);
            if(!$channel_info) {
                $laravel_request->session()->flash('error', "Error: This is not a valid channel ID. A channel ID looks something like this 'UCIiXRFHI3aItnDaOI29Jodg'");
                return redirect()->back();
            }
            if(!empty(Channels::where('channelid',$channelid)->where('user_id',$laravel_request->session()->get('user_id'))->get()->first())){
                $laravel_request->session()->flash('error', "Channel id '".$channelid."' already exists");
                return redirect()->back();
            }
            $channelname = (isset($channel_info->brandingSettings->channel->title))?$channel_info->brandingSettings->channel->title:'no title';
            $channel_data = array(
                'channelname' =>  $channelname,
                'channelid' => $channelid,
                'user_id' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            );
            $res = DB::table('channels')->insert($channel_data);
            if($res){
                $channels = DB::table('channels')->where('user_id','=', $user_id)->get();
                session(['channels' => $channels]);
                if(!session('default_channel')){
                    $random_channel = DB::table('channels')->where('user_id', $user_id)->first();
                    if($random_channel){
                        session(['default_channel' => $random_channel]);
                        session(['prof_pic' => $this->getProfileImage($random_channel->channelid)]);
                    } 
                }
            }
            
        }
        return redirect()->back();
    }
    
    public function deleteChannel(Request $laravel_request){
        $user_id = $laravel_request->session()->get('user_id');
        if($laravel_request->input('channel_id')){
            $channel_id = $laravel_request->input('channel_id');
            $chanelLongId = $laravel_request->session()->get('default_channel')->channelid;

            $res = DB::table('channels')->where('id', $channel_id)->delete();
            if($res){
                $remKeys = DB::table('keywords')->where('channel_id', $chanelLongId)->where('user_id', $user_id);
                if(!empty($remKeys->get())){
                    foreach ($remKeys->get() as $remKey){
                        DB::table('search')->where('keyword', $remKey->id)->delete();
                    }
                    $remKeys->delete();
                }
            }

            $channels = DB::table('channels')->where('user_id','=', $user_id)->get();
            session(['channels' => $channels]);
            $random_channel = DB::table('channels')->where('user_id', $user_id)->first();
            if($random_channel != null){
                session(['default_channel' => $random_channel]);
            }
            else{
                $laravel_request->session()->forget('default_channel');
            }
            echo 'done';
        }
    }

    public function deleteKeyword(Request $request)
    {
        $user_id = $request->session()->get('user_id');
        $channel_id = $request->session()->get('default_channel')->channelid;
        $keyword = $request->input('keyword');
        $keyword_id = $request->input('keyword_id');
        if(is_array($keyword) && !empty($keyword)){

            foreach ($keyword as $keyw){
                $keyword_table = Keywords::where('id', $keyw);
                $kdel = $keyword_table->delete();
                $search = Search::where('keyword', $keyw)->delete();
                if(!$search || !$kdel){
                    echo 'error';
                    return;
                }
            }

            echo 'success';
            return;
        }else{
            $keyword_table = Keywords::where('id', $keyword_id)
                ->where('user_id', $user_id)
                ->where('channel_id', $channel_id);
            $kdel = $keyword_table->delete();
            $search = Search::where('keyword', $keyword_id)->delete();
            if($search && $kdel){
                echo 'success';
                return;
            }
            echo 'error';
            return;
        }
    }

    public function changeChannel(Request $laravel_request){ 

        if($laravel_request->input('channel_id')){
            $id = $laravel_request->input('channel_id');
            $selected_channel = DB::table('channels')->where('id', $id)->first();
            if($selected_channel){             
                session(['default_channel' => $selected_channel]);
                session(['prof_pic' => $this->getProfileImage($selected_channel->channelid)]);
            }
            echo 'done';
        }
    }
}