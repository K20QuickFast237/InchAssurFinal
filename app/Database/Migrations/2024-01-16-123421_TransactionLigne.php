<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TransactionLigne extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('transaction_lignes', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `transaction_lignes` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `transaction_id` int unsigned NOT NULL,
            `ligne_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `transaction_id_UNIQUE` (`transaction_id`,`ligne_id`),
            KEY `transactLigne_transaction_foreign_idx` (`transaction_id`),
            KEY `transactLigne_ligne_foreign_idx` (`ligne_id`),
            CONSTRAINT `transactLigne_ligne_foreign` FOREIGN KEY (`ligne_id`) REFERENCES `lignetransactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `transactLigne_transaction_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('transaction_lignes');
    }
}
