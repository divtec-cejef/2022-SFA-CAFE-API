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
                'Message' => 'Le versement a bien été effectué.'
            ]);
        } else {
            return Response()->json([
                'Message' => 'Le versement n\'a pas pu être effectué.'
            ]);
        }
    }
}
