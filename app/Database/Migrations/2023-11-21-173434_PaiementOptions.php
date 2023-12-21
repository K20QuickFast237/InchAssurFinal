<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PaiementOptions extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('paiement_options', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `paiement_options` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `type` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
            `depot_initial_taux` float NOT NULL,
            `montant_cible` float DEFAULT NULL,
            `cycle_longueur` int DEFAULT NULL,
            `cycle_nombre` int DEFAULT NULL,
            `cycle_taux` float DEFAULT NULL,
            `etape_duree` int DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `PayOpts_nom_foreign_idx` (`type`),
            CONSTRAINT `PayOpts_nom_foreign` FOREIGN KEY (`type`) REFERENCES `paiement_options_lists` (`nom`) ON DELETE RESTRICT ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('paiement_options');
    }
}
