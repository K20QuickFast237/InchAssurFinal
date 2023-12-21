<?php

namespace App\Traits;

trait StatutsListTrait
{
    /**
     * listStatuts
     * 
     * renvoie la liste des status d'une entite
     *
     * @return array
     */
    public function statutsList(): array
    {
        $data = [];

        foreach (static::$statuts as $key => $value) {
            $data[] = [
                'name' => $value,
                'value' => $key,
            ];
        }

        return $data;
    }
}
