<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{

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
            'nom'     => 'required|max:100',
            'prenom'   => 'required|max:100',
            'email'     => 'required|unique:email|max:75',
            'password'   => 'required|max:75',
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
