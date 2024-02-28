<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MoovTransaction;
use App\Services\Moov\TransfertMoovService;
use Illuminate\Support\Facades\Auth;

class MoovToMoovTransactionControlleur extends Controller
{
    protected $transactionService;
    /**
     * Create a new MoovToMoovTransactionControlleur instance.
     *
     * @return void
     */
    public function __construct(TransfertMoovService $transactionService)
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
        
        // Effectuer le transfert
        $DepositResult = $this->transactionService->getCashByFlooz($senderNumber, $amount);

        // Vérifier si le transfert a réussi
        if ($DepositResult['success']) {
            // Effectuer le transfert
            $transactionResult = $this->transactionService->transfertFlooz($receiverNumber, $amount, $senderNumber);

            if ($transactionResult['success']) {
            $user = Auth::user();

            // Enregistrer la transaction dans la base de données
            MoovTransaction::create([
                'senderNumber' => $senderNumber,
                'receiverNumber' => $receiverNumber,
                'amount' => $amount,
                'status' => 'success',
                'message' => $message,
                'type' => 'moov to moov',
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
