<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Sinistres extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('sinistres', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `sinistres` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(25) COLLATE utf8mb4_bin NOT NULL,
            `titre` varchar(150) COLLATE utf8mb4_bin NOT NULL,
            `description` text COLLATE utf8mb4_bin NOT NULL,
            `statut` tinyint NOT NULL DEFAULT '1' COMMENT 'Termine/En cours',
            `auteur_id` int unsigned NOT NULL,
            `type_id` int unsigned NOT NULL,
            `souscription_id` int unsigned NOT NULL,
            `conversation_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `sinistre_auteur_foreign_idx` (`auteur_id`),
            KEY `sinistre_type_foreign_idx` (`type_id`),
            KEY `sinistre_souscription_foreign_idx` (`souscription_id`),
            KEY `sinistre_conversation_foreign_idx` (`conversation_id`),
            CONSTRAINT `sinistre_auteur_foreign` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
            CONSTRAINT `sinistre_conversation_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `sinistre_souscription_foreign` FOREIGN KEY (`souscription_id`) REFERENCES `souscriptions` (`id`) ON UPDATE CASCADE,
            CONSTRAINT `sinistre_type_foreign` FOREIGN KEY (`type_id`) REFERENCES `sinistre_types` (`id`) ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('sinistres');
    }
}
