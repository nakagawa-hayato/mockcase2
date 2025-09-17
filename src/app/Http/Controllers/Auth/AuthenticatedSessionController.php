<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    // POST /login
    public function store(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            // セッション再生成（セッション固定化攻撃対策）
            $request->session()->regenerate();

                // ✅ メール未認証なら確認画面へリダイレクト
                if (! Auth::user() || ! Auth::user()->hasVerifiedEmail()) {
                    return redirect()->route('verification.notice'); 
                    // => resources/views/auth/verify-email.blade.php が表示される
                }
        
            return redirect()->intended('/attendance'); // intended がなければ '/'
        }

        // 認証失敗
        throw ValidationException::withMessages([
            'email' => ['ログイン情報が登録されていません'],
        ]);
    }

    // POST /logout
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}