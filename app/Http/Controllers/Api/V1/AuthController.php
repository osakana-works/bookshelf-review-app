<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * ログインしてAPIトークンを発行する
     */
    public function login(LoginRequest $request) : JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
        ], 200);
    }

    /**
     * ログアウトしてAPIトークンを削除する
     */
    public function logout(Request $request) : JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'ログアウトしました。',
        ], 200);
    }
}
