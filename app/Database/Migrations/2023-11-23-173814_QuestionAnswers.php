<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class QuestionAnswers extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('question_answers', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `question_answers` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `question_id` int unsigned NOT NULL,
            `choix` text COLLATE utf8mb4_bin NOT NULL,
            `added_price` float NOT NULL DEFAULT '0',
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `quest_ans_question_id_foreign_idx` (`question_id`),
            CONSTRAINT `quest_ans_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('question_answers');
    }
}
