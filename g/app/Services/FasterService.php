<?php 

namespace App\Services;

class FasterService 
{
    public function sendeCode($code, $destinator, $tr = null)
    {
        $url      = env("FASTER_MESSAGE_URL");
        $apiKey   = env('FASTER_MESSAGE_APP_KEY');
        $smsData  = [
            'from' =>"WAOUH MONDE",
            'to'   => $destinator,
            'text' => $tr ==null ? "WM-$code : votre code de vérification ".env('APP_NAME') : "WM-$code : votre code de vérification ".env('APP_NAME').".\n 5F serait prélevé de votre compte.",
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-KEY: " . $apiKey]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $smsData);
        $result = curl_exec($ch);
        curl_close($ch);
        // dd($result);
        // Utilisez $result comme vous en avez besoin, par exemple, pour traiter la réponse de l'API.
        // return response()->json(['message' => 'Message envoyé avec succès']);
    }
}