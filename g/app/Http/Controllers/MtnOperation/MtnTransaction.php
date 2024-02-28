<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Mtn\TransactionMtnService;
use App\Services\Mtn\AuthMtnService;


class MtnTransaction extends Controller
{
    protected $transactionMtnService;
    protected $authMtnService;

    public function __construct(TransactionMtnService $transactionMtnService)
    {
        $this->transactionMtnService = $transactionMtnService;
    }

    public function requestToPay(Request $request)
    {
        $request->validate([
            'partyId' => 'required|string',
            'amount' => 'required|string',
            'message' => 'required|string',
            'xreferenceid' => 'required|string',
            'bearerToken' => 'required|string',
        ]);
        // $bearerToken, 
        // $xreferenceid
        $partyId = $request->input('partyId');
        $amount = $request->input('amount');
        $message = $request->input('message');

        try {
            $this->transactionMtnService->requestToPay($partyId, $amount, $message, $bearerToken, $xreferenceid);
            // Succès - Répondre avec une réponse appropriée
            return response()->json(['message' => 'Opération réussie'], 200);
        } catch (\Exception $e) {
            // Erreur - Gestion des exceptions
            return response()->json(['error' => 'Une erreur s\'est produite'], 500);
        }
    }
}
