<?php

namespace App\Services\Mtn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DepositTransaction;
use Ramsey\Uuid\Uuid;

use Illuminate\Support\Facades\Http;
use League\CommonMark\Environment\Environment;

class GenerateMtnToken
{
    protected $url;
    public function __construct()
    {
        $this->url = 'https://sandbox.momodeveloper.mtn.com/collection/token/';
    }
    private function apiRequest($collectionOcpPrimary, $requestData, $uuid, $route)
    {
        $baseUrl = "https://sandbox.momodeveloper.mtn.com";
        $url = $baseUrl . $route;
        // Assurez-vous d'utiliser le paramètre $collectionOcpPrimary passé à la fonction
        $response = Http::withHeaders([
            'X-Reference-Id' => $uuid,
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => $collectionOcpPrimary, // Utilisation du paramètre
        ])->post($url, $requestData); // Utilisation directe de $requestData

        if ($response->successful()) {
            $data = response()->json([
                'XReferenceId' => $uuid,
                'Status' => 'Api user created successfully',
                'StatusCode' => $response->status(),
                'Data' => $response->json()
            ]);
            $data = json_encode($data);
            return $data;
        } else {
            // Gérer l'erreur de manière appropriée
            // Vous pourriez lancer une exception personnalisée ici ou retourner une réponse d'erreur
            $data = json_encode(response()->json(['error' => 'La requête a échoué'], 500));
            return $data;
        }
    }
    public function generateMtnToken()
    {
        $response = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => env('MTN_OPC_APIM_SUB_KEY'),
            'Authorization' => env('AUTHORIZATION'),
        ])->post($this->url);

        // Récupérer le contenu de la réponse
        $body = $response->body();
        $body_j = json_decode($body);

        return $body_j;
    }
    private function generateUuid()
    {
        $uuid4 = Uuid::uuid4();
        return $uuid4->toString();
    }
    public function createApiUser()
    {
        //creer un uuid
        $uuid = $this->generateUuid();
        // dd($uuid);
        //recuperer l'ocp correct pour la requete
        $collectionOcpPrimary = "8ab12f244bfa4524b091ed6018eec7aa";

        $requestData = [
            "providerCallbackHost" => "string"
        ];
        $apiUser =
            $this->apiRequest($collectionOcpPrimary, $requestData, $uuid, "/v1_0/apiuser");
        $apiUser = json_decode($apiUser, true);
        return $uuid;
    }
    private function createApiKeyAndReturnCredential()
    {
        $apiUsersUuid = $this->createApiUser();
        echo $apiUsersUuid;
        echo '/n';
        $collectionOcpPrimary = "8ab12f244bfa4524b091ed6018eec7aa";
        $requestData = "";
        $route = "/v1_0/apiuser/" . $apiUsersUuid . "/apikey";
        $data = json_decode($this->apiRequest($collectionOcpPrimary, $requestData, $apiUsersUuid, $route), true);
        $concat = $data["original"]["XReferenceId"] . ":" . $data["original"]["Data"]["apiKey"];
        $credentials = base64_encode($data["original"]["XReferenceId"] . ":" . $data["original"]["Data"]["apiKey"]);
        $data["original"]["Data"]["credentials"] = $credentials;
        return $data;
    }
    public function getApiToken()
    {
        $credential =   $this->createApiKeyAndReturnCredential();
        $authorization = "Basic " . $credential["original"]["Data"]["credentials"];
        print($authorization);
        print("/n");
        $apiUsersUuid = $credential["original"]["XReferenceId"];
        $collectionOcpPrimary = "8ab12f244bfa4524b091ed6018eec7aa";
        $requestData = "";
        // $route = "/v1_0/apiuser/" . $apiUsersUuid . "/apikey";
        $data = json_decode($this->getTokenRequest($apiUsersUuid, $collectionOcpPrimary, $authorization), true);
        return $data;
    }
    private function getTokenRequest($uuid, $collectionOcpPrimary, $authorization)
    {
        $baseUrl = "https://sandbox.momodeveloper.mtn.com";
        $url = $baseUrl . "/collection/token/";
        // Assurez-vous d'utiliser le paramètre $collectionOcpPrimary passé à la fonction
        $response = Http::withHeaders([
            'Authorization' => $authorization,
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => $collectionOcpPrimary, // Utilisation du paramètre
        ])->post($url); // Utilisation directe de $requestData

        if ($response->successful()) {
            $data = response()->json([
                'XReferenceId' => $uuid,
                'Status' => 'Api user created successfully',
                'StatusCode' => $response->status(),
                'Data' => $response->json()
            ]);
            $data = json_encode($data);
            return $data;
        } else {
            // Gérer l'erreur de manière appropriée
            // Vous pourriez lancer une exception personnalisée ici ou retourner une réponse d'erreur
            $data = json_encode(response()->json(['error' => 'La requête a échoué'], 500));
            return $data;
        }
    }
}
