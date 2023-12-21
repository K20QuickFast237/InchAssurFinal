<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssuranceReductions extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('assurance_reductions', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `assurance_reductions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `assurance_id` int unsigned NOT NULL,
            `reduction_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `Assur_reduction_UNIQUE` (`reduction_id`,`assurance_id`),
            KEY `assur_reduct_assurance_id_foreign_idx` (`assurance_id`),
            KEY `assur_reduct_reduction_id_foreign_idx` (`reduction_id`),
            CONSTRAINT `assur_reduct_assurance_id_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `assur_reduct_reduction_id_foreign` FOREIGN KEY (`reduction_id`) REFERENCES `reductions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('assurance_reductions');
    }
}
