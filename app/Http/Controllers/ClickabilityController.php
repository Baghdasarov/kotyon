<?php

namespace App\Http\Controllers;

use App\Keywords;
use App\Top;
use Illuminate\Http\Request;
use DB;
use DateTime;
use Carbon\Carbon;
use Goutte\Client;
use App\Search as Search;

class ClickabilityController extends Controller
{
    public function index(Request $laravel_request){
        if($laravel_request->session()->has('user_id')){
            $channel_session = session('default_channel');

            $groupsQuery =
                Keywords:://select('group')->
                    where('channel_id', $channel_session->channelid)
                    ->where('group', '!=' , '')
                    ->distinct()
                    ->get()
                    ->toArray();
            $group=[];

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
                $grouKey = str_replace(' ','_',$grou);
                $groupsCharts[$grouKey]=$grou;
            }
            unset($group);
            if($laravel_request->ajax()){


                return response()->json($array);
            }

            return view('clickability',compact('groupsCharts'));
        }else{
            return redirect('login');
        }
    }
}
