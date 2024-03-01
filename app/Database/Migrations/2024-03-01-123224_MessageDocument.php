<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MessageDocument extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('message_documents', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `message_documents` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `message_id` int unsigned NOT NULL,
            `document_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `Unique` (`message_id`,`document_id`),
            KEY `message_docuemnt_foreign_idx` (`document_id`),
            KEY `message_image_foreign_idx` (`message_id`),
            CONSTRAINT `messageDoc_docuemnt_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `messageDoc_message_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('message_documents');
    }
}
