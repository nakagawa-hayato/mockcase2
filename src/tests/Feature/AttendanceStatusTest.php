<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\UsersTableSeeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用ユーザー作成
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);
    }

    /** @test */
    public function 出勤から退勤まで一連の流れが動作する()
    {
        $this->actingAs($this->user);

        // 出勤登録
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'clock_in_at' => Carbon::today()->setTime(9, 0),
        ]);

        $this->assertEquals('working', $attendance->status);

        // 休憩開始
        $break = BreakTime::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::today()->setTime(12, 0),
        ]);

        $this->assertTrue($break->isOpen());
        $this->assertEquals('on_break', $attendance->fresh()->status);

        // 休憩終了
        $break->update(['end_time' => Carbon::today()->setTime(12, 30)]);

        $this->assertFalse($break->fresh()->isOpen());
        $this->assertEquals('working', $attendance->fresh()->status);

        // 退勤
        $attendance->update(['clock_out_at' => Carbon::today()->setTime(18, 0)]);

        $this->assertEquals('finished', $attendance->fresh()->status);
        $this->assertEquals('08:30', $attendance->fresh()->work_hm); // 9時間 - 30分休憩
    }
}
