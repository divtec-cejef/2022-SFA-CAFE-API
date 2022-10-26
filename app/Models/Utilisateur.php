<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Model
{

    // Messages d'erreurs
    public const emailAlreadyUsed = "L'adresse e-mail est déjà utilisée.";  // E-mail déjà utilisée à la création du compte
    public const unableToCreateUser = "Impossible de créer l'utilisateur. Veuillez contacter un administrateur."; // Erreur serveur à la création de l'utilisateur
    public const emailNotFound = "L'adresse e-mail n'existe pas.";          // Tentative de connexion à un e-mail inexistant dans la BDD

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    // protected $table = 'utilisateurs';

    /**
     * La clé primaire associée à la table.
     *
     * @var string
     */
    // protected $primaryKey = 'id';

    /**
     * Validation des données
     * @return string[] qui contient les règles des propriétés
     */
    static function validateRules()
    {
        return [
            'nom'     => 'string|required|max:100',
            'prenom'   => 'string|required|max:100',
            'email'     => 'string|required|unique:utilisateurs,email|max:75',
            'password'   => 'string|required|max:75',
            'actif'  => 'boolean',
            'admin' => 'boolean'
        ];
    }

    /**
     * Liste des attributs modifiables
     *
     * @var array
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'actif',
        'admin'
    ];

    /**
     * Liste des attributs cachés
     * Seront exclus dans les réponses
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'actif' => 'boolean',
        'admin' => 'boolean'
    ];
}
