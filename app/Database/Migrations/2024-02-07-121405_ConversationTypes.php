<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ConversationTypes extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('conversation_types', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `conversation_types` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `nom` varchar(75) COLLATE utf8mb4_bin NOT NULL,
            `description` varchar(255) COLLATE utf8mb4_bin NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('conversation_types');
    }
}
