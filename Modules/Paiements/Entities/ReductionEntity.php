<?php

namespace Modules\Paiements\Entities;

use CodeIgniter\Entity\Entity;

class ReductionEntity extends Entity
{
    const EXPIREE = 0, VALIDE = 1;
    public static $statuts = ["Exprée", "Valide"];

    protected $datamap = [
        "idReduction" => "id",
        "idAuteur"   => "auteur_id"
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'      => "integer",
        'taux' => "integer",
        'valeur' => "float",
        'auteur_id' => "integer",
        'utilise_nombre' => "integer",
        'usage_max_nombre' => "integer",
        'etat'  => "etatcaster[Exprée,Valide]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];

    public function getIsActive()
    {
        $state = (int)$this->attributes['etat'];
        $state = $state && (strtotime($this->attributes['expiration_date']) >= strtotime(date('Y-m-d')));
        $state = $state && ($this->attributes['usage_max_nombre'] > $this->attributes['utilise_nombre']);

        return $state;
    }

    public function apply(float $prixInitial)
    {
        $valeur = (float)$this->attributes['valeur'];
        $taux   = (int)$this->attributes['taux'];
        if ($taux && $valeur) {
            $reductionTaux = ($prixInitial * $taux) / 100;
            $reduction     = $reductionTaux > $valeur ? $valeur : $reductionTaux;
        } elseif (!$taux) {
            $reduction = ($prixInitial * $taux) / 100;
        } else {
            $reduction = $valeur;
        }

        return $reduction;
    }

    public function update()
    {
        $this->attributes['utilise_nombre']++;
        if ($this->attributes['utilise_nombre'] >= $this->attributes['usage_max_nombre']) {
            $this->attributes['etat'] = self::EXPIREE;
        }
        model("ReductionsModel")->update($this->attributes['id'], $this);
    }
}
