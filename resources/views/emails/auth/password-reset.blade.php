<x-mail::message>
Click on the following link to complete your password resetting
 
<x-mail::panel>
    You can ignore this email if You are not the author of this action.
</x-mail::panel>

<x-mail::button :url="$resetUrl">
Reset password
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>