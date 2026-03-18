<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 16-1: 会員登録後、認証メールが送信される
     */
    public function test_verification_email_is_sent_after_registration()
    {
        \Illuminate\Support\Facades\Notification::fake();

        $response = $this->post('/register', [
            'name'                  => 'テストユーザー',
            'email'                 => 'new_verify@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録後にリダイレクトされること
        $response->assertRedirect();

        // ユーザーがDBに作成されていること
        $user = User::where('email', 'new_verify@example.com')->first();
        $this->assertNotNull($user);

        // メール認証通知が送信されていること
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }

    /**
     * 16-2: メール認証誘導画面で「認証はこちらから」を押すとメール認証サイトに遷移する
     */
    public function test_verification_notice_page_has_link_to_mail_verification_site()
    {
        $user = User::create([
            'name'     => '未認証ユーザー',
            'email'    => 'unverified@example.com',
            'password' => \Hash::make('password123'),
            'role'     => 0,
        ]);
        // メール未認証のままにする

        // メール認証誘導画面にアクセス
        $response = $this->actingAs($user)->get('/email/verify');
        $response->assertStatus(200);

        // 「認証はこちらから」リンクが表示されていること
        $response->assertSee('認証はこちらから', false);

        // MailHogへのリンクが含まれていること
        $response->assertSee('http://localhost:8025', false);
    }

    /**
     * 16-3: メール認証を完了すると勤怠登録画面に遷移する
     */
    public function test_user_is_redirected_to_attendance_after_email_verification()
    {
        $user = User::create([
            'name'     => 'メール認証ユーザー',
            'email'    => 'verify_complete@example.com',
            'password' => \Hash::make('password123'),
            'role'     => 0,
        ]);
        // メール未認証のまま

        // 署名付き認証URLを生成
        $verificationUrl = \Illuminate\Support\Facades\URL::signedRoute(
            'verification.verify',
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        // 認証リンクにアクセス（ログイン状態）
        $response = $this->actingAs($user)->get($verificationUrl);

        // 勤怠登録画面（/attendance）にリダイレクトされること
        $response->assertRedirect('/attendance');

        // メール認証が完了していること
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
