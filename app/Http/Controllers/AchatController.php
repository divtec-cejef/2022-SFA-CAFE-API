<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AchatController extends Controller
{
    /**
     * Fonction pour l'achat d'un ou plusieurs café(s) d'un utilisateur
     *
     * @response 201
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function achatCafe(Request $request, $id): JsonResponse
    {
        if ($this->validate($request, Achat::validateRules())) {
            Achat::create([
                'libelle' => $request->libelle,
                'quantite' => $request->quantite == null ? 1 : $request->quantite,
                'prix' => $request->prix,
                'id_utilisateur' => (int)$id
            ]);

            // Deuxième méthode
//            $data = $request->all();
//            $data['id_utilisateur'] = (int)$id;
//            Achat::create($data);

            return Response()->json([
                'message' => 'Votre achat a bien été effectué.'
            ], 201);
        } else {
            return Response()->json([
                'error' => 'L\'achat n\'a pas pu être effectué.'
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
    public function deleteAchat($id): JsonResponse
    {
        Achat::findOrFail($id)->delete();
        return Response()->json([
            'message' => 'L\'achat a bien été annulé.'
        ], 202);
    }
}
