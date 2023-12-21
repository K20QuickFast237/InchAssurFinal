<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CategoryProducts extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('categorie_produits', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `categorie_produits` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `nom` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `image_id` int unsigned DEFAULT NULL,
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateSuppression` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `nom_UNIQUE` (`nom`),
            KEY `image_foreign_idx` (`image_id`),
            CONSTRAINT `image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('categorie_produits');
    }
}
