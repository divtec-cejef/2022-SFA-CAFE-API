<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Versement extends Model
{

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    // protected $table = 'achats';

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
            'libelle'     => 'string|required|max:200',
            'montant'     => 'required|numeric',
            'id_utilisateur'     => 'integer'
        ];
    }

    /**
     * Liste des attributs modifiables
     *
     * @var array
     */
    protected $fillable = [
        'libelle',
        'montant',
        'id_utilisateur'
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
