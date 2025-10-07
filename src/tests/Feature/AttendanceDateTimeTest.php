<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\User;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 固定ユーザー投入
        $this->seed(\Database\Seeders\UsersTableSeeder::class);
    }

    /** @test */
    public function 勤怠打刻画面に現在の日時が表示される()
    {
        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 10, 2, 15, 30));

        // Seeder で投入したユーザーを取得
        $user = User::first();
        $this->assertNotNull($user, 'Seederユーザーが存在しません');

        // ✅ メール認証済みにする
        $user->email_verified_at = now();
        $user->save();

        // ログインしてアクセス
        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertStatus(200);

        // UIに合わせた期待値
        $expectedDate = Carbon::now()->isoFormat('YYYY年MM月DD日(ddd)');
        $expectedTime = Carbon::now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
