<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssuranceServices extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('assurance_services', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `assurance_services` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `assurance_id` int unsigned NOT NULL,
            `service_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `assur_service_UNIQUE` (`assurance_id`,`service_id`),
            KEY `assurance_services_assurance_id_foreign_idx` (`assurance_id`),
            KEY `assurance_services_service_id_foreign_idx` (`service_id`),
            CONSTRAINT `assurance_services_assurance_id_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON UPDATE CASCADE,
            CONSTRAINT `assurance_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('assurance_services');
    }
}
