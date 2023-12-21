<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SouscriptionBeneficiaires extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('souscription_beneficiaires', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `souscription_beneficiaires` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `souscription_id` int unsigned NOT NULL,
            `beneficiaire_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `sous_benef_UNIQUE` (`souscription_id`,`beneficiaire_id`),
            KEY `sousBenef_souscription_foreign_idx` (`souscription_id`),
            KEY `sousBenef_beneficiaire_foreign_idx` (`beneficiaire_id`),
            CONSTRAINT `sousBenef_beneficiaire_foreign` FOREIGN KEY (`beneficiaire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `sousBenef_souscription_foreign` FOREIGN KEY (`souscription_id`) REFERENCES `souscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('souscription_beneficiaires');
    }
}
