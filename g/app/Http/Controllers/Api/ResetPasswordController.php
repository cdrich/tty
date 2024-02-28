<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    // public function resetPassword(ResetPasswordRequest $request)
    // {
    //     $passwordReset = PasswordResetToken::where('token', $request->token)->first();

    //     if ($passwordReset->created > now()->addHour()) {
    //         return response()->json(["message"=>"Your code is expired."], 422);
    //     }

    //     $user = User::firstWhere('email', $passwordReset->email);

    //     $user->update($request->only('password'));

    //     $passwordReset->delete();

    //     return response()->json(["message"=>"Password has been successfully reset"], 200);
    // }
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Chercher le token dans la table PasswordResetToken
        $passwordReset = PasswordResetToken::where('token', $request->token)->first();

        // Vérifier si le token existe
        if (!$passwordReset) {
            return response()->json(["message" => "Code invalide."], 422);
        }

        // Vérifier si le code est expiré
        if ($passwordReset->created_at > now()->addHour()) {
            return response()->json(["message" => "Votre code est expiré."], 422);
        }

        // Trouver l'utilisateur par email
        $user = User::firstWhere('email', $passwordReset->email);

        // Mettre à jour le mot de passe de l'utilisateur
        $user->update(['password' => bcrypt($request->password)]);

        // Supprimer le token de réinitialisation
        $passwordReset->delete();

        return response()->json(["message" => "Le mot de passe a été réinitialisé avec succès."], 200);
    }

}
