<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Conversations extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('conversations', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `conversations` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `image_id` int unsigned DEFAULT NULL,
            `type` varchar(75) COLLATE utf8mb4_bin NOT NULL,
            `etat` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '1' COMMENT 'inactive/active',
            `origin` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'App',
            PRIMARY KEY (`id`),
            KEY `conversation_image_foreign_idx` (`image_id`),
            KEY `conversation_type_foreign_idx` (`type`),
            KEY `conversation_origin` (`origin`),
            CONSTRAINT `conversation_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `conversation_origin` FOREIGN KEY (`origin`) REFERENCES `conversation_origins` (`nom`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `conversation_type_foreign` FOREIGN KEY (`type`) REFERENCES `conversation_types` (`nom`) ON DELETE RESTRICT ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('conversations');
    }
}
