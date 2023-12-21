<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Souscriptions extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('souscriptions', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `souscriptions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `cout` float NOT NULL,
            `souscripteur_id` int unsigned NOT NULL,
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `assurance_id` int unsigned NOT NULL,
            `etat` tinyint NOT NULL DEFAULT '0',
            `dateDebutValidite` date DEFAULT NULL,
            `dateFinValidite` date DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `souscription_souscripteur_id_foeriegn_idx` (`souscripteur_id`),
            KEY `souscription_assurance_foreign_idx` (`assurance_id`),
            CONSTRAINT `souscription_assurance_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `souscription_souscripteur_id_foeriegn` FOREIGN KEY (`souscripteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
          ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('souscriptions');
    }
}
