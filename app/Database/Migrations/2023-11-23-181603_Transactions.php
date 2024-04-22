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
            `code` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `motif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `pay_option_id` int unsigned NOT NULL,
            `beneficiaire_id` int unsigned DEFAULT NULL,
            `prix_total` float NOT NULL,
            `tva_taux` float unsigned NOT NULL DEFAULT '0',
            `valeur_tva` float NOT NULL,
            `net_a_payer` float NOT NULL,
            `avance` float NOT NULL,
            `reste_a_payer` float NOT NULL,
            `etat` tinyint NOT NULL,
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `transact_pay_opt_id_foreign_idx` (`pay_option_id`),
            KEY `transact_tva_taux_foreign` (`tva_taux`),
            KEY `transact_benef_foreign_idx` (`beneficiaire_id`),
            CONSTRAINT `transact_benef_foreign` FOREIGN KEY (`beneficiaire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `transact_pay_opt_id_foreign` FOREIGN KEY (`pay_option_id`) REFERENCES `paiement_options` (`id`),
            CONSTRAINT `transact_tva_taux_foreign` FOREIGN KEY (`tva_taux`) REFERENCES `tvas` (`taux`) ON DELETE RESTRICT ON UPDATE CASCADE
          ) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='une transaction est l''équivalent d''une facture';
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('transactions');
    }
}
