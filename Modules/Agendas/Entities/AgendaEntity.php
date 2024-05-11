<?php

namespace Modules\Agendas\Entities;

use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;

class AgendaEntity extends Entity
{
    use EtatsListTrait;

    const DAY = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche',];
    const NOT_AVAILABLE = 0, AVAILABLE = 1;

    protected $datamap = [
        'idAgenda'     => 'id',
        'heureDebut'   => 'heure_dispo_debut',
        'heureFin'     => 'heure_dispo_fin',
        'jour'         => 'jour_dispo',
        'proprietaire' => 'proprietaire_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'                => "integer",
        'statut'            => "etatcaster['Indisponible','Disponible']",
    ];

    public function getProprietaireId()
    {
        if (isset($this->attributes['proprietaire_id']) && gettype($this->attributes['proprietaire_id']) === 'string') {
            $this->attributes['proprietaire_id'] = model("UtilisateursModel")->getSimplified($this->attributes['proprietaire_id']);
        }

        return $this->attributes['proprietaire_id'];
    }
}
