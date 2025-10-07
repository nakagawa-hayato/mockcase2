<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示される()
    {
        $this->actingAs($this->admin);

        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
            ]);

            StampCorrectionRequest::factory()->pending()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
            ]);
        }

        $response = $this->get('/stamp_correction_request/list?status=pending');
        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
        }
    }

    /** @test */
    public function 承認済みの修正申請が全て表示される()
    {
        $this->actingAs($this->admin);

        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
            ]);

            StampCorrectionRequest::factory()->approved()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
            ]);
        }

        $response = $this->get('/stamp_correction_request/list?status=approved');
        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
        }
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示される()
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        $request = StampCorrectionRequest::factory()->pending()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'reason' => 'テスト理由',
        ]);

        $response = $this->get("/stamp_correction_request/{$request->id}");
        $response->assertStatus(200)
                 ->assertSee('テスト理由')
                 ->assertSee($user->name)
                 ->assertSee('09:00')
                 ->assertSee('18:00');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $this->actingAs($this->admin);

        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        $request = StampCorrectionRequest::factory()->pending()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);

        // ルート定義に合わせて PUT メソッドで送信
        $response = $this->put("/stamp_correction_request/approve/{$request->id}");

        $response->assertRedirect(); // 承認後はリダイレクト想定

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }
}

