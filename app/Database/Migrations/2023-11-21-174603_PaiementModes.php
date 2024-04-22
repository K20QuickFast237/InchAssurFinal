<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PaiementModes extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('paiement_modes', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `paiement_modes` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `image_id` int unsigned DEFAULT NULL,
            `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `operateur` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            PRIMARY KEY (`id`),
            KEY `paiement_mode_image_foreign_idx` (`image_id`),
            KEY `paiement_mode_nom_foreign` (`nom`),
            CONSTRAINT `paiement_mode_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `paiement_mode_nom_foreign` FOREIGN KEY (`nom`) REFERENCES `paiement_mode_lists` (`nom`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('paiement_modes');
    }
}
