<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 9-1: 自分が行った勤怠情報がすべて表示されている
     */
    public function test_all_own_attendance_records_are_displayed()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 当月に複数の勤怠レコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-02',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'status' => 0,
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-03',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'status' => 0,
        ]);

        // 勤怠一覧画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/list?year=2026&month=3');
        $response->assertStatus(200);

        // 各日付の出勤時刻が表示されていることを確認
        $response->assertSee('03/01', false);
        $response->assertSee('09:00', false);

        $response->assertSee('03/02', false);
        $response->assertSee('09:30', false);

        $response->assertSee('03/03', false);
        $response->assertSee('10:00', false);
    }

    /**
     * 9-2: 勤怠一覧画面に遷移した際に現在の月が表示される
     */
    public function test_current_month_is_displayed_in_attendance_list()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 現在時刻を2026年3月に設定
        Carbon::setTestNow(Carbon::parse('2026-03-09 09:00:00'));

        // パラメータなしで勤怠一覧画面にアクセス（現在月が表示されるはず）
        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);

        // 現在の年月「2026/3」が表示されていることを確認
        $response->assertSee('2026/3', false);

        Carbon::setTestNow();
    }

    /**
     * 9-3: 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_previous_month_is_displayed_when_prev_button_clicked()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 前月（2月）に勤怠レコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        // 3月の勤怠一覧を表示中に、前月（2月）パラメータでアクセス
        $response = $this->actingAs($user)->get('/attendance/list?year=2026&month=2');
        $response->assertStatus(200);

        // 「2026/2」が表示されていることを確認
        $response->assertSee('2026/2', false);

        // 前月（2月）に作成した勤怠の日付が表示されていることを確認
        $response->assertSee('02/10', false);
        $response->assertSee('09:00', false);
    }

    /**
     * 9-4: 「翌月」を押した時に表示月の翌月の情報が表示される
     */
    public function test_next_month_is_displayed_when_next_button_clicked()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 翌月（4月）に勤怠レコードを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'status' => 0,
        ]);

        // 3月の勤怠一覧を表示中に、翌月（4月）パラメータでアクセス
        $response = $this->actingAs($user)->get('/attendance/list?year=2026&month=4');
        $response->assertStatus(200);

        // 「2026/4」が表示されていることを確認
        $response->assertSee('2026/4', false);

        // 翌月（4月）に作成した勤怠の日付が表示されていることを確認
        $response->assertSee('04/10', false);
        $response->assertSee('09:30', false);
    }

    /**
     * 9-5: 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_detail_link_navigates_to_attendance_detail()
    {
        $user = User::create([
            'name' => 'テストユーザー',
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

        // 勤怠一覧画面にアクセス
        $response = $this->actingAs($user)->get('/attendance/list?year=2026&month=3');
        $response->assertStatus(200);

        // 詳細リンクが表示されていることを確認
        $response->assertSee('詳細', false);
        $response->assertSee('/attendance/detail/' . $attendance->id, false);

        // 詳細リンクをクリック（GETリクエスト）
        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 勤怠詳細画面に遷移し、出勤・退勤時刻が表示されていることを確認
        $response->assertSee('09:00', false);
        $response->assertSee('18:00', false);
    }
}
