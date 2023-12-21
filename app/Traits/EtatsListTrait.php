<?php

namespace App\Traits;

trait EtatsListTrait
{
    /**
     * etatsList
     * 
     * renvoie la liste des etats d'une entite
     *
     * @return array
     */
    public function etatsList(): array
    {
        $data = [];

        foreach (static::$etats as $key => $value) {
            $data[] = [
                'name' => $value,
                'value' => $key,
            ];
        }

        return $data;
    }
}
