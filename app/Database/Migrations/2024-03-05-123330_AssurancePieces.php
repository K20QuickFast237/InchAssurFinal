<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssurancePieces extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('assurance_pieces', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `assurance_pieces` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `assurance_id` int unsigned NOT NULL,
            `piece_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `Unique` (`assurance_id`,`piece_id`),
            KEY `AssurPiece_assur_foreign_idx` (`assurance_id`),
            KEY `AssurPiece_piece_foreign_idx` (`piece_id`),
            CONSTRAINT `AssurPiece_assur_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `AssurPiece_piece_foreign` FOREIGN KEY (`piece_id`) REFERENCES `document_titres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('assurance_pieces');
    }
}
