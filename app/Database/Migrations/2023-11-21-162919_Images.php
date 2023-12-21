<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Images extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('images', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `images` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `uri` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `extension` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `isLink` tinyint NOT NULL DEFAULT '0',
            `type` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('images');
    }
}
