<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Assurances extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('assurances', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `assurances` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `nom` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
            `short_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `prix` float NOT NULL,
            `type_id` int unsigned NOT NULL,
            `pieces_a_joindre` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
            `type_contrat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `duree` int DEFAULT NULL,
            `listeServices` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `assureur_id` int unsigned NOT NULL,
            `categorie_id` int unsigned NOT NULL,
            `etat` int NOT NULL DEFAULT '0' COMMENT 'definit l''etat du produit (désactivé, actif etc...)',
            PRIMARY KEY (`id`),
            UNIQUE KEY `nom_UNIQUE` (`nom`),
            KEY `assurances_type_foreign_idx` (`type_id`),
            KEY `assurances_assureur_id_foreign_idx` (`assureur_id`),
            KEY `assurances_categorie_id_foreign_idx` (`categorie_id`),
            CONSTRAINT `assurances_assureur_id_foreign` FOREIGN KEY (`assureur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `assurances_categorie_id_foreign` FOREIGN KEY (`categorie_id`) REFERENCES `categorie_produits` (`id`),
            CONSTRAINT `assurances_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `assurance_types` (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('assurances');
    }
}
