<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Mtn\TransactionMtnService;
use App\Models\MtnTransaction;
use Illuminate\Support\Facades\Auth;

class MtnToMtnTransactionControlleur extends Controller
{
    protected $transactionService;
    /**
     * Create a new MtnToMtnTransactionControlleur instance.
     *
     * @return void
     */
    public function __construct(TransactionMtnService $transactionService)
    {
        $this->transactionService = $transactionService;
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

        // Effectuer le transfert
        $DepositResult = $this->transactionService->requestToPay($receiverNumber, $amount, $message);

        // Vérifier si le transfert a réussi
        if ($DepositResult['statut code'] == 200) {
            // Effectuer le transfert
            $transactionResult = $this->transactionService->requestToWithdraw();

            if ($transactionResult['statut code'] == 200) {
            $user = Auth::user();
            // Enregistrer la transaction dans la base de données
            MtnTransaction::create([
                'user_id' => $user->id,
                'senderNumber' => $senderNumber,
                'receiverNumber' => $receiverNumber,
                'amount' => $amount,
                'status' => 'success',
                'message' => $message,
                'type' => 'mtn to mtn',
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
