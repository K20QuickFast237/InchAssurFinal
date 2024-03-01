<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CategorieMkps extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('categorie_mkps', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `categorie_mkps` (
            `id` int unsigned NOT NULL,
            `nom` varchar(45) COLLATE utf8mb4_bin NOT NULL,
            `description` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('categorie_mkps');
    }
}
