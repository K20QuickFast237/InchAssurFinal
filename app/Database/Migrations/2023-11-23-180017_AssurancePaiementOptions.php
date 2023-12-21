<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssurancePaiementOptions extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('assurance_paiement_options', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `assurance_paiement_options` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `assurance_id` int unsigned NOT NULL,
            `paiement_option_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `assurance_paiement_option_assurance_id_foreign_idx` (`assurance_id`),
            KEY `assurance_paiement_option_option_id_foreign_idx` (`paiement_option_id`),
            CONSTRAINT `assurance_paiement_option_assurance_id_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `assurance_paiement_option_option_id_foreign` FOREIGN KEY (`paiement_option_id`) REFERENCES `paiement_options` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('assurance_paiement_options');
    }
}
