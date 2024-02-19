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

        $categorieProd = [
            [
                "nom"         => "Assurances santé",
                "description" => "Assurance et services liés à la santé santé.",
                "image_id"    => 1,
            ],
            [
                "nom"         => "Assurance Auto",
                "description" => "Assurance pour vos véhicules de toutes sortes.",
            ],
            [
                "nom"         => "Assurance loisir",
                "description" => "Assurance loisir.",
            ],
            [
                "nom"         => "Autre",
            ],
        ];
        // Using Query Builder
        $this->db->table('categorie_produits')->insertBatch($categorieProd);

        $conversationType = [
            [
                "nom"         => "Incident",
                "description" => "les conversations de suivi des incidents.",
            ],
            [
                "nom"         => "Sinistre",
                "description" => "Les conversations de suivi des sinistres.",
            ],
            [
                "nom"         => "Groupe",
                "description" => "Les conversations libres de groupe.",
            ],
            [
                "nom"         => "Message",
                "description" => "les conversations avec un utilisateur précis.",
            ],
            [
                "nom"         => "Autre",
                "description" => "les conversations internes ou intégrées.",
            ],
        ];
        // Using Query Builder
        $this->db->table('conversation_types')->insertBatch($conversationType);

        $incidentType = [
            [
                "nom"         => "Problème de connexion",
                "description" => "Vous faites face à un disfonctionnement du processus de connexion.",
                "statut"      => 1,
            ],
            [
                "nom"         => "Problème de création de compte",
                "description" => "Vous ne parvenez pas à créer votre compte pour un quelconque motif.",
                "statut"      => 1,
            ],
        ];
        // Using Query Builder
        $this->db->table('incident_types')->insertBatch($incidentType);

        $payOptionList = [
            [
                "nom"         => "Unique",
                "description" => "Payer en une seule fois le montnant requis.",
            ],
            [
                "nom"         => "A Echéance",
                "description" => "Après un dépot initial, une date échéance est convenue pour atteindre le montnat requis.",
            ],
            [
                "nom"         => "Planifié",
                "description" => "Payer un montant défini à intervalle défini, autant de fois que nécessaire pour atteindre le montant requis.",
            ],
        ];
        // Using Query Builder
        $this->db->table('paiement_options_lists')->insertBatch($payOptionList);

        $paiementPays = [
            [
                "nom"  => "Cameroun",
                "code" => "CM",
            ],
            [
                "nom"  => "Senegal",
                "code" => "SN",
            ],
            [
                "nom"  => "Congo-Kinshasa",
                "code" => "CD",
            ],
            [
                "nom"  => "Liberia",
                "code" => "LR",
            ],
            [
                "nom"  => "Congo-Brazzaville",
                "code" => "CG",
            ],
            [
                "nom"  => "Uganda",
                "code" => "UG",
            ],
            [
                "nom"  => "Benin",
                "code" => "BJ",
            ],
            [
                "nom"  => "Guinee-Conakry",
                "code" => "GN",
            ],
            [
                "nom"  => "Gabon",
                "code" => "GA",
            ],
        ];
        // Using Query Builder
        $this->db->table('paiement_pays')->insertBatch($paiementPays);

        $paiementPays = [
            [
                "nom"           => "Décès",
                "description"   => "La personne couverte par l'assurance est décédé.",
                "statut"        => 1,
                "catProduit_id" => 3
            ],
            [
                "nom"           => "Catastrophes naturelles",
                "description"   => "La garantie catastrophe naturelle couvre les dommages matériels subis par le véhicule assuré à la suite d’un événement naturel de forte ampleur.",
                "statut"        => 1,
                "catProduit_id" => 4
            ],
            [
                "nom"           => "Vandalisme",
                "description"   => "Un acte de vandalisme sur une voiture est une dégradation volontaire du véhicule. Le vandalisme a deux objectifs : provoquer le propriétaire de la voiture et lui occasionner des dépenses lourdes de réparations.",
                "statut"        => 1,
                "catProduit_id" => 4
            ],
            [
                "nom"           => "Stationnement",
                "description"   => "De façon générale, dès qu’une voiture occupe une place de façon ininterrompue au-delà de la durée légale de 7 jours, le stationnement est considéré comme abusif.",
                "statut"        => 1,
                "catProduit_id" => 4
            ],
            [
                "nom"           => "Incendie",
                "description"   => "Comme pour le vol, la garantie incendie n’est effective qu’en cas de déclaration préalable aux autorités. L’assuré dispose de 5 jours pour déclarer le sinistre à son assureur en lui envoyant une attestation de dépôt de plainte.",
                "statut"        => 1,
                "catProduit_id" => 4
            ],
            [
                "nom"           => "Vol",
                "description"   => "Un vol de voiture doit être déclaré dans les 24 heures à la police et dans les 48 heures à l’assurance. Les autorités remettent au propriétaire du véhicule une attestation de dépôt de plainte, qui doit ensuite être envoyée par lettre recommandée (ou remise en main propre) à l’assureur.",
                "statut"        => 1,
                "catProduit_id" => 4
            ],
            [
                "nom"           => "Bris de glace",
                "description"   => "La garantie optionnelle bris de glace est utile en cas de vitre cassée ou fissurée. Si vous êtes victime d’un bris de glace, vous avez 5 jours pour déclarer le sinistre à votre assureur et obtenir un dédommagement",
                "statut"        => 1,
                "catProduit_id" => 4
            ],
            [
                "nom"           => "Accident",
                "description"   => "Un automobiliste victime d’un accident de voiture a 5 jours pour remplir un constat à l’amiable et l’envoyer à son assureur auto",
                "statut"        => 1,
                "catProduit_id" => 4
            ],
            [
                "nom"           => "Autre",
                "statut"        => 1,
                "catProduit_id" => 6
            ],
        ];
        // Using Query Builder
        $this->db->table('paiement_pays')->insertBatch($paiementPays);
    }
}
