<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProduitDocuments extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('produit_documents', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `produit_documents` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `produit_id` int unsigned NOT NULL,
            `document_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `prod_doc_document_foreign_idx` (`document_id`),
            KEY `prod_doc_produit_foreign_idx` (`produit_id`),
            CONSTRAINT `prod_doc_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON UPDATE CASCADE,
            CONSTRAINT `prod_doc_produit_foreign` FOREIGN KEY (`produit_id`) REFERENCES `assurances` (`id`) ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('produit_documents');
    }
}
