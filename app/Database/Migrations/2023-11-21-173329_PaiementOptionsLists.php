<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PaiementOptionsLists extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('paiement_options_lists', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `paiement_options_lists` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `nom` varchar(75) COLLATE utf8mb4_bin NOT NULL,
            `description` varchar(255) COLLATE utf8mb4_bin NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `nom_UNIQUE` (`nom`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('paiement_options_lists');
    }
}
