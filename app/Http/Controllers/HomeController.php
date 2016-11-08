<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Input;
use DateTime;
class HomeController extends Controller
{
    public function index(){
        return view('home');
    }
    
    public function youtube(Request $laravel_request){
        $videodata = array();
        $domain = "novelconcept.org";
        $dir = "/ytb/";
        $api_key = "AIzaSyB0aC7v_GYru8bY0_N4ARHi_sKmA_4ZAU8";
        $client_id = "116557245044-p6084545s459inpuuvk6nkgfun06lhff.apps.googleusercontent.com";
        $client_secret = "WSq-mKIX6tyhhgOpaenBXYoZ";
        $max_res = 100;
        $timeout = 3;

        $at = "";
        $rt = "";

        if ($laravel_request->cookie('access_token')) {
            $at = $laravel_request->cookie('access_token');
        }

        if ($laravel_request->cookie('refresh_token')) {
            $rt = $laravel_request->cookie('refresh_token');
        }

        if (Input::has("code")){
            $code =  Input::get('code');
            $url = 'https://accounts.google.com/o/oauth2/token';
            $data = array(
                        'code' => $code,
                        'client_id' => $client_id, 
                        'client_secret' => $client_secret, 
                        'redirect_uri' => 'http://secground.ru/ytb/youtube.php', 
                        'grant_type' => 'authorization_code'
                    );
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                ),
            );
            $context  = $this->stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if ($result === false) {
                echo "Error with getting token.";
            }
            $result = json_decode($result, true);
            //var_dump($result);
            $at = $result['access_token'];
            $ei = $result['expires_in'];
            if (isset($result['refresh_token'])) {
                $rt = $result['refresh_token'];
                setcookie ("refresh_token", $rt, time()+3600*24*100);
            }
            setcookie ("access_token", $at, time()+$ei);
        }

        if (!$at && !$rt){
            //header('Location: https://accounts.google.com/o/oauth2/auth?client_id='.$client_id.'&redirect_uri=http://'.$domain.$dir.'youtube.php&scope=https://www.googleapis.com/auth/youtube.force-ssl https://www.googleapis.com/auth/youtubepartner&response_type=code&access_type=offline');
        }

        function makeRequest($url, $data, $method="GET", $headers){
            if (!$url) return;
            $data = split("&", $data);
            $cdata = array();
            foreach ($data as $key => $value){
                $cdata[$key] = $value;
            }
            $header = "";
            foreach ($headers as $key => $value){
                $header .= $key.": ".$value."\r\n";
            }

            $options = array(
                'http' => array(
                    'header'  => $header,
                    'method'  => $method,
                    'content' => http_build_query($cdata)
                ),
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            if ($result === false) {
                echo "Error with getting token.";
            }
            $result = json_decode($result);
            return $result;
        }

        function requestAPI($url){
            $ch = curl_init();
            curl_setopt_array(
                $ch, array( 
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true
            ));

            $resp = curl_exec($ch);          
            curl_close($ch);
            
            return $resp;
        }

        $items = array();
        $max = $max_res;
        if ($laravel_request->input('max_res_0')){
            $max = $laravel_request->input('max_res_0');
        }
        for ($i=1; $i<=50; $i++){
            //echo 'term_'.$i.": ".$_POST['term_'.$i];
            if (!$laravel_request->input('term_'.$i) || !$laravel_request->input('sterm_'.$i)){
                continue;
            }
            $item = [$laravel_request->input('term_'.$i), $laravel_request->input('sterm_'.$i), $max];
            array_push($items, $item);
        }

        $langs = array("usa"=>"us", "uk"=>"gb", "denmark"=>"dk", "japan"=>"jp");
        $lang = "us";
        $loc = "";
        $radius = "100km";
        if ($laravel_request->input('location')){
            $loc = $laravel_request->input('location');
        }
        if ($laravel_request->input('lang') && $langs[$laravel_request->input('lang')]){
            $lang = $langs[$laravel_request->input('lang')];
        }
        if (count($items) > 0 && !$laravel_request->input('token')){
            exit();
        }
        $token = $laravel_request->input('token') ? $laravel_request->input('token') : "";        
        $prec = 0;
        $curStep = 0;
        $count = count($items) * $max_res;
        $results = array();

        foreach ($items as $key => $value){
            $tres = array();
            $term = $value[0];
            $term = urlencode($term);
            $sterm = $value[1];
            if (isset($value[2])){
                $max_res = $value[2];
            }
            $t = array();
            $t['term1'] = $term;
            $t['term2'] = $sterm;
            $iter = 10;
            if ($max_res <= 50){
                $iter = $max_res;
            }
            $nextPage = "null";
            $vids = array();
            for ($i=$iter; $i<=$max_res; $i+=$iter){
                //if ($i+$iter > $max_res) $iter = $iter - ($i - $max_res);
                $request = 'https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults='.$iter.'&order=relevance&q='.$term.'&regionCode='.$lang.'&type=video&key='.$api_key;
                if ($loc){
                    $request .= "&location=".urlencode($loc)."&locationRadius=".$radius;
                }
                if ($nextPage != "null") {
                    $request .= "&pageToken=".$nextPage;
                }
                //echo $request."<br><br>";
                $res = requestAPI($request);
                
                $res = json_decode($res, true);

                $res = $this->arrayCastRecursive($res);

                //var_dump($res);
                if (isset($res['nextPageToken'])){
                    $nextPage = $res['nextPageToken'];                    
                }
                
                $res = $res['items'];                  
                //var_dump($res);
                foreach ($res as $rkey => $rval){
                    $vid = $rval['id']['videoId'];
                    array_push($vids, $vid);
                }
            }
            $t['vids'] = $vids;
            array_push($results, $t);
        }

        if (count($results) > 0){
            
            $token = $laravel_request->input('token');
            
            $fname = "tmp/tmp_".$token.".csv";
            if (!$token) {
                exit();                
            }
            $files = glob('./tmp/*');           
            foreach($files as $file){
                if(\is_file($file)){
                    unlink($file);
                }                    
            }
            $file = fopen($fname, "w") or die("Unable to open file!");
            
            $d = ";";
            $txt = "Exact Match Keyword".$d."Keyword".$d."Search Rank".$d."Video Name".$d."Video URL".$d."Upload Date".$d."Video Age(days)".$d."Views".$d."Likes".$d."Dislikes".$d."Comments".$d."Total Watch Time (in minutes)".$d."Length (in minutes)".$d."Subscribes Driven".$d."Shares".$d."Max Video Resolution".$d."Title length".$d."Times term1 in title".$d."Times term2 in title".$d."Description length".$d."Times term1 in description".$d."Times term2 in description".$d."Non-YouTube links in Description".$d."YouTube links in Description".$d."Num. of tags".$d."Times term1 in tags".$d."Times term2 in tags".$d."Num. of subs".$d."Words in subs".$d."Times term1 in subs".$d."Times term2 in subs".$d."Term1 in channel name".$d."Term2 in channel name".$d."Total channel videos".$d."Total channel subscribers".$d."Channel age (days)";
            $txt .= '\n';
            fwrite($file, $txt);
            fclose($file);
            chmod($fname, 0777);
            echo json_encode($results);
            exit();
        }

        if ($laravel_request->input('vid') && $laravel_request->input('term1') && $laravel_request->input('term2')) {
            $vid = $laravel_request->input('vid');
            $term = $laravel_request->input('term1');
            $sterm = $laravel_request->input('term2');
            $vreq = 'https://www.googleapis.com/youtube/v3/videos?&key='.$api_key.'&id='.$vid.'&part=contentDetails,statistics,snippet';
            $vres = requestAPI($vreq);
            $vres = json_decode($vres, true);
            $vres = $this->arrayCastRecursive($vres);
            if (isset($vres['items'])){
                $vres = $vres['items'];
            }
            else {
                $vres = array();
            }
            if (!isset($vres['snippet'])){
                $vres = $vres[0];
            }
            $date = $vres['snippet']['publishedAt'];
            $date = explode("T", $date);
            $date = $date[0];
            //var_dump($date);
            $year = explode("-", $date);
            $year = $year[0];
            $month = explode("-", $date);
            if (count($month) >= 2){
                $month = $month[1];
            }
            else{
                $month = $month[0];
            }
            $day = explode("-", $date);
            if (count($day) >= 3){
                $day = $day[2];
            }
            else{
                $day = $day[1];
            }
            $up_date = $year."/".$day."/".$month;
            $date = new DateTime($date);
            $now = date("Y-m-d H:i:s");
            $now = new DateTime($now);
            $dif = date_diff($now, $date);
            $dif = $dif->format("%a");
            $chid = $vres['snippet']['channelId'];
            $subUrl = 'http://downsub.com/?url=https://www.youtube.com/watch?v='.$vid;
            //var_dump($subUrl);
            $sres = requestAPI($subUrl);
            $sres = json_decode($sres, true);
            $sres = $this->arrayCastRecursive($sres);
            //var_dump($sres['lang']);
            $surl = "";
            $nost = 0;
            $subText = "";
            //var_dump($sres);
            if (isset($sres['lang'])) {
                $nost = count($sres['lang']);
                $sres['lang'] = $sres['lang'][0];
            }
            //var_dump($sres['n']);
            if (isset($sres['lang']['url'])){
                $surl = $sres['lang']['url'];
            }
            else {
                $sres['lang'] = array('url' => '');
            }
            if (isset($sres['lang']) && isset($sres['lang']['n'])){
                if ($sres['lang']['n'] != "en"){
                    $surl = "";
                    if (isset($sres['autotrans'])) {
                        $sres = $sres['autotrans'];
                    }
                    else{
                        $sres = array();
                    }
                    if (!$sres || !isset($sres)){
                        $sres = array();
                    }
                    //var_dump($sres);
                    $nost = count($sres);
                    foreach ($sres as $skey => $sval){
                        if ($sval['n'] == "en"){
                            if (strpos($sval['url'], "https://") !== false) $surl = $sval['url'];
                            else $surl += $sval['url'];
                        }
                    }
                }
            }
            //var_dump($surl);
            if (isset($sres['autotrans'])) {
                $sres = $sres['autotrans'];
            }
            if ($surl) {
                $sres = requestAPI($surl);
                $sres = html_entity_decode($sres);
                //var_dump($surl);
                //var_dump($sres);
                $subText = $sres;
                /*$sres = simplexml_load_string($sres);
                $subText = "";
                $sres = $sres['text'];
                var_dump($sres);
                if (!$sres || !isset($sres)) $sres = array();
                foreach ($sres as $skey => $svalue){
                  $subText .= $svalue;
                }*/
            } else {
                $subText = "";
            }
            //var_dump($nost);
            //var_dump(strlen($subText));
            $creq = 'https://www.googleapis.com/youtube/v3/channels?&key='.$api_key.'&id='.$chid.'&part=contentDetails,statistics,snippet';
            $cres = requestAPI($creq);
            $cres = json_decode($cres, true);
            $cres = $this->arrayCastRecursive($cres);
            $cres = $cres['items'];
            if (!isset($cres['snippet'])){
                $cres = $cres[0];
            }
            $ctitle = $cres['snippet']['title'];
            $cdesc = $cres['snippet']['description'];
            if (isset($cres['snippet']) && isset($cres['snippet']['publishedAt'])){
                $cage = $cres['snippet']['publishedAt'];
                $t = new DateTime($cage);
                $t2 = new DateTime();
                $dDiff = $t->diff($t2);
                $cage = $dDiff->days;
            }
            $cstats = $cres['statistics'];
            $subscr = $cstats['subscriberCount'];
            $videoCount = $cstats['videoCount'];
            $title = $vres['snippet']['title'];
            $desc = $vres['snippet']['description'];
            $ctitle = $vres['snippet']['channelTitle'];
            $details = $vres['contentDetails'];
            $def = $details['definition'];
            $duration = $details['duration'];
            //var_dump($duration);
            $delim = explode("H", $duration);
            $hour = str_replace("PT", "", $delim[0]);
            $duration = str_replace("PT", "", $duration);
            if (strpos($hour, "S") !== false){
                $hour = 0;
            }
            if (strpos($duration, "H") !== false){
                $duration = substr($duration, strpos($duration, "H")+1);
            }
            //var_dump($duration);
            if (strpos($duration, "M") !== false) {
                $minute = explode("M", $duration);
                $minute = $minute[0];
            } else{
                $minute = 0;
            }
            if (strpos($duration, "M") !== false){
                $duration = substr($duration, strpos($duration, "M")+1);
            }
            $seconds = explode("S", $duration);
            $seconds = $seconds[0];
            //var_dump($seconds);
            $seconds = $seconds / 60;
            $mdur = $hour * 60 + $minute + round($seconds, 1);
            echo $vid." "."\n";
            $stats = $vres['statistics'];
            $viewCount = $stats['viewCount'];
            $lcount = isset($stats['likeCount']) ? $stats['likeCount'] : 0;
            $dlcount = isset($stats['dislikeCount']) ? $stats['dislikeCount'] : 0;
            if (isset($stats['commentCount'])){
                $commCount = $stats['commentCount'];
            }
            else{
                $commCount = 0;
            }
            $ind = "";
            if (isset($vres['snippet']['thumbnails']['maxres'])){
                $ind = "maxres";
            }
            else if (isset($vres['snippet']['thumbnails']['standard'])){
                $ind = "standard";
            }
            else if (isset($vres['snippet']['thumbnails']['high'])){
                $ind = "high";
            }
            else if (isset($vres['snippet']['thumbnails']['medium'])){
                $ind = "medium";
            }
            else if (isset($vres['snippet']['thumbnails']['default'])){
                $ind = "default";
            }
            $maxres = $vres['snippet']['thumbnails'][$ind]['width']."x".$vres['snippet']['thumbnails'][$ind]['height'];
            $mpres = $vres['snippet']['thumbnails'][$ind]['width'];
            if ($def == "hd") {
                if ($mpres <= 720){
                    $mpres = 720;
                }
                else{
                    $mpres = 1080;
                }
            } else {
                $mpres = 480;
            }
            //var_dump($mpres);
            $term_original = urldecode($term);
            $sterm_original = urldecode($sterm);
            $term = strtolower(urldecode($term));
            $sterm = strtolower(urldecode($sterm));
            $sterm2 = $sterm;
            //echo $term." '".strtolower($ctitle)."'\n";
            if (!$sterm){
                $sterm2 = "null_string_second_term_check";
            }
            if ($ctitle) {
                $t1icn = substr_count(strtolower($ctitle), $term);
                if ($t1icn > 0){
                    $t1icn = "Yes";
                }
                else{
                    $t1icn = "No";
                }
                $t2icn = substr_count(strtolower($ctitle), $sterm2);
                if ($t2icn > 0){
                    $t2icn = "Yes";
                }
                else{
                    $t2icn = "No";
                }
            } else {
                $t1icn = 0;
                $t2icn = 0;
            }
            if ($subText) {
                $t1ist = substr_count(strtolower($subText), $term);
                $t2ist = substr_count(strtolower($subText), $sterm2);
            } else {
                $t1ist = 0;
                $t2ist = 0;
            }
            if ($subText) {
                $wist = explode(" ", $subText);
                $wist = count($wist);
            } else{
                $wist = 0;
            }
            if ($wist == 0){
                $wist = "N/A";
            }
            if ($desc) {
                $t1idc = substr_count(strtolower($desc), $term);
                $t2idc = substr_count(strtolower($desc), $sterm2);
            } else {
                $t1idc = 0;
                $t2idc = 0;
            }
            //var_dump($t1icn);
            //var_dump($subText);
            //var_dump($wist);
            //var_dump($ost);
            //echo "\n\n";
            $desclen = strlen($desc);
            $t1ivn = substr_count(strtolower($title), $term);
            $t2ivn = substr_count(strtolower($title), $sterm2);
            $vnamelen = strlen($title);
            $rank = $laravel_request->input('rank');
            $vurl = "https://www.youtube.com/watch?v=".$vid;

            $ylinks = 0;
            $nylinks = 0;
            $regx = '(http[\w:\/\.?=%-]*)';
            preg_match_all($regx, $desc, $match);
            $match = $match[0];
            //var_dump($match);
            foreach ($match as $dkey => $dvalue){
                if ((strpos($dvalue, "https://www.youtube.com") === false) && (strpos($dvalue, "http://www.youtube.com") === false)) {
                    //$nylinks .= $dvalue.",";
                    $nylinks++;
                } else {
                    //$ylinks .= $dvalue.",";
                    $ylinks++;
                }
                //$k = (strrpos($dvalue, "https://www.youtube.com") === false) && (strrpos($dvalue, "http://www.youtube.com") === false);
                //echo $dvalue." ".$k."\n";
            }
            //echo $ylinks." ".$nylinks."\n";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_URL, 'https://www.youtube.com/watch?v='.$vid);
            $response = curl_exec($ch);
            //var_dump($response);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $header = explode('\r\n', $header);
            preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
            $cookies = array();
            foreach($matches[1] as $item) {
                parse_str($item, $cookie);
                $cookies = array_merge($cookies, $cookie);
            }
            $cookie = "";
            foreach ($cookies as $key => $value) {
                $cookie .= $key."=".$value.";";
            }
            $t = substr($response, $header_size);
            $regx = '/\'XSRF_TOKEN\'\: "(\w*=)"/';
            preg_match($regx, $t, $match);
            //echo 'https://www.youtube.com/watch?v='.$vid."\n";
            //var_dump($response);
            $tkn = $match[1];
            $tkn = urlencode($tkn);
            $shares = 0;
            $subDriven = 0;
            $t1it = 0;
            $t2it = 0;
            if($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_URL, 'https://www.youtube.com/insight_ajax?action_get_statistics_and_data=1&v='.$vid);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Cookie: '.$cookie));
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, "session_token=".$tkn);
                //echo $vid." ".$tkn;
                $out = curl_exec($curl);
                curl_close($curl);
                preg_match_all("/\<span class=\"menu-metric-value\"\>(.*)\<\/span\>/", $out, $mtc);
                $m = 0;
                if (isset($mtc[1])){
                    $mtc = $mtc[1];
                }
                if (isset($mtc[0])) {
                    $mtc = $mtc[0];
                    $mtc = explode(":", $mtc);
                    //var_dump($mtc);
                    $m = (int) $mtc[0];
                    $s = (int) $mtc[1];
                    $s = $s / 60;
                    $s = round($s, 1);
                    //echo "s:".$s." m:".$m."\n";
                    $m += $s;
                } else $m = 0;
                if ($m == 0){
                    $m = $viewCount * $mdur;
                }
                else{
                    $m = $viewCount * $m;
                }
                $average = $m;
                //echo $vid." ".$m."\n";

                preg_match_all("/\<div class=\"bragbar-metric\"\>(\d*)\<\/div\>/", $out, $mtc);
                $mtc = $mtc[0];
                foreach ($mtc as $key => $value) {
                    $value = preg_match("(\d+)", $value, $m);
                    $value = $m[0];
                    $mtc[$key] = $value;
                }
                if (count($mtc) > 0) {
                    $subDriven = $mtc[0];
                    if (isset($mtc[1])){
                        $shares = $mtc[1];
                    }                        
                }
                //echo "shares:".$shares.", driven_by:".$subDriven."\n";
                //echo "driven_by:".$subDriven."\n";
            }

            $regx = '/\<meta property\=\"og\:video\:tag\" content\=\"(.*)\"\>/';
            preg_match_all($regx, $t, $match);
            if (count($match) > 0){
                $match = $match[0];
            }
            else{
                $match = array();
            }
            //var_dump($match);
            foreach ($match as $key => $value){
                preg_match('/content="(.*)"\>/', $value, $m);
                $match[$key] = $m[1];
                if ($term){
                    if (strpos($m[1], $term) !== false){
                        $t1it++;
                    }
                }
                if ($sterm){
                    if (strpos($m[1], $sterm) !== false){
                        $t2it++;
                    }
                }
            }
            $numOfTags = count($match);
            //echo $numOfTags." ,".$t1it." ".$t2it."\n";
            //print_r(count($match));

            /*
            if ($ylinks[strlen($ylinks)-1] == ","){
              $ylinks = substr($ylinks, 0, strlen($ylinks)-1);
            }
            if ($nylinks[strlen($nylinks)-1] == ","){
            $nylinks = substr($nylinks, 0, strlen($nylinks)-1);
            }
            */

            /*
            $res = $term.','.$sterm.','.$rank.',"'.$title.'",'.$vurl.','.$up_date.','.$dif.','.$viewCount.','.$lcount.','.$dlcount.','.$commCount.','.$mdur.','.$subDriven.','.$shares.','.$mpres.','
            .$vnamelen.','.$t1ivn.','.$t2ivn.','.$desclen.','.$t1idc.','.$t2idc.','.$nylinks.','.$ylinks.','.$numOfTags.','.$t1it.','.$t2it.','.$nost.','.$wist.','.$t1ist.','.$t2ist.','.$videoCount.','.$subscr;
            */
            $d = ";";
            $res = $term.$d.$sterm.$d.$rank.$d.'"'.$title.'"'.$d.$vurl.$d.$up_date.$d.$dif.$d.$viewCount.$d.$lcount.$d.$dlcount.$d.$commCount.$d.$average.$d.$mdur.$d.$subDriven.$d.$shares.$d.$mpres.$d
            .$vnamelen.$d.$t1ivn.$d.$t2ivn.$d.$desclen.$d.$t1idc.$d.$t2idc.$d.$nylinks.$d.$ylinks.$d.$numOfTags.$d.$t1it.$d.$t2it.$d.$nost.$d.$wist.$d.$t1ist.$d.$t2ist.$d.$t1icn.$d.$t2icn.$d.$videoCount.$d.$subscr.$d.$cage;
            //$res .= "\n";
