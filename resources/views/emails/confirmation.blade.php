<x-mail::message>
One step away from setting up your account

<x-mail::panel>
{{$code}}
</x-mail::panel>
is your code to confirm your identity.

<x-mail::button :url="'https://budgetmanager.free.nf/dashboard'" color="primary">
Go to Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
