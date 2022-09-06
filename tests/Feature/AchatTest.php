<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use App\Models\Achat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AchatTest extends TestCase
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
     * Test de la méthode POST achat
     * Ce dernier ajoute un enregistrement dans la table achats
     * La quantité n'est pas spécifiée afin de tester la valeur par défaut
     *
     * @return void
     */
    public function testAchat(): void
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
        // Quantité est exprès non spécifié afin de vérifier que la valeur par défaut s'applique correctement
        $achatResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/utilisateur/' . $id . '/achat', [
                'libelle'=>'Achat de café',
                'prix'=>0.5
            ]);

        $achatResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
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
        $achatResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/utilisateur/' . $id . '/achat', [
                'libelle'=>'Achat de café',
                'prix'=>0.5,
                'quantite'=>4
            ]);

        $achatResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
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
}
