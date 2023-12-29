<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialiseData extends Seeder
{
    public function run()
    {
        $types = [
            [
                'nom'         => "Individuelle",
                'description' => "Assurance pour une seule personne",
            ],
            [
                'nom'         => "Collective",
                'description' => "Permet de prendre une assurance qui couvre plusieurs personnes",
            ],
            [
                'nom'         => "De Bien",
                'description' => "Assurance pour les biens que vous n'aimerez pas perdre",
            ],
            [
                'nom'         => "D'activité",
                'description' => "Assurance pour vous aider a démarrer votre activité sans stress",
            ],
        ];

        // Simple Queries
        // $this->db->query("INSERT INTO assurance_types (nom,description) VALUES(:nom:,:description:)", $types);

        // Using Query Builder
        $this->db->table('assurance_types')->insertBatch($types);

        $profils = [
            [
                "titre"       => "Particulier",
                "description" => "Enregistre des membres (de sa famille) et achete des produits individuel ou de groupe.",
                "niveau"      => "IA1",
            ],
            [
                "titre"       => "Famille",
                "description" => "Enregistre des membres (de sa famille) et achete des produits individuel ou de groupe.",
                "niveau"      => "IA2",
            ],
            [
                "titre"       => "Entreprise",
                "description" => "Enregistre des utilisateurs (ses employés) et achete des produits en groupe (pour ses employés).",
                "niveau"      => "IA3",
            ],
            [
                "titre"       => "Prescripteur",
                "description" => "Prescipteur",
                "niveau"      => "IA4",
            ],
            [
                "titre"       => "Agent",
                "description" => "Agent",
                "niveau"      => "IA5",
            ],
            [
                "titre"       => "Infirmier",
                "description" => "Infirmier",
                "niveau"      => "IA6",
            ],
            [
                "titre"       => "Medecin",
                "description" => "Effectue des consultations et propose ses services de consultation.",
                "niveau"      => "IA7",
            ],
            [
                "titre"       => "Assureur",
                "description" => "Ajoute des Assurances.",
                "niveau"      => "IA8",
            ],
            [
                "titre"       => "Partenaire",
                "description" => "Partenaire",
                "niveau"      => "IA9",
            ],
            [
                "titre"       => "Administrateur",
                "description" => "Administre la plateforme en effectuant des configurations et autres opérations.",
                "niveau"      => "IA10",
            ],
            [
                "titre"       => "Super Admin",
                "description" => "Super Administrateur",
                "niveau"      => "IA11",
            ],
            [
                "titre"       => "Membre",
                "description" => "Ne peut effectuer aucune action",
                "niveau"      => "IA12",
            ],
        ];

        // Using Query Builder
        $this->db->table('profils')->insertBatch($profils);
    }
}
