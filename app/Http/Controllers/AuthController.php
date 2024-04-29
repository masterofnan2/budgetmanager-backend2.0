<?php

namespace App\Http\Controllers;

use App\Actions\AuthActions;
use App\Actions\CycleActions;
use App\Actions\Mail\ConfirmationActions;
use App\Actions\Mail\PasswordResetActions;
use App\Actions\TokenActions;
use App\Actions\UserActions;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function verifyEmailConformity(Request $request, AuthActions $authActions)
    {
        $request->validate(['email' => 'required|email|unique:users']);
        response()->json(['valid' => $authActions->validateEmail($request->input('email'))]);
    }

    public function makeEmailConfirmation(ConfirmationActions $confirmationActions)
    {
        return response()->json(['sent' => $confirmationActions->makeEmailConfirmation()]);
    }

    public function matchConfirmationCode(ConfirmationActions $confirmationActions, Request $request, UserActions $userActions)
    {
        $requestCode = $request->input('code');
        $matched = $confirmationActions->hasMatched($requestCode);

        if ($matched) {
            $userActions->setUserToVerified()->save();
        }

        return response()->json(['matched' => $matched]);
    }

    public function login(LoginRequest $request, CycleActions $CycleActions, TokenActions $tokenActions)
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $CycleActions
            ->setUserId($user->id)
            ->getCurrent();

        return response()->json([
            'token' => $tokenActions->setUser($user)->createToken(),
            'user' => $user
        ]);
    }

    public function signup(SignupRequest $request, CycleActions $CycleActions, TokenActions $tokenActions)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        $CycleActions
            ->setUserId($user->id)
            ->createCycle();

        return response()->json([
            'token' => $tokenActions->setUser($user)->createToken(),
            'user' => $user
        ]);
    }

    public function getUser(CycleActions $CycleActions)
    {
        $user = Auth::user();
        $CycleActions->getCurrent();

        return response()->json([
            'user' => $user
        ]);
    }

    public function forgottenPassword(Request $request, AuthActions $authActions, PasswordResetActions $passwordResetActions)
    {
        $request->validate(['email' => 'required|email|exists:users']);

        $email = $request->input('email');
        $emailIsValid = $authActions->validateEmail($email);

        if ($emailIsValid) {
            $sent = $passwordResetActions
                ->setUser(User::where('email', $email)->first())
                ->makePasswordReset();

            return response()->json(['sent' => $sent]);
        }
    }

    public function resetPassword(Request $request, PasswordResetActions $passwordResetActions, TokenActions $tokenActions)
    {
        $request->validate([
            'token' => 'exists:password_reset_tokens',
            'password' => 'required|min:6|max:40|confirmed',
        ]);

        $token = $request->input('token');
        $newPassword = $request->input('password');

        $user = $passwordResetActions
            ->setToken($token)
            ->setNewPassword($newPassword)
            ->reset()
            ->get('user');

        return response()->json([
            'user' => $user,
            'token' => $tokenActions
                ->setUser($user)
                ->createToken()
        ]);
    }
}
