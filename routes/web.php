<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

});

Route::get('channels', 'ChannelController@index')->name('channel.index');
Route::delete('channels/{channel}/delete', 'ChannelController@destroy')->name('channel.delete');
Route::get('channels/create', 'ChannelController@create')->name('channel.create');
Route::post('channels/create', 'ChannelController@store');