//            $video_data = array(
//                    'search term' => $term,
//                    'secondary term' => $sterm,
//                    'search rank' => $rank,
//                    'video name' => $title,
//                    'video url' => $vurl,
//                    'upload update' => $up_date,
//            );
//            
            $fname = "tmp/tmp_".$token.".csv";
            //echo $fname;
            $content = file_get_contents("./".$fname);
            $ct = explode("\n", $content);
            $ncont = "";
            $rank = (int) $rank;
            for ($i=0; $i<count($ct); $i++) {
                $value = $ct[$i];
                $rnk = explode($d, $value);
                //var_dump($rnk);
                if (isset($rnk[2])){
                    $rnk = (int) $rnk[2];
                }
                else{
                    $rnk = 0;
                }
                $res = str_replace("\n", "", $res);
                $value = str_replace("\n", "", $value);
                if ($rank > $rnk && isset($ct[$i+1]) && $rank < (int) $ct[$i+1]){
                    $value = $res."\n".$value;
                }
                else if ($rank > $rnk && !isset($ct[$i+1])){
                    $value = $value."\n".$res;
                }
                $ncont .= $value."\n";
            }
            //var_dump($ct);
            //$content .= $res;
            $content = str_replace("\\n", "\n", $ncont);
            while (strpos($content, "\n\n") !== false){
                $content = str_replace("\n\n", "\n", $content);
            }
            file_put_contents($fname, $content);

            sleep($timeout);
            echo "1";
            exit();
        }
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
