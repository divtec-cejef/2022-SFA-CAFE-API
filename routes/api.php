<?php

use App\Http\Controllers\AchatController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\VersementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route pour créer un nouvel utilisateur
Route::post('register', [UtilisateurController::class, 'register']);

// Route pour se connecter et recevoir un token de connexion
Route::post('login', [UtilisateurController::class, 'authenticate']);

// Route pour récupérer les informations d'un utilisateur selon son ID
Route::get('utilisateur/{id}', [UtilisateurController::class, 'showUtilisateur'])
    ->middleware('auth:sanctum');

// Route pour acheter un ou plusieurs café(s) en tant qu'utilisateur
Route::post('utilisateur/{id}/achat', [AchatController::class, 'achatCafe'])
    ->middleware('auth:sanctum');

// Route pour verser un montant à son solde
Route::post('utilisateur/{id}/versement', [VersementController::class, 'versement'])
    ->middleware('auth:sanctum');

// Route pour verser un montant à son solde
Route::get('utilisateur/{id}/solde', [UtilisateurController::class, 'showSolde'])
    ->middleware('auth:sanctum');
