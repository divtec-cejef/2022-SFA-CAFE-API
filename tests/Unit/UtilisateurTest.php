<?php


use App\Models\Utilisateur;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\TestCase;

class UtilisateurTest extends TestCase
{
    // Migration de la base de donnée lors de l'exécution des tests, puis effectue un rollback lorsque les tests sont terminés
    use DatabaseTransactions;

    // Création des variables qui serviront à effectuer les tests
    private $utilisateurs = [];
    private $achats = [];
    private $versements = [];

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

       $utilisateur = Utilisateur::factory()->make();

        $this->post('api/utilisateur/register',
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password,
                'actif' => $utilisateur->actif
            ]);

        $this->assertResponseOk(); //Affirme que la réponse a un code d'état 201:
        $this->seeJsonContains(
            [
                'nom' => $utilisateur->nom,
                'prenom' => $utilisateur->prenom,
                'email' => $utilisateur->email,
                'password' => $utilisateur->password,
                'actif' => $utilisateur->actif
            ]);
        $this->seeInDatabase('utilisateurs', [
            'nom' => $utilisateur->nom,
            'prenom' => $utilisateur->prenom,
            'email' => $utilisateur->email,
            'password' => $utilisateur->password,
            'actif' => $utilisateur->actif
        ]);
    }
}
