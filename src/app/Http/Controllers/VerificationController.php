<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class VerificationController extends Controller
{
    // 認証待ち画面
    public function show(Request $request)
    {
        return view('auth.verify');
    }

    // 認証リンク検証
    public function verify(Request $request, $id, $hash)
    {
        $user = Auth::user();
        if (!$user || $user->getKey() != $id) {
            abort(403);
        }
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }
        return redirect('/attendance')->with('verified', true);
    }

    // 認証メール再送信
    public function resend(Request $request)
    {
        $user = Auth::user();
        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
        return back()->with('resent', true);
    }
}
