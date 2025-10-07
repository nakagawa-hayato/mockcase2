<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $users;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成（role = 'admin'）
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 一般ユーザー作成
        $this->users = User::factory()->count(3)->create();

        // 勤怠データ作成（今日、前日、翌日）
        foreach ($this->users as $user) {
            // 今日
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => Carbon::today()->toDateString(),
                'clock_in_at' => Carbon::today()->setHour(9)->setMinute(0),
                'clock_out_at' => Carbon::today()->setHour(18)->setMinute(0),
            ]);

            // 前日
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => Carbon::yesterday()->toDateString(),
                'clock_in_at' => Carbon::yesterday()->setHour(9)->setMinute(0),
                'clock_out_at' => Carbon::yesterday()->setHour(18)->setMinute(0),
            ]);

            // 翌日
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => Carbon::tomorrow()->toDateString(),
                'clock_in_at' => Carbon::tomorrow()->setHour(9)->setMinute(0),
                'clock_out_at' => Carbon::tomorrow()->setHour(18)->setMinute(0),
            ]);
        }
    }

    /** @test */
    public function 当日の全ユーザー勤怠情報が表示される()
    {
        $today = Carbon::today();

        $response = $this->actingAs($this->admin)
                         ->get('/admin/attendance/list');

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $response->assertSee($user->name);
        }

        // ビューで表示されている日付形式に合わせる
        $response->assertSee($today->format('Y/m/d'));
    }

    /** @test */
    public function 前日ボタンで前日の勤怠情報が表示される()
    {
        $yesterday = Carbon::yesterday();

        $response = $this->actingAs($this->admin)
                         ->get("/admin/attendance/list?date={$yesterday->toDateString()}");

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $response->assertSee($user->name);
        }

        $response->assertSee($yesterday->format('Y/m/d'));
    }

    /** @test */
    public function 翌日ボタンで翌日の勤怠情報が表示される()
    {
        $tomorrow = Carbon::tomorrow();

        $response = $this->actingAs($this->admin)
                         ->get("/admin/attendance/list?date={$tomorrow->toDateString()}");

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $response->assertSee($user->name);
        }

        $response->assertSee($tomorrow->format('Y/m/d'));
    }
}
