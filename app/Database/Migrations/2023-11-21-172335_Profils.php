<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Profils extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('profils', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `profils` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `titre` varchar(70) NOT NULL,
            `niveau` varchar(45) NOT NULL,
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateSuppression` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('profils');
    }
}
