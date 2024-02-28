<?php

namespace App\Services\Mtn;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DepositTransaction;
use Ramsey\Uuid\Uuid;

use Illuminate\Support\Facades\Http;
use League\CommonMark\Environment\Environment;
use League\OAuth1\Client\Credentials\Credentials;

class AuthMtnService
{
    protected $url;
    public function __construct()
    {
        $this->url = 'https://sandbox.momodeveloper.mtn.com/collection/token/';
    }
    private function apiRequest($collectionOcp, $requestData, $uuid, $route)
    {
        $baseUrl = "https://sandbox.momodeveloper.mtn.com";
        $url = $baseUrl . $route;

        try {
            $response = Http::withHeaders([
                'X-Reference-Id' => $uuid,
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $collectionOcp,
            ])->post($url, $requestData);

            if ($response->successful()) {
                // Retourne la réponse JSON reçue
                return $response->json();
            } else {
                // Retourne une réponse JSON avec un message d'erreur et le code d'erreur
                return response()->json(['error' => 'La requête a échoué'], $response->status());
            }
        } catch (\Exception $e) {
            // Retourne une réponse JSON avec un message d'erreur interne et le code d'erreur 500
            return response()->json(['error' => 'Erreur lors de la requête : ' . $e->getMessage()], 500);
        }
    }

    private function generateUuid()
    {
        try {
            $uuid4 = Uuid::uuid4();
            return $uuid4->toString();
        } catch (\Exception $e) {
            // En cas d'erreur lors de la génération de l'UUID, retourner une chaîne vide
            return '';
        }
    }

    private function createApiUser($collectionOcp, $uuid)
    {
        $requestData = [
            "providerCallbackHost" => "string"
        ];

        $apiUser =
            $this->apiRequest($collectionOcp, $requestData, $uuid, "/v1_0/apiuser");

        $apiUser = json_decode($apiUser, true);

        return $uuid;
    }

    private function generateCredential($uuid, $collectionOcp)
    {
        $route = "/v1_0/apiuser/" . $uuid . "/apikey";

        try {
            // Appel à la fonction apiRequest pour obtenir les informations nécessaires
            $response = $this->apiRequest($collectionOcp, "", $uuid, $route);

            // Vérifier si les données sont valides
            if (isset($uuid) && isset($response["apiKey"])) {
                $concat = $uuid . ":" . $response["apiKey"];
                $credentials = base64_encode($concat);
                return $credentials;
            } else {
                // Si les données nécessaires ne sont pas présentes, retourner une chaîne vide
                return '';
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une chaîne vide
            return '';
        }
    }

    private function getApiToken($uuid, $credential, $collectionOcp, $routes)
    {
        try {
            $authorization = "Basic " . $credential;

            // Appel à la fonction getTokenRequest pour obtenir le token
            $tokenResponse = $this->getTokenRequest($uuid, $collectionOcp, $authorization, $routes);

            // Vérification de la réponse
            if ($tokenResponse) {
                return $tokenResponse;
            } else {
                // Si la réponse n'est pas valide, retourner une valeur par défaut ou une erreur appropriée
                return ['error' => 'La réponse de la requête du token est invalide'];
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une valeur par défaut ou une erreur appropriée
            return ['error' => 'Erreur lors de la récupération du token : ' . $e->getMessage()];
        }
    }

    private function getTokenRequest($uuid, $collectionOcp, $authorization, $route)
    {
        $baseUrl = "https://sandbox.momodeveloper.mtn.com";
        // $route = "/collection/token/";
        $url = $baseUrl . $route;
        // Assurez-vous d'utiliser le paramètre $collectionOcp passé à la fonction

        $response = Http::withHeaders([
            'Authorization' => $authorization,
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => $collectionOcp, // Utilisation du paramètre
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

    public function getMtnBarrearToken()
    {
        try {
            if (env("APP_ENV_MODE") == "sandbox") {
                $collectionOcp = env('COLLECTION_OCP_PRIMARY');
                $uuid = $this->generateUuid();
                $this->createApiUser($collectionOcp, $uuid);
                $credential = $this->generateCredential($uuid, $collectionOcp);
            } else if (env("APP_ENV_MODE") == "production") {
                $env = env("MTN_ENV_MODE");
                $concat = env("MTN_API_USER") . ":" . env("MTN_API_KEY");
                $credentials = base64_encode($concat);
            }
            if (isset($credential)) {
                $bearerToken = $this->getApiToken($uuid, $credential, $collectionOcp, "/collection/token/");
                // Validation du token et décoder le JSON
                $decodedToken = json_decode($bearerToken, true);
                // Construction des données à retourner
                if ($decodedToken === null) {
                    throw new \Exception("Erreur lors du décodage du token.");
                }

                return $decodedToken['original'];
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retourne une réponse JSON avec le message d'erreur
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getMtnBarrearTokenForDisbursement()
    {
        try {
            if (env("APP_ENV_MODE") == "sandbox") {
                $collectionOcp = env('DISBURSEMENT_OCP_PRIMARY');
                $uuid = $this->generateUuid();
                $this->createApiUser($collectionOcp, $uuid);
                $credential = $this->generateCredential($uuid, $collectionOcp);
            } else if (env("APP_ENV_MODE") == "production") {
                $env = env("MTN_ENV_MODE");
                $collectionOcp = env('PROD_COLLECTION_OCP_PRIMARY');
                $uuid = env("MTN_API_USER");
                $concat = env("MTN_API_USER") . ":" . env("MTN_API_KEY");
                $credential = base64_encode($concat);
                return $credential;
            }
            if (isset($credential)) {
                $bearerToken = $this->getApiToken($uuid, $credential, $collectionOcp, "/disbursement/token/");
                // Validation du token et décoder le JSON
                $decodedToken = json_decode($bearerToken, true);
                // Construction des données à retourner
                if ($decodedToken === null) {
                    throw new \Exception("Erreur lors du décodage du token.");
                }

                return $decodedToken['original'];
            }
        } catch (\Exception $e) {
            // En cas d'erreur, retourne une réponse JSON avec le message d'erreur
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
