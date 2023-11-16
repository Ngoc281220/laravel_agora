<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MeetingController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [MeetingController::class, 'meetingUser'])->name('home');
Route::get('/createMeeting', [MeetingController::class, 'createMeeting'])->name('createMeeting');

Route::get('/joinMeeting/{url}', [MeetingController::class, 'joinMeeting'])->name('joinMeeting');

Route::post('/createRoom', [MeetingController::class, 'createRoom'])->name('createRoom');

Route::get('/publisher', [MeetingController::class, 'joinPublisher'])->name('joinPublisher');

Route::get('/subscriber', [MeetingController::class, 'joinSubscriber'])->name('joinSubscriber.');


