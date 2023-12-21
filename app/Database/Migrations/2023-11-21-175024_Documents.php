<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Documents extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('documents', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `documents` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `titre` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `type` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'image, document, audio, video',
            `extension` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `isLink` tinyint NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `document_titre_foreign` (`titre`),
            CONSTRAINT `document_titre_foreign` FOREIGN KEY (`titre`) REFERENCES `document_titres` (`nom`) ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('documents');
    }
}
