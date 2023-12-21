<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AssuranceQuestions extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('assurance_questions', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `assurance_questions` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `assurance_id` int unsigned NOT NULL,
            `question_id` int unsigned NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `Assur_quest_UNIQUE` (`assurance_id`,`question_id`),
            KEY `ass_quest_assurance_id_foreign_idx` (`assurance_id`),
            KEY `ass_quest_question_id_foreign_idx` (`question_id`),
            CONSTRAINT `ass_quest_assurance_id_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `ass_quest_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('assurance_questions');
    }
}
