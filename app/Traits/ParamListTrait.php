<?php

namespace App\Traits;

trait ParamListTrait
{
    /**
     * etatsList
     * 
     * renvoie la liste d'une entité correspondant au paramètre
     *
     * @return array
     */
    public function paramList(string $listName): array
    {
        $data = [];

        foreach (static::$$listName as $key => $value) {
            $data[] = [
                'name' => $value,
                'value' => $key,
            ];
        }

        return $data;
    }
}
