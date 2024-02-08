<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class IncidentImages extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('incident_images', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `incident_images` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `incident_id` int unsigned NOT NULL,
            `image_id` int unsigned NOT NULL,  
            PRIMARY KEY (`id`),
            KEY `imgIncident_incident_foreign_idx` (`incident_id`),
            KEY `imgIncident_image_foreign_idx` (`image_id`),
            CONSTRAINT `imgIncident_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `imgIncident_incident_foreign` FOREIGN KEY (`incident_id`) REFERENCES `incidents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('incident_images');
    }
}
