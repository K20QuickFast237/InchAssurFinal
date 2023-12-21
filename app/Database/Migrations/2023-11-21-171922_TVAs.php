<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TVAs extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('tva', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `tva` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `taux` float NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('tva');
    }
}
