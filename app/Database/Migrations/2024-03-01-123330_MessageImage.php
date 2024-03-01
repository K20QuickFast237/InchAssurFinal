<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MessageImage extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('message_images', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `message_images` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `message_id` int unsigned NOT NULL,
            `image_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `Unique` (`message_id`,`image_id`),
            KEY `messageImg_image_foreign_idx` (`image_id`),
            KEY `messageImg_message_foreign_idx` (`message_id`),
            CONSTRAINT `messageImg_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `messageImg_message_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('message_images');
    }
}
