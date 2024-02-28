<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperatorMobile extends Model
{
    use HasFactory;
    // protected $table = 'operator_numbers';
    protected $fillable = ["label","logo_url"] ;

    public function transactionNumbers()
    {
        return $this->hasMany(\App\Models\TransactionNumber::class);
    }
}
