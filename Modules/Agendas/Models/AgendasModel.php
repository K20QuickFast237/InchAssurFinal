<?php

namespace Modules\Agendas\Models;

use CodeIgniter\Model;
use Modules\Agendas\Entities\AgendaEntity;

class AgendasModel extends Model
{
    // public $day = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche',];
    const DAY = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche',];
    const NOT_AVAILABLE = 0, AVAILABLE = 1;

    protected $DBGroup          = 'default';
    protected $table            = 'agendas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = AgendaEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['titre', 'heure_dispo_debut', 'heure_dispo_fin', 'statut', 'slots', 'jour_dispo', 'proprietaire_id'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'dateCreation';
    protected $updatedField  = 'dateModification';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['encodeSlots'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['encodeSlots'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = ['decodeSlots'];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    protected function decodeSlots(array $data)
    {
        if ($data['data'] === null) {
            return $data;
        }
        if (isset($data['data']->slots)) {
            $data['data']->slots = json_decode($data['data']->slots ?? [], true);
        } else
            
        if (isset($data['data']['slots'])) {
            $data['data']['slots'] = json_decode($data['data']['slots'] ?? [], true);
        } else {
            for ($i = 0; $i < count($data['data']); $i++) {
                if (isset($data['data'][$i]->slots)) {
                    $data['data'][$i]->slots = json_decode($data['data'][$i]->slots ?? [], true);
                } else
                if (isset($data['data'][$i]['slots'])) {
                    $data['data'][$i]['slots'] = json_decode($data['data'][$i]['slots'] ?? [], true);
                }
            }
        }
        return $data;
    }

    protected function encodeSlots(array $data)
    {
        if ($data['data'] === null) {
            return $data;
        }
        if (isset($data['data']->slots)) {
            $data['data']->slots = json_encode($data['data']->slots);
        } else
            
        if (isset($data['data']['slots'])) {
            $data['data']['slots'] = json_encode($data['data']['slots']);
        } else {
            for ($i = 0; $i < count($data['data']); $i++) {
                if (isset($data['data'][$i]->slots)) {
                    $data['data'][$i]->slots = json_encode($data['data'][$i]->slots);
                } else
                if (isset($data['data'][$i]['slots'])) {
                    $data['data'][$i]['slots'] = json_encode($data['data'][$i]['slots']);
                }
            }
        }
        return $data;
    }







    // n'est plus utilisée
    protected function inFormatJour(array $data)
    {
        if (isset($data['data']['jour_dispo'])) {
            $data['data']['jour_dispo'] = array_search($data['data']['jour_dispo'], self::DAY);
        } else {
            for ($i = 0; $i < count($data['data']); $i++) {
                if (isset($data['data'][$i]['jour_dispo'])) {
                    $data['data'][$i]['jour_dispo'] = array_search($data['data'][$i]['jour_dispo'], self::DAY);
                }
            }
        }

        if (!isset($data['data']['titre']) || empty($data['data']['titre'])) {
            $data['data']['titre'] = 'Disponible';
        } else {
            for ($i = 0; $i < count($data['data']); $i++) {
                if (!isset($data['data'][$i]['titre']) || empty($data['data'][$i]['titre'])) {
                    $data['data'][$i]['titre'] = 'Disponible';
                }
            }
        }
        return $data;
    }

    protected function outFormatJour(array $data)
    {
        if ($data['data'] === null) {
            return $data;
        }
        if (isset($data['data']->jour_dispo)) {
            $data['data']->jour_dispo = self::DAY[$data['data']->jour_dispo];
        } else
        if (isset($data['data']['jour_dispo'])) {
            $data['data']['jour_dispo'] = self::DAY[$data['data']['jour_dispo']];
        } else {
            for ($i = 0; $i < count($data['data']); $i++) {
                if (isset($data['data'][$i]->jour_dispo)) {
                    $data['data'][$i]->jour_dispo = self::DAY[$data['data'][$i]->jour_dispo];
                } else
                if (isset($data['data'][$i]['jour_dispo'])) {
                    $data['data'][$i]['jour_dispo'] = self::DAY[$data['data'][$i]['jour_dispo']];
                }
            }
        }
        return $data;
    }


    /**
     * findMedByDate
     *
     * recherche les medecins en fonction de leur agenda.
     * 
     * @param  string $day
     * @param  string $hour
     * @return array une liste de userIDs
     */
    public function findMedIdsByDate($day = null, $hour = null)
    {
        //extraire le jour et l'heure
        $day = ($day != null) ? date('Y-m-d', strtotime($day)) : null;
        $hour = ($hour != null) ? date('H:i:s', strtotime($hour)) : null;
        // $h_debut = $hour ? date('H:i:s', strtotime("$hour + 10 minute")) : null;
        // $h_fin = $hour ? date('H:i:s', strtotime("$hour - 10 minute")) : null;

        // renvoyer tous les medecins ayant cette combinaison heure et date dans son agenda
        if (($day !== null) && ($hour !== null)) {
            $sql = "SELECT proprietaire_id FROM IA_agendas WHERE jour_dispo = :jour: AND CAST(heure_dispo_debut AS TIME) <= :heure_debut: AND CAST(heure_dispo_fin AS TIME) >= :heure_fin:";
        }
        // si une date est fournie sans heure, 
        // renvoyer tous les medecnis ayant un créneau de disponibilité à cette date
        elseif (($day !== null) && ($hour === null)) {
            $sql = "SELECT proprietaire_id FROM IA_agendas WHERE jour_dispo = :jour:";
        }
        // si une heure est fournie sans date, 
        // renvoyer tous les medecnis ayant un créneau de disponibilité à cette heure du jour courant
        elseif (($hour !== null) && ($day === null)) {
            $day = date('Y-m-d');
            $sql = "SELECT proprietaire_id FROM IA_agendas WHERE jour_dispo = :jour: AND CAST(heure_dispo_debut AS TIME) <= :heure_debut: AND CAST(heure_dispo_fin AS TIME) >= :heure_fin:";
        }

        $query = $this->db->query($sql, [
            'jour'        => $day,
            'heure_debut' => $hour,   // ou $h_debut
            'heure_fin'   => $hour,  // ou $h_fin
        ]);
        $results = $query->getResultArray();
        $results = array_column($results, 'proprietaire_id');

        return $results;
    }

    public function fromTodayAgenda(int $medID)
    {
        return $this->asArray()->where('proprietaire_id', $medID)
            ->where('jour_dispo >=', date('Y-m-d'))
            ->where('statut', self::AVAILABLE)
            ->orderBy('jour_dispo', 'ASC')
            ->orderBy('heure_dispo_debut', 'ASC')
            ->findAll();
    }

    public function bulkFromTodayAgenda(array $medID)
    {
        $result = [];
        $list = $this->asArray()->whereIn('proprietaire_id', $medID)
            ->where('jour_dispo >=', date('Y-m-d'))
            ->where('statut', self::AVAILABLE)
            ->orderBy('jour_dispo', 'ASC')
            ->orderBy('heure_dispo_debut', 'ASC')
            ->findAll();
        $i = 0;
        // print_r($list);
        foreach ($medID as $item) {
            foreach ($list as $key => $value) {
                if ($value['proprietaire_id'] == $item) {
                    unset($value['proprietaire_id']);
                    $result[$i][] = $value;
                    // unset($list[$key]) ;
                }
            }
            // if (empty($result[$i])) {
            //     $result[$i] = [];
            // }
            $i++;
        }
        // foreach ($medID as $key => $value) {
        //     $result[] = array_filter($list, function ($val) use ($value){
        //         return $val['proprietaire_id'] == $value;
        //     });
        // }

        return $result;
    }

    /**
     * hasAgenda
     *
     * Sorte de filtre qui prends un tableau d'identifiants de medecins
     * et retourne un tableau ne contenant que les identifiants de ceux
     * qui pocèdent un agenda
     * 
     * @param  array $medIDs
     * @return array
     */
    public static function hasAgenda(array $medIDs)
    {
        if (empty($medIDs)) {
            return [];
        }
        $db      = \Config\Database::connect();
        $builder = $db->table('IA_agendas');
        $builder->select('distinct(proprietaire_id)');
        $medIDs  = $builder->whereIn('proprietaire_id', $medIDs);
        $medIDs  = $builder->get();

        return array_column($medIDs->getResultArray(), 'proprietaire_id');
    }
}
