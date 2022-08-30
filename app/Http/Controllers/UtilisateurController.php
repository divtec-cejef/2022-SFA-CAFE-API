<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UtilisateurController extends Controller
{
    /**
     * Crée un nouvel utilisateur
     *
     * @response 201
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        if ($this->validate($request, Utilisateur::validateRules())) {
            Utilisateur::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            return Response()->json([
                'Success' => 'L\'utilisateur a bien été créé.'
            ]);
        } else {
            return Response()->json([
                'Error' => 'L\'utilisateur n\'a pas pu être créé.'
            ]);
        }
    }

    /**
     * Authentifier l'utilisateur
     *
     * @response 200
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function authenticate(Request $request): JsonResponse
    {
        $utilisateur = Utilisateur::where('email', $request->email)->first();

        if (Hash::check($request->password, $utilisateur->password)) {
            return Response()->json([
                'token' => $utilisateur->createToken(time())->plainTextToken
            ]);
        } else {
            return Response()->json([
                'error' => 'Les informations de connexion sont incorrectes.'
            ]);
        }

    }

    /**
     * Fonction de test pour la connexion à l'API
     *
     * @return JsonResponse
     */
    public function dashboard(): JsonResponse {
        return Response()->json([
            'success' => 'Bienvenue'
        ]);
    }

    /**
     * Affiche une tache selon son id
     *
     * @response 200
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showUser($id)
    {
        return Utilisateur::findOrFail($id);
    }
}
