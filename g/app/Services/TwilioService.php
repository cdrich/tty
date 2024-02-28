<?php

namespace App\Services;
use Twilio\Rest\Client;

class TwilioService 
{
    protected $client;
    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'),env('JWILIO_AUTH_TOKEN'));
    }

    public function sendSMS($to, $message)
    {
        // Ajouter le préfixe "229" au numéro de téléphone
        $formattedNumber = '+229' . $to;
    
        return $this->client->messages->create($formattedNumber, [
            'from' => env('TWILIO_PHONE_NUMBER'),
            'body' => "WM-$message votre code de verification ".env('APP_NAME'),
        ]);
    }
}