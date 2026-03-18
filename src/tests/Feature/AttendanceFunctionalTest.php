<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

class AttendanceFunctionalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 6-1: 出勤ボタンが正しく機能する
     */
    public function test_clock_in_button_works_correctly()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        Carbon::setTestNow(Carbon::parse('2026-03-09 09:00:00'));

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外', false);
        $response->assertSee('出勤', false);

        $response = $this->actingAs($user)->post('/attendance/clock-in');
        $response->assertRedirect('/attendance');

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', '2026-03-09')
            ->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('09:00:00', $attendance->clock_in);
        $this->assertNull($attendance->clock_out);
        $this->assertEquals(0, $attendance->status);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中', false);
        $response->assertSee('退勤', false);
        $response->assertSee('休憩入', false);

        Carbon::setTestNow();
    }

    /**
     * 6-2: 出勤は一日一回のみできる（退勤済みの場合、出勤ボタンが表示されない）
     */
    public function test_clock_in_button_not_displayed_after_clock_out()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済', false);
        $response->assertSee('お疲れ様でした。', false);
        $response->assertDontSee('出勤', false);
    }

    /**
     * 6-3: 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_in_time_displayed_in_attendance_list()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        Carbon::setTestNow(Carbon::parse('2026-03-09 09:00:00'));

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->actingAs($user)->get('/attendance/list?year=2026&month=3');
        $response->assertStatus(200);
        $response->assertSee('09:00', false);
        $response->assertSee('03/09', false);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', '2026-03-09')
            ->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('09:00:00', $attendance->clock_in);

        Carbon::setTestNow();
    }

    /**
     * 7-1: 休憩ボタンが正しく機能する
     */
    public function test_break_start_button_works_correctly()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        Carbon::setTestNow(Carbon::parse('2026-03-09 09:00:00'));

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => 0,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中', false);
        $response->assertSee('休憩入', false);

        Carbon::setTestNow(Carbon::parse('2026-03-09 12:00:00'));
        $response = $this->actingAs($user)->post('/attendance/break-start');
        $response->assertRedirect('/attendance');

        $break = AttendanceBreak::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($break);
        $this->assertEquals('12:00:00', $break->start);
        $this->assertNull($break->end);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中', false);
        $response->assertSee('休憩戻', false);

        Carbon::setTestNow();
    }

    /**
     * 7-2: 休憩は1日に何回でもできる
     */
    public function test_break_can_be_taken_multiple_times()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        Carbon::setTestNow(Carbon::parse('2026-03-09 09:00:00'));

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => 0,
        ]);

        // 1回目の休憩入（12:00）→休憩戻（13:00）
        Carbon::setTestNow(Carbon::parse('2026-03-09 12:00:00'));
        $this->actingAs($user)->post('/attendance/break-start');
        Carbon::setTestNow(Carbon::parse('2026-03-09 13:00:00'));
        $this->actingAs($user)->post('/attendance/break-end');

        $firstBreak = AttendanceBreak::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($firstBreak);
        $this->assertEquals('12:00:00', $firstBreak->start);
        $this->assertEquals('13:00:00', $firstBreak->end);

        // 再度「休憩入」ボタンが表示されることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中', false);
        $response->assertSee('休憩入', false);

        Carbon::setTestNow();
    }

    /**
     * 7-3: 休憩戻ボタンが正しく機能する
     */
    public function test_break_end_button_works_correctly()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        Carbon::setTestNow(Carbon::parse('2026-03-09 09:00:00'));

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => 0,
        ]);

        // 休憩入（12:00）
        Carbon::setTestNow(Carbon::parse('2026-03-09 12:00:00'));
        $this->actingAs($user)->post('/attendance/break-start');

        $break = AttendanceBreak::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($break);
        $this->assertEquals('12:00:00', $break->start);
        $this->assertNull($break->end);

        // 「休憩中」ステータスと「休憩戻」ボタンが表示されることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中', false);
        $response->assertSee('休憩戻', false);

        // 休憩戻（13:00）
        Carbon::setTestNow(Carbon::parse('2026-03-09 13:00:00'));
        $response = $this->actingAs($user)->post('/attendance/break-end');
        $response->assertRedirect('/attendance');

        $break->refresh();
        $this->assertEquals('12:00:00', $break->start);
        $this->assertEquals('13:00:00', $break->end);

        // ステータスが「出勤中」に戻ることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中', false);
        $response->assertSee('退勤', false);
        $response->assertSee('休憩入', false);

        Carbon::setTestNow();
    }

    /**
     * 7-4: 休憩戻は一日に何回でもできる
     */
    public function test_break_end_can_be_done_multiple_times()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        Carbon::setTestNow(Carbon::parse('2026-03-09 09:00:00'));

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => 0,
        ]);

        // 1回目の休憩入（12:00）→休憩戻（13:00）
        Carbon::setTestNow(Carbon::parse('2026-03-09 12:00:00'));
        $this->actingAs($user)->post('/attendance/break-start');
        Carbon::setTestNow(Carbon::parse('2026-03-09 13:00:00'));
        $this->actingAs($user)->post('/attendance/break-end');

        // 2回目の休憩入（15:00）
        Carbon::setTestNow(Carbon::parse('2026-03-09 15:00:00'));
        $this->actingAs($user)->post('/attendance/break-start');

        // 「休憩戻」ボタンが表示されることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩戻', false);

        Carbon::setTestNow();
    }

    /**
     * 7-5: 休憩時刻が勤怠一覧画面で確認できる
     */
    public function test_break_time_displayed_in_attendance_list()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        Carbon::setTestNow(Carbon::parse('2026-03-09 09:00:00'));

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => 0,
        ]);

        // 休憩入（12:00）→休憩戻（13:00）
        Carbon::setTestNow(Carbon::parse('2026-03-09 12:00:00'));
        $this->actingAs($user)->post('/attendance/break-start');
        Carbon::setTestNow(Carbon::parse('2026-03-09 13:00:00'));
        $this->actingAs($user)->post('/attendance/break-end');

        // 勤怠一覧画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/list?year=2026&month=3');
        $response->assertStatus(200);

        // 休憩時間「01:00」が表示されていることを確認
        $response->assertSee('01:00', false);

        // 日付「03/09」が表示されていることを確認
        $response->assertSee('03/09', false);

        Carbon::setTestNow();
    }

    /**
     * 8-1: 退勤ボタンが正しく機能する
     */
    public function test_clock_out_button_works_correctly()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        Carbon::setTestNow(Carbon::parse('2026-03-09 09:00:00'));

        // 今日の勤怠レコードを作成（出勤中の状態）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'status' => 0,
        ]);

        // 「出勤中」ステータスと「退勤」ボタンが表示されていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中', false);
        $response->assertSee('退勤', false);

        // 退勤ボタンをクリック（18:00）
        Carbon::setTestNow(Carbon::parse('2026-03-09 18:00:00'));
        $response = $this->actingAs($user)->post('/attendance/clock-out');
        $response->assertRedirect('/attendance');

        // データベースに退勤時刻が記録されたことを確認
        $attendance->refresh();
        $this->assertEquals('18:00:00', $attendance->clock_out);

        // 再度画面にアクセスして、ステータスが「退勤済」になっていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済', false);
        $response->assertSee('お疲れ様でした。', false);

        Carbon::setTestNow();
    }

    /**
     * 8-2: 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_clock_out_time_displayed_in_attendance_list()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        Carbon::setTestNow(Carbon::parse('2026-03-09 09:00:00'));

        // 出勤処理
        $this->actingAs($user)->post('/attendance/clock-in');

        // 退勤処理（18:00）
        Carbon::setTestNow(Carbon::parse('2026-03-09 18:00:00'));
        $this->actingAs($user)->post('/attendance/clock-out');

        // 勤怠一覧画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/list?year=2026&month=3');
        $response->assertStatus(200);

        // 退勤時刻「18:00」が表示されていることを確認
        $response->assertSee('18:00', false);

        // 日付「03/09」が表示されていることを確認
        $response->assertSee('03/09', false);

        // データベースに退勤時刻が正しく記録されていることを確認
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', '2026-03-09')
            ->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('18:00:00', $attendance->clock_out);

        Carbon::setTestNow();
    }
}
