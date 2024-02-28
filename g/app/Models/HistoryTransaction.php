<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryTransaction extends Model
{
    use HasFactory;
    protected $fillable = ["user_id","referenceId","amount","description","destination"] ;

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
