<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminAuthController extends Controller
{
    /**
     * 管理者ログイン画面を表示
     */
    public function showLoginForm()
    {
        return view('auth.admin.login');
    }
}
