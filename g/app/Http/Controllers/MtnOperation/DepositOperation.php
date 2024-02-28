<?php

namespace App\Http\Controllers\MtnOperation;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DepositTransaction;
use App\Services\Mtn\GenerateMtnToken;

class DepositOperation extends Controller
{
    public function __construct(GenerateMtnToken $tr)
    {
        $this->tr = $tr;
    }

    public function makeRequestToPay(Request $request)
    {
        try {
            // Validation des données reçues
            $validatedData = $request->validate([
                "payeeNote" => ["required", "string"],
                "externalId" => ["required", "string"],
                "amount" => ["required", "string"],
                "currency" => ["required", "string"],
                "payer_partyIdType" => ["required", "string"],
                "payer_partyId" => ["required", "string"],
                "payerMessage" => ["required", "string"],
            ]);

            // Création d'une nouvelle transaction de retrait dans la base de données
            DepositTransaction::create([
                "payeeNote" => $validatedData["payeeNote"],
                "externalId" => $validatedData["externalId"],
                "amount" => $validatedData["amount"],
                "currency" => $validatedData["currency"],
                "payer_partyIdType" => $validatedData["payer_partyIdType"],
                "payer_partyId" => $validatedData["payer_partyId"],
                "payerMessage" => $validatedData["payerMessage"],
            ]);

            // Préparation des données pour la requête à l'API
            $requestData = [
                "payeeNote" => $validatedData["payeeNote"],
                "externalId" => $validatedData["externalId"],
                "amount" => $validatedData["amount"],
                "currency" => $validatedData["currency"],
                "payer" => [
                    "partyIdType" => $validatedData["payer_partyIdType"],
                    "partyId" => $validatedData["payer_partyId"],
                ],
                "payerMessage" => $validatedData["payerMessage"]
            ];
            $token = $this->tr->getApiToken();
            $uuid = $token["original"]["XReferenceId"];
            $tokenReal = $token["original"]["Data"]["access_token"];
            $tokenReal = "Bearer " . $tokenReal;
            // echo $token->json();
            // Envoi de la requête à l'API avec axios
            $response = Http::withHeaders([
                'Authorization' => $tokenReal,
                'X-Reference-Id' => $uuid,
                'X-Target-Environment' => 'sandbox',
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => '8ab12f244bfa4524b091ed6018eec7aa',
            ])->post('https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay', $requestData);
            // Traitement de la réponse
            return response()->json([
                'status' => 'success',
                'statusCode' => $response->status(),
                'data' => $response->json(),
                'token' => $tokenReal,

            ]);;
        } catch (\Exception $e) {
            // Gestion des erreurs
            return $e->getMessage();
        }
    }
    private function GetPaymentStatus($tokenReal, $uuid)
    {
        $url = 'https://sandbox.momodeveloper.mtn.com/collection//v2_0/payment/' . $uuid;
        $response = Http::withHeaders([
            'Authorization' => $tokenReal,
            'X-Reference-Id' => $uuid,
            'X-Target-Environment' => 'sandbox',
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => '8ab12f244bfa4524b091ed6018eec7aa',
        ])->post($url);
    }
    public function getBalance(Request $request)
    {

        $url = 'https://sandbox.momodeveloper.mtn.com/collection//v2_0/payment/'; //. $uuid;

        $response = Http::withHeaders([
            // 'Authorization' => $tokenReal,
            // 'X-Reference-Id' => $uuid,
            'X-Target-Environment' => 'sandbox',
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => '8ab12f244bfa4524b091ed6018eec7aa',
        ])->post($url);
    }
    public function SendMomoToFlooz(Request $request)
    {
        try {
            $validatedData = $request->validate([
                "payeeNote" => ["required", "string"],
                "externalId" => ["required", "string"],
                "amount" => ["required", "string"],
                "currency" => ["required", "string"],
                "payer_partyIdType" => ["required", "string"],
                "payer_partyId" => ["required", "string"],
                "payerMessage" => ["required", "string"],
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
