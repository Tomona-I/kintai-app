<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // カスタムログインレスポンスを登録
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
        
        // 会員登録後のリダイレクト先を打刻画面に設定
        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Http\Responses\RegisterResponse::class
        );

        // ログアウト後のリダイレクト先をログイン画面に設定
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LogoutResponse::class,
            \App\Http\Responses\LogoutResponse::class
        );

        // カスタムログインリクエストを登録
        $this->app->bind(
            \Laravel\Fortify\Http\Requests\LoginRequest::class,
            \App\Http\Requests\LoginRequest::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        // ログインビューの指定
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 会員登録ビューの指定
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // 一般ユーザー（role=0）のみログイン許可
        Fortify::authenticateUsing(function (Request $request) {
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && \Hash::check($request->password, $user->password) && $user->role === 0) {
                return $user;
            }
            
            // パスワードが間違っている、またはroleが違う場合
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ['ログイン情報が登録されていません'],
            ]);
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
