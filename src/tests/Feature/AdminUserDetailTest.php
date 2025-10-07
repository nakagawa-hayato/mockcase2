<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminUserDetailTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $users;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 一般ユーザーを複数作成
        $this->users = User::factory()->count(3)->create();

        // 各ユーザーに勤怠を作成
        foreach ($this->users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => Carbon::today()->toDateString(),
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
            ]);

            // 休憩作成
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'start_time' => '12:00',
                'end_time' => '13:00',
            ]);
        }
    }

    /** @test */
    public function 管理者は全ユーザーの氏名とメールアドレスを確認できる()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/staff/list');

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $response->assertSee($user->name)
                     ->assertSee($user->email);
        }
    }

    /** @test */
    public function 選択したユーザーの勤怠情報が正しく表示される()
    {
        $user = $this->users->first();
        $attendance = Attendance::where('user_id', $user->id)->first();

        $response = $this->actingAs($this->admin)
            ->get('/admin/attendance/list?user_id='.$user->id);

        $response->assertStatus(200)
                ->assertSee($attendance->clock_in_at->format('H:i'))
                ->assertSee($attendance->clock_out_at->format('H:i'));

        // 休憩時間の合計を計算して表示と一致させる
        $totalBreak = $attendance->breakTimes->sum(function ($break) {
            return Carbon::parse($break->end_time)->diffInMinutes(Carbon::parse($break->start_time));
        });

        $hours = floor($totalBreak / 60);
        $minutes = $totalBreak % 60;
        $breakTime = sprintf('%02d:%02d', $hours, $minutes);

        $response->assertSee($breakTime);
    }

    /** @test */
    public function 前月ボタンで前月の勤怠が表示される()
    {
        $user = $this->users->first();
        $prevMonth = Carbon::today()->subMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $prevMonth->toDateString(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/attendance/list?user_id='.$user->id.'&date='.$prevMonth->toDateString());

        $response->assertStatus(200)
                 ->assertSee($attendance->clock_in_at->format('H:i'))
                 ->assertSee($attendance->clock_out_at->format('H:i'));
    }

    /** @test */
    public function 翌月ボタンで翌月の勤怠が表示される()
    {
        $user = $this->users->first();
        $nextMonth = Carbon::today()->addMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->toDateString(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/attendance/list?user_id='.$user->id.'&date='.$nextMonth->toDateString());

        $response->assertStatus(200)
                 ->assertSee($attendance->clock_in_at->format('H:i'))
                 ->assertSee($attendance->clock_out_at->format('H:i'));
    }

    /** @test */
    public function 勤怠一覧から詳細画面に遷移できる()
    {
        $user = $this->users->first();
        $attendance = Attendance::where('user_id', $user->id)->first();

        $response = $this->actingAs($this->admin)
            ->get('/admin/attendance/'.$attendance->id);

        $response->assertStatus(200)
                 ->assertSee($user->name)
                 ->assertSee($attendance->clock_in_at->format('H:i'))
                 ->assertSee($attendance->clock_out_at->format('H:i'));
    }
}
