<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Keywords;
use App\Search;
use App\Channels;
use App\User;
use App\Clickability;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClickabilityWeek extends Command
{

    /** @var string YouTube API key */
    protected $api_key = 'AIzaSyB0aC7v_GYru8bY0_N4ARHi_sKmA_4ZAU8';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clickability_week';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clickability week';

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

        ini_set('max_execution_time', 0);
        $getUsers = User::get()->toArray();
        foreach ($getUsers as $getUser){
            $getChanels = Channels::where('user_id',$getUser['id'])->get()->toArray();
            if(empty($getChanels)){
                continue;
            }
            foreach ($getChanels as $getChanel){
                $groupsQuery =
                    Keywords:://select('group')->
                    where('channel_id', $getChanel['channelid'])
                        ->where('user_id', $getUser['id'])
                        ->where('group', '!=', '')
                        ->distinct()
                        ->get()
                        ->toArray();
                $group = [];
                if (empty($groupsQuery)) {
                    continue;
                }
                foreach ($groupsQuery as $groupQuery) {
                    if ($groupQuery['group'] != "") {
                        $ob = json_decode($groupQuery['group']);
                        if ($ob === null) {
                            $group[$groupQuery['group']] = $groupQuery['group'];
                        } else {
                            $newGroupArr[] = $ob;
                        }
                    };
                };

                if (isset($newGroupArr) && !empty($newGroupArr)) {
                    foreach ($newGroupArr as $newGroupAr) {
                        foreach ($newGroupAr as $newGr) {
                            $group[] = $newGr;
                        }
                    }
                }
                $group = array_unique($group);

                foreach ($group as $ke => $grou) {
                    $grouKey = str_replace(' ', '____', $grou);
                    $groupsCharts[$grouKey] = $grou;
                }
                unset($group);

                $allTerms = Keywords::
                where('channel_id', $getChanel['channelid'])
                    ->where('group', '!=', '')
                    ->where('user_id', $getUser['id'])
                    ->get()
                    ->toArray();

                foreach ($allTerms as $keyCount => $allTerm) {
                    $AllTermGroupDecode = json_decode($allTerm['group']);
                    if ($AllTermGroupDecode === null) {
                        $getTerm[$allTerm['group']][$keyCount] = $allTerm;
                    } else {
                        foreach ($groupsCharts as $key => $groupsChart) {
                            if (in_array($groupsChart, $AllTermGroupDecode)) {
                                $getTerm[$key][$keyCount] = $allTerm;
                            }
                        }

                    }
                }

                $rankScore = [1 => 15, 2 => 12.5, 3 => 10.5, 4 => 8, 5 => 7.5, 6 => 6.5, 7 => 5.5, 8 => 5, 9 => 4.5, 10 => 3.5, 11 => 3, 12 => 3, 13 => 2.15, 14 => 2, 15 => 2, 16 => 1.5, 17 => 1.5, 18 => 1, 19 => 1, 20 => 1, 21 => 0.35, 22 => 0.3, 23 => 0.3, 24 => 0.25, 25 => 0.25, 26 => 0.25, 27 => 0.2, 28 => 0.2, 29 => 0.2, 30 => 0.15, 31 => 0.15, 32 => 0.15, 33 => 0.1, 34 => 0.1, 35 => 0.1, 36 => 0.1, 37 => 0.05, 38 => 0.05, 39 => 0.05, 40 => 0.05];
                $rankRes = [];
                foreach ($getTerm as $getTerKey => $getTer) {
                    foreach ($getTer as $getTerKeyChild => $getTr) {

                        $count = 1;

                        $getVideo = $this->getVideos($getTr['keyword'], $max_res = 40, $lang = 'us', 40);

                        foreach ($getVideo as $video_id => $getVid) {
                            if (!isset($rankRes[$getTerKey][$getVid['snippet']['channelId']]['score'])) {
                                $rankRes[$getTerKey][$getVid['snippet']['channelId']]['score'] = $rankScore[$count];
                                $rankRes[$getTerKey][$getVid['snippet']['channelId']]['title'] = $getVid['snippet']['channelTitle'];
                                $rankRes[$getTerKey][$getVid['snippet']['channelId']]['quantity'] = count($getTerm[$getTerKey]);
                            } else {
                                $rankRes[$getTerKey][$getVid['snippet']['channelId']]['score'] += $rankScore[$count];
                            }
                            if($count<40){
                                $count++;
                            }

                        }

                    }
                }
                $getMyChanelRes = [];

                foreach ($rankRes as $keyRes => $rankR) {
                    $stepCount = 0;
                    foreach ($rankR as $keyRankAll => $rankAll) {
                        $getMyChanelRes[$keyRes][$stepCount]['name'] = $rankR[$keyRankAll]['title'];
                        $getMyChanelRes[$keyRes][$stepCount]['y'] = $rankR[$keyRankAll]['score'] / $rankRes[$keyRes][$keyRankAll]['quantity'];
                        if ($keyRankAll == $getChanel['channelid']) {
                            $getMyChanelRes[$keyRes][$stepCount]['bold'] = 'clickPieBold';
                        } else {
                            $getMyChanelRes[$keyRes][$stepCount]['bold'] = 'clickPieNotBold';
                        }

                        $getMyChanelRes[$keyRes][$stepCount] = array_values(array_sort($getMyChanelRes[$keyRes], function ($value) {
                            return $value['y'];
                        }));
                        $getMyChanelRes[$keyRes] = array_reverse($getMyChanelRes[$keyRes][$stepCount]);
                        $stepCount++;
                    }
                }

                $getEndResult = [];
                $data_chart='';
                foreach ($getMyChanelRes as $groupKey => $getMyChanelR) {
                    $count = 1;
                    foreach ($getMyChanelR as $nKey => $getMyChanel) {
                        if ($getMyChanelRes[$groupKey][$nKey]['bold'] == "clickPieBold") {
                            $getEndResult[$groupKey][$count]['name'] = $getMyChanelRes[$groupKey][$nKey]['name'];
                            $getEndResult[$groupKey][$count]['y'] = $getMyChanelRes[$groupKey][$nKey]['y'];
                            $getEndResult[$groupKey][$count]['bold'] = $getMyChanelRes[$groupKey][$nKey]['bold'];
                            $getEndResult[$groupKey][$count]['otherCount'] = '';
                            $data_chart = $getMyChanelRes[$groupKey][$nKey]['y'];
                            $count++;
                        } else {
                            if ($nKey > 8) {
                                if (!isset($getEndResult[$groupKey][0]['y'])) {
                                    $getEndResult[$groupKey][0]['name'] = 'Other';
                                    $getEndResult[$groupKey][0]['y'] = $getMyChanel['y'];
                                    $getEndResult[$groupKey][0]['bold'] = $getMyChanelRes[$groupKey][$nKey]['bold'];
                                    $getEndResult[$groupKey][0]['otherCount'] = 1;
                                } else {
                                    $getEndResult[$groupKey][0]['name'] = 'Other';
                                    $getEndResult[$groupKey][0]['y'] += $getMyChanel['y'];
                                    $getEndResult[$groupKey][0]['otherCount']++;
                                }
                            } else {
                                $getEndResult[$groupKey][$count]['name'] = $getMyChanelRes[$groupKey][$nKey]['name'];
                                $getEndResult[$groupKey][$count]['y'] = $getMyChanelRes[$groupKey][$nKey]['y'];
                                $getEndResult[$groupKey][$count]['bold'] = $getMyChanelRes[$groupKey][$nKey]['bold'];
                                $getEndResult[$groupKey][$count]['otherCount'] = '';
                                $count++;
                            }
                        }

                    }
                    ksort($getEndResult[$groupKey]);
                }

                if (!isset($getEndResult[$groupKey][0])) {
                    $getEndResult[$groupKey][0] = $getEndResult[$groupKey][1];
                    unset($getEndResult[$groupKey][1]);
                }
                $insert=array('channel_id'=>$getChanel['channelid'],'user_id'=>$getUser['id'],'data'=>json_encode($getEndResult),'data_chart'=>$data_chart);
                $this->info("creating:  ".$getChanel['channelname']." for ".$getUser['firstname']);
                Clickability::insert($insert);
                sleep(10);
            }
            sleep(40);
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
