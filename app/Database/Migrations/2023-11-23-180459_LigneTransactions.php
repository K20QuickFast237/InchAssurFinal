<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LigneTransactions extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('lignetransactions', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `lignetransactions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `produit_id` int unsigned NOT NULL,
            `produit_group_name` varchar(45) COLLATE utf8mb4_bin NOT NULL,
            `souscription_id` int unsigned DEFAULT NULL COMMENT 'présente dans le cas des souscriptions',
            `quantite` int NOT NULL,
            `prix_unitaire` float NOT NULL COMMENT 'Normalement devait être une clé vers le prix du produit (totologie volontaire), Mais étant donné que les assurances et les autres produits sont dans deux tables distinctes, ceci ne peut être mis en place.',
            `prix_total` float NOT NULL,
            `reduction_id` int unsigned DEFAULT NULL,
            `prix_total_net` float NOT NULL COMMENT 'Prix total obtenu après application de la réduction',
            PRIMARY KEY (`id`),
            KEY `ligneTransact_reduction_id_foreign_idx` (`reduction_id`),
            KEY `ligneTransact_souscript_id_foreign_idx` (`souscription_id`),
            KEY `lignetransact_prodGroupName_doreign` (`produit_group_name`),
            CONSTRAINT `lignetransact_prodGroupName_doreign` FOREIGN KEY (`produit_group_name`) REFERENCES `prodgroupnames` (`tableName`) ON UPDATE CASCADE,
            CONSTRAINT `ligneTransact_reduction_id_foreign` FOREIGN KEY (`reduction_id`) REFERENCES `reductions` (`id`),
            CONSTRAINT `ligneTransact_souscript_id_foreign` FOREIGN KEY (`souscription_id`) REFERENCES `souscriptions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('lignetransactions');
    }
}
