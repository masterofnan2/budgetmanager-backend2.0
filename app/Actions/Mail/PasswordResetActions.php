<?php

namespace App\Actions\Mail;

use App\Actions\Actions;
use App\Actions\Helper;
use App\Mail\Auth\PasswordReset;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Str;


class PasswordResetActions extends Actions
{
    protected $user;
    protected $token;
    protected $newPassword;
    protected $Model;

    public function __construct()
    {
        $this->Model = DB::table('password_reset_tokens');
    }

    protected function sendEmail(): ?SentMessage
    {
        return Mail::to($this->user)->send(new PasswordReset($this->token));
    }

    protected function createToken(): string
    {
        $token = Str::random(32);

        $this->Model->insert([
            'email' => $this->user->email,
            'token' => $token,
            'created_at' => Helper::getIsoString(date_create())
        ]);

        return $token;
    }

    protected function setTokenUser(): PasswordResetActions
    {
        $passwordResetToken = $this->Model
            ->select('email')
            ->where('token', $this->token)
            ->first();

        $user = User::where('email', $passwordResetToken->email)->first();
        $this->user = $user;

        return $this;
    }

    protected function dropToken(): int
    {
        $deleted = 0;

        if ($this->token) {
            $deleted = DB::table('password_reset_tokens')
                ->where(['token' => $this->token])
                ->delete();
        }

        return $deleted;
    }

    protected function changeToNewPassword(): bool
    {
        $changed = false;

        if ($this->newPassword) {
            if (!$this->user) {
                $this->setTokenUser();
            }

            $this->user->password = Hash::make($this->newPassword);
            $changed = $this->user->save();
        }

        return $changed;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    public function setToken(string $token): PasswordResetActions
    {
        $this->token = $token;
        return $this;
    }

    public function makePasswordReset(): bool
    {
        $this->token = $this->createToken();
        return boolval($this->sendEmail());
    }

    public function setNewPassword(string $newPassword): PasswordResetActions
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    public function reset(): PasswordResetActions
    {
        $passwordChanged = $this->changeToNewPassword();

        if ($passwordChanged) {
            $this->dropToken();
        }

        return $this;
    }
}