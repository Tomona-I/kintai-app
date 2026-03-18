<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 11-1: 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_validation_error_when_clock_in_is_after_clock_out()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/admin/attendance/update/' . $attendance->id, [
                'attendance_id' => $attendance->id,
                'clock_in'      => '19:00',
                'clock_out'     => '18:00',
                'notes'         => 'テスト',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['clock_out']);
        $response->assertJsonFragment([
            '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * 11-2: 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_validation_error_when_break_start_is_after_clock_out()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test2@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/admin/attendance/update/' . $attendance->id, [
                'attendance_id'   => $attendance->id,
                'clock_in'        => '09:00',
                'clock_out'       => '18:00',
                'breaks'          => [
                    ['start' => '19:00', 'end' => '20:00'],
                ],
                'notes'           => 'テスト',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['breaks.0.start']);
        $response->assertJsonFragment([
            '休憩時間が不適切な値です'
        ]);
    }

    /**
     * 11-3: 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_validation_error_when_break_end_is_after_clock_out()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test3@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/admin/attendance/update/' . $attendance->id, [
                'attendance_id'   => $attendance->id,
                'clock_in'        => '09:00',
                'clock_out'       => '18:00',
                'breaks'          => [
                    ['start' => '10:00', 'end' => '19:00'],
                ],
                'notes'           => 'テスト',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['breaks.0.end']);
        $response->assertJsonFragment([
            '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * 11-4: 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_validation_error_when_notes_is_empty()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'test4@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/admin/attendance/update/' . $attendance->id, [
                'attendance_id'   => $attendance->id,
                'clock_in'        => '09:00',
                'clock_out'       => '18:00',
                'notes'           => '',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['notes']);
        $response->assertJsonFragment([
            '備考を記入してください'
        ]);
    }

    /**
     * 11-5: 修正申請処理が実行される（管理者の承認画面・申請一覧に表示）
     */
    public function test_attendance_update_request_is_listed_for_admin()
    {
        // 一般ユーザー作成
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'user@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        // 管理者ユーザー作成
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        // 勤怠レコードを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        // 一般ユーザーで修正申請（退勤時刻を変更）
        $response = $this->actingAs($user)
            ->withHeaders(['Referer' => '/attendance/detail/' . $attendance->id])
            ->post('/attendance', [
                'attendance_id' => $attendance->id,
                'clock_in' => '09:00',
                'clock_out' => '19:00',
                'notes' => '修正申請',
            ]);
        $response->assertRedirect();

        // DB上でstatus=1（申請中）になっていること
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 1,
            'clock_out' => '2026-03-09 19:00', 
            'notes' => '修正申請',
        ]);

        // 管理者で承認画面（申請一覧）に表示されること
        $adminResponse = $this->actingAs($admin)->get('/admin/attendance/list?year=2026&month=3&day=9');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('山田太郎', false);
        $adminResponse->assertSee('2026/03/09', false);
        $adminResponse->assertSee('19:00', false);
    }

    /**
     * 11-6: 「承認待ち」にログインユーザーが行った申請がすべて表示されている
     */
    public function test_application_list_shows_all_pending_requests_for_user()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'user2@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 2件の勤怠レコードを申請状態で作成
        $attendance1 = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 1,
            'notes' => '申請1',
        ]);
        $attendance2 = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 1,
            'notes' => '申請2',
        ]);

        // 申請一覧画面にアクセス
        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        // 2件とも表示されていること
        $response->assertSee('申請1', false);
        $response->assertSee('申請2', false);
        $response->assertSee('2026/03/09', false);
        $response->assertSee('2026/03/10', false);
    }

    /**
     * 11-6: 承認済みに管理者が承認した修正申請がすべて表示されている
     */
    public function test_approved_requests_are_listed_in_approved_section()
    {
        // 一般ユーザー作成
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'user2@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        // 管理者ユーザー作成
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin2@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        // 勤怠レコードを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 0,
        ]);

        // 一般ユーザーで修正申請（退勤時刻を変更）
        $this->actingAs($user)
            ->withHeaders(['Referer' => '/attendance/detail/' . $attendance->id])
            ->post('/attendance', [
                'attendance_id' => $attendance->id,
                'clock_in' => '09:00',
                'clock_out' => '19:00',
                'notes' => '承認テスト',
            ]);

        // 管理者が承認（status=2に更新）
        $attendance->refresh();
        $attendance->status = 2;
        $attendance->save();

        // 申請一覧画面で「承認済み」欄に表示されていること
        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('承認済み', false);
        $response->assertSee('2026/03/10', false);
        $response->assertSee('承認テスト', false);
    }

    /**
     * 11-7: 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
     */
    public function test_application_list_detail_link_navigates_to_attendance_detail()
    {
        $user = User::create([
            'name' => '山田太郎',
            'email' => 'user3@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 勤怠レコードを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-11',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 1, // 申請中
        ]);

        // 申請一覧画面を取得
        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        // 詳細リンクのURLが正しいことを確認
        $detailUrl = route('attendance.detail', ['id' => $attendance->id], false);
        $response->assertSee($detailUrl, false);

        // 詳細画面に遷移できることを確認
        $detailResponse = $this->actingAs($user)->get($detailUrl);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('勤怠詳細', false);
        $detailResponse->assertSee('山田太郎', false);
    }

    /**
     * 12-1: その日になされた全ユーザーの勤怠情報が正確に確認できる（管理者）
     */
    public function test_admin_can_see_all_users_attendance_for_the_day()
    {
        // 管理者ユーザー作成
        $admin = \App\Models\User::create([
            'name' => '管理者',
            'email' => 'admin3@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        // 一般ユーザー2名作成
        $user1 = \App\Models\User::create([
            'name' => '山田太郎',
            'email' => 'user4@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user1->markEmailAsVerified();
        $user2 = \App\Models\User::create([
            'name' => '佐藤花子',
            'email' => 'user5@example.com',
            'password' => \Hash::make('password456'),
            'role' => 0,
        ]);
        $user2->markEmailAsVerified();

        // 2名分の勤怠レコードを同じ日付で作成
        $date = '2026-03-12';
        \App\Models\Attendance::create([
            'user_id' => $user1->id,
            'date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 2,
        ]);
        \App\Models\Attendance::create([
            'user_id' => $user2->id,
            'date' => $date,
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'status' => 2,
        ]);

        // 管理者で勤怠一覧画面にアクセス
        $response = $this->actingAs($admin)->get("/admin/attendance/list?year=2026&month=3&day=12");
        $response->assertStatus(200);
        $response->assertSee('山田太郎', false);
        $response->assertSee('佐藤花子', false);
        $response->assertSee('2026/03/12', false);
        $response->assertSee('09:00', false);
        $response->assertSee('18:00', false);
        $response->assertSee('08:30', false);
        $response->assertSee('17:30', false);
    }

    /**
     * 12-2: 勤怠一覧画面にその日の日付が表示されている（管理者）
     */
    public function test_admin_attendance_list_shows_current_date()
    {
        $admin = \App\Models\User::create([
            'name' => '管理者',
            'email' => 'admin4@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        // 現在日付を取得
        $year = 2026;
        $month = 3;
        $day = 14;

        // 管理者で勤怠一覧画面にアクセス
        $response = $this->actingAs($admin)->get("/admin/attendance/list?year={$year}&month={$month}&day={$day}");
        $response->assertStatus(200);
        $response->assertSee("{$year}年{$month}月{$day}日", false);
        $response->assertSee(sprintf("%04d/%02d/%02d", $year, $month, $day), false);
    }

    /**
     * 12-3: 「前日」ボタンで前日勤怠情報が表示される（管理者）
     */
    public function test_admin_attendance_list_prev_day_navigation()
    {
        $admin = \App\Models\User::create([
            'name' => '管理者',
            'email' => 'admin5@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        // 前日と当日の勤怠データを作成
        $user = \App\Models\User::create([
            'name' => '山田太郎',
            'email' => 'user6@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        $today = '2026-03-14';
        $prev = '2026-03-13';
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 2,
        ]);
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $prev,
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'status' => 2,
        ]);

        // 「前日」ボタンのリンク先を取得
        $response = $this->actingAs($admin)->get('/admin/attendance/list?year=2026&month=3&day=14');
        $response->assertStatus(200);
        $response->assertSee('2026/03/14', false);
        $response->assertSee('09:00', false);
        $response->assertSee('18:00', false);
        $this->assertStringContainsString('/admin/attendance/list?year=2026&amp;month=3&amp;day=13', $response->getContent());

        // 前日画面に遷移
        $prevResponse = $this->actingAs($admin)->get('/admin/attendance/list?year=2026&month=3&day=13');
        $prevResponse->assertStatus(200);
        $prevResponse->assertSee('2026/03/13', false);
        $prevResponse->assertSee('08:30', false);
        $prevResponse->assertSee('17:30', false);
    }

    /**
     * 12-4: 「翌日」を押下した時に次の日の勤怠情報が表示される（管理者）
     */
    public function test_admin_attendance_list_next_day_navigation()
    {
        $admin = \App\Models\User::create([
            'name' => '管理者',
            'email' => 'admin6@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        // 翌日と当日の勤怠データを作成
        $user = \App\Models\User::create([
            'name' => '山田太郎',
            'email' => 'user7@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();
        $today = '2026-03-14';
        $next = '2026-03-15';
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 2,
        ]);
        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => $next,
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'status' => 2,
        ]);

        // 「翌日」ボタンのリンク先を取得
        $response = $this->actingAs($admin)->get('/admin/attendance/list?year=2026&month=3&day=14');
        $response->assertStatus(200);
        $response->assertSee('2026/03/14', false);
        $response->assertSee('09:00', false);
        $response->assertSee('18:00', false);
        $this->assertStringContainsString('/admin/attendance/list?year=2026&amp;month=3&amp;day=15', $response->getContent());

        // 翌日画面に遷移
        $nextResponse = $this->actingAs($admin)->get('/admin/attendance/list?year=2026&month=3&day=15');
        $nextResponse->assertStatus(200);
        $nextResponse->assertSee('2026/03/15', false);
        $nextResponse->assertSee('08:30', false);
        $nextResponse->assertSee('17:30', false);
    }

    /**
     * 13-1: 管理者用勤怠詳細画面の内容が選択した情報と一致する
     */
    public function test_admin_attendance_detail_shows_selected_data()
    {
        $admin = \App\Models\User::create([
            'name' => '管理者',
            'email' => 'admin7@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = \App\Models\User::create([
            'name' => '山田太郎',
            'email' => 'user8@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 2,
        ]);

        // 管理者で勤怠詳細画面にアクセス
        $response = $this->actingAs($admin)->get('/admin/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細', false);
        $response->assertSee('山田太郎', false);
        $response->assertSee('2026年', false);
        $response->assertSee('3月16日', false);
        $response->assertSee('09:00', false);
        $response->assertSee('18:00', false);
    }

    /**
     * 13-2: 管理者が勤怠詳細画面で出勤時間を退勤時間より後に設定した場合、エラーメッセージが表示される
     */
    public function test_admin_attendance_detail_validation_error_when_clock_in_is_after_clock_out()
    {
        $admin = \App\Models\User::create([
            'name' => '管理者',
            'email' => 'admin8@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = \App\Models\User::create([
            'name' => '山田花子',
            'email' => 'user9@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-17',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 2,
        ]);

        // 管理者で勤怠詳細画面から出勤時間を退勤時間より後にして保存
        $response = $this->actingAs($admin)
            ->postJson('/admin/attendance/update/' . $attendance->id, [
                'attendance_id' => $attendance->id,
                'clock_in'      => '19:00',
                'clock_out'     => '18:00',
                'notes'         => 'テスト',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['clock_out']);
        $response->assertJsonFragment([
            '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * 13-3: 管理者が勤怠詳細画面で休憩開始時間を退勤時間より後に設定した場合、エラーメッセージが表示される
     */
    public function test_admin_attendance_detail_validation_error_when_break_start_is_after_clock_out()
    {
        $admin = \App\Models\User::create([
            'name' => '管理者',
            'email' => 'admin9@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = \App\Models\User::create([
            'name' => '佐藤花子',
            'email' => 'user10@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-18',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 2,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/admin/attendance/update/' . $attendance->id, [
                'attendance_id' => $attendance->id,
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'breaks'        => [
                    ['start' => '19:00', 'end' => '20:00'],
                ],
                'notes'         => 'テスト',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['breaks.0.start']);
        $response->assertJsonFragment([
            '休憩時間が不適切な値です'
        ]);
    }

    /**
     * 13-4: 管理者が勤怠詳細画面で休憩終了時間を退勤時間より後に設定した場合、エラーメッセージが表示される
     */
    public function test_admin_attendance_detail_validation_error_when_break_end_is_after_clock_out()
    {
        $admin = \App\Models\User::create([
            'name' => '管理者',
            'email' => 'admin10@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = \App\Models\User::create([
            'name' => '鈴木一郎',
            'email' => 'user11@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-19',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 2,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/admin/attendance/update/' . $attendance->id, [
                'attendance_id' => $attendance->id,
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'breaks'        => [
                    ['start' => '10:00', 'end' => '19:00'],
                ],
                'notes'         => 'テスト',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['breaks.0.end']);
        $response->assertJsonFragment([
            '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /**
     * 13-5: 管理者が勤怠詳細画面で備考欄を未入力のまま保存した場合、エラーメッセージが表示される
     */
    public function test_admin_attendance_detail_validation_error_when_notes_is_empty()
    {
        $admin = \App\Models\User::create([
            'name' => '管理者',
            'email' => 'admin11@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = \App\Models\User::create([
            'name' => '田中次郎',
            'email' => 'user12@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-20',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status' => 2,
        ]);

        $response = $this->actingAs($admin)
            ->postJson('/admin/attendance/update/' . $attendance->id, [
                'attendance_id' => $attendance->id,
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'notes'         => '',
            ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['notes']);
        $response->assertJsonFragment([
            '備考を記入してください'
        ]);
    }

    /**
     * 14-1: 管理者がスタッフ一覧画面ですべての一般ユーザーの氏名・メールアドレスを確認できる
     */
    public function test_admin_can_see_all_general_users_name_and_email_on_staff_list()
    {
        $admin = \App\Models\User::create([
            'name' => '管理者',
            'email' => 'admin12@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $users = [
            \App\Models\User::create([
                'name' => '一般ユーザーA',
                'email' => 'userA@example.com',
                'password' => \Hash::make('password123'),
                'role' => 0,
            ]),
            \App\Models\User::create([
                'name' => '一般ユーザーB',
                'email' => 'userB@example.com',
                'password' => \Hash::make('password123'),
                'role' => 0,
            ]),
            \App\Models\User::create([
                'name' => '一般ユーザーC',
                'email' => 'userC@example.com',
                'password' => \Hash::make('password123'),
                'role' => 0,
            ]),
        ];
        foreach ($users as $user) { $user->markEmailAsVerified(); }

        $response = $this->actingAs($admin)->get('/admin/staff/list');
        $response->assertStatus(200);
        foreach ($users as $user) {
            $response->assertSee($user->name, false);
            $response->assertSee($user->email, false);
        }
    }

    /**
     * 14-2: 管理者がスタッフ勤怠一覧画面で選択したユーザーの勤怠情報が正しく表示される
     */
    public function test_admin_can_see_selected_user_attendance_on_staff_attendance_list()
    {
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin14@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = User::create([
            'name' => '一般ユーザーD',
            'email' => 'userD@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 勤怠データを2日分作成
        $attendance1 = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'notes' => '通常勤務',
            'status' => 0,
        ]);
        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-11',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'notes' => '遅刻',
            'status' => 0,
        ]);

        // 管理者でスタッフ勤怠一覧画面にアクセス
        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?year=2026&month=3');
        $response->assertStatus(200);
        // 勤怠情報が正しく表示されているか
        $response->assertSee('03/10', false);
        $response->assertSee('09:00', false);
        $response->assertSee('18:00', false);
        $response->assertSee('03/11', false);
        $response->assertSee('10:00', false);
        $response->assertSee('19:00', false);
    }

    /**
     * 14-3: 管理者がスタッフ勤怠一覧画面で「前月」を押下したとき前月の情報が表示される
     */
    public function test_admin_staff_attendance_list_prev_month_navigation()
    {
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin143@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = User::create([
            'name' => '一般ユーザーE',
            'email' => 'userE@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 前月（2026年2月）の勤怠データを作成
        $prevMonthAttendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-15',
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'notes' => '前月勤務',
            'status' => 0,
        ]);

        // 当月（2026年3月）の勤怠データを作成
        $currMonthAttendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'notes' => '当月勤務',
            'status' => 0,
        ]);

        // 当月（2026年3月）のスタッフ勤怠一覧画面にアクセス
        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?year=2026&month=3');
        $response->assertStatus(200);
        // 前月ボタンが表示されていることを確認
        $response->assertSee('前月', false);

        // 前月（2026年2月）のURLに遷移
        $prevResponse = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?year=2026&month=2');
        $prevResponse->assertStatus(200);
        // 前月（2月）の勤怠情報が表示されていること
        $prevResponse->assertSee('02/15', false);
        $prevResponse->assertSee('08:30', false);
        $prevResponse->assertSee('17:30', false);
        // 当月（3月）のデータは表示されていないこと
        $prevResponse->assertDontSee('03/01', false);
    }

    /**
     * 14-4: 管理者がスタッフ勤怠一覧画面で「翌月」を押下したとき翌月の情報が表示される
     */
    public function test_admin_staff_attendance_list_next_month_navigation()
    {
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin144@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = User::create([
            'name' => '一般ユーザーF',
            'email' => 'userF@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 当月（2026年3月）の勤怠データを作成
        $currMonthAttendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-05',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'notes' => '当月勤務',
            'status' => 0,
        ]);

        // 翌月（2026年4月）の勤怠データを作成
        $nextMonthAttendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-04-10',
            'clock_in' => '08:00:00',
            'clock_out' => '17:00:00',
            'notes' => '翌月勤務',
            'status' => 0,
        ]);

        // 当月（2026年3月）のスタッフ勤怠一覧画面にアクセス
        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?year=2026&month=3');
        $response->assertStatus(200);
        // 翌月ボタンが表示されていることを確認
        $response->assertSee('翌月', false);

        // 翌月（2026年4月）のURLに遷移
        $nextResponse = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?year=2026&month=4');
        $nextResponse->assertStatus(200);
        // 翌月（4月）の勤怠情報が表示されていること
        $nextResponse->assertSee('04/10', false);
        $nextResponse->assertSee('08:00', false);
        $nextResponse->assertSee('17:00', false);
        // 当月（3月）のデータは表示されていないこと
        $nextResponse->assertDontSee('03/05', false);
    }

    /**
     * 14-5: 管理者がスタッフ勤怠一覧画面で「詳細」を押下するとその日の勤怠詳細画面に遷移する
     */
    public function test_admin_staff_attendance_detail_link_navigates_to_detail_page()
    {
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin145@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = User::create([
            'name' => '一般ユーザーG',
            'email' => 'userG@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-20',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'notes' => '詳細テスト',
            'status' => 0,
        ]);

        // スタッフ勤怠一覧画面にアクセスして詳細リンクを確認
        $listResponse = $this->actingAs($admin)->get('/admin/attendance/staff/' . $user->id . '?year=2026&month=3');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('詳細', false);

        // 詳細リンク先（勤怠詳細画面）に遷移
        $detailResponse = $this->actingAs($admin)->get('/admin/attendance/detail/' . $attendance->id);
        $detailResponse->assertStatus(200);
        // 勤怠詳細画面に正しい情報が表示されていること
        $detailResponse->assertSee('勤怠詳細', false);
        $detailResponse->assertSee($user->name, false);
        $detailResponse->assertSee('09:00', false);
        $detailResponse->assertSee('18:00', false);
    }

    /**
     * 15-1: 管理者が申請一覧画面で全ユーザーの承認待ち修正申請を確認できる
     */
    public function test_admin_can_see_all_pending_requests_on_application_list()
    {
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin151@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user1 = User::create([
            'name' => '申請ユーザーA',
            'email' => 'apply_a@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user1->markEmailAsVerified();

        $user2 = User::create([
            'name' => '申請ユーザーB',
            'email' => 'apply_b@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user2->markEmailAsVerified();

        // 承認待ち（status=1）の勤怠を各ユーザー分作成
        Attendance::create([
            'user_id' => $user1->id,
            'date' => '2026-03-10',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'notes' => 'ユーザーAの申請',
            'status' => 1,
        ]);
        Attendance::create([
            'user_id' => $user2->id,
            'date' => '2026-03-11',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'notes' => 'ユーザーBの申請',
            'status' => 1,
        ]);

        // 承認済み（status=2）のデータも作成（承認待ちタブに表示されないことを確認用）
        Attendance::create([
            'user_id' => $user1->id,
            'date' => '2026-03-05',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'notes' => '承認済み申請',
            'status' => 2,
        ]);

        $response = $this->actingAs($admin)->get('/admin/application-list');
        $response->assertStatus(200);

        // 承認待ちタブに両ユーザーの申請が表示されていること
        $response->assertSee('申請ユーザーA', false);
        $response->assertSee('申請ユーザーB', false);
        $response->assertSee('ユーザーAの申請', false);
        $response->assertSee('ユーザーBの申請', false);
        $response->assertSee('承認待ち', false);
    }

    /**
     * 15-2: 管理者が申請一覧画面で全ユーザーの承認済み修正申請を確認できる
     */
    public function test_admin_can_see_all_approved_requests_on_application_list()
    {
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin152@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user1 = User::create([
            'name' => '承認済みユーザーA',
            'email' => 'approved_a@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user1->markEmailAsVerified();

        $user2 = User::create([
            'name' => '承認済みユーザーB',
            'email' => 'approved_b@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user2->markEmailAsVerified();

        // 承認済み（status=2）の勤怠を各ユーザー分作成
        Attendance::create([
            'user_id' => $user1->id,
            'date' => '2026-03-12',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'notes' => 'ユーザーAの承認済み申請',
            'status' => 2,
        ]);
        Attendance::create([
            'user_id' => $user2->id,
            'date' => '2026-03-13',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'notes' => 'ユーザーBの承認済み申請',
            'status' => 2,
        ]);

        // 承認待ち（status=1）のデータも作成（承認済みタブに表示されないことの確認用）
        Attendance::create([
            'user_id' => $user1->id,
            'date' => '2026-03-14',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'notes' => '承認待ち申請',
            'status' => 1,
        ]);

        $response = $this->actingAs($admin)->get('/admin/application-list');
        $response->assertStatus(200);

        // 承認済みデータが画面に含まれていること
        $response->assertSee('承認済みユーザーA', false);
        $response->assertSee('承認済みユーザーB', false);
        $response->assertSee('ユーザーAの承認済み申請', false);
        $response->assertSee('ユーザーBの承認済み申請', false);
        $response->assertSee('承認済み', false);
    }

    /**
     * 15-3: 管理者が修正申請の詳細画面を開くと申請内容が正しく表示される
     */
    public function test_admin_can_see_correct_detail_on_approval_page()
    {
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin153@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = User::create([
            'name' => '詳細確認ユーザー',
            'email' => 'detail_user@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 承認待ちの勤怠データを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-15',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'notes' => '詳細確認用の申請備考',
            'status' => 1,
        ]);

        // 休憩データも作成
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'start' => '12:00:00',
            'end' => '13:00:00',
        ]);

        // 管理者で修正申請詳細画面にアクセス
        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/approve/' . $attendance->id);
        $response->assertStatus(200);

        // 申請内容が正しく表示されていること
        $response->assertSee('詳細確認ユーザー', false);
        $response->assertSee('2026年', false);
        $response->assertSee('3月15日', false);
        $response->assertSee('09:30', false);
        $response->assertSee('18:30', false);
        $response->assertSee('12:00', false);
        $response->assertSee('13:00', false);
        $response->assertSee('詳細確認用の申請備考', false);
    }

    /**
     * 15-4: 管理者が修正申請の詳細画面で「承認」ボタンを押すと申請が承認される
     */
    public function test_admin_can_approve_attendance_request()
    {
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin154@example.com',
            'password' => \Hash::make('adminpass'),
            'role' => 1,
        ]);
        $admin->markEmailAsVerified();

        $user = User::create([
            'name' => '承認対象ユーザー',
            'email' => 'approve_target@example.com',
            'password' => \Hash::make('password123'),
            'role' => 0,
        ]);
        $user->markEmailAsVerified();

        // 承認待ち（status=1）の勤怠データを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-03-16',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'notes' => '承認テスト申請',
            'status' => 1,
        ]);

        // 管理者で承認ボタンを押下（POST）
        $response = $this->actingAs($admin)->post('/admin/stamp_correction_request/approve/' . $attendance->id);

        // 申請一覧ページへリダイレクトされること
        $response->assertRedirect();

        // DBのstatusが承認済み（2）に更新されていること
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 2,
        ]);
    }
}
