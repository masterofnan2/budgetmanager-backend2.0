<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserActions extends Actions
{
    protected $user;

    public function __construct()
    {
        $user = Auth::user();
        $this->user = User::find($user->id);
    }

    public function setUserToVerified(): UserActions
    {
        $this->user->email_verified_at = Helper::getIsoString(date_create());
        return $this;
    }

    public function user(): User|Model
    {
        return $this->user;
    }

    public function save(): bool
    {
        return $this->user->save();
    }
}