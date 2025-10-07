<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // シーダーでユーザー投入
        $this->seed(\Database\Seeders\UsersTableSeeder::class);

        // テスト用ユーザー取得・メール認証済みにする
        $this->user = User::where('email', 'taro.y@coachtech.com')->first();
        $this->user->email_verified_at = now();
        $this->user->save();

        // 今月と先月の勤怠を作成
        Attendance::create([
            'user_id' => $this->user->id,
            'date' => now()->toDateString(),
            'clock_in_at' => now()->subHours(8),
            'clock_out_at' => now()->subHours(1),
        ]);

        Attendance::create([
            'user_id' => $this->user->id,
            'date' => now()->subMonth()->toDateString(),
            'clock_in_at' => now()->subMonth()->subHours(8),
            'clock_out_at' => now()->subMonth()->subHours(1),
        ]);
    }

    /** @test */
    public function 自分の勤怠情報が全て表示されている()
    {
        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);

        // 今月の勤怠が表示される
        $attendance = Attendance::where('user_id', $this->user->id)
            ->whereDate('date', now()->toDateString())
            ->first();
        $response->assertSee($attendance->date->format('m/d'));
    }

    /** @test */
    public function 現在の月が表示されている()
    {
        $response = $this->actingAs($this->user)->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSee(now()->format('Y/m'));
    }

    /** @test */
    public function 前月の勤怠情報が表示される()
    {
        $prevYm = now()->subMonth()->format('Ym');
        $response = $this->actingAs($this->user)->get('/attendance/list?ym=' . $prevYm);
        $response->assertStatus(200);

        $attendance = Attendance::where('user_id', $this->user->id)
            ->whereDate('date', now()->subMonth()->toDateString())
            ->first();

        $response->assertSee($attendance->date->format('m/d'));
    }

    /** @test */
    public function 翌月の勤怠情報が表示される()
    {
        $nextYm = now()->addMonth()->format('Ym');
        $response = $this->actingAs($this->user)->get('/attendance/list?ym=' . $nextYm);
        $response->assertStatus(200);

        // 翌月の勤怠は存在しないので空表示の確認
        $response->assertDontSee(now()->format('m/d'));
    }

    /** @test */
    public function 勤怠詳細画面に遷移できる()
    {
        $attendance = Attendance::where('user_id', $this->user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $response = $this->actingAs($this->user)
            ->get('/attendance/detail/' . $attendance->id . '');

        $response->assertStatus(200);
        $response->assertSee($attendance->clock_in_at->format('H:i'));
    }
}
