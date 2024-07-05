<?php

namespace Modules\Agendas\Entities;

use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;

class AgendaEntity extends Entity
{
    use EtatsListTrait;

    const DAY = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche',];
    const NOT_AVAILABLE = 0, AVAILABLE = 1;
    // const DEFAULT_DUREE = 30;

    protected $datamap = [
        'idAgenda'     => 'id',
        'heureDebut'   => 'heure_dispo_debut',
        'heureFin'     => 'heure_dispo_fin',
        'jour'         => 'jour_dispo',
        'proprietaire' => 'proprietaire_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'     => "integer",
        'duree'  => "integer",
        'statut' => "etatcaster[Indisponible,Disponible]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];

    public function getProprietaireId()
    {
        if (isset($this->attributes['proprietaire_id']) && gettype($this->attributes['proprietaire_id']) === 'string') {
            $this->attributes['proprietaire_id'] = model("UtilisateursModel")->getSimplified($this->attributes['proprietaire_id']);
        }

        return $this->attributes['proprietaire_id'];
    }

    public function unsetSlot(int $slotID)
    {
        return $this->removeSlot($slotID);
    }
    public function removeSlot(int $slotID) // The name will be changed to unsetSlot
    {
        /* old method, not more used
            $prev = count($this->attributes['slots']);
            $this->attributes['slots'] = array_values(array_filter($this->attributes['slots'], function ($slot) use ($slotID) {
                return $slot['id'] != $slotID;
            }));
            if ($prev == count($this->attributes['slots'])) {
                throw new \Exception('Le slot n\'existe pas');
            }
        */
        $this->attributes['slots'] = array_map(function ($sl) use ($slotID) {
            if ($sl['id'] == $slotID) {
                $sl['occupe'] = true;
            }
            return $sl;
        }, $this->attributes['slots']);
        // verify state
        $condition = array_filter($this->attributes['slots'], function ($slot) {
            return !$slot['occupe'];
        });
        if (count($condition) <= 0) {
            model("AgendasModel")->where('id', $this->attributes['id'])
                ->set('statut', self::NOT_AVAILABLE)
                ->update();
        }
    }

    public function setSlot(int $slotID)
    {
        $this->attributes['slots'] = array_map(function ($sl) use ($slotID) {
            if ($sl['id'] == $slotID) {
                $sl['occupe'] = false;
            }
            return $sl;
        }, $this->attributes['slots']);

        // verify state
        if (!$this->attributes['statut']) {
            model("AgendasModel")->where('id', $this->attributes['id'])
                ->set('statut', self::AVAILABLE)
                ->update();
        }
    }
}
