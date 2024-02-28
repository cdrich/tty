<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebcoomHistory extends Model
{
    use HasFactory;
    protected $fillable = ["history_transaction_id","referenceId"];
}
