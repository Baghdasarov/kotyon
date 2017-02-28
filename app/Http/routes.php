<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'DashboardController@index');
Route::get('dashboard', 'DashboardController@index');
Route::get('rankings/{csv?}', 'DashboardController@rankings');
Route::get('analysis/csv', 'AnalysisController@csvDownload');
Route::get('rankingsJson', 'DashboardController@rankingsJson');
Route::get('clickabilityJson', 'DashboardController@clickabilityJson');
Route::get('analysisJson', 'AnalysisController@analysisJson');
Route::get('rankingsJsonOption', 'DashboardController@rankingsJsonOption');
Route::post('startSearch', 'DashboardController@startSearch');
Route::get('startSearch', 'DashboardController@startSearch');
Route::post('getVideos', 'AnalysisController@getVideos');
Route::post('changeKeywordGroup', 'DashboardController@changeKeywordGroup');
Route::get('clickability', 'DashboardController@clickability');
Route::get('analysis', 'AnalysisController@index');

Route::post('login', 'LoginController@login');
Route::get('login', 'LoginController@index');
Route::get('logout', 'LoginController@logout');
Route::get('register', 'LoginController@register');
Route::post('register', 'LoginController@register');
Route::get('home', 'HomeController@index');
Route::post('youtube', 'DashboardController@youtube');
Route::post('editProfile', 'DashboardController@editProfile');
Route::post('addChannel', 'DashboardController@addChannel');
Route::post('deleteChannel', 'DashboardController@deleteChannel');
Route::post('changeChannel', 'DashboardController@changeChannel');
Route::get('data/{data}', 'HomeController@data');


Route::post('deleteKeyword', 'DashboardController@deleteKeyword');
Route::post('setPreferred', 'DashboardController@setPreferred');
