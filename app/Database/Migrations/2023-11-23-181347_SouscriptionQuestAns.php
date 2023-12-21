<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SouscriptionQuestAns extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('souscription_questionans', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `souscription_questionans` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `souscription_id` int unsigned NOT NULL,
            `questionans_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `souscript_quest_ans_souscript_id_foreign_idx` (`souscription_id`),
            KEY `souscripti_quest_ans_quest_id_foreign_idx` (`questionans_id`),
            CONSTRAINT `souscript_quest_ans_souscript_id_foreign` FOREIGN KEY (`souscription_id`) REFERENCES `souscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `souscripti_quest_ans_quest_id_foreign` FOREIGN KEY (`questionans_id`) REFERENCES `question_answers` (`id`) ON DELETE RESTRICT
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('souscription_questionans');
    }
}
