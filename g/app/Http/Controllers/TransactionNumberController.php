<?php

namespace App\Http\Controllers;

use App\Models\HistoryTransaction;
use App\Models\OperatorMobile;
use App\Models\TransactionNumber;
use App\Repositories\Transaction\TransactionNumberRepository;
use App\Services\FasterService;
use App\Services\InitOperationService;
use App\Services\Moov\TransfertMoovService;
use App\Services\Mtn\TransactionMtnService;
use Illuminate\Http\Request;
use Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Transaction Numbers",
 *     description="Manage your Transaction Number"
 * )
 */
class TransactionNumberController extends Controller
{
    protected $transactionNumber, $faster, $mtn, $moov;
    protected $init;
    public function __construct(TransactionNumberRepository $transactionNumber,
    FasterService $fasterServive, TransactionMtnService $transactionMtnService,
    InitOperationService $initOperationService, TransfertMoovService $transfertMoovService)
    {
        $this->middleware("auth:api");
        $this->transactionNumber = $transactionNumber;
        $this->faster = $fasterServive;
        $this->mtn = $transactionMtnService;
        $this->init = $initOperationService;
        $this->moov = $transfertMoovService;
    }
    /**
     * Display a listing of the resource.
     */
    
    /**
 * @OA\Get(
 *     path="/api/transaction-numbers",
 *     summary="Récupérer tous les numéros de transaction de l'utilisateur",
 *     tags={"Transaction Numbers"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des numéros de transaction de l'utilisateur",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé, utilisateur non connecté",
 *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string", description="Message d'erreur"))
 *     )
 * )
 */
    public function index()
    {
        return $this->transactionNumber->getAllUserTransactionNumber();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/add-transaction-number",
     *     summary="Ajouter un nouveau numéro de transaction",
     *     tags={"Transaction Numbers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone_number", type="string", format="numeric", description="Numéro de téléphone"),
     *             @OA\Property(property="operator_mobile_id", type="integer", description="ID de l'opérateur mobile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Numéro de transaction ajouté avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", description="Message de succès"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête incorrecte, des erreurs de validation sont présentes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string", description="Liste des erreurs de validation"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé, utilisateur non connecté",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string", description="Message d'erreur"))
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "phone_number"=> "required|numeric|digits:11|",
            "operator_mobile_id"=> "required|integer",
        ]);
        if ($validator->fails())
        {
            $errors = $validator->errors()->all();
            return response()->json(['errors' => $errors], 400);
        }
        /** les prefixes mobiles */
        $ensemblePrefixesMtn = ['50', '51', '52', '53', '54', '56', '57', '59', '61', '62', '66', '67', '69', '90', '91', '96', '97'];
        $prefixesMoov = ['60','63', '64', '68', '87', '89', '92', '93', '94', '95', '87', '98'];

        // Extraction du préfixe
        $prefixe = substr($request->phone_number, 3, 2);
        //Chercher l'operateur selectionné
        $opratorName = OperatorMobile::findOrfail($request->operator_mobile_id);
        //Initiate operation
        $referenceId = $this->init->initTransaction();
        

