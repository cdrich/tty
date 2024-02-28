<?php 

namespace App\Repositories\Transaction;

use App\Models\TransactionNumber;
use App\Services\Mtn\TransactionMtnService;

class TransactionNumberRepository
{
    protected $transactionNumber, $mtn;
    public function __construct(TransactionNumber $transactionNumber, TransactionMtnService $mtn)
    {
        $this->transactionNumber = $transactionNumber;
        $this->mtn = $mtn;
    }

    public function getAllUserTransactionNumber()
    {
        $userNumbers = $this->transactionNumber->where('user_id', auth()->user()->id)
                        ->where('is_active',true)->with('operator_mobile')->get();
                        
        // $userNumbersTran = $this->mtn->getUserBasicInfo($userNumbers);
        return response()->json(["transactionNumber"=> $userNumbers],200);
        
    }


    public function createUserTransactionNumber(array $data)
    {
        $this->transactionNumber->create([
            "phone_number"=> $data["phone_number"],
            "operator_mobile_id"=> $data["operator_mobile_id"],
            "user_id"=> auth()->user()->id
            // "subscription"=>$mtnReturn["sub"],
            // "name"=>$mtnReturn["name"],
            // "given_name"=>$mtnReturn["given_name"],
            // "family_name"=>$mtnReturn["family_name"],
            // "birthdate"=>$mtnReturn["birthdate"],
            // "locale"=>$mtnReturn["locale"],
            // "gender"=>$mtnReturn["gender"]
        ]);
    }

    public function updateUserTransactionNumber($transactionNumber, array $data = null)
    {
        $transNum = $this->transactionNumber->where('id',$transactionNumber)->where('user_id',auth()->user()->id)->first();
        if(! empty($transNum))
        {
            $transNum->update([
                "phone_number"=> $data["phone_number"] ?? $transNum->phone_number,
                "operator_mobile_id"=> $data["operator_mobile_id"] ?? $transNum->operator_mobile_id,
            ]);
        }
        
    }

    public function desactivateUserTransactionNumber($transNumId)
    {
        if($this->transactionNumber->where('id',$transNumId)->exists())
        {
            $this->transactionNumber->where("user_id",auth()->user()->id)->where('id', $transNumId)->update([
                'is_active'=> false
            ]);
        }
    }
}