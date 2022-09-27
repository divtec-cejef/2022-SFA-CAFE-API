<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    // protected $table = 'configs';

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
            'valeur'   => 'string|required|max:100'
        ];
    }

    /**
     * Liste des attributs modifiables
     *
     * @var array
     */
    protected $fillable = [
        'nom',
        'valeur'
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
