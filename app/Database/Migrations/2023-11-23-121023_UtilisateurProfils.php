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
            `defaultProfil` tinyint NOT NULL,
            `attributeur_id` int unsigned NOT NULL,
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateSuppression` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `utilisateurprofils_utilisateur_id_foreign_idx` (`utilisateur_id`),
            KEY `utilisateurprofils_profil_id_foreign_idx` (`profil_id`),
            KEY `utilisateurprofils_attributeur_foreign_idx` (`attributeur_id`),
            CONSTRAINT `utilisateurprofils_attributeur_foreign` FOREIGN KEY (`attributeur_id`) REFERENCES `utilisateurs` (`id`),
            CONSTRAINT `utilisateurprofils_profil_id_foreign` FOREIGN KEY (`profil_id`) REFERENCES `profils` (`id`),
            CONSTRAINT `utilisateurprofils_utilisateur_id_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON UPDATE CASCADE          
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('utilisateur_profils');
    }
}
