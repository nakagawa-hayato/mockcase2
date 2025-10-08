<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use App\Models\User;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // まずアプリのセットアップ
        parent::setUp();

        // テスト用の「現在時刻」を固定（シーダー実行より前に設定しておくと安全）
        Carbon::setTestNow(Carbon::create(2025, 10, 2, 15, 30));
        Carbon::setLocale('ja');

        // 固定ユーザーを投入（Seeder が now() 等を使っている場合に備える）
        $this->seed(\Database\Seeders\UsersTableSeeder::class);
    }

    protected function tearDown(): void
    {
        // 固定時刻の解除
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @test */
    public function 勤怠打刻画面に現在の日時が表示される()
    {
        // Seederで作成した最初のユーザーを取得してログイン
        $user = User::first();
        $this->assertNotNull($user, 'Seederユーザーが存在しません');

        // メール認証済みにする（/attendance が認証済みユーザー向けの場合）
        $user->email_verified_at = Carbon::now();
        $user->save();

        // ページへアクセス
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        // UI に合わせた期待値（固定した時刻を使用）
        $expectedDate = Carbon::now()->isoFormat('YYYY年MM月DD日(ddd)');
        $expectedTime = Carbon::now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }
}
