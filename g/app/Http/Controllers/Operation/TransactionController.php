<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Mail\TransactionConfirmed;
use App\Models\HistoryTransaction;
use App\Models\TransactionNumber;
use App\Models\WebcoomHistory;
use App\Services\InitOperationService;
use App\Services\Moov\TransfertMoovService;
use App\Services\Mtn\TransactionMtnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;
use Ramsey\Uuid\Uuid;

/**
 * @OA\Tag(
 *     name="Transaction",
 *     description="Operations related to transactions"
 * )
 */

class TransactionController extends Controller
{
    protected $mtnservice, $init, $moovservice;

    public function __construct(TransactionMtnService $serviceMtn, InitOperationService $init, TransfertMoovService $moovservice)
    {
        $this->middleware("auth:api");
        $this->mtnservice = $serviceMtn;
        $this->init = $init;
        $this->moovservice = $moovservice;
    }
    /**
     * @OA\Post(
     *     path="/api/create-deposit",
     *     summary="Create a deposit transaction",
     *     tags={"Transaction"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "destination", "comptId", "message"},
     *             @OA\Property(property="amount", type="number", description="Amount for deposit"),
     *             @OA\Property(property="destination", type="string", description="Recipient's mobile number"),
     *             @OA\Property(property="comptId", type="integer", description="Account ID"),
     *             @OA\Property(property="message", type="string", description="Transaction message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deposit transaction created successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request, missing or invalid parameters",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string"))
     *     ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function createDeposite(Request $request)
    {
        $validation = Validator::make($request->all(),[
            "amount"=> "required|numeric",
            "destination"=> "required|numeric|digits:11",
            "comptId"=> "required|integer",
            "message"=>"required"
        ]);
        
        if ($validation->fails()) {
            $error = $validation->errors();
            return response()->json(["error"=>$error->getMessages()],400);
        }
        // Recuperer le numero operant
        $authNumber = TransactionNumber::where('user_id', auth()->user()->id)->where('id',$request->comptId)->value("phone_number");
        //Prefixe des numeros operant
        $ensemblePrefixesMtn = ['50', '51', '52', '53', '54', '56', '57', '59', '61', '62', '66', '67', '69', '90', '91', '96', '97'];
        $prefixesMoov = ['60','63', '64','65', '68', '87', '89', '92', '93', '94', '95', '87', '98'];

        // Extraction du préfixe
        $prefixeAuth = substr($authNumber, 3, 2);
        $prefixeDes = substr($request->destination, 3, 2);
        // dd( $authNumber,$prefixeAuth, $prefixeDes);

        $referenceId = $this->init->initTransaction();
        $history = HistoryTransaction::where("referenceId",$referenceId)->first();
        if (in_array($prefixeAuth, $ensemblePrefixesMtn) && in_array($prefixeDes, $ensemblePrefixesMtn)) {
        
            $checkAccount = $this->mtnservice->checkDisbursAccountBalance();
            // dd($checkAccount);
            if($checkAccount["availableBalance"] < $request->amount)
            {
                return response()->json(["message"=>"Votre operation ne peut pas aboutir. Reesayer plus tard."],202);
            }else{
                //Transfert de l'argent du compte de client vers compte marchant de webcoompay
                $payment = $this->mtnservice->requestToPay($authNumber, $referenceId, $request->amount, $request->message,);
                
                if ($payment['status'] == "SUCCESSFUL") {
                    // Mettre la table history à jour
                    $history->update(["amount"=>$request->amount,"description"=>$request->message,"destination"=>$request->destination]);
                    
                    $referenceIdDp = (Uuid::uuid4())->toString();
                    $opDp = $this->mtnservice->deposit($referenceIdDp, $request->amount, $request->destination, $request->message);
                    
                    dd($opDp);
                    return response()->json(["message"=>$opDp["message"]],$opDp['statusCode']);
                    if ($opDp['status'] == 200) {
                        //Creer un nv enregtrm de webcoom_transaction
                        WebcoomHistory::create(["referenceId"=>$referenceIdDp,"history_transaction_id"=>$history->id]);
                        
                        // Envoie de mail de confirmation de transfert
                        Mail::to(auth()->user()->email)->send(new TransactionConfirmed($request->amount, $request->destination, $request->message));
                    }
                    return response()->json(["message"=>"Transfert effectué pour $request->amount FCFA à $request->destination."],200);
                }elseif($payment['status'] == "FAILED"){
                    $history->delete();
                    return response()->json(["message"=> "Transfert Annulé. Veuillez vérifier votre fond."],306);
                }else{
                    $history->delete();
                    return response()->json(["message"=> "Transfert non validé."],306);
                } 
            }
            
        }
        if (in_array($prefixeAuth, $prefixesMoov) && in_array($prefixeDes, $prefixesMoov)) {
            $history->delete();
            return response()->json(["message"=>"Operation en cours de développement."],400);
        }
        if (in_array($prefixeAuth, $prefixesMoov) && in_array($prefixeDes, $ensemblePrefixesMtn)) {
            // Encaissement de l'argent du compt Moov de l'utilisateur actuel vers le compt marchand MOOV
            $history->delete();
            return response()->json(["message"=>"Operation en cours de développement."],400);
            // // Initier une transaction
            // $referenceId = $this->init->initTransaction();

            // // Enclencher le transfert de l'argent(amount*99/100=0.99) du compte marchant MTN vers le client MTN
            // $newAmount = $request->amount*0.99;
            // $returnMtn = $this->mtnservice->deposit($referenceId, $newAmount, $request->destination, $request->message);

            // // Mettre à jour la table historique des transaction
            // if ($returnMtn['status'] == 200) {
            //     # code...
            //     // echo "Mettre à jour la table historique avec mtn";
            // }
            // return response()->json(["message"=>"Transfert effectué pour $request->amount FCFA à $request->destination."],200);
        }
        if (in_array($prefixeAuth,$ensemblePrefixesMtn) && in_array($prefixeDes,$prefixesMoov)){
            // // Initier une transaction
            // $referenceId = $this->init->initTransaction();
            $history->delete();
            return response()->json(["message"=>"Operation en cours de développement."],400);
            // // Demander l'approbation de l'utilisateur actuel pour un paiement vers le compte marchant MTN
            // $app = $this->mtnservice->requestToPay($authNumber, $referenceId, $request->amount, $request->message);
            // // Cherker si l'operation s'est bien passée
            // if ($app["status"] == 200) {
            //     // Enclencher le transfert des 0.99 du montant approuvé vers le compt moov du client
            //     $newAmount = $request->amount*0.99;
            //     $tran = $this->moovservice->transfertFlooz($request->destination, $newAmount ,$referenceId);
            //     if ($tran["status"] == 200) {
            //         // echo "Mettre à jour la table historique avec moov info";
            //     }
            // }else{
            //     return response()->json(["error"=>"Un problème est survenu."],400);
            // }
            // return response()->json(["message"=> "Transfert effectué pour $request->amount FCFA à $request->destination."],202);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/get-user-transaction-history",
     *     summary="Get user's transaction history",
     *     tags={"Transaction"},
     *     @OA\Response(
     *         response=200,
     *         description="User's transaction history retrieved successfully"
     *     ),
     *     security={{ "apiAuth": {} }}
     * )
     */
    public function getUserTransactionHistorique()
    {
        $transHistorique = HistoryTransaction::where("user_id",auth()->user()->id)->get();
        return response()->json(["historyTran"=>$transHistorique],200);
    }
}
