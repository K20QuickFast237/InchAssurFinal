<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProfessionLists extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('profession_lists', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `profession_lists` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `titre` varchar(255) COLLATE utf8mb4_bin NOT NULL,
            `description` text COLLATE utf8mb4_bin,
            PRIMARY KEY (`id`),
            UNIQUE KEY `titre_UNIQUE` (`titre`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('profession_lists');
    }
}
