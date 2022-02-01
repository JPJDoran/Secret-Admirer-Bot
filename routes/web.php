<?php

use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

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

// Landing page
Route::get('/', [BotController::class, 'landingPage']);

// Post a tweet
Route::post('/tweet', [BotController::class, 'sendTweet']);

// Twitter OAuth callback route
Route::get('/oAuth', [BotController::class, 'twitterOAuth']);

// Destroy session effectively logging out the twitter user
Route::get('/logout', function () {
    Session::flush();

    return redirect('/');
});
