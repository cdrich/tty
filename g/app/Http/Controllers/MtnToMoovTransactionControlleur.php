<?php

namespace App\Http\Controllers;
use App\Models\MoovTransaction;
use App\Models\MtnTransaction;
use App\Services\Moov\TransfertMoovService;
use App\Services\Mtn\TransactionMtnService;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class MtnToMoovTransactionControlleur extends Controller
{
    protected $transactionService;
    protected $transactionServiceMoov;
    /**
     * Create a new MtnToMoovTransactionControlleur instance.
     *
     * @return void
     */

    public function __construct(TransactionMtnService $transactionService, TransfertMoovService $transactionServiceMoov)
    {
        $this->transactionService = $transactionService;
        $this->transactionServiceMoov = $transactionServiceMoov;
    }

    public function performTransaction(Request $request)
    {
        // Valider les données de la requête
        $validatedData = $request->validate([
            'senderNumber' => 'required|string',
            'receiverNumber' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'message' => 'string',
        ]);

        $senderNumber = $validatedData["senderNumber"];
        $receiverNumber = $validatedData["receiverNumber"];
        $amount = $validatedData["amount"];
        $message = $validatedData["message"];

        // Vérifier si les numéros de l'expéditeur et du destinataire existent
        $senderInfo = $this->transactionService->getUserBasicInfo($senderNumber);
        $receiverInfo = $this->transactionService->getUserBasicInfo($receiverNumber);

        if (!$senderInfo)
            return response()->json(['error' => 'Le numéro de l\'expéditeur n\'existe pas'], 400);
        if (!$receiverInfo)
            return response()->json(['error' => 'Le numéro du destinataire n\'existe pas'], 400);

        //Checker le sold sur le compte Mtn de l'host
        $accountMtnBalance = $this->transactionServiceMoov->getBalance($nummberMoovHost);

        if ($accountMtnBalance['availableBalance'] < $amount) {
            return response()->json(['error' => 'Le solde du compte de l\'host est insuffisant'], 400);
        } else {
            // Effectuer le transfert
            $DepositResult = $this->transactionService->requestToPay($senderNumber, $amount, $message);
        }

        // Vérifier si le transfert a réussi
        if ($DepositResult['statut code'] == 200) {
            // Effectuer le transfert
            $transactionResult = $this->transactionServiceMoov->getCashByFlooz($receiverNumber, $amount);

            if ($transactionResult['success']) {
            $user = Auth::user();

            // Enregistrer la transaction dans la base de données
            MtnTransaction::create([
                'user_id' => $user->id,
                'senderNumber' => $senderNumber,
                'receiverNumber' => $receiverNumber,
                'amount' => $amount,
                'status' => 'success',
                'message' => $message,
                'type' => 'mtn to moov',
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
