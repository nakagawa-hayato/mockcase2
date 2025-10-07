<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成（role = 'admin'）
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password'),
        ]);


        // 勤怠作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        // 休憩作成
        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);
    }

    /** @test */
    public function 勤怠詳細画面に表示されるデータが正しい()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.edit', $this->attendance->id));

        $response->assertStatus(200)
                 ->assertSee($this->user->name)
                 ->assertSee('09:00')
                 ->assertSee('18:00')
                 ->assertSee('12:00')
                 ->assertSee('13:00');
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラー()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.attendance.update', $this->attendance->id), [
                'clock_in_at' => '19:00',
                'clock_out_at' => '18:00',
                'breaks' => [
                    ['start_time' => '12:00', 'end_time' => '13:00']
                ],
                'reason' => '確認',
            ]);

        $response->assertSessionHasErrors([
            'clock_in_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラー()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.attendance.update', $this->attendance->id), [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'breaks' => [
                    ['start_time' => '19:00', 'end_time' => '20:00']
                ],
                'reason' => '確認',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.start_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラー()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.attendance.update', $this->attendance->id), [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'breaks' => [
                    ['start_time' => '17:00', 'end_time' => '19:00']
                ],
                'reason' => '確認',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.start_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }
}
