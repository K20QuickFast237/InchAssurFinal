<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TVAs extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('tvas', true);

        # add the fields
        $this->db->simpleQuery("
        SELECT * FROM newinchassurdb2.tvas;CREATE TABLE `tvas` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `taux` float NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `taux_UNIQUE` (`taux`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('tvas');
    }
}
