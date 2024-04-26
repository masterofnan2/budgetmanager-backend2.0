<?php

namespace App\Actions;

use App\Actions\Actions;
use Illuminate\Validation\ValidationException;


class AuthActions extends Actions
{
    public function validateEmail($email): bool
    {
        // $CERTIFICATE = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . 'check-email.crt';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://emailvalidation.abstractapi.com/v1/?api_key=b899e76d986f4f18a7f4da21dd59aae7&email={$email}",
            CURLOPT_SSL_VERIFYPEER => false,
            // CURLOPT_CAINFO => $CERTIFICATE,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: email-checker.p.rapidapi.com",
                "X-RapidAPI-Key: b7e1071b14msh1e80bc49461b677p1296a4jsn88863fa29281"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw ValidationException::withMessages(['email' => 'Failed to verify email.']);
        }

        $response = json_decode($response);
        if (!(isset($response->deliverability) && $response->deliverability === 'DELIVERABLE')) {
            throw ValidationException::withMessages(['email' => 'Email is not valid']);
        }

        return true;
    }
}