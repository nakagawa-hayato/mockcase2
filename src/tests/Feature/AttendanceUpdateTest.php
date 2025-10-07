<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'user',
            'password' => bcrypt('password123'),
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function 出勤時間が退勤時間より後ならエラーメッセージが表示される()
    {
        $response = $this->actingAs($this->user)->post(
            "/attendance/stamp_correction_request/{$this->attendance->id}",
            [
                'clock_in_at' => '18:00',
                'clock_out_at' => '09:00',
                'reason' => 'テスト',
            ]
        );

        $response->assertSessionHasErrors(['clock_in_out']);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後ならエラーメッセージが表示される()
    {
        $response = $this->actingAs($this->user)->post(
            "/attendance/stamp_correction_request/{$this->attendance->id}",
            [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'reason' => 'テスト',
                'breaks' => [
                    ['start_time' => '19:00', 'end_time' => '19:30']
                ],
            ]
        );

        $response->assertSessionHasErrors(['breaks.0.start_end']);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後ならエラーメッセージが表示される()
    {
        $response = $this->actingAs($this->user)->post(
            "/attendance/stamp_correction_request/{$this->attendance->id}",
            [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'reason' => 'テスト',
                'breaks' => [
                    ['start_time' => '17:00', 'end_time' => '19:00']
                ],
            ]
        );

        $response->assertSessionHasErrors(['breaks.0.start_end']);
    }

    /** @test */
    public function 備考未入力ならエラーメッセージが表示される()
    {
        $response = $this->actingAs($this->user)->post(
            "/attendance/stamp_correction_request/{$this->attendance->id}",
            [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'reason' => '',
            ]
        );

        $response->assertSessionHasErrors(['reason']);
    }

    /** @test */
    public function 修正申請が保存され管理者に承認待ちとして表示される()
    {
        $this->actingAs($this->user)->post(
            "/attendance/stamp_correction_request/{$this->attendance->id}",
            [
                'clock_in_at' => '09:30',
                'clock_out_at' => '18:00',
                'reason' => '体調不良で遅刻',
            ]
        );

        $response = $this->actingAs($this->admin)->get("/stamp_correction_request/list?status=pending");

        $response->assertStatus(200);
        $response->assertSee('体調不良で遅刻');
    }

    /** @test */
    public function 管理者が承認すると承認済みに表示される()
    {
        $this->actingAs($this->user)->post(
            "/attendance/stamp_correction_request/{$this->attendance->id}",
            [
                'clock_in_at' => '09:30',
                'clock_out_at' => '18:00',
                'reason' => '体調不良で遅刻',
            ]
        );

        $requestId = StampCorrectionRequest::first()->id;

        $this->actingAs($this->admin)->put("/stamp_correction_request/approve/{$requestId}");

        $response = $this->actingAs($this->admin)->get("/stamp_correction_request/list?status=approved");
        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }

    /** @test */
    public function 修正申請の詳細ページが表示される()
    {
        $this->actingAs($this->user)->post(
            "/attendance/stamp_correction_request/{$this->attendance->id}",
            [
                'clock_in_at' => '09:30',
                'clock_out_at' => '18:00',
                'reason' => '体調不良で遅刻',
            ]
        );

        $requestId = StampCorrectionRequest::first()->id;

        $response = $this->actingAs($this->user)->get("/stamp_correction_request/{$requestId}");
        $response->assertStatus(200);
        $response->assertSee('体調不良で遅刻');
    }
}
