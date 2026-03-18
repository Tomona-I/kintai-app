<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 10-1: 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_attendance_detail_shows_login_user_name()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 勤怠レコードを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 名前欄にログインユーザーの名前が表示されていることを確認
        $response->assertSee('山田太郎', false);
    }

    /**
     * 10-2: 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_attendance_detail_shows_correct_date()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test2@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 勤怠レコードを作成（日付: 2026-03-09）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 日付欄に選択した日付が表示されていることを確認（Y年 n月j日 形式）
        $response->assertSee('2026年', false);
        $response->assertSee('3月9日', false);
    }

    /**
     * 10-3: 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_shows_correct_clock_times()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test3@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 出勤09:00、退勤18:00で勤怠レコードを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 出勤・退勤欄に打刻した時刻が表示されていることを確認
        $response->assertSee('09:00', false);
        $response->assertSee('18:00', false);
    }

    /**
     * 10-4: 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_shows_correct_break_times()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test4@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 勤怠レコードを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        // 休憩レコードを作成（12:00〜13:00）
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'start' => '12:00:00',
            'end' => '13:00:00',
        ]);

        // 勤怠詳細画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 休憩欄に打刻した時刻が表示されていることを確認
        $response->assertSee('12:00', false);
        $response->assertSee('13:00', false);
    }
}
