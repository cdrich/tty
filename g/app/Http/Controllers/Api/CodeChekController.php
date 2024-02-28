<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CodeCheckRequest;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;

class CodeChekController extends Controller
{
    /**
     * Check if the code is exist and vaild one (Setp 2)
     *
     * @param  mixed $request
     * @return void
     */
    public function checkCode(CodeCheckRequest $request)
    {
        $passwordReset = PasswordResetToken::firstWhere('token', $request->token);
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return response()->json(['message' => "Votre code est attendu."], 422);
        }

        return response()->json([
            'code' => $passwordReset->token,
            'message' => "Votre code est valide."
        ], 200);
       
    }
    
}
