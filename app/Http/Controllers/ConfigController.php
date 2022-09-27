<?php

namespace App\Http\Controllers;

use App\Models\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Laravel\Sanctum\PersonalAccessToken;

class ConfigController extends Controller
{
    /**
     * @param $token
     * @return JsonResponse|Collection Toutes les constantes de l'application ou un message d'erreur
     */
    public function getAllConfigs($token): JsonResponse|Collection
    {
        // Il est obligatoire de passer par le token de connexion afin de garantir la sécurité
        //Récupère l'utilisateur à partir du token de connexion
        $utilisateur = PersonalAccessToken::findToken($token)->first()->tokenable;
        if($utilisateur->admin){
            return Config::all();
        } else {
            return Response()->json([
               'error' => 'Vous n\'êtes pas autorisé à accéder à cette source.'
            ],401);
        }
    }
}
