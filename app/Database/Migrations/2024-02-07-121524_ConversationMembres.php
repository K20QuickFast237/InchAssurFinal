<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConversationMembres extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('conversation_membres', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `conversation_membres` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `conversation_id` int unsigned NOT NULL,
            `membre_id` int unsigned NOT NULL,
            `isAdmin` tinyint NOT NULL DEFAULT '0' COMMENT 'Dans une conversation de groupe, seul un admin peut supprimer la conversation. Dans les autres cas, tout le monde est admin par défaut.',
            PRIMARY KEY (`id`,`conversation_id`),
            UNIQUE KEY `convMemb_conv_memb_uniq` (`conversation_id`,`membre_id`),
            KEY `convMemb_membre_foreign_idx` (`membre_id`),
            KEY `convMemb_conversation_foreign_idx` (`conversation_id`),
            CONSTRAINT `convMemb_conversation_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `convMemb_membre_foreign` FOREIGN KEY (`membre_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('conversation_membres');
    }
}
