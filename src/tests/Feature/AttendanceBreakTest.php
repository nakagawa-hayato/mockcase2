<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 固定ユーザー投入
        $this->seed(\Database\Seeders\UsersTableSeeder::class);

        // シーダーで投入したユーザーを取得（勤務中のユーザーを仮定）
        $this->user = User::where('email', 'taro.y@coachtech.com')->first();
        $this->user->email_verified_at = now();
        $this->user->save();

        // 初期状態は出勤済みにする
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'clock_in_at' => now(),
        ]);
    }

    /** @test */
    public function 休憩入ボタンが正しく機能する()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        // 休憩入
        $response = $this->actingAs($this->user)->post('/attendance/break-in');
        $response->assertRedirect();

        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->assertEquals('on_break', $attendance->status);
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        // まず休憩入
        $this->actingAs($this->user)->post('/attendance/break-in');

        // 休憩戻
        $this->actingAs($this->user)->post('/attendance/break-out');

        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->assertEquals('working', $attendance->status);
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        // 1回目休憩入・戻
        $this->actingAs($this->user)->post('/attendance/break-in');
        $this->actingAs($this->user)->post('/attendance/break-out');

        // 2回目休憩入・戻
        $this->actingAs($this->user)->post('/attendance/break-in');
        $this->actingAs($this->user)->post('/attendance/break-out');

        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->assertEquals('working', $attendance->status);
        $this->assertCount(2, $attendance->breakTimes);
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        // 休憩入・戻
        $this->actingAs($this->user)->post('/attendance/break-in');
        $this->actingAs($this->user)->post('/attendance/break-out');

        $response = $this->actingAs($this->user)->get('/attendance/list'); // 勤怠一覧
        $response->assertStatus(200);

        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $break = $attendance->breakTimes->first();

        $response->assertSee($break->start_time->format('H:i'));
        $response->assertSee($break->end_time->format('H:i'));
    }
}

