<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfert extends Model
{
    use HasFactory;
    protected $fillable = ["amount","type","user_id","recever","transaction_number_id"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactionNumbers()
    {
        return $this->belongsTo(TransactionNumber::class);
    }
}
