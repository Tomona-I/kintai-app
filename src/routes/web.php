<?php

use Illuminate\Support\Facades\Route;

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

// Fortifyが自動的に以下のルートを登録します:
// GET  /login - ログインフォーム
// POST /login - ログイン処理
// GET  /register - 会員登録フォーム
// POST /register - 会員登録処理
// POST /logout - ログアウト

// 一般ユーザー用ルート（認証必須）
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', function () {
        return view('attendance');
    });
    Route::get('/attendance/list', function () {
        return view('attendance-list');
    });
    Route::get('/attendance/detail/{id}', function ($id) {
        return view('attendance-detail');
    });
    Route::get('/stamp_correction_request/list', function () {
        if (Auth::check() && Auth::user()->role === 1) {
            return view('admin.application-list');
        }
        return view('application-list');
    });
});

// 管理者用ルート（認証必須）
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/attendance/list', function () {
        return view('admin.attendance-list');
    });
    Route::get('/attendance/{id}', function ($id) {
        return view('admin.attendance-detail');
    });
    Route::get('/staff/list', function () {
        return view('admin.staff-list');
    });
    Route::get('/attendance/staff/{id}', function ($id) {
        return view('admin.staff-attendance');
    });
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', function ($id) {
        return view('admin.approval');
    });
});