<?php

namespace App\Exceptions;

use App\Models\Utilisateur;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        QueryException::class
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Retourne une exception lors de la création de l'utilisateur
     * Retourne une erreur lorsque l'adresse e-mail est déjà utilisée
     * Retourne une erreur général lorsqu'il s'agit d'une erreur serveur
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof QueryException) {
            $errorCode = $exception->errorInfo[1];
            if($errorCode == 1062){
                return response()->json([
                    'message' => Utilisateur::emailAlreadyUsed
                ], 409);
            } else {
                return response([
                    'message'=> Utilisateur::unableToCreateUser
                ], 500);
            }
        }
        return parent::render($request, $exception);
    }
}
