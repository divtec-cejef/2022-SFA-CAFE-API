<?php

namespace App\Exceptions;

use App\Models\Utilisateur;
use ErrorException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Psr\Log\LogLevel;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        QueryException::class,
        ErrorException::class
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
     * Retourne les différentes exceptions
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response|JsonResponse
     * @throws Throwable
     */
    public function render($request, Throwable $e): Response|JsonResponse
    {
        // Erreurs générées suite au Register
        if ($e instanceof QueryException) {
            $errorCode = $e->errorInfo[1];
            if($errorCode == 1062){
                return response()->json([
                    'error' => Utilisateur::emailAlreadyUsed
                ], 409);
            } else {
                return response([
                    'error'=> Utilisateur::unableToCreateUser
                ], 500);
            }
        }

        // Erreur générée suite au Login
        if ($e instanceof ErrorException) {
            $errorCode = $e->getCode();
            if ($errorCode == 0) {
                return response()->json([
                    'error' => Utilisateur::emailNotFound
                ], 400);
            }
        }
        return parent::render($request, $e);
    }
}
