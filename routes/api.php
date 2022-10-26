<?php

use App\Http\Controllers\AchatController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\VersementController;
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
Route::post('utilisateur/register', [UtilisateurController::class, 'register']);

// Route pour se connecter et recevoir un token de connexion
Route::post('utilisateur/login', [UtilisateurController::class, 'authenticate']);

// Route pour récupérer les informations d'un utilisateur selon son ID
Route::get('utilisateur/{id}', [UtilisateurController::class, 'showUtilisateur'])
    ->middleware('auth:sanctum');

// Route pour acheter un ou plusieurs café(s) en tant qu'utilisateur
Route::post('utilisateur/{id}/achat', [AchatController::class, 'achatCafe'])
    ->middleware('auth:sanctum');

// Route pour verser un montant à son solde
Route::post('utilisateur/{id}/versement', [VersementController::class, 'versement'])
    ->middleware('auth:sanctum');

// Route pour visualiser son solde
Route::get('utilisateur/{id}/solde', [UtilisateurController::class, 'showSolde'])
    ->middleware('auth:sanctum');

// Route pour visualiser l'historique de ses transactions
Route::get('utilisateur/{id}/historique', [UtilisateurController::class, 'showHistorique'])
    ->middleware('auth:sanctum');

// Route pour accéder aux paramètres de l'application en tant qu'utilisateur
Route::get('settings', [UtilisateurController::class, 'getSettings'])
    ->middleware('auth:sanctum');

// Route pour supprimer un achat
Route::delete('delete/achat/{id}', [AchatController::class, 'deleteAchat'])
    ->middleware('auth:sanctum');

// Route pour supprimer un versement
Route::delete('delete/versement/{id}', [VersementController::class, 'deleteVersement'])
    ->middleware('auth:sanctum');

/*--------------------------------------------- Administrateur ---------------------------------------------*/

// Route pour accéder aux paramètres de l'application en tant qu'administrateur
Route::get('administrateur/settings', [UtilisateurController::class, 'getAllSettings'])
    ->middleware('auth:sanctum');

// Route pour modifier un paramètre de l'application en tant qu'administrateur
Route::put('administrateur/settings/update', [UtilisateurController::class, 'updateSetting'])
    ->middleware('auth:sanctum');

// Route pour récupérer les informations de tous les utilisateurs
Route::get('administrateur/utilisateurs', [UtilisateurController::class, 'showAllUtilisateur'])
    ->middleware('auth:sanctum');

// Route pour activer/désactiver un utilisateur
Route::patch('administrateur/utilisateur/{id}/statut', [UtilisateurController::class, 'changeStateUtilisateur'])
    ->middleware('auth:sanctum');

// Route pour supprimer un utilisateur
Route::delete('administrateur/utilisateur/{id}/delete', [UtilisateurController::class, 'deleteUtilisateur'])
    ->middleware('auth:sanctum');
