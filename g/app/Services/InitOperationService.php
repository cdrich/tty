<?php
namespace App\Services;
use App\Models\HistoryTransaction;
use Ramsey\Uuid\Uuid;

class InitOperationService
{
    public function __construct()
    {

    }

    public function initTransaction()
    {
        $history = HistoryTransaction::create([
            "user_id" => auth()->user()->id,
            "referenceId" => (Uuid::uuid4())->toString()
        ]);
        return $history->referenceId;
    }
}
