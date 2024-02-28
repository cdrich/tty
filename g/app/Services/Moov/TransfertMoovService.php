<?php 
namespace App\Services\Moov;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransfertMoovService 
{
    
    protected $url_base;
    public function __construct()
    {

        $url_base = 'https://testapimarchand2.moov-africa.bj:2010/com.merchant.api/UssdPush';
        if(env("APP_ENV_MODE") == "sandbox") {
            $this->url_base = 'https://testapimarchand2.moov-africa.bj:2010/com.merchant.api/UssdPush';

        }else{
            $this->url_base = 'https://apimarchand.moov-africa.bj/com.tlc.merchant.api/UssdPush';
        }
    }
    public function transfertFlooz($msisdnThatReceive, $amount, $referenceid)
    {
       $token = env('MOOV_TOKEN') ;
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-16\"?>
        <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:api=\"http://api.merchant.tlc.com/\">
            <soapenv:Header/>
            <soapenv:Body>
                    <api:transferFlooz>
                    <token>{$token}</token>
                        <request>
                            <destination>{$msisdnThatReceive}</destination>
                            <amount>{$amount}</amount>
                            <referenceid>{$referenceid}</referenceid>
                            <walletid>0</walletid>
                            <extendeddata>remarks</extendeddata>
                        </request>
                    </api:transferFlooz>
            </soapenv:Body>
        </soapenv:Envelope>";

        //Effectuer la requette vers l'API
        $apiUrl =env('BASE_URL_MOOV'); 
        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Content-Type' => 'text/xml; charset=UTF8',
        ])->send('POST', $apiUrl, [
            'body' => $xmlRequest,
        ]);
        if ($response->successful()) {
            $xmlContent = $response->body();
            print($xmlContent);
            $xpathQueries = [
                'Status' => '//status',
                'TransactionID' => '//transactionid',
                'Message' => '//message',
                'Referenceid' => '//referenceid',
                'SenderKeycost' => '//senderkeycost',
                'SenderBonus' => '//senderbonus',
                'SenderBalanceafter' => '//senderbalancebefore',
                'SenderBalancebefore' => '//senderbalanceafter',
            ];
                 
            try {
                $data = $this->parseXmlContent($xmlContent, $xpathQueries);
                return response()->json(['success' => true, 'data' => $data]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else { 
            return response()->json(['success' => false, 'message' => 'Request failed.']);
        }
       
      

    }

    /**
     * Push Transfer
     */
    public function getTransferStatus($referenceId)
    {
        $xmlData = '<?xml version="1.0" encoding="utf-16"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:api="http://api.checktransaction.tlc.com/">
            <soapenv:Header/>
                <soapenv:Body>
                    <api:getTransactionStatus>
                        <token>'.env("MOOV_TOKEN").'</token>
                        <request>
                            <transid>'.$referenceId.'</transid>
                        </request>
                    </api:getTransactionStatus>
        </soapenv:Envelope>';

        try {
            $result_soap = Http::withOptions([
                'verify'=> false,
            ])->withHeader('Content-Type','application/xml',)
            ->withBody($xmlData,'text/xml;charset=utf-8')
                ->post($this->url_base);

            $result = $result_soap->getBody()->getContents();
            $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $result);
            $xml = new \SimpleXMLElement($response);
            $array = json_decode(json_encode((array)$xml));
            $response_json_soap = $array->soapBody->ns2getTransactionStatusResponse->result;
            return $response_json_soap ;
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return false;
        }
    }

    public function pushTransaction($msisdn, $amount, $message, $referenceId)
    {
        $token = env('MOOV_TOKEN') ;
        $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-16\"?>
        <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:api=\"http://api.merchant.tlc.com/\">
            <soapenv:Header/>
            <soapenv:Body>
                    <api:Push>
                        <token>{$token}</token>
                        <msisdn>{$msisdn}</msisdn>
                        <message>Veuillez entrer votre code moov pour '.$message.' le numero.</message>
                        <amount>{$amount}</amount>
                        <externaldata1>{$referenceId}</externaldata1>
                        <externaldata2>{$amount}</externaldata2>
                        <fee>0</fee>
                    </api:Push>
            </soapenv:Body>
            </soapenv:Envelope>";

        //Effectuer la requette vers l'API
        $apiUrl =env('BASE_URL_MOOV'); 
        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Content-Type' => 'text/xml; charset=UTF8',
        ])->send('POST', $apiUrl, [
            'body' => $xmlRequest,
        ]);
        if ($response->successful()) {
            $xmlContent = $response->body();
            print($xmlContent);
            // $xpathQueries = [
            //     'Accounttype' => '//accounttype',
            //     'Dateofbirth' => '//dateofbirth',
            //     'Firstname' => '//firstname',
            //     'Idnumber' => '//idnumber',
            //     'Idtype' => '//idtype',
            //     'Message' => '//message',
            //     'Lastname' => '//lastname',
            //     'Msisdn' => '//msisdn',
            //     'secondname' => '//secondname',
            //     'status' => '//status',
            //     'subscriberstatus' => '//subscriberstatus',
            // ];
                 
            try {
                // $data = $this->parseXmlContent($xmlContent, $xpathQueries);
                // return response()->json(['success' => true, 'data' => $data]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else { 
            return response()->json(['success' => false, 'message' => 'Request failed.']);
        }
    }

   public function getBalance($msisdn)
    {
        $token = env('MOOV_TOKEN') ;
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

        $apiUrl =env('BASE_URL_MOOV'); 

        // Envoyer la requête à l'API
        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Content-Type' => 'text/xml; charset=UTF8',
        ])->send('POST', $apiUrl, [
            'body' => $xmlRequest,
        ]);

        if ($response->successful()) {
            $xmlContent = $response->body();
            $xpathQueries = [
                'Balance' => '//balance',
                'Message' => '//message',
                'Status' => '//status',
            ];

            try {
                $data = $this->parseXmlContent($xmlContent, $xpathQueries);
                return response()->json(['success' => true, 'data' => $data]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Request failed.']);
        }
    }


    public function getMobileStatus($msisdn)
    {
       $token = env('MOOV_TOKEN') ;
       $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-16\"?>
        <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:api=\"http://api.merchant.tlc.com/\">
            <soapenv:Header/>
            <soapenv:Body>
                <api:getMobileAccountStatus>
                    <token>{$token}</token> 
                     <request>
                        <msisdn>{$msisdn}</msisdn>
                    </request>
                </api:getMobileAccountStatus>
            </soapenv:Body>
        </soapenv:Envelope>";
        //Effectuer la requette vers l'API
        $apiUrl =env('BASE_URL_MOOV'); 
        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Content-Type' => 'text/xml; charset=UTF8',
        ])->send('POST', $apiUrl, [
            'body' => $xmlRequest,
        ]);

        if ($response->successful()) {
            $xmlContent = $response->body();
            // print($xmlContent);
            $xpathQueries = [
                'Accounttype' => '//accounttype',
                'Dateofbirth' => '//dateofbirth',
                'Firstname' => '//firstname',
                'Idnumber' => '//idnumber',
                'Idtype' => '//idtype',
                'Message' => '//message',
                'Lastname' => '//lastname',
                'Msisdn' => '//msisdn',
                'secondname' => '//secondname',
                'status' => '//status',
                'subscriberstatus' => '//subscriberstatus',
            ];
                 
            try {
                $data = $this->parseXmlContent($xmlContent, $xpathQueries);
                return response()->json(['success' => true, 'data' => $data]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else { 
            return response()->json(['success' => false, 'message' => 'Request failed.']);
        }
    }

    public function getCashByFlooz($cashintrans,$amount)
    {

       $token = env('MOOV_TOKEN') ;
       $xmlRequest = "<?xml version=\"1.0\" encoding=\"utf-16\"?>
        <soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:api=\"http://api.merchant.tlc.com/\">
            <soapenv:Header/>
            <soapenv:Body>
            <api:PushWithPending> 
                <token>{$token}</token> 
                <msisdn>".$cashintrans."</msisdn>
                <message>Paiement de 500 FCFA au marchand acc_3670216780 . Veuillez
                saisir votre code secret pour confirmer ou tapez 0 pour annuler.
                </message> 
                <amount>{$amount}</amount>
                <externaldata1>
                pi_NyM_1642619082990
                </externaldata1> 
                <externaldata2>
                pi_NyM_1642619082990
                </externaldata2> 
                <fee>0</fee>
            </api:PushWithPending>
            </soapenv:Body>
        </soapenv:Envelope>";

       //Effectuer la requette vers l'API
        $apiUrl =env('BASE_URL_MOOV'); 
        $response = Http::withOptions([
            'verify' => false,
        ])->withHeaders([
            'Content-Type' => 'text/xml; charset=UTF8',
        ])->send('POST', $apiUrl, [
            'body' => $xmlRequest,
        ]);

        if ($response->successful()) {
            $xmlContent = $response->body();
            print($xmlContent);
            $xpathQueries = [
                'Description' => '//description',
                'Referenceid' => '//referenceid',
                'Status' => '//status',
                'transid' => '//transid',
            ];
                 
            try {
                $data = $this->parseXmlContent($xmlContent, $xpathQueries);
                return response()->json(['success' => true, 'data' => $data]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        } else { 
            return response()->json(['success' => false, 'message' => 'Request failed.']);
        }

    }
  
    public function parseXmlContent($xmlContent, $xpathQueries)
    {
        $data = [];
        try {
            $xml = new \SimpleXMLElement($xmlContent);
            foreach ($xpathQueries as $key => $xpathQuery) {
                $result = $xml->xpath($xpathQuery);
                if ($result && !empty($result)) {
                    $data[$key] = (string)$result[0];
                } else {
                    // Si le résultat est vide ou le XPath ne correspond à aucun élément
                    $data[$key] = null;
                }
            }
            return $data;
        } catch (\Exception $e) {
            // Gestion des erreurs de parsing XML
            throw new \Exception('Failed to parse XML. Error: ' . $e->getMessage());
        }
    }


}
