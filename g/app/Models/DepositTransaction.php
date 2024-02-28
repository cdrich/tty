<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payeeNote',
        'externalId',
        'amount',
        'currency',
        'payer_partyIdType',
        'payer_partyId',
        'payerMessage',
    ];
}