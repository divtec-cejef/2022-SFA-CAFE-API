<?php

namespace App\Http\Controllers;

use App\Models\Versement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VersementController extends Controller
{
    /**
     * Fonction de versement d'un utilisateur
     *
     * @response 201
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function versement(Request $request, $id): JsonResponse
    {
        if ($this->validate($request, Versement::validateRules())) {
            Versement::create([
                'libelle' => $request->libelle,
                'montant' => $request->montant,
                'id_utilisateur' => $id
            ]);
            return Response()->json([
                'message' => 'Votre versement a bien été effectué.'
            ], 201);
        } else {
            return Response()->json([
                'error' => 'Le versement n\'a pas pu être effectué.'
            ], 500);
        }
    }

    /**
     * Fonction d'effacement d'un virement
     *
     * @response 202
     * @param $id
     * @return JsonResponse
     */
    public function deleteVersement($id): JsonResponse
    {
        Versement::findOrFail($id)->delete();
        return Response()->json([
            'message' => 'Le versement a bien été annulé.'
        ], 202);
    }
}
