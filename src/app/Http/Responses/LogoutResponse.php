<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // 管理者ログアウトの場合は管理者ログイン画面へ
        if ($request->input('is_admin')) {
            return redirect('/admin/login');
        }

        // 一般ユーザーはログイン画面へ
        return redirect('/login');
    }
}
