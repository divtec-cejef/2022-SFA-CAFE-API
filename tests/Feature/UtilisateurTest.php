<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class UtilisateurTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Création d'utilisateurs avec la factory
     * Cette méthode est lancée avec l'exécution des tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->utilisateurs = Utilisateur::factory()->count(3)->create();

    }

    /**
     * Test de la méthode POST Register en créant un nouvel utilisateur sans problème
     *
     * @return void
     */
    public function testRegister(): void
    {
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);
        $this->assertDatabaseHas('utilisateurs', [ // Check si la base de données contient l'utilisateur qui vient d'être ajouté
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email' => $utilisateur->email
            // 'password' => $utilisateur->password
        ]);
    }

    /**
     * Test de la méthode POST Register en créant un nouvel utilisateur avec une adresse e-mail déjà existante
     *
     * @return void
     */
    public function testRegisterEmailAlreadyUsed(): void
    {
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);
        $this->assertDatabaseHas('utilisateurs', [ // Check si la base de données contient l'utilisateur qui vient d'être ajouté
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email' => $utilisateur->email,
            // 'password' => $utilisateur->password
        ]);

        // Appelle la route api/utilisateur/register pour créer un second utilisateur avec les mêmes paramètres
        $secondResponse = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $secondResponse->assertStatus(409); // Affirme que la réponse a un code d'état 409
        $secondResponse->assertJson([
            'error'=> 'L\'adresse e-mail est déjà utilisée.'
        ]);
    }

    /**
     * Test de la méthode POST Register en créant un nouvel utilisateur sans un champs obligatoire
     * Test non essentiel étant donné que les données seront checkés dans le Frontend avant l'envoi dans le Backend
     *
     * @return void
     */
    public function testRegisterWithUnfilledValues(): void
    {
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => '', //Non remplissage du champ e-mail
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(500); // Affirme que la réponse a un code d'état 500
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'error' => 'Impossible de créer l\'utilisateur. Veuillez contacter un administrateur.'
        ]);
    }

    /**
     * Test de la méthode POST Login en se connectant avec un email et un mot de passe utilisateur en tant normal
     * Ce dernier retournera un Token
     *
     * @return void
     */
    public function testLogin(): void
    {
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);

        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $loginResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $loginResponse->assertJson(fn (AssertableJson $json) => $json->has('token')); // Check si la réponse contient un champ token

    }

    /**
     * Test de la méthode POST Login en se connectant avec un email et un mauvais mot de passe utilisateur
     * Ce dernier retournera un message d'erreur
     *
     * @return void
     */
    public function testLoginBadPassword(): void
    {
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);

        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => $utilisateur->email,
                'password' => '1234' // Mauvais mot de passe
            ]);

        $loginResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $loginResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'error' => 'Le mot de passe est incorrecte.'
        ]);
    }

    /**
     * Test de la méthode POST Login en se connectant avec un email inexistant et un mauvais mot de passe utilisateur
     * Ce dernier retournera un message d'erreur pour l'email
     *
     * @return void
     */
    public function testLoginBadEmail(): void
    {
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);

        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => '1234@gmail.com',
                'password' => '1234' // Mauvais mot de passe
            ]);

        $loginResponse->assertStatus(400); // Affirme que la réponse a un code d'état 400
        $loginResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'error' => 'L\'adresse e-mail n\'existe pas.'
        ]);
    }

    /**
     * Test de la méthode GET showUser
     * Ce dernier retournera l'ensemble des données d'un utilisateur
     *
     * @return void
     */
    public function testShowUser(): void
    {
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);

        // Appelle la route api/utilisateur/login pour login l'utilisateur et récupérer un token
        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $loginResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $loginResponse->assertJson(fn (AssertableJson $json) => $json->has('token')); // Check si la réponse contient un champ token

        $token = $loginResponse->json(['token']); // Récupération du token
        $id = Utilisateur::where('email', $utilisateur->email)->first()->id; // Récupération de l'ID de l'utilisateur qui vient d'être ajouté

        // Appelle la route api/utilisateur/id pour récupérer les données de l'utilisateur créé précédemment
        // Il est obligatoire de passer en Header le token afin de pouvoir accéder aux données
        $showUserResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('get', 'api/utilisateur/' . $id);

        $showUserResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $showUserResponse->assertJson([ // Check si l'utilisateur est le même qui a été ajouté préalablement & Check si les valeurs correspondent
            'id' => $id,
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email' => $utilisateur->email
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
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);

        // Appelle la route api/utilisateur/login pour login l'utilisateur et récupérer un token
        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $loginResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $loginResponse->assertJson(fn (AssertableJson $json) => $json->has('token')); // Check si la réponse contient un champ token

        $token = $loginResponse->json(['token']); // Récupération du token
        $id = Utilisateur::where('email', $utilisateur->email)->first()->id; // Récupération de l'ID de l'utilisateur qui vient d'être ajouté

        // Appelle la route api/utilisateur/id pour récupérer les données de l'utilisateur créé précédemment 3x
        // Dont une fois avec 3 cafés
        // Il est obligatoire de passer en Header le token afin de pouvoir accéder aux données
        for ($i = 0; $i < 3; $i++) {
            $quantite = 0;

            // Troisième commande avec 3 cafés
            if($i == 2)
                $quantite = 3;


            $achatResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->post('api/utilisateur/' . $id . '/achat', [
                    'libelle'=>'Achat de café',
                    'prix'=>0.5,
                    'quantite'=> $quantite
                ]);

            $achatResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
            $achatResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
                'Message' => 'L\'achat a bien été effectué.'
            ]);
        }

        // Récupère le solde de l'utilisateur
        $soldeResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('api/utilisateur/' . $id . '/solde');


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
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);

        // Appelle la route api/utilisateur/login pour login l'utilisateur et récupérer un token
        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $loginResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $loginResponse->assertJson(fn (AssertableJson $json) => $json->has('token')); // Check si la réponse contient un champ token

        $token = $loginResponse->json(['token']); // Récupération du token
        $id = Utilisateur::where('email', $utilisateur->email)->first()->id; // Récupération de l'ID de l'utilisateur qui vient d'être ajouté

        // Appelle la route api/utilisateur/id pour récupérer les données de l'utilisateur créé précédemment 3x
        // Dont une fois avec 3 cafés
        // Il est obligatoire de passer en Header le token afin de pouvoir accéder aux données
        for ($i = 0; $i < 3; $i++) {
            $quantite = 0;

            // Troisième commande avec 3 cafés
            if($i == 2)
                $quantite = 3;


            $achatResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->post('api/utilisateur/' . $id . '/achat', [
                    'libelle'=>'Achat de café',
                    'prix'=>0.5,
                    'quantite'=> $quantite
                ]);

            $achatResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
            $achatResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
                'Message' => 'L\'achat a bien été effectué.'
            ]);
        }

        $versementResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/utilisateur/' . $id . '/versement', [
                'libelle'=>'Don de 10.-',
                'montant'=>10
            ]);

        $versementResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $versementResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'Le versement a bien été effectué.'
        ]);

        // Récupère le solde de l'utilisateur
        $soldeResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('api/utilisateur/' . $id . '/solde');


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
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);

        // Appelle la route api/utilisateur/login pour login l'utilisateur et récupérer un token
        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $loginResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $loginResponse->assertJson(fn (AssertableJson $json) => $json->has('token')); // Check si la réponse contient un champ token

        $token = $loginResponse->json(['token']); // Récupération du token
        $id = Utilisateur::where('email', $utilisateur->email)->first()->id; // Récupération de l'ID de l'utilisateur qui vient d'être ajouté

        $achatResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/utilisateur/' . $id . '/achat', [
                'libelle'=>'Achat de café',
                'prix'=>0.5
            ]);

        $achatResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $achatResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'achat a bien été effectué.'
        ]);


        $versementResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/utilisateur/' . $id . '/versement', [
                'libelle'=>'Don de 10.-',
                'montant'=>10
            ]);

        $versementResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $versementResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'Le versement a bien été effectué.'
        ]);

        // Récupère le solde de l'utilisateur
        $HistoriqueResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('api/utilisateur/' . $id . '/historique');

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
        // Génère un utilisateur
        $utilisateur = Utilisateur::factory()->make();

        // Appelle la route api/utilisateur/register pour créer un nouvel utilisateur
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);

        // Appelle la route api/utilisateur/login pour login l'utilisateur et récupérer un token
        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => $utilisateur->email,
                'password' => $utilisateur->password
            ]);

        $loginResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $loginResponse->assertJson(fn (AssertableJson $json) => $json->has('token')); // Check si la réponse contient un champ token

        $token = $loginResponse->json(['token']); // Récupération du token
        $id = Utilisateur::where('email', $utilisateur->email)->first()->id; // Récupération de l'ID de l'utilisateur qui vient d'être ajouté

        // Récupère le solde de l'utilisateur
        $HistoriqueResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('api/utilisateur/' . $id . '/historique');

        $HistoriqueResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $HistoriqueResponse->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Historique' => 'Aucune transaction n\'a été effectué avec cet utilisateur.'
        ]);
    }
}
