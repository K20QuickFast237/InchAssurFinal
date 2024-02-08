<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SinistreImages extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('sinistre_images', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `sinistre_images` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `sinistre_id` int unsigned NOT NULL,
            `image_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `imgSinstre_sinistre_foreign_idx` (`sinistre_id`),
            KEY `imgSinistre_image_foreign_idx` (`image_id`),
            CONSTRAINT `imgSinistre_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `imgSinstre_sinistre_foreign` FOREIGN KEY (`sinistre_id`) REFERENCES `sinistres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('sinistre_images');
    }
}
