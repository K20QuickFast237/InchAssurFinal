<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Questions extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('questions', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `questions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `auteur_id` int unsigned NOT NULL,
            `libelle` varchar(255) COLLATE utf8mb4_bin NOT NULL,
            `description` text COLLATE utf8mb4_bin,
            `tarif_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `field_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `requis` tinyint NOT NULL,
            `options` text COLLATE utf8mb4_bin NOT NULL,
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `question_auteur_foreign` (`auteur_id`),
            CONSTRAINT `question_auteur_foreign` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('questions');
    }
}
