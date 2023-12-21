<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Paiements extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('paiements', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `paiements` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(75) COLLATE utf8mb4_bin NOT NULL,
            `montant` float NOT NULL,
            `statut` tinyint NOT NULL,
            `mode_id` int unsigned NOT NULL,
            `auteur_id` int unsigned NOT NULL,
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateSuppression` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `code_UNIQUE` (`code`),
            KEY `paiement_mode_id_foreign_idx` (`mode_id`),
            KEY `paiement_auteur_id_foreign_idx` (`auteur_id`),
            CONSTRAINT `paiement_auteur_id_foreign` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`),
            CONSTRAINT `paiement_mode_id_foreign` FOREIGN KEY (`mode_id`) REFERENCES `paiement_modes` (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('paiements');
    }
}
