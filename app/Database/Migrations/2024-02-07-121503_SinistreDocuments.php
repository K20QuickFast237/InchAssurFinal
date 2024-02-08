<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SinistreDocuments extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('sinistre_documents', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `sinistre_documents` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `sinistre_id` int unsigned NOT NULL,
            `document_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `docSinistre_sinistre_foreign_idx` (`sinistre_id`),
            KEY `docSinistre_document_foreign_idx` (`document_id`),
            CONSTRAINT `docSinistre_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `docSinistre_sinistre_foreign` FOREIGN KEY (`sinistre_id`) REFERENCES `sinistres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('sinistre_documents');
    }
}
