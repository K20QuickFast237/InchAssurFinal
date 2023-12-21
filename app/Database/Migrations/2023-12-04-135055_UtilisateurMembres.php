<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UtilisateurMembres extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('utilisateur_membres', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `utilisateur_membres` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `utilisateur_id` int unsigned NOT NULL,
            `membre_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `Usermemb_user_foreign` (`utilisateur_id`),
            KEY `Usermemb_membre_foreign` (`membre_id`),
            CONSTRAINT `Usermemb_membre_foreign` FOREIGN KEY (`membre_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `Usermemb_user_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('utilisateur_membres');
    }
}