        if (in_array($prefixe, $ensemblePrefixesMtn) && $opratorName->label == 'MTN') {
            
            // Vérifier si le numero est operationnel pour les transactions
            // $verifyNum = $this->mtn->getUserBasicInfo($request->phone_number);
            // dd($verifyNum);
            // if ($verifyNum["sub"] == 1) {

            //     //Enclancher le processus de prélévement de 1F symbolique
            //     $amount = 1; $message = "Entrer votre code momo pour paiement de 1 fcfa d'enregistrement de votre numéro.";
            //     $mtnReturn = $this->mtn->requestToPay($request->phone_number, $referenceId,$amount,$message);
                
            //     /**
            //      * On verifie s'il a approuvé le code et que le prelevement a été effectif
            //      * On passe à l'enregistrement du numéro
            //      */
            //     if ($mtnReturn["status"] == 200) {
            //         $this->transactionNumber->createUserTransactionNumber($request->all(),$verifyNum);
            //         return response()->json(["message"=>"Transaction number was succefully added."]);
            //     }
            // }else{
            //     return response()->json(["message"=> "$request->phone_number unauthorize to operate."]);
            // }
            //Enclancher le processus de prélévement de 1F symbolique

            //Verifier si le numero exist déjà dans la base de donnée
            $phoneExist = TransactionNumber::where("phone_number", "=", $request->phone_number)->where("is_active", false)->first();

            $amount = 1; $message = "Enregistrement"; 
            $regisHistory = HistoryTransaction::where('referenceId',$referenceId)->first();

            if ($phoneExist) {
                $mtnReturn = $this->mtn->requestToPay($request->phone_number, $referenceId, $amount, $message);

                if ($mtnReturn["status"] == "SUCCESSFUL") {
                    $phoneExist->update(["is_active" => true]);
                    $regisHistory->update([
                        "description"=>"Activation du numéro $request->phone_number",
                        "destination"=>"webcoom",
                        "amount"=>$amount
                    ]);
                    return response()->json(["message" => "Le numéro de transaction activé avec succès."], 200);
                } elseif ($mtnReturn["status"] == "FAILED") {
                    $regisHistory->delete();
                    return response()->json(["message" => "Operation échoué."], 203);
                } elseif ($mtnReturn["status"] == "PENDING") {
                    $regisHistory->delete();
                    return response()->json(["message" => "Operation suspendu. Veuillez reesayer."], 300);
                } else {
                    $regisHistory->delete();
                    return response()->json(["message" => "Votre operation ne peut pas aboutir. Veuillez reesayer."], 300);
                }
            } else {
                $existingActiveNumber = TransactionNumber::where("phone_number", "=", $request->phone_number)->where("is_active", true)->first();

                if ($existingActiveNumber) {
                    // Le numéro existe et est déjà actif, ne déclenche pas le paiement
                    $regisHistory->delete();
                    return response()->json(["message" => "Le numéro de transaction existe et operationnel. Paiement non effectué."], 200);
                }

                // Le numéro n'existe pas, crée un nouvel enregistrement
                $mtnReturn = $this->mtn->requestToPay($request->phone_number, $referenceId, $amount, $message);
                
                if ($mtnReturn["status"] == "SUCCESSFUL") {
                    $this->transactionNumber->createUserTransactionNumber($request->all());
                    $regisHistory->update([
                        "description"=>"$message du numéro $request->phone_number",
                        "destination"=>"webcoom",
                        "amount"=>$amount
                    ]);
                    return response()->json(["message" => "Le numero de transaction enregistré avec succès."], 200);
                } elseif ($mtnReturn["status"] == "FAILED") {
                    $regisHistory->delete();
                    return response()->json(["message" => "Operation échoué."], 300);
                } elseif ($mtnReturn["status"] == "PENDING") {
                    $regisHistory->delete();
                    return response()->json(["message" => "Operation suspendu. Veuillez reesayer."], 300);
                } else {
                    $regisHistory->delete();
                    return response()->json(["message" => "Votre operation ne peut pas aboutir. Veuillez reesayer."], 400);
                }
            }

            
        } elseif (in_array($prefixe, $prefixesMoov) && $opratorName->label == 'MOOV') {
            //Enclancher le processus de prélévement de 1F symbolique
            $amount = 1; $message = "Enregistrement";
            $moovReturn = $this->moov->pushTransaction($request->phone_number, $amount, $message,$referenceId);
            dd($moovReturn);
            /**
             * On verifie s'il a approuvé le code et que le prelevement a été effectif
             * On passe à l'enregistrement du numéro
             */
            // if ($mtnReturn) {
            //     $this->transactionNumber->createUserTransactionNumber($request->all());
            //     return response()->json(["message"=>"Transaction number was succefully added."],201);
            // }
            return response()->json(["message"=>"Opération en développement."],202);
        } else {
            return response()->json(['message' => 'Le numéro n\'est pas valide pour l\'operateur selectionné.'], 400);
        }

    }

    /**
     * Display the specified resource.
     */

     /**
     * @OA\Post(
     *     path="/api/generate-code-after-save",
     *     summary="Générer un code après avoir enregistré le numéro de transaction",
     *     tags={"Transaction Numbers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone_number", type="string", format="numeric", description="Numéro de téléphone")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code généré avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="code_verify", type="integer", description="Code de vérification"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé, utilisateur non connecté",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string", description="Message d'erreur"))
     *     )
     * )
     */
    public function generateCodeAfterSave(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "phone_number"  => "required|numeric|digits:11",
        ]);
        if ($validator->fails()) {
            $error = $validator->errors();
            return response()->json(["error"=>$error],401);
        }
        //Envoyer de code
        $randomCode = random_int(1000,9999);
        $this->faster->sendeCode($randomCode,$request->phone_number, 'number');

        return response()->json(["code_verify"=>$randomCode],200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/update-transaction-number/{id}",
     *     summary="Mettre à jour le numéro de transaction",
     *     tags={"Transaction Numbers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du numéro de transaction",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone_number", type="string", format="numeric", description="Numéro de téléphone"),
     *             @OA\Property(property="operator_mobile_id", type="integer", description="ID de l'opérateur mobile")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Numéro de transaction mis à jour avec succès",
     *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", description="Message de succès"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requête incorrecte, des erreurs de validation sont présentes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string", description="Liste des erreurs de validation"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé, utilisateur non connecté",
     *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string", description="Message d'erreur"))
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        
        $validator = Validator::make($request->all(),[
            "phone_number" => "required|numeric|digits:11",
            "operator_mobile_id"=> "required|integer",
        ]);
        
        if ($validator->fails())
        {
            $errors = $validator->errors()->all();
            return response()->json(['errors' => $errors], 400);
        }
        // dd('ok');
        $this->transactionNumber->updateUserTransactionNumber($id,$request->all());
        return response()->json(["message"=> "Your transaction number updated."],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
 * @OA\Delete(
 *     path="/api/delete-transaction-number/{id}",
 *     summary="Supprimer le numéro de transaction",
 *     tags={"Transaction Numbers"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du numéro de transaction",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Numéro de transaction supprimé avec succès",
 *         @OA\JsonContent(type="object", @OA\Property(property="message", type="string", description="Message de succès"))
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé, utilisateur non connecté",
 *         @OA\JsonContent(type="object", @OA\Property(property="error", type="string", description="Message d'erreur"))
 *     )
 * )
 */
    public function destroy(string $id)
    {
        $this->transactionNumber->desactivateUserTransactionNumber($id);
        return response()->json(['message'=> 'Votre numéro de transaction est supprimé avec succès.'],200);
    }
}
