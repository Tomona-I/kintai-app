<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

class AttendanceDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 4-1: 勤怠打刻画面に現在の日付情報がUIと同じ形式で表示される
     */
    public function test_attendance_page_displays_current_date_in_correct_format()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        
        // メール認証済みにする
        $user->markEmailAsVerified();

        // 現在の日時を取得（Carbon::nowを使用）
        Carbon::setTestNow(Carbon::parse('2026-03-09 14:30:00'));
        $now = Carbon::now();
        $expectedDate = $now->isoFormat('YYYY年M月D日(ddd)');
        $expectedTime = $now->format('H:i');

        // ユーザーとしてログイン
        $response = $this->actingAs($user)->get('/attendance');

        // ステータスコード200が返ってくることを確認
        $response->assertStatus(200);

        // レスポンスに現在の日付が含まれることを確認
        $response->assertSee($expectedDate, false);
        
        // レスポンスに現在の時刻が含まれることを確認（分単位で比較）
        $response->assertSee($expectedTime, false);
        
        // テスト後、Carbonの時刻をリセット
        Carbon::setTestNow();
    }

    /**
     * 5-1: 勤務外の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_before_work_correctly()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        
        // メール認証済みにする
        $user->markEmailAsVerified();

        // 勤怠レコードを作成しない（勤務外の状態）
        
        // ユーザーとしてログイン
        $response = $this->actingAs($user)->get('/attendance');

        // ステータスコード200が返ってくることを確認
        $response->assertStatus(200);

        // レスポンスに「勤務外」というステータスが含まれることを確認
        $response->assertSee('勤務外', false);
        
        // 「出勤」ボタンが表示されることを確認
        $response->assertSee('出勤', false);
    }

    /**
     * 5-2: 出勤中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_working_correctly()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        
        // メール認証済みにする
        $user->markEmailAsVerified();

        // 今日の勤怠レコードを作成（出勤中の状態）
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null, // まだ退勤していない
            'status' => 0,
        ]);
        
        // ユーザーとしてログイン
        $response = $this->actingAs($user)->get('/attendance');

        // ステータスコード200が返ってくることを確認
        $response->assertStatus(200);

        // レスポンスに「出勤中」というステータスが含まれることを確認
        $response->assertSee('出勤中', false);
        
        // 「退勤」ボタンと「休憩入」ボタンが表示されることを確認
        $response->assertSee('退勤', false);
        $response->assertSee('休憩入', false);
    }

    /**
     * 5-3: 休憩中の場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_on_break_correctly()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        
        // メール認証済みにする
        $user->markEmailAsVerified();

        // 今日の勤怠レコードを作成（出勤中の状態）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null, // まだ退勤していない
            'status' => 0,
        ]);
        
        // 休憩レコードを作成（休憩中の状態）
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'start' => '12:00:00',
            'end' => null, // まだ休憩戻していない
        ]);
        
        // ユーザーとしてログイン
        $response = $this->actingAs($user)->get('/attendance');

        // ステータスコード200が返ってくることを確認
        $response->assertStatus(200);

        // レスポンスに「休憩中」というステータスが含まれることを確認
        $response->assertSee('休憩中', false);
        
        // 「休憩戻」ボタンが表示されることを確認
        $response->assertSee('休憩戻', false);
    }

    /**
     * 5-4: 退勤済みの場合、勤怠ステータスが正しく表示される
     */
    public function test_status_displays_after_work_correctly()
    {
        // テストユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        
        // メール認証済みにする
        $user->markEmailAsVerified();

        // 今日の勤怠レコードを作成（退勤済みの状態）
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00', // 退勤済み
            'status' => 0,
        ]);
        
        // ユーザーとしてログイン
        $response = $this->actingAs($user)->get('/attendance');

        // ステータスコード200が返ってくることを確認
        $response->assertStatus(200);

        // レスポンスに「退勤済」というステータスが含まれることを確認
        $response->assertSee('退勤済', false);
        
        // 「お疲れ様でした。」メッセージが表示されることを確認
        $response->assertSee('お疲れ様でした。', false);
    }
}
