<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

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
    public function 勤務外ユーザーが出勤ボタンを押すとステータスが勤務中になる()
    {
        $this->actingAs($this->user)
            ->get('/attendance')
            ->assertStatus(200)
            ->assertSee('出勤');

        // 出勤処理
        $this->post('/attendance/clock-in')
            ->assertRedirect();

        $attendance = Attendance::where('user_id', $this->user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals('working', $attendance->status);
    }

    /** @test */
    public function 退勤済ユーザーは出勤できない()
    {
        // まず出勤・退勤済みのレコード作成
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->subHours(8),
            'clock_out_at' => Carbon::now(),
        ]);

        $this->actingAs($this->user)
            ->get('/attendance')
            ->assertStatus(200)
            ->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面に正しく表示される()
    {
        // 出勤処理
        $this->actingAs($this->user)
            ->post('/attendance/clock-in');

        $attendance = Attendance::where('user_id', $this->user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        $this->assertNotNull($attendance);

        // 勤怠一覧画面確認
        $this->actingAs($this->user)
            ->get('/attendance/list') // 勤怠一覧ルート（環境に合わせて変更）
            ->assertStatus(200)
            ->assertSee($attendance->clock_in_hm);
    }
}
