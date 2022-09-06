<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use App\Models\Versement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class VersementTest extends TestCase
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
     * Test de la méthode POST versement
     * Ce dernier ajoute un enregistrement dans la table versements
     *
     * @return void
     */
    public function testVersement(): void
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
        $versementResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->post('api/utilisateur/' . $id . '/versement', [
                'libelle'=>'Don de 20.-',
                'montant'=>20
            ]);

        $versementResponse->assertStatus(200); // Affirme que la réponse a un code d'état 200
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
