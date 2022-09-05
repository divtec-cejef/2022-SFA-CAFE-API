<?php

namespace Tests\Feature;

use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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

        $response->assertStatus(200); // Affirme que la réponse a un code d'état 201
        $response->assertJson($response->original); // Check si la réponse est la même que celle retournée à l'utilisateur -- à voir si utile
        $this->assertDatabaseHas('utilisateurs', [ // Check si la base de données contient l'utilisateur qui vient d'être ajouté
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email' => $utilisateur->email,
            // 'password' => $utilisateur->password
        ]);
    }
}
