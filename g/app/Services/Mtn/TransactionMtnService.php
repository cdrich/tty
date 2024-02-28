<?php

namespace App\Services\Mtn;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;
use App\Services\Mtn\AuthMtnService;
use PhpParser\Node\Expr\Print_;
use PhpParser\Node\Stmt\Else_;
use Ramsey\Uuid\Uuid;

class TransactionMtnService
{
    protected $url, $token, $client, $tokenD, $auth;
    private $baseUrl;
    public function __construct(AuthMtnService $auth)
    {
        $this->url = 'https://proxy.momoapi.mtn.com';
        $this->auth = $auth;
        $this->baseUrl = env('BASE_URL_MTN');
    }

    /** 
     * Accepter un paiement chez un utilisateur
     *  $partyId : numeros du destinataire type:string , $amount : montant a retirer ,type:string ; $message : message suivit de la requete ,tpye : string.
     */
    // public function requestToPay($partyId, $amount, $message)
    public function requestToWithdraw()
    {
        $partyId = "69424709";
        $amount = "200";
        $message = "string";
        $tokenAndId = $this->auth->getMtnBarrearToken();
        $bearerToken = 'Bearer ' . $tokenAndId['Data']['access_token'];
        $xreferenceid = $tokenAndId["XReferenceId"];
        $appEnvMode = env("APP_ENV_MODE");
        if ($appEnvMode == "sandbox") {
            $currency = "EUR";
        } else {
            $currency = "XOF";
        }
        // return $currency;

        $body = [
            "amount" => $amount, // montant de l'operation
            "currency" => $currency, // la devise de l'operation
            "externalId" => $xreferenceid, // reference
            "payer" => [
                "partyIdType" => "MSISDN",
                "partyId" => $partyId // le numero du client qui paie
            ],
            "payerMessage" => $message,
            "payeeNote" => $message
        ];

        try {
            $url = $this->baseUrl . "/collection/v1_0/requesttowithdraw";
            // return $tokenAndId;
            $response = Http::withHeaders([
                'X-Reference-Id' => $xreferenceid,
                'X-Target-Environment' => 'sandbox',
                'Ocp-Apim-Subscription-Key' => env('COLLECTION_OCP_PRIMARY'),
                'Content-Type' => 'application/json',
                'Authorization' => $bearerToken
            ])->post($url, $body);
            // return $response;

            // return "hi";
            if ($response->getStatusCode() == 202) {
                sleep(15);
                json_encode($response);
                // return $response;
                $status = $this->getPaymentStatus($xreferenceid, $bearerToken);
                $requestToPayResponse = [
                    "message" => "you make withdraw money successfuly",
                    "data" => [
                        "financialTransactionId" => $status["financialTransactionId"],
                        "referenceId" => $status["referenceId"],
                        "status" => $status["status"],
                    ],
                    "statut code" => $response->getStatusCode(),
                ];
                json_encode($requestToPayResponse);
                return $requestToPayResponse;
            }
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }
    // /** 
    //  * Verifier le solde du compte 
    //  */
    public function getAccountBallance()
    {
        $tokenAndId = $this->auth->getMtnBarrearToken();
        $bearerToken = 'Bearer ' . $tokenAndId['Data']['access_token'];
        // return $tokenAndId;
        try {
            $url = $this->baseUrl . "/collection/v1_0/account/balance";
            // return $url;
            $response = Http::withHeaders([
                'X-Target-Environment' => 'sandbox',
                'Ocp-Apim-Subscription-Key' => env('COLLECTION_OCP_PRIMARY'),
                'Content-Type' => 'application/json',
                'Authorization' => $bearerToken
            ])->get($url);

            return $response;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    // /** 
    //  * Get payment status
    //  * Verifier qu'un paiement est effectif
    //  */
    private function getPaymentStatus($xreferenceid, $bearerToken)
    {
        // $tokenAndId = $this->auth->getMtnBarrearToken();
        // $bearerToken = 'Bearer ' . $tokenAndId['Data']['access_token'];
        // $xreferenceid = $tokenAndId["XReferenceId"];
        try {
            $url = $this->baseUrl . "/collection/v2_0/payment/" . $xreferenceid;
            // return $url;
            $response = Http::withHeaders([
                'X-Reference-Id' => $xreferenceid,
                'X-Target-Environment' => 'sandbox',
                'Ocp-Apim-Subscription-Key' => env('COLLECTION_OCP_PRIMARY'),
                'Content-Type' => 'application/json',
                'Authorization' => $bearerToken
            ])->get($url);

            return $response;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    private function getDepositStatus($xreferenceid, $bearerToken)
    {
        // $tokenAndId = $this->auth->getMtnBarrearToken();
        // $bearerToken = 'Bearer ' . $tokenAndId['Data']['access_token'];
        // $xreferenceid = $tokenAndId["XReferenceId"];
        try {
            $url = $this->baseUrl . "/collection/v2_0/payment/" . $xreferenceid;
            $response = Http::withHeaders([
                'X-Reference-Id' => $xreferenceid,
                'X-Target-Environment' => env("APP_ENV_MODE"),
                'Ocp-Apim-Subscription-Key' => env('COLLECTION_OCP_PRIMARY'),
                'Content-Type' => 'application/json',
                'Authorization' => $bearerToken
            ])->get($url);

            return $response;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function checkDisbursAccountBalance()
    {
        $client = new Client();
        $headers = [
            'headers' => [
                'X-Target-Environment' => env('APP_MTN_ENV_MODE'),
                'Ocp-Apim-Subscription-Key' => env('D_MTN_OPC_APIM_SUB_KEY'),
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer " . $this->tokenD
            ]
        ];

        $response = $client->get($this->url . "/disbursement/v1_0/account/balance", $headers);

        // $response = $client->getResponse();

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get user info
     * $accountHolderMSISDN : numeros a verifier , type :string
     */
    public function getUserBasicInfo($accountHolderMSISDN)
    {
        // return "hey you start";

        //  $tokenForRequest =
        $tokenAndId = $this->auth->getMtnBarrearToken();
        $bearerToken = 'Bearer ' . $tokenAndId['Data']['access_token'];
        $xreferenceid = $tokenAndId["XReferenceId"];
        // $accountHolderMSISDN = "69424709";

        try {
            $url = $this->baseUrl . "/collection/v1_0/accountholder/msisdn/" . $accountHolderMSISDN . "/basicuserinfo";

            $response = Http::withHeaders([
                'X-Reference-Id' => $xreferenceid,
                'X-Target-Environment' => 'sandbox',
                'Ocp-Apim-Subscription-Key' => env('COLLECTION_OCP_PRIMARY'),
                'Content-Type' => 'application/json',
                'Authorization' => $bearerToken
            ])->get($url);
            return $response;
        } catch (\Throwable $th) {
            throw $th;
        }
        // $headers = [
        //     'headers' => [
        //         'X-Target-Environment' => env('APP_MTN_ENV_MODE'),
        //         'Ocp-Apim-Subscription-Key' => env('MTN_OPC_APIM_SUB_KEY'),
        //         'Content-Type' => 'application/json',
        //         'Authorization' => "Bearer " . $this->token,
        //     ],
        // ];
        // dd($headers, $accountHolderMSISDN);
        // try {
        //     $url = sprintf('https://sandbox.momodeveloper.mtn.com/collection/v1_0/accountholder/msisdn/%s/basicuserinfo', $accountHolderMSISDN);
        //     $response = $this->client->post($url, $headers);
        //     return $response->getBody();
        // } catch (RequestException $e) {
        //     // Gérer les erreurs (par exemple, journalisation, traitement d'erreur, etc.)
        //     return  ['error' => $e->getMessage()];
        // }
        // $url = sprintf($this->url . '/collection/v1_0/accountholder/msisdn/%s/basicuserinfo', $accountHolderMSISDN);
        // $response = $this->client->get($url, $headers);
        // return json_decode($response->getBody()->getContents(), true);
    }
    /**
     * Envoyer de l'argent vers un numero mtn
     * $accountHolderMSISDN : numeros a verifier , type :string
     */

    // public function deposit($uuid, $collectionOcp, $message)
    public function deposit()
    {
        $partyId = "69424709";
        $amount = "200";
        $message = "string";
        $tokenAndId = $this->auth->getMtnBarrearTokenForDisbursement();
        $bearerToken = 'Bearer ' . $tokenAndId['Data']['access_token'];
        $xreferenceid = $tokenAndId["XReferenceId"];
        $appEnvMode = env("APP_ENV_MODE");
        if ($appEnvMode == "sandbox") {
            $currency = "EUR";
        } else {
            $currency = "XOF";
        }
        // return $currency;

        $body = [
            "amount" => $amount, // montant de l'operation
            "currency" => $currency, // la devise de l'operation
            "externalId" => $xreferenceid, // reference
            "payer" => [
                "partyIdType" => "MSISDN",
                "partyId" => $partyId // le numero du client qui paie
            ],
            "payerMessage" => $message,
            "payeeNote" => $message
        ];

        try {
            $collectionOcp = env('DISBURSEMENT_OCP_PRIMARY');
            // return $collectionOcp;
            $url = $this->baseUrl . "/disbursement/v1_0/deposit";
            // return $url;
            $response = Http::withHeaders([
                'X-Reference-Id' => $xreferenceid,
                'X-Target-Environment' => 'sandbox',
                'Ocp-Apim-Subscription-Key' => $collectionOcp,
                'Content-Type' => 'application/json',
                'Authorization' => $bearerToken
            ])->post($url, $body);
            // return $response;

            // return "hi";
            if ($response->getStatusCode() == 202) {
                sleep(15);
                json_encode($response);
                // return $response;
                $status = $this->getDepositStatus($xreferenceid, $bearerToken);
                $requestToPayResponse = [
                    "message" => "you make withdraw money successfuly",
                    "data" => [
                        "financialTransactionId" => $status["financialTransactionId"],
                        "referenceId" => $status["referenceId"],
                        "status" => $status["status"],
                    ],
                    "statut code" => $response->getStatusCode(),
                ];
                json_encode($requestToPayResponse);
                return $requestToPayResponse;
            }
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }
}
