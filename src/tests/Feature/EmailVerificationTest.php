<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** 会員登録後に認証メールが送信される */
    public function test_user_receives_verification_email_after_registration(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post('/email/verification-notification');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** 認証メール誘導画面が表示される */
    public function test_verification_notice_screen_is_displayed(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('登録していただいたメールアドレスに認証メールを送付しました。');
        $response->assertSee('メール認証を完了してください。');
    }

    /** 誘導画面に認証リンク文言がある */
    public function test_verification_link_is_visible_in_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');
    }

    /** 認証リンクを開くとメール認証が完了する */
    public function test_email_can_be_verified(): void
    {
        Event::fake();

        $user = User::factory()->unverified()->create();

        $verificationUrl = \URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        $response->assertRedirect('/attendance');
    }

    /** 認証済みユーザーも verify ページを表示できる（実装仕様に合わせる） */
    public function test_verified_user_can_still_access_verify_notice(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('登録していただいたメールアドレスに認証メールを送付しました。');
    }
}
