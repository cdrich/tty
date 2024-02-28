<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class MtnTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'senderNumber',
        'receiverNumber',  
        'amount',         
        'status',
        'message',
        'type',     
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
