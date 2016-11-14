<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cookie;
use DB;
use Carbon;
class LoginController extends Controller
{
    public function index(Request $request){
        
//         $users = DB::select('select * from users');
//         dd($users);
        return view('login');
    }

    public function getProfileImage($channelID)
    {
        $client = new \Goutte\Client();
        $guzzleClient = new \GuzzleHttp\Client(array(
            'verify' => false,
        ));
        $client->setClient($guzzleClient);
        $crawler = $client->request('GET', 'https://www.youtube.com/channel/'.$channelID);
        return $crawler->filter('.channel-header-profile-image')->each(function ($node) {
            return $node->attr('src');
        })[0];
    }

    public function login(Request $request){
        if($request->input('submit')){
            $email = $request->input('email');
            $password = $request->input('password');
            $result = DB::table('users')->where('email',$email)->where('password', $password)->first();
            if(!empty($result)){
                session(['user_id' =>$result->id]);
                $channels = DB::table('channels')->where('user_id', $result->id)->get();
                session(['channels' => $channels]);
                $random_channel = DB::table('channels')->where('user_id', $result->id)->first();
                if($random_channel){
                    session(['default_channel' => $random_channel]);
                    session(['prof_pic' => $this->getProfileImage($random_channel->channelid)]);
                }
                session(['profile_data' => $result]);

                return redirect('dashboard');
            }
            else {
                print_r('wrong username of password');
                return view('login');
            }
        }
        
        
        
//        $pass = "@novelconceptdata2000";
//        //$pass = "1111";
//        $loggenIn = false;
//        if($request->input('password')){
//            
//            $password = $request->input('password');
//            if($password == $pass){
//                Cookie::queue(Cookie::make('pass', $password, time()+3600*24*365));
//                $loggenIn = true;
//            }            
//            if(!$loggenIn){
//                return view('login');
//            }
//            else {
//                return redirect('home');
//            }
//        }        
    }

    public function register(Request $request){

        if($request->input('submit')){
            $firstname = $request->input('firstname');
            $lastname = $request->input('lastname');
            $email = $request->input('email');
            $code = $request->input('code');
            $email_check = DB::table('users')->where('email', $email)->first();
            if($email_check){                
                dd('Email already exists');                
            }

            $ch = curl_init();
            curl_setopt_array(
                $ch, array(
                CURLOPT_URL => "http://coderiders.am/key.txt",
                CURLOPT_RETURNTRANSFER => true,
            ));
            $resp = curl_exec($ch);
            curl_close($ch);

            $resp = explode(',',$resp);
            if(!in_array($code,$resp)){
                print_r('wrong your code');
                return view('register');
            }

            $password = $request->input('password');
            $data = array(
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'password' => $password,
                'created_at' => Carbon\Carbon::now(),
                'updated_at' => Carbon\Carbon::now(),
            );
            $result = DB::table('users')->insert([$data]);
            if($result){
                return redirect('');
            }
        }
        return view('register');
    }
    
    public function logout(Request $request){        
        $request->session()->forget('user_id');
        if($request->session()->has('key_error')){
            $request->session()->forget('key_error');
        }
        if($request->session()->has('default_channel')){
            $request->session()->forget('default_channel');
        }
        return redirect('');
    }
}
