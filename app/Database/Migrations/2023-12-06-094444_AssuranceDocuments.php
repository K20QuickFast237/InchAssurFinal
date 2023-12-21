<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssuranceDocuments extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('utilisateur_documents', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `assurance_documents` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `assurance_id` int unsigned NOT NULL,
            `document_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `assur_doc_document_foreign_idx` (`document_id`),
            KEY `assur_doc_produit_foreign_idx` (`assurance_id`),
            CONSTRAINT `assur_doc_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON UPDATE CASCADE,
            CONSTRAINT `assur_doc_assurance_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('utilisateur_documents');
    }
}
