<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UtilisateurDocuments extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('utilisateur_documents', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `utilisateur_documents` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `utilisateur_id` int unsigned NOT NULL,
            `document_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `userDoc_document_foreign_idx` (`document_id`),
            KEY `userDoc_user_foreign` (`utilisateur_id`),
            CONSTRAINT `userDoc_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `userDoc_user_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('utilisateur_documents');
    }
}
