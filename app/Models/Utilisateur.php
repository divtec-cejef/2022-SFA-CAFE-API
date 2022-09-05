<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Model
{

    // Messages d'erreurs
    // Affiche ce message lors qu'une adresse e-mail est déjà utilisée
    public const emailAlreadyUsed = "L'adresse e-mail est déjà utilisée.";
    public const unableToCreateUser = "Impossible de créer l'utilisateur.";

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
            'actif'  => 'boolean'
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
        'actif'
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
}
