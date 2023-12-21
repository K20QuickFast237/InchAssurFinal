<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ProdGroupNames extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('prodgroupnames', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `prodgroupnames` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `nom` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
            `tableName` varchar(45) COLLATE utf8mb4_bin NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `tableName_UNIQUE` (`tableName`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Table utilitaire qui nous permet de retrouver les nom ainsi que les noms de tables des différents genres de produits fournis (mis en vente) sur la plateforme.';          
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('prodgroupnames');
    }
}
