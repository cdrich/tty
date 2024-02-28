<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WithdrawalController extends Controller
{
    /**
     * Handle the withdrawal request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function withdraw(Request $request)
    {
        // Récupérer les données de la requête
        $token = $request->input('token');
        $msisdn = $request->input('msisdn');
        
        // Préparer la requête XML pour l'API
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-16\"?>
        <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:api=\"http://api.merchant.tlc.com/\">
            <soapenv:Header/>
            <soapenv:Body>
                <api:getBalance>
                    <token>{$token}</token>
                    <request>
                        <msisdn>{$msisdn}</msisdn>
                    </request>
                </api:getBalance>
            </soapenv:Body>
        </soapenv:Envelope>";

        // URL de l'API
        $apiUrl = 'https://testapimarchand2.moov-africa.bj:2010/com.tlc.merchant.api/UssdPush'; // URL fournie

        // Envoyer la requête à l'API
        $response = Http::withOptions([
            'verify' => false, // Désactive la vérification SSL
        ])->withHeaders([
            'Content-Type' => 'text/xml; charset=UTF8',
        ])->send('POST', $apiUrl, [
            'body' => $xmlRequest,
        ]);

        // Traiter la réponse
        if ($response->successful()) {
     
            return response()->json(['success' => true, 'data' => $response->body()]);
        } else {
            // Gérer l'erreur ici
            return response()->json(['success' => false, 'error' => $response->body()], $response->status());
        }
    }
}
