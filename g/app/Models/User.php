<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'users';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'code',
        'is_verified',
        'phone'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
  
    
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }    

    public function transactionNumbers()
    {
        return $this->hasMany(\App\Models\TransactionNumber::class);
    }

    public function transferts()
    {
        return $this->hasMany(\App\Models\Transfert::class);
    }

    public function historyTransactions()
    {
        return $this->hasMany(\App\Models\HistoryTransaction::class);
    }

    public function mtnTransactions()
    {
        return $this->hasMany(\App\Models\MtnTransaction::class, 'user_id');
    }
    // public function verificationCode()
    // {
    //     return $this->hasOne(\App\Models\VerificationCode::class,'user_id');
    // }
}