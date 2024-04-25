<?php

namespace App\Http\Controllers;

use App\Actions\AuthActions;
use App\Actions\CycleActions;
use App\Actions\Mail\ConfirmationActions;
use App\Actions\UserActions;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SignupRequest;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function verifyEmailConformity(Request $request, AuthActions $authActions)
    {
        $request->validate(['email' => 'required|email|unique:users']);
        $response = $authActions->email_is_valable($request->input('email'));

        if (!$response || $response->deliverability !== 'DELIVERABLE') {
            throw ValidationException::withMessages(['email' => 'Email is not valid']);
        }

        return response()->json(['valable' => boolval($response)]);
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

    public function login(LoginRequest $request, CycleActions $CycleActions)
    {
        $validated = $request->validated();
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

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
            'token' => $user->createToken($user_agent)->plainTextToken,
            'user' => $user
        ]);
    }

    public function signup(SignupRequest $request, CycleActions $CycleActions)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $user = User::create($validated);

        $CycleActions
            ->setUserId($user->id)
            ->createCycle();

        return response()->json([
            'token' => $user->createToken($user_agent)->plainTextToken,
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
}
