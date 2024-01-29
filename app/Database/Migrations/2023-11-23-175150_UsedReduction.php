<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UsedReduction extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('used_reductions', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `used_reductions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `utilisateur_id` int unsigned NOT NULL,
            `reduction_id` int unsigned NOT NULL,
            `prix_initial` float NOT NULL,
            `prix_deduit` float NOT NULL,
            `prix_final` float NOT NULL,
            `dateCreation` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `usedReduction_utilisateur_foreign_idx` (`utilisateur_id`),
            KEY `usedReduction_reduction_foreign_idx` (`reduction_id`),
            CONSTRAINT `usedReduction_reduction_foreign` FOREIGN KEY (`reduction_id`) REFERENCES `reductions` (`id`),
            CONSTRAINT `usedReduction_utilisateur_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('used_reductions');
    }
}
