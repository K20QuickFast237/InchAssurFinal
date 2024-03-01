<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Message extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('messages', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `messages` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `msg_text` tinytext COLLATE utf8mb4_bin NOT NULL,
            `from_user_id` int unsigned NOT NULL,
            `to_conversation_id` int unsigned NOT NULL,
            `statut` tinyint NOT NULL,
            `etat` tinyint NOT NULL DEFAULT '1',
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `message_from_foreign_idx` (`from_user_id`),
            KEY `message_to_foreign_idx` (`to_conversation_id`),
            CONSTRAINT `message_from_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `message_to_foreign` FOREIGN KEY (`to_conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('messages');
    }
}
