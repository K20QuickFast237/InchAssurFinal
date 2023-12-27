<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Utilisateurs extends Migration
{
    public function up()
    {
        # drop the table if it exists
        $this->forge->dropTable('utilisateurs', true);

        # add the fields
        $this->db->simpleQuery("
        CREATE TABLE `utilisateurs` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `code` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `user_id` int unsigned DEFAULT NULL,
            `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `prenom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `date_naissance` date DEFAULT NULL,
            `sexe` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `profession` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
            `tel1` int NOT NULL,
            `tel2` int DEFAULT NULL,
            `photo_profil` int unsigned DEFAULT NULL,
            `photo_cni` int unsigned DEFAULT NULL,
            `etat` int DEFAULT '1' COMMENT 'Stocke le parametre On line ou Off line sous forme chiffree',
            `statut` int DEFAULT '0' COMMENT 'status definit l''etat du compte (bloqué, actif etc...)',
            `specialisation` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `dateSuppression` datetime DEFAULT NULL,
            `documents` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
            `ville` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `etatcivil` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `nbr_enfant` int DEFAULT NULL,
            `membres` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
            PRIMARY KEY (`id`),
            UNIQUE KEY `code_UNIQUE` (`code`),
            UNIQUE KEY `tel1_UNIQUE` (`tel1`),
            UNIQUE KEY `email_UNIQUE` (`email`),
            UNIQUE KEY `tel2_UNIQUE` (`tel2`),
            KEY `utilisateurs_photo_profil_foreign_idx` (`photo_profil`),
            KEY `utilisateurs_photo_cni_foreign_idx` (`photo_cni`),
            KEY `utilisateur_sexe_foreign` (`sexe`),
            KEY `utilisateur_etatcivil_foreign` (`etatcivil`),
            KEY `utilisateurs_user_foreign_idx` (`user_id`),
            CONSTRAINT `utilisateur_etatcivil_foreign` FOREIGN KEY (`etatcivil`) REFERENCES `etatcivil_lists` (`nom`) ON UPDATE CASCADE,
            CONSTRAINT `utilisateur_sexe_foreign` FOREIGN KEY (`sexe`) REFERENCES `sexe_lists` (`nom`) ON UPDATE CASCADE,
            CONSTRAINT `utilisateur_user_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `utilisateurs_photo_cni_foreign` FOREIGN KEY (`photo_cni`) REFERENCES `images` (`id`),
            CONSTRAINT `utilisateurs_photo_profil_foreign` FOREIGN KEY (`photo_profil`) REFERENCES `images` (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function down()
    {
        # drop the table
        $this->forge->dropTable('utilisateurs');
    }
}
