<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SocialLinks extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('sociallinks', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `sociallinks` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `nom` varchar(45) COLLATE utf8mb4_bin NOT NULL,
            `url` text COLLATE utf8mb4_bin NOT NULL,
            `utilisateur_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `sociallink_utilisateur_foreign` (`utilisateur_id`),
            CONSTRAINT `sociallink_utilisateur_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('sociallinks');
    }
}
