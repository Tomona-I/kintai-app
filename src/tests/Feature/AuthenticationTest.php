<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1-1: 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_registration_fails_when_name_is_empty()
    {
        // 名前以外のユーザー情報を入力
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('name');
        
        // 「お名前を入力してください」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('name'));
    }

    /**
     * 1-2: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_registration_fails_when_email_is_empty()
    {
        // メールアドレス以外のユーザー情報を入力
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('email');
        
        // 「メールアドレスを入力してください」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('email'));
    }

    /**
     * 1-3: パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_registration_fails_when_password_is_too_short()
    {
        // パスワードを8文字未満にしてユーザー情報を入力
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'pass123',  // 7文字
            'password_confirmation' => 'pass123',
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('password');
        
        // 「パスワードは8文字以上で入力してください」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('password'));
    }

    /**
     * 1-4: パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_registration_fails_when_password_confirmation_does_not_match()
    {
        // 確認用のパスワードとパスワードを一致させずにユーザー情報を入力
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',  // 異なるパスワード
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('password');
        
        // 「パスワードと一致しません」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('password'));
    }

    /**
     * 1-5: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_registration_fails_when_password_is_empty()
    {
        // パスワード以外のユーザー情報を入力
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('password');
        
        // 「パスワードを入力してください」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('password'));
    }

    /**
     * 1-6: フォームに内容が入力されていた場合、データが正常に保存される
     */
    public function test_user_can_register_successfully()
    {
        // ユーザー情報を入力
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // リダイレクトされることを確認
        $response->assertRedirect();
        
        // データベースに登録したユーザー情報が保存されることを確認
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }

    /**
     * 2-1: ログイン時にメールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_when_email_is_empty()
    {
        // ユーザーを登録
        User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);

        // メールアドレス以外のユーザー情報を入力してログイン
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('email');
        
        // 「メールアドレスを入力してください」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('email'));
    }

    /**
     * 2-2: ログイン時にパスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_when_password_is_empty()
    {
        // ユーザーを登録
        User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);

        // パスワード以外のユーザー情報を入力してログイン
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('password');
        
        // 「パスワードを入力してください」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('password'));
    }

    /**
     * 2-3: 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_when_credentials_do_not_match()
    {
        // ユーザーを登録
        User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);

        // 誤ったメールアドレスでログインを試みる
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('email');
        
        // 「ログイン情報が登録されていません」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('email'));
    }

    /**
     * 3-1: 管理者ログイン時にメールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_when_email_is_empty()
    {
        // 管理者ユーザーを登録
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => \Hash::make('password123'),
            'role' => 1,  // 管理者
        ]);

        // メールアドレス以外のユーザー情報を入力してログイン
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('email');
        
        // 「メールアドレスを入力してください」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('email'));
    }

    /**
     * 3-2: 管理者ログイン時にパスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_when_password_is_empty()
    {
        // 管理者ユーザーを登録
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => \Hash::make('password123'),
            'role' => 1,  // 管理者
        ]);

        // パスワード以外のユーザー情報を入力してログイン
        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('password');
        
        // 「パスワードを入力してください」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('password'));
    }

    /**
     * 3-3: 管理者ログイン時に登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_when_credentials_do_not_match()
    {
        // 管理者ユーザーを登録
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => \Hash::make('password123'),
            'role' => 1,  // 管理者
        ]);

        // 誤ったメールアドレスでログインを試みる
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        // バリデーションエラーが返ってくることを確認
        $response->assertSessionHasErrors('email');
        
        // 「ログイン情報が登録されていません」というメッセージが含まれることを確認
        $errors = session('errors');
        $this->assertTrue($errors->has('email'));
    }
}
