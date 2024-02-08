<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Incidents extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('incidents', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `incidents` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(75) COLLATE utf8mb4_bin NOT NULL,
            `titre` varchar(150) COLLATE utf8mb4_bin NOT NULL,
            `description` varchar(255) COLLATE utf8mb4_bin NOT NULL,
            `statut` tinyint NOT NULL DEFAULT '1' COMMENT 'inactif/actif',
            `type_id` int unsigned NOT NULL,
            `auteur_id` int unsigned NOT NULL,
            `conversation_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `incidents_type_foreign_idx` (`type_id`),
            KEY `incidents_auteur_foreign_idx` (`auteur_id`),
            KEY `incidents_conversation_id_idx` (`conversation_id`),
            CONSTRAINT `incidents_auteur_foreign` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `incidents_conversation_id` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `incidents_type_foreign` FOREIGN KEY (`type_id`) REFERENCES `incident_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('incidents');
    }
}
