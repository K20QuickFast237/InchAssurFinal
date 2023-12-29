<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssuranceCategories extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('assurance_categories', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `assurance_categories` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `assurance_id` int unsigned NOT NULL,
            `categorie_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `assurance_categorie_UNIQUE` (`categorie_id`,`assurance_id`),
            KEY `assurCat_assur_foreign_idx` (`assurance_id`),
            KEY `assurCat_categorie_foreign_idx` (`categorie_id`),
            CONSTRAINT `assurCat_assur_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `assurCat_categorie_foreign` FOREIGN KEY (`categorie_id`) REFERENCES `categorie_produits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('assurance_categories');
    }
}
