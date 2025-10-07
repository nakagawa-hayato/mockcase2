<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 固定ユーザー投入
        $this->seed(\Database\Seeders\UsersTableSeeder::class);
        $this->user = User::where('email', 'taro.y@coachtech.com')->first();
        $this->user->email_verified_at = now();
        $this->user->save();

        // 勤怠データ作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::parse('2025-10-01'),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);

        // 休憩データ作成
        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'start_time' => '12:00:00',
            'end_time'   => '13:00:00',
        ]);
    }

    /** @test */
    public function 勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
    }

    /** @test */
    public function 勤怠詳細画面の日付が選択した日付になっている()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSeeTextInOrder(['2025年', '10月1日']);
    }

    /** @test */
    public function 出勤退勤時刻が正しく表示されている()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 休憩時刻が正しく表示されている()
    {
        $response = $this->actingAs($this->user)->get("/attendance/detail/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
