<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssuranceCategoriesmkp extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('assurance_categoriesmkp', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `assurance_categoriesmkp` (
            `id` int unsigned NOT NULL,
            `assurance_id` int unsigned NOT NULL,
            `categorie_mkp_id` int NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `UNIQUE` (`assurance_id`,`categorie_mkp_id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('assurance_categoriesmkp');
    }
}
