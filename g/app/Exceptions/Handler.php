<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Swift_TransportException;


class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            // Gérer l'exception d'authentification API
            return response()->json(['error' => 'Authentification requise.'], 401);
        }

        if ($exception instanceof UnauthorizedHttpException) {
            // Gérer l'exception de demande sans authentification à API
            return response()->json(['error' => 'Resource non authorisée. Veuillez-vous connecter.'], 401);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return response()->json(['error'=> 'Action non authorisé.'],404);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json(["error"=>"Le model contacté n'existe pas."]);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json(["error"=> "Aucune donné disponible pour cette demande. Verifiez votre url."],404);
        }

        if ($exception instanceof ValidationException) {
            return response()->json(["error"=>"Erreur de validation des données."],422);
        }

        if ($exception instanceof TokenMismatchException) {
            return response()->json(["error"=> "Erreur de sécurité des données en transit. Reesayez."],404);
        }

        if($exception instanceof QueryException)
        {
            return response()->json(["error"=> "Exception de requête."],400);
        }

        if ($exception instanceof ConnectionException)
        {
            return response()->json(["error"=> "Aucune connexion au serveur."],500);
        }
      
        return parent::render($request, $exception);
    }
}
