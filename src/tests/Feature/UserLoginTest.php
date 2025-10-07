<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // テスト用ユーザーを1人作成
        $this->user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    /** @test */
    public function メールアドレスが未入力の場合はバリデーションエラーになる()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors')->getBag('default')->get('email');
        $this->assertContains('メールアドレスを入力してください', $errors);
    }

    /** @test */
    public function パスワードが未入力の場合はバリデーションエラーになる()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors')->getBag('default')->get('password');
        $this->assertContains('パスワードを入力してください', $errors);
    }

    /** @test */
    public function 登録内容と一致しない場合はバリデーションエラーになる()
    {
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors')->getBag('default')->get('email');
        $this->assertContains('ログイン情報が登録されていません', $errors);
    }
}
