<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザーを1人作成
        $this->admin = User::create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function メールアドレスが未入力の場合はバリデーションエラーになる()
    {
        $response = $this->post('/admin/login', [
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
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors')->getBag('default')->get('password');
        $this->assertContains('パスワードを入力してください', $errors);
    }

    /** @test */
    public function 登録内容と一致しない場合はバリデーションエラーになる()
    {
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors')->getBag('default')->get('email');
        $this->assertContains('ログイン情報が登録されていません', $errors);
    }
}
