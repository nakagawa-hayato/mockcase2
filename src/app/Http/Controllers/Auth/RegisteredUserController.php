<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    // POST /register
    public function store(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // メール検証を利用する場合は Registered イベントを投げると通知が飛ぶ
        event(new Registered($user));

        Auth::login($user);

        // メール検証が必須なら検証通知ページへ（例）
        return redirect()->route('verification.notice')->with('status', '確認メールを送信しました。メールをご確認ください。');
    }
}

