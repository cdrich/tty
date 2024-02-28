<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionNumber extends Model
{
    use HasFactory;
   
    protected $fillable = [
        "user_id",
        "phone_number",
        "is_active",
        "operator_mobile_id",
        // 'subscription',
        // 'name',
        // 'given_name',
        // 'family_name',
        // 'birthdate',
        // 'locale',
        // 'gender'
        ] ;

    public function operator_mobile()
    {
        return $this->belongsTo(\App\Models\OperatorMobile::class,"operator_mobile_id");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function transferts()
    {
        return $this->hasMany(Transfert::class);
    }
}
