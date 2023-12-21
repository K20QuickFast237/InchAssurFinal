<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssuranceImages extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('assurance_images', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `assurance_images` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `assurance_id` int unsigned NOT NULL,
            `image_id` int unsigned NOT NULL,
            `isDefault` tinyint NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `assurance_images_assurance_id_foreign_idx` (`assurance_id`),
            KEY `assurance_images_image_id_foreign_idx` (`image_id`),
            CONSTRAINT `assurance_images_assurance_id_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `assurance_images_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('assurance_images');
    }
}
