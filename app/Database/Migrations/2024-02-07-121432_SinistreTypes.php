<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SinistreTypes extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('sinistre_types', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `sinistre_types` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `nom` varchar(150) COLLATE utf8mb4_bin NOT NULL,
            `description` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
            `statut` tinyint NOT NULL DEFAULT '1' COMMENT 'inactif/actif',
            `catProduit_id` int unsigned NOT NULL COMMENT 'actif/inactif',
            PRIMARY KEY (`id`),
            UNIQUE KEY `nom_UNIQUE` (`nom`),
            KEY `sinistra_catProd_id_foreign_idx` (`catProduit_id`),
            CONSTRAINT `sinistra_catProd_id_foreign` FOREIGN KEY (`catProduit_id`) REFERENCES `categorie_produits` (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('sinistre_types');
    }
}
