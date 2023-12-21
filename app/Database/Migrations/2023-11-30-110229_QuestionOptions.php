<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class QuestionOptions extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('question_options', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `question_options` (
            `id` int NOT NULL AUTO_INCREMENT,
            `label` varchar(75) COLLATE utf8mb4_bin DEFAULT NULL,
            `prix` float NOT NULL DEFAULT '0',
            `format` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Pour les questions impliquant un téléversement de fichier, le type de fichier à téléverser sera spécifié ici (video, document, image, audio)',
            `subquestions` varchar(75) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'une liste d''identifiants de questions dites sous questions associées au choix de cette option.',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('question_options');
    }
}
