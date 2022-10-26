<?php

namespace App\Http\Controllers;

use App\Models\Achat;
use App\Models\Config;
use App\Models\Utilisateur;
use App\Models\Versement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Throwable;
use function Psy\debug;
use function Symfony\Component\String\s;

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

        // Vérification si le compte est activé
        $state = Utilisateur::where('id', $utilisateur->id)->first()->actif;
        if (!$state) {
            return Response()->json([
                'error' => 'Ce compte est désactivé.'
            ], 401);
        }

        if (Hash::check($request->password, $utilisateur->password)) {
            return Response()->json([
                'user' => [
                    'id' => $utilisateur->id,
                    'nom' => $utilisateur->nom,
                    'prenom' => $utilisateur->prenom,
                    'token' => $utilisateur->createToken(time())->plainTextToken,
                    'is_admin' => $utilisateur->admin
                ]
            ], 201);
        } else {
            return Response()->json([
                'error' => 'Le mot de passe est incorrecte.'
            ], 400);
        }
    }

    /**
     * Affiche un utilisateur selon son id
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
     * @return JsonResponse Solde du compte
     */
    public function showSolde($id): JsonResponse
    {

        // Récupère tous les achats réalisés par l'utilisateur
        $allAchats = Achat::where('id_utilisateur', $id)->get();

        // Récupère tous les versements réalisés par l'utilisateur
        $allVersements = Versement::where('id_utilisateur', $id)->get();

        // Récupération de tous les prix des achats et de tous les versements
        $solde = 0;
        foreach ($allAchats as $achat) {
            $solde -= $achat->prix * $achat->quantite;
        }
        foreach ($allVersements as $versement) {
            $solde += $versement->montant;
        }

        return Response()->json([
            'solde' => $solde
        ]);
    }

    /**
     * Affiche l'historique des transactions d'un utilisateur selon son id
     * Etant donné que c'est la première requête qui est effectuée sur le dashboard
     * elle va vérifier directement si le compte est bloqué
     *
     * @response 200
     * @param $id
     * @return mixed Historique de transactions du compte
     */
    public function showHistorique($id): mixed
    {
        if (!Utilisateur::where('id', $id)->first()->actif) {
            return Response()->json([
                'error' => 'Ce compte est désactivé.'
            ], 401);
        }

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
        usort($allTransactions, function ($a, $b) {
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

    /**
     * Retourne les paramètres de l'application pour les utilisateurs
     *
     * @return mixed Toutes les constantes de l'application
     */
    public function getSettings(): mixed
    {
        return Config::all();
    }

    /*--------------------------------------------- Administrateur ---------------------------------------------*/

    /**
     *
     * Vérifie les droits de l'utilisateur (admin ou utilisateur)
     *
     * @param $request
     * @return bool
     */
    public function isAdmin($request): bool
    {
        // Récupération du token depuis l'header
        $token = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $token);

        // Il est obligatoire de passer par le token de connexion afin de garantir la sécurité
        // Vérification que l'utilisateur a bien les droits administrateurs à partir du token de connexion.
        $utilisateur = PersonalAccessToken::findToken($token)->first()->tokenable;
        if ($utilisateur->admin) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Retourne les paramètres de l'application pour les administrateurs
     *
     * @param Request $request
     * @return JsonResponse|Collection Toutes les constantes de l'application ou un message d'erreur
     */
    public function getAllSettings(Request $request): JsonResponse|Collection
    {
        if ($this->isAdmin($request)) {
            return Config::all();
        } else {
            return Response()->json([
                'error' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.'
            ], 401);
        }
    }

    /**
     * Mise à niveau d'un paramètre de l'application
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSetting(Request $request): JsonResponse
    {
        if ($this->isAdmin($request)) {
            Config::where('id', $request->id)->update(array('valeur' => $request->valeur));
            return Response()->json([
                'message' => 'Le paramètre "' . $request->nom . '" a bien été modifié.'
            ]);
        } else {
            return Response()->json([
                'error' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.'
            ], 401);
        }
    }

    /**
     * Affiche toutes les informations utilisateurs
     *
     * @response 200
     * @return array|Collection|JsonResponse Liste des utilisateurs
     */
    public function showAllUtilisateur(Request $request): array|Collection|JsonResponse
    {
        if($this->isAdmin($request)){
            $utilisateurs = Utilisateur::all('id', 'nom', 'prenom', 'email', 'actif', 'admin');

            // Ajout du solde pour chaque utilisateur
            foreach ($utilisateurs as $key => $utilisateur) {
                $soldeUtilisateur = $this->showSolde($utilisateur->id)->getData()->solde;
                $utilisateurs[$key]['solde'] = $soldeUtilisateur;
            }

            return $utilisateurs;
        } else {
            return Response()->json([
                'error' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.'
            ],401);
        }
    }

    /**
     * Active/Désactive un compte utilisateur
     *
     * @response 200
     * @return JsonResponse Json comportant les informations
     */
    public function changeStateUtilisateur($id, Request $request): JsonResponse
    {
        if($this->isAdmin($request)){
            $state = Utilisateur::where('id', $id)->first()->actif;
            $newState = true;

            if ($state) {
                $newState = false;
            }

            Utilisateur::where('id', $id)->update(array('actif' => $newState));
            if ($newState) {
                return Response()->json([
                    'message' => 'Le compte a été activé'
                ]);
            } else {
                return Response()->json([
                    'message' => 'Le compte a été désactivé'
                ]);
            }
        } else {
            return Response()->json([
                'error' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.'
            ],401);
        }
    }

    /**
     * Suppression d'un utilisateur
     *
     * @response 202
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUtilisateur($id, Request $request): JsonResponse
    {
        if($this->isAdmin($request)){
            Utilisateur::findOrFail($id)->delete();
            return Response()->json([
                'message' => 'L\'utilisateur a bien été supprimé'
            ], 202);
        } else {
            return Response()->json([
                'error' => 'Vous n\'êtes pas autorisé à accéder à cette ressource.'
            ],401);
        }
    }
}
