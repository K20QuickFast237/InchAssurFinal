<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DocumentTitres extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('document_titres', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `document_titres` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `nom` varchar(75) COLLATE utf8mb4_bin NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `nom_UNIQUE` (`nom`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('document_titres');
    }
}
