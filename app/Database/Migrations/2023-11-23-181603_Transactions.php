<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Transactions extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('transactions', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `transactions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(75) COLLATE utf8mb4_bin NOT NULL,
            `motif` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
            `pay_option_id` int unsigned NOT NULL,
            `prix_total` float NOT NULL,
            `tva_id` int unsigned NOT NULL,
            `valeur_tva` float NOT NULL,
            `net_a_payer` float NOT NULL,
            `avance` float NOT NULL,
            `reste_a_payer` float NOT NULL,
            `etat` tinyint NOT NULL,
            PRIMARY KEY (`id`),
            KEY `transact_pay_opt_id_foreign_idx` (`pay_option_id`),
            KEY `transact_tva_id_foreign_idx` (`tva_id`),
            CONSTRAINT `transact_pay_opt_id_foreign` FOREIGN KEY (`pay_option_id`) REFERENCES `paiement_options` (`id`),
            CONSTRAINT `transact_tva_id_foreign` FOREIGN KEY (`tva_id`) REFERENCES `tva` (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='une transaction est l''équivalent d''une facture';          
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('transactions');
    }
}
