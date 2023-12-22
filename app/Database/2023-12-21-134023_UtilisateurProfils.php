<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UtilisateurProfils extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('utilisateur_profils', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `utilisateur_profils` (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `utilisateur_id` int unsigned NOT NULL,
                `profil_id` int unsigned NOT NULL,
                `defaultProfil` tinyint NOT NULL DEFAULT '0',
                `attributor` int unsigned DEFAULT NULL,
                `dateCreation` datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `utilisateurProfils_profil_utilisateur_uniq` (`utilisateur_id`,`profil_id`),
                KEY `utilisateurProfils_utilisateur_foreign` (`utilisateur_id`),
                KEY `utilisateurProfils_profil_foreign` (`profil_id`) /*!80000 INVISIBLE */,
                KEY `utilisateurProfils_attributor_foreign_idx` (`attributor`),
                CONSTRAINT `utilisateurProfils_attributor_foreign` FOREIGN KEY (`attributor`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `utilisateurProfils_profil_foreign` FOREIGN KEY (`profil_id`) REFERENCES `profils` (`id`),
                CONSTRAINT `utilisateurProfils_utilisateur_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('utilisateur_profils');
    }
}
