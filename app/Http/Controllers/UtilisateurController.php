<?php

namespace App\Http\Controllers;

use App\Exceptions\WrongEmailException;
use App\Models\Achat;
use App\Models\Utilisateur;
use App\Models\Versement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class UtilisateurController extends Controller
{
    /**
     * Crée un nouvel utilisateur
     *
     * @response 201
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        //throw_if($this->validate($request, Utilisateur::validateRules()), UniqueEmailException::class, 'Failed to create ');
        Utilisateur::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        return Response()->json([
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);
    }

    /**
     * Authentifier l'utilisateur avec email et mot de passe
     * Redistribue un token pour l'utilisateur
     *
     * @response 200
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
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
                'Message' => 'Les informations de connexion sont incorrectes.'
            ]);
        }
    }

    /**
     * Affiche une utilisateur selon son id
     * Retourne une erreur si l'utilisateur n'a pas été trouvé ou s'il est inexistant
     *
     * @response 200
     * @param $id
     * @return mixed Json comportant l'utilisateur avec l'id en paramètre
     */
    public function showUtilisateur($id): mixed
    {
        return Utilisateur::findOrFail($id);
    }

    /**
     * Affiche le solde d'un utilisateur selon son id
     *
     * @response 200
     * @param $id
     * @return mixed Solde du compte
     */
    public function showSolde($id): mixed
    {
        // Récupère tous les achats réalisés par l'utilisateur
        $allAchats = Achat::where('id_utilisateur', $id)->get();

        // Récupère tous les versements réalisés par l'utilisateur
        $allVersements = Versement::where('id_utilisateur', $id)->get();

        // Récupération de tous les prix des achats et de tous les versements
        $solde = 0;
        foreach ($allAchats as $achat){
            $solde -= $achat->prix * $achat->quantite;
        }
        foreach ($allVersements as $versement){
            $solde += $versement->montant;
        }

        return Response()->json([
            'Solde' => $solde
        ]);
    }

    /**
     * Affiche l'historique des transactions d'un utilisateur selon son id
     *
     * @response 200
     * @param $id
     * @return mixed Historique de transactions du compte
     */
    public function showHistorique($id): mixed
    {
        // Récupère tous les achats réalisés par l'utilisateur
        $allAchats = Achat::where('id_utilisateur', $id)->get();

        // Récupère tous les versements réalisés par l'utilisateur
        $allVersements = Versement::where('id_utilisateur', $id)->get();

        // Merge les deux tableaux et affiche la date de création pour chaque transaction
        $allTransactions = $allAchats->merge($allVersements)->makeVisible('created_at')->toArray();

        // Trie la liste en fonction des dates (récentes -> anciennes)
        usort($allTransactions, function($a, $b) {
            return strtotime($a['created_at']) + strtotime($b['created_at']);
        });

        return Response()->json([
            'Historique' => $allTransactions
        ]);
    }
}
