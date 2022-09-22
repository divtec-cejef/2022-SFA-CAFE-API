<?php

namespace Tests\Feature;

use App\Models\Achat;
use App\Models\Utilisateur;
use App\Models\Versement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UtilisateurTest extends TestCase
{

    use DatabaseTransactions;

    // Déclaration des variables utilisées pour les tests
    private $utilisateur, $token, $id;

    /**
     * Register et login d'un utilisateur afin de créer un token
     * Cette méthode est lancée avec l'exécution de chaque test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Génère un utilisateur
        $this->utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $this->post('api/utilisateur/register',
            [
                'nom' => $this->utilisateur->nom,
                'prenom' => $this->utilisateur->prenom,
                'email' => $this->utilisateur->email,
                'password' => $this->utilisateur->password
            ]);

        // Appelle la route api/utilisateur/login pour login l'utilisateur et récupérer un token
        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => $this->utilisateur->email,
                'password' => $this->utilisateur->password
            ]);

        $this->token = $loginResponse->json(['token']); // Récupération du token
        $this->id = Utilisateur::where('email', $this->utilisateur->email)->first()->id; // Récupération de l'ID de l'utilisateur qui vient d'être ajouté
    }

    /**
     * Test de la méthode GET showUser
     * Ce dernier retournera l'ensemble des données d'un utilisateur
     *
     * @return void
     */
    public function testShowUser(): void
    {
        // Appelle la route api/utilisateur/id pour récupérer les données de l'utilisateur créé précédemment
        // Il est obligatoire de passer en Header le token afin de pouvoir accéder aux données
        $showUserResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->json('get', 'api/utilisateur/' . $this->id);

        $showUserResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $showUserResponse->assertJson([ // Check si l'utilisateur est le même qui a été ajouté préalablement & Check si les valeurs correspondent
            'id' => $this->id,
            'nom' => $this->utilisateur->nom,
            'prenom' => $this->utilisateur->prenom,
            'email' => $this->utilisateur->email
            // 'password' => $utilisateur->password
        ]);
    }

    /**
     * Test de la méthode GET solde
     * Ce dernier ajoute trois enregistrements dans la table achats
     * Le solde du compte est ensuite calculé en fonction des achats effectués
     *
     * @return void
     */
    public function testSoldeWithOnlyPurchased(): void
    {
        // Appelle la route api/utilisateur/id pour récupérer les données de l'utilisateur créé précédemment 3x
        // Dont une fois avec 3 cafés
        // Il est obligatoire de passer en Header le token afin de pouvoir accéder aux données
        for ($i = 0; $i < 3; $i++) {
            $quantite = 0;

            // Troisième commande avec 3 cafés
            if($i == 2)
                $quantite = 3;


            $achatResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->post('api/utilisateur/' . $this->id . '/achat', [
                    'libelle'=>'Achat de café',
                    'prix'=>0.5,
                    'quantite'=> $quantite
                ]);

            $achatResponse->assertStatus(201); // Affirme que la réponse a un code d'état 201
            $achatResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
                'Message' => 'L\'achat a bien été effectué.'
            ]);
        }

        // Récupère le solde de l'utilisateur
        $soldeResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('api/utilisateur/' . $this->id . '/solde');


        $soldeResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $soldeResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Solde' => -2.5
        ]);
    }

    /**
     * Test de la méthode GET solde
     * Ce dernier ajoute trois enregistrements dans la table achats et un enregistrement dans la table versement
     * Le solde du compte est ensuite calculé en fonction des achats et versements effectués
     *
     * @return void
     */
    public function testSoldeAll(): void
    {
        // Appelle la route api/utilisateur/id pour récupérer les données de l'utilisateur créé précédemment 3x
        // Dont une fois avec 3 cafés
        // Il est obligatoire de passer en Header le token afin de pouvoir accéder aux données
        for ($i = 0; $i < 3; $i++) {
            $quantite = 0;

            // Troisième commande avec 3 cafés
            if($i == 2)
                $quantite = 3;

            $achatResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->post('api/utilisateur/' . $this->id . '/achat', [
                    'libelle'=>'Achat de café',
                    'prix'=>0.5,
                    'quantite'=> $quantite
                ]);

            $achatResponse->assertStatus(201); // Affirme que la réponse a un code d'état 201
            $achatResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
                'Message' => 'L\'achat a bien été effectué.'
            ]);
        }

        $versementResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('api/utilisateur/' . $this->id . '/versement', [
                'libelle'=>'Don de 10.-',
                'montant'=>10
            ]);

        $versementResponse->assertStatus(201); // Affirme que la réponse a un code d'état 201
        $versementResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'Le versement a bien été effectué.'
        ]);

        // Récupère le solde de l'utilisateur
        $soldeResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('api/utilisateur/' . $this->id . '/solde');


        $soldeResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $soldeResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Solde' => 7.5
        ]);
    }

    /**
     * Test de la méthode GET Historique
     * L'historique du compte est représenté par les transactions effectués (achats et versements)
     *
     * @return void
     */
    public function testHistoryWithDatas(): void
    {
        $achatResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('api/utilisateur/' . $this->id . '/achat', [
                'libelle'=>'Achat de café',
                'prix'=>0.5
            ]);

        $achatResponse->assertStatus(201); // Affirme que la réponse a un code d'état 201
        $achatResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'achat a bien été effectué.'
        ]);


        $versementResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('api/utilisateur/' . $this->id . '/versement', [
                'libelle'=>'Don de 10.-',
                'montant'=>10
            ]);

        $versementResponse->assertStatus(201); // Affirme que la réponse a un code d'état 201
        $versementResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'Le versement a bien été effectué.'
        ]);

        // Récupère le solde de l'utilisateur
        $HistoriqueResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('api/utilisateur/' . $this->id . '/historique');

        $HistoriqueResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        assert(sizeof($HistoriqueResponse["Historique"]) == 2); // Check si la réponse contient deux transactions
    }

    /**
     * Test de la méthode GET Historique
     * L'historique du compte est représenté par les transactions effectués (achats et versements)
     *
     * @return void
     */
    public function testHistoryWithNoDatas(): void
    {
        // Récupère le solde de l'utilisateur
        $HistoriqueResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('api/utilisateur/' . $this->id . '/historique');

        $HistoriqueResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $HistoriqueResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Historique' => 'Aucune transaction n\'a été effectué avec cet utilisateur.'
        ]);
    }

    /*******************************************************************************************************************************
     *******************************************************************************************************************************
     **********************************************************Achat****************************************************************
     *******************************************************************************************************************************
     ******************************************************************************************************************************/

    /**
     * Test de la méthode POST achat
     * Ce dernier ajoute un enregistrement dans la table achats
     * La quantité n'est pas spécifiée afin de tester la valeur par défaut
     *
     * @return void
     */
    public function testAchat(): void
    {
        // Appelle la route api/utilisateur/id pour récupérer les données de l'utilisateur créé précédemment
        // Il est obligatoire de passer en Header le token afin de pouvoir accéder aux données
        // Quantité est exprès non spécifié afin de vérifier que la valeur par défaut s'applique correctement
        $achatResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('api/utilisateur/' . $this->id . '/achat', [
                'libelle'=>'Achat de café',
                'prix'=>0.5
            ]);

        $achatResponse->assertStatus(201); // Affirme que la réponse a un code d'état 201
        $achatResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'achat a bien été effectué.'
        ]);

        $lastAchatForeignID = Achat::latest()->first()->id_utilisateur; // Récupération de la clé étrangère de la table utilisateur dans le dernière achat
        $this->assertDatabaseHas('achats', [
            'libelle'=>'Achat de café',
            'prix'=>0.5,
            'quantite' => 1,
            'id_utilisateur' => $lastAchatForeignID
        ]);
    }

    /**
     * Test de la méthode POST achat
     * Ce dernier ajoute un enregistrement dans la table achats
     * La quantité est spécifié
     *
     * @return void
     */
    public function testAchatWithMultipleQte(): void
    {
        // Appelle la route api/utilisateur/id pour récupérer les données de l'utilisateur créé précédemment
        // Il est obligatoire de passer en Header le token afin de pouvoir accéder aux données
        $achatResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('api/utilisateur/' . $this->id . '/achat', [
                'libelle'=>'Achat de café',
                'prix'=>0.5,
                'quantite'=>4
            ]);

        $achatResponse->assertStatus(201); // Affirme que la réponse a un code d'état 201
        $achatResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'achat a bien été effectué.'
        ]);

        $lastAchatForeignID = Achat::latest()->first()->id_utilisateur; // Récupération de la clé étrangère de la table utilisateur dans le dernière achat
        $this->assertDatabaseHas('achats', [
            'libelle'=>'Achat de café',
            'prix'=>0.5,
            'quantite' => 4,
            'id_utilisateur' => $lastAchatForeignID
        ]);
    }

    /*******************************************************************************************************************************
     *******************************************************************************************************************************
     ********************************************************Versement**************************************************************
     *******************************************************************************************************************************
     ******************************************************************************************************************************/

    /**
     * Test de la méthode POST versement
     * Ce dernier ajoute un enregistrement dans la table versements
     *
     * @return void
     */
    public function testVersement(): void
    {
        // Appelle la route api/utilisateur/id pour récupérer les données de l'utilisateur créé précédemment
        // Il est obligatoire de passer en Header le token afin de pouvoir accéder aux données
        $versementResponse = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('api/utilisateur/' . $this->id . '/versement', [
                'libelle'=>'Don de 20.-',
                'montant'=>20
            ]);

        $versementResponse->assertStatus(201); // Affirme que la réponse a un code d'état 201
        $versementResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'Le versement a bien été effectué.'
        ]);

        $lastVersementForeignID = Versement::latest()->first()->id_utilisateur; // Récupération de la clé étrangère de la table utilisateur dans le dernière achat
        $this->assertDatabaseHas('versements', [
            'libelle'=>'Don de 20.-',
            'montant'=>20,
            'id_utilisateur' => $lastVersementForeignID
        ]);
    }
}
