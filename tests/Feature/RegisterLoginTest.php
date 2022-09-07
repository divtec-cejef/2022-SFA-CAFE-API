<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RegisterLoginTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Register un utilisateur afin de préparer le login
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
        $response = $this->post('api/utilisateur/register',
            [
                'nom' => $this->utilisateur->nom,
                'prenom' => $this->utilisateur->prenom,
                'email' => $this->utilisateur->email,
                'password' => $this->utilisateur->password
            ]);

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 200
        $response->assertJson([ // Check si la réponse est la même que celle retournée à l'utilisateur
            'Message' => 'L\'utilisateur a bien été créé.'
        ]);
        $this->assertDatabaseHas('utilisateurs', [ // Check si la base de données contient l'utilisateur qui vient d'être ajouté
            'nom' => $this->utilisateur->nom,
            'prenom' => $this->utilisateur->prenom,
            'email' => $this->utilisateur->email
            // 'password' => $utilisateur->password
        ]);
    }

    /**
     * Test de la méthode POST Register en créant un nouvel utilisateur sans problème
     *
     * @return void
     */
    public function testRegister(): void
    {
        //Appelle le setup
        parent::setUp();
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
        //Appelle le setup
        parent::setUp();

        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => $this->utilisateur->email,
                'password' => $this->utilisateur->password
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
        //Appelle le setup
        parent::setUp();

        $loginResponse = $this->post('api/utilisateur/login',
            [
                'email' => $this->utilisateur->email,
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
        //Appelle le setup
        parent::setUp();

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

}
