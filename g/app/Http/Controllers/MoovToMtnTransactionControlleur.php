<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoovTransaction;
use App\Models\MtnTransaction;
use App\Services\Moov\TransfertMoovService;
use App\Services\Mtn\TransactionMtnService;
use Illuminate\Support\Facades\Auth;

class MoovToMtnTransactionControlleur extends Controller
{
    protected $transactionService;
    protected $transactionServiceMtn;
    /**
     * Create a new MoovToMtnTransactionControlleur instance.
     *
     * @return void
     */
    public function __construct(TransfertMoovService $transactionService, TransactionMtnService $transactionServiceMtn)
    {
        $this->transactionService = $transactionService;
        $this->transactionServiceMtn = $transactionServiceMtn;
    }

    public function performTransaction(Request $request)
    {
        // Valider les données de la requête
        $validatedData = $request->validate([
            'senderNumber' => 'required|string',
            'receiverNumber' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'message' => 'nullable|string',
        ]);

        $senderNumber = $validatedData["senderNumber"];
        $receiverNumber = $validatedData["receiverNumber"];
        $amount = $validatedData["amount"];
        $message = $validatedData["message"];

        // Vérifier si les numéros de l'expéditeur et du destinataire existent
        $senderInfo = $this->transactionService->getMobileStatus($senderNumber);
        $receiverInfo = $this->transactionService->getMobileStatus($receiverNumber);

        if (!$senderInfo['status'])
            return response()->json(['error' => 'Le numéro de l\'expéditeur n\'existe pas'], 400);
        if (!$receiverInfo['status'])
            return response()->json(['error' => 'Le numéro du destinataire n\'existe pas'], 400);
    
        //Checker le sold sur le compte Mtn de l'host
        $accountMtnBalance = $this->transactionServiceMtn->getAccountBallance($senderInfo['tokenForRequest'], $senderInfo['xReferenceId']);

        if ($accountMtnBalance['availableBalance'] < $amount) {
            return response()->json(['error' => 'Le solde du compte de l\'host est insuffisant'], 400);
        } else {
            // Effectuer le transfert
            $DepositResult = $this->transactionService->getCashByFlooz($senderNumber, $amount);
        }

        if ($DepositResult['success']) {
            // Effectuer le transfert depuis le compte mtn de l'host vers le receiverNumber
            $transactionResult = $this->transactionServiceMtn->requestToWithdraw();

            if ($transactionResult['statut code'] == 200) {
            $user = Auth::user();

            // Enregistrer la transaction dans la base de données
            MoovTransaction::create([
                'user_id' => $user->id,
                'senderNumber' => $senderNumber,
                'receiverNumber' => $receiverNumber,
                'amount' => $amount,
                'status' => 'success',
                'message' => $message,
                'type' => 'moov to mtn',
            ]);

                return response()->json(['message' => 'Transfert réussi', 'data' => $transactionResult['data']], 200);
            } else {
                // En cas d'échec, retourner un message d'erreur
                return response()->json(['error' => 'Échec du transfert', 'message' => $transactionResult['message']], 400);
            }
        } else {
            // En cas d'échec, retourner un message d'erreur
            return response()->json(['error' => 'Échec de la reception', 'message' => $DepositResult['message']], 400);
        }
    }
}
