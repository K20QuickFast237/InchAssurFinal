<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Portefeuilles extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('portefeuilles', true);

        # add the fields
        $this->db->simpleQuery("
            CREATE TABLE `portefeuilles` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `solde` float NOT NULL,
                `devise` varchar(45) DEFAULT NULL,
                `utilisateur_id` int unsigned NOT NULL,
                PRIMARY KEY (`id`),
                KEY `portefeuille_user_foreign_idx` (`utilisateur_id`),
                CONSTRAINT `portefeuille_user_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
            ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('portefeuilles');
    }
}
