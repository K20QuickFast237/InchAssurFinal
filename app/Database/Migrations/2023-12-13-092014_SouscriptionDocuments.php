<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SouscriptionDocuments extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('souscription_documents', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `souscription_documents` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `souscription_id` int unsigned NOT NULL,
            `document_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `sous_doc_UNIQUE` (`document_id`,`souscription_id`),
            KEY `sousDoc_document_foreign_idx` (`document_id`),
            KEY `sousDoc_souscription_foreign_idx` (`souscription_id`),
            CONSTRAINT `sousDoc_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `sousDoc_souscription_foreign` FOREIGN KEY (`souscription_id`) REFERENCES `souscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('souscription_documents');
    }
}
