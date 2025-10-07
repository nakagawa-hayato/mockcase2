<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーでユーザーを投入
        $this->seed(\Database\Seeders\UsersTableSeeder::class);

        // テスト用ユーザー取得・メール認証済みにする
        $this->user = User::where('email', 'taro.y@coachtech.com')->first();
        $this->user->email_verified_at = now();
        $this->user->save();

        // 出勤済みの勤怠を作成
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'clock_in_at' => now()->subHours(4), // 出勤4時間前
        ]);
    }

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        // 勤怠画面にアクセス
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        // 退勤処理
        $response = $this->actingAs($this->user)->post('/attendance/clock-out');
        $response->assertRedirect('/attendance'); // リダイレクト確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
        ]);

        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->assertNotNull($attendance->clock_out_at);
        $this->assertEquals('finished', $attendance->status);
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        // 退勤処理
        $this->actingAs($this->user)->post('/attendance/clock-out');

        // 勤怠一覧画面にアクセス
        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);

        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->assertNotNull($attendance->clock_out_at);
        $response->assertSee($attendance->clock_out_at->format('H:i'));
    }
}

