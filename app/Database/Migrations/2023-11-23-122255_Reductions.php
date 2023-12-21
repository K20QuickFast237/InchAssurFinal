<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Reductions extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('reductions', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `reductions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `description` text COLLATE utf8mb4_bin NOT NULL,
            `code` varchar(45) COLLATE utf8mb4_bin NOT NULL,
            `auteur_id` int unsigned NOT NULL,
            `valeur` float DEFAULT NULL,
            `taux` float DEFAULT NULL,
            `usage_max_nombre` int DEFAULT NULL,
            `expiration_date` date DEFAULT NULL,
            `utilise_nombre` int NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `code_UNIQUE` (`code`),
            KEY `reduction_auteur_id_foreign_idx` (`auteur_id`),
            CONSTRAINT `reduction_auteur_id_foreign` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('reductions');
    }
}
