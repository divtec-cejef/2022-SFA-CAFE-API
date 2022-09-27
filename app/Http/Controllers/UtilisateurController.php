<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\Utilisateur;
use App\Models\Versement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Throwable;
use function Psy\debug;

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
        Utilisateur::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        return Response()->json([
            'message' => 'L\'utilisateur a bien été créé.'
        ], 201);
    }

    /**
     * Authentifier l'utilisateur avec email et mot de passe
     * Redistribue un token pour l'utilisateur
     *
     * @response 201
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function authenticate(Request $request): JsonResponse
    {
        $utilisateur = Utilisateur::where('email', $request->email)->first();

        if (Hash::check($request->password, $utilisateur->password)) {
            return Response()->json([
                'user' => [
                    'id' => $utilisateur->id,
                    'nom' => $utilisateur->nom,
                    'prenom' => $utilisateur->prenom,
                    'token' => $utilisateur->createToken(time())->plainTextToken
                ]
            ], 201);
        } else {
            return Response()->json([
                'error' => 'Le mot de passe est incorrecte.'
            ], 400);
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
            'solde' => $solde
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
        $allTransactions = $allAchats->concat($allVersements)->makeVisible('created_at')->toArray();

        foreach ($allTransactions as $key => $transaction) {
            $allTransactions[$key]['created_at'] = date('d.m.Y H:i', strtotime($transaction['created_at']));
        }

        // Trie la liste en fonction des dates (récentes -> anciennes)
        usort($allTransactions, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        if (!empty($allTransactions)) {
            return Response()->json([
                'historique' => $allTransactions
            ]);
        } else {
            return Response()->json([
                'message' => 'Vous n\'avez pas encore effectué de transaction ! Achetez donc un café ٩( ๑╹ ꇴ╹)۶'
            ]);
        }
    }
}
