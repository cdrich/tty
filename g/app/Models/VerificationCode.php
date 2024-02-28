<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    use HasFactory;
    protected $fillable = ["user_id","code_verification"];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
