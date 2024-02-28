<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCodeResetPassword;
use Symfony\Component\Mailer\Exception\TransportException;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // Delete all old code that user send before.
        PasswordResetToken::where('email', $request->email)->delete();

        // Create a new code
        $codeData = PasswordResetToken::create($request->data());

        // Send email to user
        try {
            Mail::to($request->email)->send(new SendCodeResetPassword($codeData->token));

            return response()->json(['message' =>'Le code vous est envoyé par email.'], 200);
        } catch (TransportException $e) {
            $errorMessage = $e->getMessage();
        
            if (strpos($errorMessage, 'Mail quota exceeded') !== false) {
                // Le quota de courrier a été dépassé, personnalisez le message d'erreur ici
                return response()->json(["message" => "Mail quota exceeded. Please try again later."], 500);
            }
        
            // Gérer d'autres erreurs SMTP si nécessaire
            return response()->json(["message" => "An error occurred while sending the email."], 500);
        }
    }
}
