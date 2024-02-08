<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Signature extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('signatures', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `signatures` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `email` varchar(255) COLLATE utf8mb4_bin NOT NULL,
            `code` varchar(45) COLLATE utf8mb4_bin NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('signatures');
    }
}
