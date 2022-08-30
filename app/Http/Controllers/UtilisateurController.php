<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UtilisateurController extends Controller
{
    /**
     * Crée un nouvel utilisateur
     *
     * @response 201
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function createUser(Request $request)
    {
        $this->validate($request, Utilisateur::validateRules());
        return Utilisateur::create($request->all());
    }

    /**
     * Confirmer la connexion
     *
     * @response 200
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function authConfirm(Request $request)
    {
        $this->validate($request, Utilisateur::validateRules());
        return Utilisateur::create($request->all());
    }

    /**
     * Affiche une tache selon son id
     *
     * @response 200
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showOneUser($id)
    {
        return Utilisateur::findOrFail($id);
    }

    /**
     * Valide la saisie des données dans la requête
     * Met à jour une tache
     *
     * @response 200
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function update($id, Request $request)
    {
        $this->validate($request, Tache::validateRules());
        Tache::findOrFail($id)->update($request->all());
        return Tache::findOrFail($id);
    }

    /**
     * Supprime une tache
     *
     * @response 204
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        Tache::findOrFail($id)->delete();
        return response('', 204);
    }

    /**
     * Valide la saisie des données dans la requête
     * Change l'état d'une tache à terminé
     *
     * @response 200
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completed($id)
    {
        $task = Tache::findOrFail($id);
        $task->complet = 1;
        $task->update();
        return Tache::findOrFail($id);
    }
}
