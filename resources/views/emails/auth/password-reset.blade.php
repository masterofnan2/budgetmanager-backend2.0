<x-mail::message>
One step away from resetting your password

<x-mail::panel>
You are not the author of this action?
</x-mail::panel>

If you didn't request a password reinitialization, you can ignore this email.

<x-mail::button :url="{{env('FRONTEND_URL') . '/auth/password-reset/' . $token}}" color="primary">
reset password
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>