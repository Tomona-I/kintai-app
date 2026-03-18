<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = auth()->user();

        // 管理者（role=1）は勤怠一覧画面へ
        if ($user->role === 1) {
            return redirect('/admin/attendance/list');
        }

        // 一般ユーザー（role=0）は打刻画面へ
        return redirect('/attendance');
    }
}
