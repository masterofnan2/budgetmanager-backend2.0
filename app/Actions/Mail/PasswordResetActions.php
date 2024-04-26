<?php

namespace App\Actions\Mail;

use App\Actions\Actions;
use App\Mail\Auth\PasswordReset;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;


class PasswordResetActions extends Actions
{
    protected $user;
    protected $token;
    protected $newPassword;

    protected function sendEmail(): ?SentMessage
    {
        return Mail::to($this->user)->send(new PasswordReset($this->token));
    }

    protected function createToken()
    {
        return Password::createToken($this->user);
    }

    protected function setTokenUser(): PasswordResetActions
    {
        $user = DB::table('password_reset_tokens')
            ->join('user', 'user.email', 'password_reset_tokens.email')
            ->select('user.*')
            ->where('token', $this->token)
            ->first();

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
        if ($this->newPassword) {
            if (!$this->user) {
                $this->setTokenUser();
            }

            $this->user->password = Hash::make($this->newPassword);
        }

        return $this->user->save();
    }

    public function setUser(Model|User $user)
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

    public function setNewPassword(string $newPassword)
    {
        $this->newPassword = $newPassword;
        return $this;
    }

    public function reset()
    {
        $this->changeToNewPassword();
        $this->dropToken();
    }
}