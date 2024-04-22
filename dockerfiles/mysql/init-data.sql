-- CREATE DATABASE  IF NOT EXISTS `inchassurdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_bin */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `inchassurdb`;

DROP TABLE IF EXISTS `images`;
CREATE TABLE `images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uri` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `extension` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `isLink` tinyint NOT NULL DEFAULT '0',
  `type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `images` WRITE;
INSERT INTO `images` VALUES (1,'uploads/categories/images/20231227/1703677397_bbbccf2f0610e2e818c8.jpeg','.jpeg',0,'image/jpeg'),(2,'uploads/assurances/images/20231228/1703759757_3037a83d39af5b83f04d.jpg','.jpg',0,'image/jpeg'),(3,'uploads/profils/UserDefaultProfil.png','.png',0,'image/png');
UNLOCK TABLES;

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `taux_couverture` float DEFAULT NULL,
  `prix_couverture` float DEFAULT NULL,
  `quantite` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `services` WRITE;
INSERT INTO `services` VALUES (1,'Couverture totale','Vous couvre pour tout incident du contrat',80,1000000,1),(2,'Couverture minimale','Vous couvre pour tout incident du contrat dans une proportion simplifiée.',40,300000,1);
UNLOCK TABLES;


DROP TABLE IF EXISTS `tvas`;
CREATE TABLE `tvas` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `taux` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `taux_UNIQUE` (`taux`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `tvas` WRITE;
INSERT INTO `tvas` VALUES (1,0),(2,19.25);
UNLOCK TABLES;

DROP TABLE IF EXISTS `profils`;
CREATE TABLE `profils` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(70) COLLATE utf8mb4_bin NOT NULL,
  `description` text COLLATE utf8mb4_bin NOT NULL,
  `niveau` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateSuppression` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `profils` WRITE;
INSERT INTO `profils` VALUES (1,'Particulier','Enregistre des membres (de sa famille) et achete des produits individuel ou de groupe.','IA1','2023-12-21 14:59:47','2023-12-21 14:59:47',NULL),(2,'Famille','Enregistre des membres (de sa famille) et achete des produits individuel ou de groupe.','IA2','2023-12-21 14:59:47','2023-12-21 14:59:47',NULL),(3,'Entreprise','Enregistre des utilisateurs (ses employés) et achete des produits en groupe (pour ses employés).','IA3','2023-12-21 14:59:47','2023-12-21 14:59:47',NULL),(4,'Prescripteur','Prescipteur','IA4','2023-12-21 14:59:47','2023-12-21 14:59:47',NULL),(5,'Agent','Agent','IA5','2023-12-21 14:59:47','2023-12-21 14:59:47',NULL),(6,'Infirmier','Infirmier','IA6','2023-12-21 14:59:47','2023-12-21 14:59:47',NULL),(7,'Medecin','Effectue des consultations et propose ses services de consultation.','IA7','2023-12-21 14:59:47','2023-12-21 14:59:47',NULL),(8,'Assureur','Ajoute des Assurances.','IA8','2023-12-21 14:59:47','2023-12-21 14:59:47',NULL),(9,'Partenaire','Partenaire','IA9','2023-12-21 14:59:47','2023-12-21 14:59:47',NULL),(10,'Administrateur','Administre la plateforme en effectuant des configurations et autres opérations.','IA10','2023-12-21 14:59:48','2023-12-21 14:59:48',NULL),(11,'Super Admin','Super Administrateur','IA11','2023-12-21 14:59:48','2023-12-21 14:59:48',NULL),(12,'Membre','Ne peut effectuer aucune action','IA12','2023-12-28 13:57:11','2023-12-28 13:57:11',NULL);
UNLOCK TABLES;

DROP TABLE IF EXISTS `etatcivil_lists`;
CREATE TABLE `etatcivil_lists` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `sexe_lists`;
CREATE TABLE `sexe_lists` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `sexe_lists` WRITE;
INSERT INTO `sexe_lists` VALUES (2,'F'),(1,'H');
UNLOCK TABLES;

DROP TABLE IF EXISTS `assurance_types`;
CREATE TABLE `assurance_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `assurance_types` WRITE;
INSERT INTO `assurance_types` VALUES (1,'Individuelle','Assurance pour une seule personne'),(2,'Collective','Permet de prendre une assurance qui couvre plusieurs personnes'),(3,'De Bien','Assurance pour les biens que vous n\'aimerez pas perdre'),(4,'D\'activité','Assurance pour vous aider a démarrer votre activité sans stress');
UNLOCK TABLES;

DROP TABLE IF EXISTS `paiement_options_lists`;
CREATE TABLE `paiement_options_lists` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `paiement_options_lists` WRITE;
INSERT INTO `paiement_options_lists` VALUES (1,'Unique','Payer en une seule fois le montnant requis.'),(2,'A Echéance','Après un dépot initial, une date échéance est convenue pour atteindre le montnat requis.'),(3,'Planifié','Payer un montant défini à intervalle défini, autant de fois que nécessaire pour atteindre le montant requis.');
UNLOCK TABLES;

DROP TABLE IF EXISTS `paiement_options`;
CREATE TABLE `paiement_options` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `depot_initial_taux` float NOT NULL,
  `montant_cible` float DEFAULT NULL COMMENT 'Afin de permettre une réutilisabilité, ceci ne sera plus pris en compte.',
  `etape_duree` int DEFAULT NULL,
  `cycle_longueur` int DEFAULT NULL,
  `cycle_nombre` int DEFAULT NULL,
  `cycle_taux` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `PayOpts_nom_foreign_idx` (`type`),
  CONSTRAINT `PayOpts_nom_foreign` FOREIGN KEY (`type`) REFERENCES `paiement_options_lists` (`nom`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `paiement_options` WRITE;
INSERT INTO `paiement_options` VALUES (1,'Unique','SinglePay','Paiement en une seule opération',100,NULL,NULL,NULL,NULL,NULL),(2,'A Echéance','TargetedPay','Paiement sur une période',25,NULL,45,NULL,NULL,NULL),(3,'Planifié','PlanedPay','Paiement cyclique',10,NULL,NULL,30,6,15);
UNLOCK TABLES;

CREATE TABLE `categorie_produits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `image_id` int unsigned DEFAULT NULL,
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateSuppression` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`),
  KEY `image_foreign_idx` (`image_id`),
  CONSTRAINT `image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `categorie_produits` WRITE;
INSERT INTO `categorie_produits` VALUES (3,'assurances santé','assurance et services liés à la santé santé.',1,'2023-12-27 12:43:17','2023-12-27 12:43:17',NULL),(4,'Assurance Auto','Assurance pour vos véhicules de toutes sortes',NULL,'2024-02-09 12:07:57','2024-02-09 12:07:57',NULL),(5,'assurance loisir ','assurance loisir',NULL,'2024-02-09 12:08:49','2024-02-09 12:08:49',NULL),(6,'Market Place','Assurancers spécialement concue pour les vendeurs de la Market Place.',NULL,'2024-02-09 12:14:45','2024-02-09 12:14:45',NULL),(8,'Autre',NULL,NULL,'2024-02-28 12:37:35','2024-02-28 12:37:35',NULL);
UNLOCK TABLES;

DROP TABLE IF EXISTS `prodgroupnames`;
CREATE TABLE `prodgroupnames` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `tableName` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tableName_UNIQUE` (`tableName`),
  UNIQUE KEY `nom_UNIQUE` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Table utilitaire qui nous permet de retrouver les nom ainsi que les noms de tables des différents genres de produits fournis (mis en vente) sur la plateforme.';
LOCK TABLES `prodgroupnames` WRITE;
INSERT INTO `prodgroupnames` VALUES (1,'Assurance','assurances');
UNLOCK TABLES;

DROP TABLE IF EXISTS `paiement_mode_lists`;
CREATE TABLE `paiement_mode_lists` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `paiement_mode_lists` WRITE;
INSERT INTO `paiement_mode_lists` VALUES (4,'Express Union Mobile Money'),(3,'MTN Mobile Money'),(2,'Orange Money'),(1,'Porte Feuille');
UNLOCK TABLES;

DROP TABLE IF EXISTS `paiement_modes`;
CREATE TABLE `paiement_modes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `image_id` int unsigned DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `real_name` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `paiement_mode_image_foreign_idx` (`image_id`),
  KEY `paiement_mode_nom_foreign` (`nom`),
  CONSTRAINT `paiement_mode_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `paiement_mode_nom_foreign` FOREIGN KEY (`nom`) REFERENCES `paiement_mode_lists` (`nom`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `paiement_modes` WRITE;
INSERT INTO `paiement_modes` VALUES (1,'Porte Feuille',NULL,NULL,'PORTE_FEUILLE'),(2,'Orange Money',NULL,NULL,'CM_ORANGEMONEY'),(3,'MTN Mobile Money',NULL,NULL,'CM_MTNMOBILEMONEY'),(4,'Express Union Mobile Money',NULL,NULL,'CM_EUMM');
UNLOCK TABLES;

DROP TABLE IF EXISTS `document_titres`;
CREATE TABLE `document_titres` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` text COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `document_titres` WRITE;
INSERT INTO `document_titres` VALUES (1,'Carte Nationale d\'identité','une description'),(2,'Photocopie Certifiée de la Carte Nationale d\'identité','une description');
UNLOCK TABLES;

DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Idéalement l''un des titres de document disponible sur la plateforme. Mais des raisons de réutilisabilité, la contrainte de clé étrangère à été supprimée.',
  `uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `type` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'image, document, audio, video',
  `extension` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `isLink` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `status_message` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last_active` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE `utilisateurs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `profil_id` int unsigned NOT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `prenom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `sexe` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `profession` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `tel1` int NOT NULL,
  `tel2` int DEFAULT NULL,
  `photo_profil` int unsigned DEFAULT '3',
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
  KEY `utilisateur_profil_foreign_idx` (`profil_id`),
  CONSTRAINT `utilisateur_etatcivil_foreign` FOREIGN KEY (`etatcivil`) REFERENCES `etatcivil_lists` (`nom`) ON UPDATE CASCADE,
  CONSTRAINT `utilisateur_profil_foreign` FOREIGN KEY (`profil_id`) REFERENCES `profils` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `utilisateur_sexe_foreign` FOREIGN KEY (`sexe`) REFERENCES `sexe_lists` (`nom`) ON UPDATE CASCADE,
  CONSTRAINT `utilisateur_user_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `utilisateurs_photo_cni_foreign` FOREIGN KEY (`photo_cni`) REFERENCES `images` (`id`),
  CONSTRAINT `utilisateurs_photo_profil_foreign` FOREIGN KEY (`photo_profil`) REFERENCES `images` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `connexions`;
CREATE TABLE `connexions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `secret` varchar(255) NOT NULL,
  `secret2` varchar(255) DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  `extra` text,
  `force_reset` tinyint(1) NOT NULL DEFAULT '0',
  `last_used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `statut` int DEFAULT '1' COMMENT 'status definit l''etat du compte (bloqué, actif etc...)',
  `codeconnect` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_secret` (`type`,`secret`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `connexions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

DROP TABLE IF EXISTS `utilisateur_profils`;
CREATE TABLE `utilisateur_profils` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int unsigned NOT NULL,
  `profil_id` int unsigned NOT NULL,
  `attributor` int unsigned NOT NULL,
  `dateCreation` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilisateurProfils_profil_utilisateur_uniq` (`utilisateur_id`,`profil_id`),
  KEY `utilisateurProfils_utilisateur_foreign` (`utilisateur_id`),
  KEY `utilisateurProfils_profil_foreign` (`profil_id`),
  KEY `utilisateurProfils_attributor_foreign_idx` (`attributor`),
  CONSTRAINT `utilisateurProfils_profil_foreign` FOREIGN KEY (`profil_id`) REFERENCES `profils` (`id`),
  CONSTRAINT `utilisateurProfils_utilisateur_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `reductions`;
CREATE TABLE `reductions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `code` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `auteur_id` int unsigned NOT NULL,
  `valeur` float DEFAULT NULL,
  `taux` float DEFAULT NULL,
  `usage_max_nombre` int DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  `utilise_nombre` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_UNIQUE` (`code`),
  KEY `reduction_auteur_id_foreign_idx` (`auteur_id`),
  CONSTRAINT `reduction_auteur_id_foreign` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `auteur_id` int unsigned NOT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `tarif_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `field_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `requis` tinyint NOT NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `question_auteur_foreign` (`auteur_id`),
  CONSTRAINT `question_auteur_foreign` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `question_answers`;
CREATE TABLE `question_answers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int unsigned NOT NULL,
  `choix` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `added_price` float NOT NULL DEFAULT '0',
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quest_ans_question_id_foreign_idx` (`question_id`),
  CONSTRAINT `quest_ans_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE `paiements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `montant` float NOT NULL,
  `statut` tinyint NOT NULL,
  `mode_id` int unsigned NOT NULL,
  `auteur_id` int unsigned NOT NULL,
  `transaction_id` int unsigned DEFAULT NULL,
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateModification` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateSuppression` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_UNIQUE` (`code`),
  KEY `paiement_mode_id_foreign_idx` (`mode_id`),
  KEY `paiement_auteur_id_foreign_idx` (`auteur_id`),
  KEY `Paiement_transaction_foreign_idx` (`transaction_id`),
  CONSTRAINT `paiement_auteur_id_foreign` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `paiement_mode_id_foreign` FOREIGN KEY (`mode_id`) REFERENCES `paiement_modes` (`id`),
  CONSTRAINT `Paiement_transaction_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `used_reductions`;
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

DROP TABLE IF EXISTS `assurances`;
CREATE TABLE `assurances` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `nom` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `short_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `prix` float NOT NULL,
  `image_id` int unsigned NOT NULL,
  `type_id` int unsigned NOT NULL,
  `pieces_a_joindre` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `duree` int DEFAULT NULL,
  `assureur_id` int unsigned NOT NULL,
  `categorie_id` int unsigned NOT NULL,
  `etat` int NOT NULL DEFAULT '1' COMMENT 'definit l''etat du produit (désactivé, actif etc...)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`),
  KEY `assurances_type_foreign_idx` (`type_id`),
  KEY `assurances_assureur_id_foreign_idx` (`assureur_id`),
  KEY `assurances_categorie_id_foreign_idx` (`categorie_id`),
  KEY `assurances_image_foreign_idx` (`image_id`),
  CONSTRAINT `assurances_assureur_id_foreign` FOREIGN KEY (`assureur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assurances_categorie_id_foreign` FOREIGN KEY (`categorie_id`) REFERENCES `categorie_produits` (`id`),
  CONSTRAINT `assurances_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assurances_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `assurance_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `assurance_services`;
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

DROP TABLE IF EXISTS `assurance_reductions`;
CREATE TABLE `assurance_reductions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `assurance_id` int unsigned NOT NULL,
  `reduction_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Assur_reduction_UNIQUE` (`reduction_id`,`assurance_id`),
  KEY `assur_reduct_assurance_id_foreign_idx` (`assurance_id`),
  KEY `assur_reduct_reduction_id_foreign_idx` (`reduction_id`),
  CONSTRAINT `assur_reduct_assurance_id_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assur_reduct_reduction_id_foreign` FOREIGN KEY (`reduction_id`) REFERENCES `reductions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `assurance_questions`;
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

DROP TABLE IF EXISTS `assurance_paiement_options`;
CREATE TABLE `assurance_paiement_options` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `assurance_id` int unsigned NOT NULL,
  `paiement_option_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assurance_paiement_option_assurance_id_foreign_idx` (`assurance_id`),
  KEY `assurance_paiement_option_option_id_foreign_idx` (`paiement_option_id`),
  CONSTRAINT `assurance_paiement_option_assurance_id_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assurance_paiement_option_option_id_foreign` FOREIGN KEY (`paiement_option_id`) REFERENCES `paiement_options` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `assurance_images`;
CREATE TABLE `assurance_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `assurance_id` int unsigned NOT NULL,
  `image_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assurance_images_assurance_id_foreign_idx` (`assurance_id`),
  KEY `assurance_images_image_id_foreign_idx` (`image_id`),
  CONSTRAINT `assurance_images_assurance_id_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assurance_images_image_id_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `souscriptions`;
CREATE TABLE `souscriptions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `cout` float NOT NULL DEFAULT '0',
  `souscripteur_id` int unsigned NOT NULL,
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assurance_id` int unsigned NOT NULL,
  `etat` tinyint NOT NULL DEFAULT '0',
  `dateDebutValidite` date DEFAULT NULL,
  `dateFinValidite` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `souscription_souscripteur_id_foeriegn_idx` (`souscripteur_id`),
  KEY `souscription_assurance_foreign_idx` (`assurance_id`),
  CONSTRAINT `souscription_assurance_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `souscription_souscripteur_id_foeriegn` FOREIGN KEY (`souscripteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `lignetransactions`;
CREATE TABLE `lignetransactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `produit_id` int unsigned NOT NULL,
  `produit_group_name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `souscription_id` int unsigned DEFAULT NULL COMMENT 'présente dans le cas des souscriptions',
  `quantite` int NOT NULL,
  `prix_unitaire` float NOT NULL COMMENT 'Normalement devait être une clé vers le prix du produit (totologie volontaire), Mais étant donné que les assurances et les autres produits sont dans deux tables distinctes, ceci ne peut être mis en place.',
  `prix_total` float NOT NULL,
  `reduction_code` varchar(45) COLLATE utf8mb4_bin DEFAULT NULL,
  `prix_reduction` float NOT NULL DEFAULT '0',
  `prix_total_net` float NOT NULL COMMENT 'Prix total obtenu après application de la réduction',
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ligneTransact_souscript_id_foreign_idx` (`souscription_id`),
  KEY `lignetransact_prodGroupName_doreign` (`produit_group_name`),
  CONSTRAINT `lignetransact_prodGroupName_doreign` FOREIGN KEY (`produit_group_name`) REFERENCES `prodgroupnames` (`nom`) ON UPDATE CASCADE,
  CONSTRAINT `ligneTransact_souscript_id_foreign` FOREIGN KEY (`souscription_id`) REFERENCES `souscriptions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `souscription_questionans`;
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

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `motif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `pay_option_id` int unsigned NOT NULL,
  `beneficiaire_id` int unsigned DEFAULT NULL,
  `prix_total` float NOT NULL,
  `tva_taux` float unsigned NOT NULL DEFAULT '0',
  `valeur_tva` float NOT NULL,
  `net_a_payer` float NOT NULL,
  `avance` float NOT NULL,
  `reste_a_payer` float NOT NULL,
  `etat` tinyint NOT NULL,
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `transact_pay_opt_id_foreign_idx` (`pay_option_id`),
  KEY `transact_tva_taux_foreign` (`tva_taux`),
  KEY `transact_benef_foreign_idx` (`beneficiaire_id`),
  CONSTRAINT `transact_benef_foreign` FOREIGN KEY (`beneficiaire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transact_pay_opt_id_foreign` FOREIGN KEY (`pay_option_id`) REFERENCES `paiement_options` (`id`),
  CONSTRAINT `transact_tva_taux_foreign` FOREIGN KEY (`tva_taux`) REFERENCES `tvas` (`taux`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='une transaction est l''équivalent d''une facture';

DROP TABLE IF EXISTS `sociallinknames`;
CREATE TABLE `sociallinknames` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `sociallinks`;
CREATE TABLE `sociallinks` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `utilisateur_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sociallink_utilisateur_foreign` (`utilisateur_id`),
  CONSTRAINT `sociallink_utilisateur_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `profession_lists`;
CREATE TABLE `profession_lists` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `titre_UNIQUE` (`titre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `profession_lists` WRITE;
INSERT INTO `profession_lists` VALUES (1,'Infirmier/infirmière','Fournit des soins médicaux de base aux patients, surveille leur état de santé et administre des médicaments sous la supervision d\'un médecin.'),(2,'Développeur/développeuse web','Conçoit, développe et maintient des sites web en utilisant des langages de programmation, des frameworks et des outils de développement.'),(3,'Enseignant/enseignante','Éduque et enseigne aux élèves dans une variété de sujets, prépare des plans de cours, évalue les progrès des élèves et les aide à atteindre leurs objectifs éducatifs.'),(4,'Avocat/avocate','Représente et conseille les clients dans des affaires juridiques, prépare des documents juridiques, plaide devant les tribunaux et négocie des accords.'),(5,'Ingénieur/ingénieure en informatique','Conçoit, développe et teste des logiciels, des systèmes informatiques et des réseaux pour répondre aux besoins spécifiques des entreprises ou des organisations.'),(6,'Médecin','Diagnostique et traite les maladies et les blessures, prescrit des médicaments, conseille sur les questions de santé et supervise les soins des patients.'),(7,'Comptable','Gère les finances et les comptes d\'une entreprise ou d\'une organisation, prépare des états financiers, effectue des audits et fournit des conseils fiscaux.'),(8,'Architecte','Conçoit des bâtiments et des structures en tenant compte à la fois des aspects esthétiques et fonctionnels, prépare des plans et supervise leur construction.'),(9,'Journaliste','Recherche, rédige et présente des informations sur des événements actuels, des problèmes et des tendances pour les médias imprimés, électroniques ou en ligne.'),(10,'Psychologue','Étudie le comportement humain, évalue et traite les problèmes émotionnels, mentaux et comportementaux, et fournit un soutien thérapeutique aux individus et aux groupes.'),(11,'Policier/policière','Maintient l\'ordre public, enquête sur les crimes, patrouille les quartiers et arrête les suspects en utilisant les lois et les procédures applicables.'),(12,'Chef/cuisinier','Planifie et prépare des repas dans les restaurants, les hôtels ou d\'autres établissements alimentaires, supervise le personnel de cuisine et assure la qualité des plats servis.'),(13,'Électricien/électricienne','Installe, entretient et répare les systèmes électriques dans les bâtiments, les usines et d\'autres installations, en veillant à leur bon fonctionnement et à leur sécurité.'),(14,'Designer graphique','Crée des visuels attrayants et des éléments graphiques pour les entreprises, les marques et les projets, en utilisant des logiciels de conception et en suivant les tendances actuelles.'),(15,'Secrétaire/administrateur','Assiste les gestionnaires et les professionnels en effectuant des tâches administratives telles que la gestion des appels téléphoniques, la planification des réunions et la tenue des dossiers.'),(16,'Technicien/technicienne de laboratoire','Effectue des tests et des analyses sur des échantillons biologiques, chimiques ou physiques, en utilisant des équipements de laboratoire et en interprétant les résultats pour la recherche ou le diagnostic.'),(17,'Traducteur/traductrice','Convertit des textes écrits d\'une langue à une autre tout en préservant leur sens, leur style et leur contexte culturel, pour faciliter la communication entre les personnes de différentes langues.'),(18,'Développeur/développeuse de jeux vidéo','Conçoit, programme et teste des jeux vidéo pour diverses plateformes, en travaillant en étroite collaboration avec des artistes, des concepteurs et d\'autres membres de l\'équipe de développement.'),(19,'Agent immobilier/agent immobilier','Facilite l\'achat, la vente ou la location de biens immobiliers en aidant les clients à trouver des propriétés, à négocier des contrats et à finaliser des transactions immobilières.'),(20,'Entrepreneur/entrepreneuse','Lance et gère une entreprise ou un projet commercial, en prenant des décisions stratégiques, en mobilisant des ressources et en assurant la croissance et la rentabilité de l\'entreprise.'),(21,'Chirurgien/chirurgienne','Effectue des opérations chirurgicales pour traiter les maladies, les blessures ou les anomalies physiques, en utilisant des techniques chirurgicales avancées et en assurant le suivi post-opératoire des patients.'),(22,'Conseiller/conseillère en orientation','Fournit des conseils professionnels aux individus sur leur carrière, leur éducation et leur développement personnel, en évaluant leurs intérêts, leurs compétences et leurs objectifs.'),(23,'Artiste peintre','Crée des œuvres d\'art visuelles en utilisant différentes techniques de peinture et en exprimant des idées, des émotions ou des concepts à travers des compositions visuelles uniques.'),(24,'Agent de voyage','Organise et planifie des voyages pour les clients, en réservant des vols, des hôtels, des excursions et d\'autres services de voyage, et en fournissant des conseils sur les destinations et les itinéraires.'),(25,'Animateur/animateure pour enfants','Organise et anime des activités récréatives, éducatives et artistiques pour les enfants dans des centres de loisirs, des camps d\'été ou d\'autres environnements pour enfants.'),(26,'Agent de police scientifique','Recueille et analyse des preuves sur les scènes de crime, en utilisant des techniques scientifiques et des technologies avancées pour aider à résoudre des enquêtes criminelles et à identifier les coupables.'),(27,'Pharmacien/pharmacienne','Prépare et délivre des médicaments sur ordonnance, fournit des conseils sur l\'utilisation appropriée des médicaments, surveille les interactions médicamenteuses et fournit des services de santé préventive.'),(28,'Écrivain/écrivaine','Crée des textes écrits tels que des romans, des articles, des scripts ou des contenus web, en utilisant son imagination, ses recherches et ses compétences en écriture pour captiver les lecteurs.'),(29,'Artiste de maquillage/maquilleuse','Applique du maquillage professionnel pour des événements spéciaux, des séances photo, des productions cinématographiques ou des défilés de mode, en utilisant des techniques artistiques pour créer des looks uniques.'),(30,'Développeur/développeuse d\'applications mobiles','Conçoit, développe et teste des applications mobiles pour les smartphones et les tablettes, en utilisant des langages de programmation et des outils de développement adaptés aux plateformes mobiles.'),(31,'Consultant/consultante en gestion','Fournit des conseils stratégiques aux entreprises sur des questions telles que la gestion, la planification stratégique, les opérations, le marketing et la croissance des affaires pour améliorer leur performance globale.'),(32,'Ingénieur/ingénieure en génie civil','Conçoit, supervise et gère la construction d\'infrastructures publiques et privées telles que des routes, des ponts, des bâtiments, des barrages et des systèmes de distribution d\'eau.'),(33,'Photographe','Capture des images visuellement saisissantes en utilisant des appareils photo professionnels, en manipulant la lumière et en choisissant des angles et des compositions pour raconter des histoires ou capturer des moments spéciaux.'),(34,'Spécialiste en ressources humaines','Recrute, forme, gère et développe le personnel d\'une entreprise, en veillant au respect des politiques et des procédures en matière de ressources humaines et à la satisfaction des employés.'),(35,'Économiste','Analyse les tendances économiques, les politiques gouvernementales et les données financières pour fournir des prévisions, des conseils et des recommandations sur des questions économiques et financières.'),(36,'Conducteur/conductrice de poids lourd','Conduit des camions lourds pour transporter des marchandises sur de longues distances, en respectant les réglementations routières et en veillant à la sécurité des marchandises et des autres usagers de la route.'),(37,'Dentiste','Diagnostique et traite les problèmes dentaires et bucco-dentaires, effectue des interventions chirurgicales dentaires et fournit des soins préventifs pour maintenir la santé dentaire des patients.'),(38,'Musicien/musicienne','Interprète de la musique en jouant d\'un instrument, en chantant ou en dirigeant un ensemble musical, en captivant les auditeurs et en transmettant des émotions à travers la musique.'),(39,'Agent de marketing','Développe et met en œuvre des stratégies de marketing pour promouvoir des produits, des services ou des marques, en utilisant des techniques telles que la publicité, les médias sociaux et les relations publiques.'),(40,'Coach personnel','Fournit un soutien, des conseils et des encouragements à des individus pour les aider à atteindre leurs objectifs personnels, professionnels ou sportifs, en les aidant à surmonter les obstacles et à maximiser leur potentiel.'),(41,'Agent de sécurité','Surveille et protège les biens, les installations ou les personnes contre les menaces, les vols, les actes de vandalisme ou les intrusions, en appliquant des protocoles de sécurité et en intervenant en cas d\'urgence.'),(42,'Analyste financier/analyste financière','Analyse les données financières, évalue les risques et les opportunités d\'investissement, et fournit des conseils sur les décisions financières telles que les investissements, les acquisitions et la gestion de portefeuille.'),(43,'Agent immobilier/agent immobilier résidentiel','Facilite la vente, l\'achat ou la location de propriétés résidentielles telles que des maisons, des appartements ou des condominiums, en guidant les clients tout au long du processus immobilier.'),(44,'Thérapeute physique','Évalue et traite les blessures, les douleurs ou les limitations physiques en utilisant des techniques de réadaptation telles que l\'exercice thérapeutique, la manipulation manuelle et les modalités physiques pour restaurer la fonction corporelle.'),(45,'Garde forestier/garde forestière','Surveille, protège et gère les ressources naturelles des forêts, en appliquant des pratiques de conservation, en luttant contre les incendies de forêt et en sensibilisant le public à la préservation de l\'environnement.'),(46,'Réceptionniste','Accueille et oriente les clients, répond aux appels téléphoniques, gère les réservations et les rendez-vous, et fournit un soutien administratif dans les hôtels, les cliniques médicales, les entreprises ou d\'autres établissements.'),(47,'Consultant/consultante en informatique','Fournit des conseils et des solutions informatiques aux entreprises ou aux clients individuels, en analysant leurs besoins informatiques, en proposant des technologies appropriées et en assurant la mise en œuvre et la maintenance des systèmes informatiques.'),(48,'Conseiller/conseillère financier/financière','Fournit des conseils financiers personnalisés aux particuliers ou aux entreprises, en évaluant leur situation financière, en identifiant leurs objectifs et en proposant des stratégies d\'investissement, de planification fiscale et de gestion de patrimoine.'),(49,'Ingénieur/ingénieure en énergie renouvelable','Conçoit, développe et implémente des solutions d\'énergie renouvelable telles que les panneaux solaires, les éoliennes et les systèmes de bioénergie pour réduire l\'empreinte carbone et promouvoir la durabilité environnementale.'),(50,'Bibliothécaire','Gère et organise les collections de livres et de ressources dans les bibliothèques publiques ou privées, fournit des services d\'information et de recherche, et encourage la lecture et l\'éducation.'),(51,'Agent de publicité','Conçoit et met en œuvre des campagnes publicitaires pour promouvoir des produits, des marques ou des événements, en utilisant des médias traditionnels, numériques ou sociaux pour atteindre le public cible.'),(52,'Conducteur/conductrice de train','Pilote des trains de passagers ou de marchandises sur des voies ferrées, en respectant les horaires, les règles de sécurité et les procédures opérationnelles pour assurer le transport efficace des voyageurs ou des marchandises.'),(53,'Urbaniste','Planifie et conçoit le développement urbain en tenant compte des aspects tels que l\'aménagement du territoire, les infrastructures, les transports, l\'environnement et les besoins socio-économiques pour créer des communautés durables et fonctionnelles.'),(54,'Conseiller/conseillère en nutrition','Fournit des conseils et des recommandations sur l\'alimentation, la nutrition et le mode de vie pour promouvoir la santé et prévenir les maladies, en tenant compte des besoins individuels et des objectifs de bien-être.'),(55,'Pilote de ligne','Pilote des avions commerciaux pour transporter des passagers ou des marchandises vers des destinations nationales ou internationales, en respectant les réglementations de l\'aviation et en assurant la sécurité des vols.'),(56,'Agent immobilier/agent immobilier de luxe','Spécialisé dans la vente et l\'achat de biens immobiliers haut de gamme, tels que des propriétés de luxe, des résidences de prestige ou des biens immobiliers exclusifs, en offrant des services personnalisés et discrets.'),(57,'Technicien/technicienne en radiologie','Effectue des examens d\'imagerie médicale tels que des radiographies, des scanners CT ou des IRM, en utilisant des équipements d\'imagerie avancés pour aider au diagnostic et au traitement des maladies ou des blessures.'),(58,'Designer de mode','Conçoit des vêtements, des accessoires et des collections de mode en suivant les tendances de l\'industrie et en exprimant sa créativité à travers des dessins, des échantillons et des prototypes de vêtements.'),(59,'Gestionnaire de projet','Planifie, organise et supervise des projets complexes dans divers domaines tels que la construction, l\'informatique, le marketing ou l\'ingénierie, en coordonnant les ressources, les échéanciers et les budgets pour atteindre les objectifs du projet.'),(60,'Conseiller/conseillère en environnement','Analyse les problèmes environnementaux, propose des solutions durables et conseille les entreprises, les gouvernements ou les organisations sur la gestion des ressources naturelles, la conservation de la biodiversité et la réduction de l\'impact environnemental.'),(61,'Conseiller/conseillère en voyages d\'aventure','Organise et planifie des voyages d\'aventure pour les clients, en proposant des destinations exotiques, des activités d\'aventure telles que la randonnée, le rafting ou le safari, et en fournissant des conseils sur la sécurité et l\'équipement.'),(62,'Conseiller/conseillère en santé mentale','Fournit un soutien, des conseils et des traitements thérapeutiques aux personnes souffrant de troubles émotionnels, comportementaux ou mentaux, en les aidant à surmonter leurs difficultés et à améliorer leur bien-être émotionnel.'),(63,'Ingénieur/ingénieure aérospatial','Conçoit, développe et teste des véhicules et des systèmes aérospatiaux tels que des avions, des satellites et des fusées, en utilisant des principes d\'ingénierie aéronautique et spatiale pour assurer leur performance et leur sécurité.'),(64,'Agent artistique','Représente des artistes, des acteurs, des musiciens ou des écrivains en négociant des contrats, en organisant des engagements professionnels et en gérant leur carrière artistique et leurs relations avec l\'industrie du divertissement.'),(65,'Conseiller/conseillère en relations publiques','Développe et met en œuvre des stratégies de communication pour promouvoir l\'image et la réputation d\'une entreprise, d\'une organisation ou d\'une personnalité publique, en gérant les relations avec les médias et le public.'),(66,'Analyste de données','Collecte, analyse et interprète des données pour fournir des informations et des insights exploitables, en utilisant des outils et des techniques d\'analyse de données pour aider les entreprises à prendre des décisions stratégiques basées sur des données.'),(67,'Agent immobilier/agent immobilier commercial','Facilite la vente, la location ou la gestion de biens immobiliers commerciaux tels que des bureaux, des magasins ou des entrepôts, en représentant les propriétaires ou les locataires dans les transactions commerciales.'),(68,'Professionnel/ professionnelle des ressources humaines','Recrute, forme, gère et développe le personnel d\'une entreprise, en veillant au respect des politiques et des procédures en matière de ressources humaines et à la satisfaction des employés.'),(69,'Analyste des systèmes informatiques','Étudie les besoins en informatique d\'une organisation et conçoit des solutions technologiques pour répondre à ces besoins, en analysant les systèmes existants, en proposant des améliorations et en supervisant leur mise en œuvre.'),(70,'Ingénieur/ ingénieure de logiciel','Conçoit, développe et teste des logiciels et des applications informatiques pour répondre aux besoins spécifiques des utilisateurs ou des clients, en utilisant des langages de programmation et des techniques de développement logiciel.'),(71,'Agent de recouvrement','Collecte les paiements en retard sur les factures ou les prêts impayés, en contactant les débiteurs, en négociant des plans de paiement et en utilisant des méthodes de recouvrement légales pour récupérer les fonds dus.'),(72,'Agent de bord/steward/hôtesse de l\'air','Assure la sécurité et le confort des passagers à bord des avions, fournit des services de restauration et d\'hospitalité, et gère les situations d\'urgence conformément aux réglementations de l\'aviation civile.'),(73,'Chercheur/cherc heuse scientifique','Conçoit et réalise des expériences, des études et des recherches pour développer de nouvelles connaissances et des avancées scientifiques dans des domaines tels que la biologie, la physique, la chimie ou la technologie.'),(74,'Analyste de marché','Étudie les tendances du marché, analyse les données économiques et identifie les opportunités commerciales pour aider les entreprises à prendre des décisions stratégiques en matière de marketing, de développement de produits ou d\'expansion commerciale.'),(75,'Conseiller/conseillère en planification financière','Fournit des conseils financiers personnalisés aux particuliers ou aux entreprises, en évaluant leur situation financière, en identifiant leurs objectifs et en proposant des stratégies d\'investissement, de planification fiscale et de gestion de patrimoine.'),(76,'Conseiller/conseillère en gestion de carrière','Fournit des conseils professionnels aux individus sur leur carrière, leur éducation et leur développement personnel, en évaluant leurs intérêts, leurs compétences et leurs objectifs.'),(77,'Chef de projet informatique','Planifie, organise et supervise des projets informatiques, en coordonnant les équipes de développement, en respectant les délais et les budgets, et en assurant la mise en œuvre réussie de solutions logicielles ou informatiques.'),(78,'Conseiller/conseillère en marketing digital','Développe et met en œuvre des stratégies de marketing numérique pour promouvoir des produits, des services ou des marques sur les plateformes en ligne, en utilisant des techniques telles que le référencement, les médias sociaux et la publicité en ligne.'),(79,'Géologue','Étudie la composition, la structure et l\'histoire de la Terre en analysant des roches, des minéraux et des phénomènes géologiques, en fournissant des informations essentielles pour l\'exploration minière, la construction et la protection de l\'environnement.'),(80,'Expert en sécurité informatique','Protège les systèmes informatiques et les réseaux contre les cybermenaces telles que les virus, les piratages et les attaques de logiciels malveillants, en mettant en œuvre des mesures de sécurité et en surveillant les activités suspectes.'),(81,'Conseiller/conseillère en relations internationales','Analyse les politiques étrangères, les conflits internationaux et les enjeux mondiaux, en proposant des solutions diplomatiques, en négociant des accords internationaux et en facilitant la coopération entre les nations.'),(82,'Coach sportif/coach sportive','Fournit un entraînement personnalisé, des conseils nutritionnels et un soutien mental aux athlètes et aux amateurs de sport pour améliorer leur performance physique, leur conditionnement et leur bien-être global.'),(83,'Analyste de la chaîne d\'approvisionnement','Analyse et optimise les processus de la chaîne d\'approvisionnement, en supervisant la gestion des stocks, le transport, la logistique et la distribution des produits pour assurer l\'efficacité opérationnelle et la satisfaction des clients.'),(84,'Conseiller/conseillère en image','Fournit des conseils sur l\'apparence personnelle, le style vestimentaire et l\'image professionnelle pour améliorer la confiance en soi, l\'estime de soi et les perspectives de carrière des clients.');
UNLOCK TABLES;

DROP TABLE IF EXISTS `question_options`;
CREATE TABLE `question_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `prix` float NOT NULL DEFAULT '0',
  `format` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Pour les questions impliquant un téléversement de fichier, le type de fichier à téléverser sera spécifié ici (video, document, image, audio)',
  `subquestions` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'une liste d''identifiants de questions dites sous questions associées au choix de cette option.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `utilisateur_membres`;
CREATE TABLE `utilisateur_membres` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int unsigned NOT NULL,
  `membre_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Usermemb_user_foreign` (`utilisateur_id`),
  KEY `Usermemb_membre_foreign` (`membre_id`),
  CONSTRAINT `Usermemb_membre_foreign` FOREIGN KEY (`membre_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `Usermemb_user_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `utilisateur_documents`;
CREATE TABLE `utilisateur_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int unsigned NOT NULL,
  `document_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userDoc_document_foreign_idx` (`document_id`),
  KEY `userDoc_user_foreign` (`utilisateur_id`),
  CONSTRAINT `userDoc_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `userDoc_user_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `assurance_documents`;
CREATE TABLE `assurance_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `assurance_id` int unsigned NOT NULL,
  `document_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assur_doc_document_foreign_idx` (`document_id`),
  KEY `assur_doc_produit_foreign_idx` (`assurance_id`),
  CONSTRAINT `assur_doc_assurance_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `assur_doc_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `souscription_documents`;
CREATE TABLE `souscription_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `souscription_id` int unsigned NOT NULL,
  `document_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sous_doc_UNIQUE` (`document_id`,`souscription_id`),
  KEY `sousDoc_document_foreign_idx` (`document_id`),
  KEY `sousDoc_souscription_foreign_idx` (`souscription_id`),
  CONSTRAINT `sousDoc_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `sousDoc_souscription_foreign` FOREIGN KEY (`souscription_id`) REFERENCES `souscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `souscription_beneficiaires`;
CREATE TABLE `souscription_beneficiaires` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `souscription_id` int unsigned NOT NULL,
  `beneficiaire_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sous_benef_UNIQUE` (`souscription_id`,`beneficiaire_id`),
  KEY `sousBenef_souscription_foreign_idx` (`souscription_id`),
  KEY `sousBenef_beneficiaire_foreign_idx` (`beneficiaire_id`),
  CONSTRAINT `sousBenef_beneficiaire_foreign` FOREIGN KEY (`beneficiaire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `sousBenef_souscription_foreign` FOREIGN KEY (`souscription_id`) REFERENCES `souscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `portefeuilles`;
CREATE TABLE `portefeuilles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `solde` float NOT NULL,
  `devise` varchar(45) DEFAULT NULL,
  `utilisateur_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `portefeuille_user_foreign_idx` (`utilisateur_id`),
  CONSTRAINT `portefeuille_user_foreign` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `assurance_categories`;
CREATE TABLE `assurance_categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `assurance_id` int unsigned NOT NULL,
  `categorie_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assurance_categorie_UNIQUE` (`categorie_id`,`assurance_id`),
  KEY `assurCat_assur_foreign_idx` (`assurance_id`),
  KEY `assurCat_categorie_foreign_idx` (`categorie_id`),
  CONSTRAINT `assurCat_assur_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `assurCat_categorie_foreign` FOREIGN KEY (`categorie_id`) REFERENCES `categorie_produits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `transaction_lignes`;
CREATE TABLE `transaction_lignes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` int unsigned NOT NULL,
  `ligne_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_id_UNIQUE` (`transaction_id`,`ligne_id`),
  KEY `transactLigne_transaction_foreign_idx` (`transaction_id`),
  KEY `transactLigne_ligne_foreign_idx` (`ligne_id`),
  CONSTRAINT `transactLigne_ligne_foreign` FOREIGN KEY (`ligne_id`) REFERENCES `lignetransactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `transactLigne_transaction_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `signatures`;
CREATE TABLE `signatures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(155) COLLATE utf8mb4_bin NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `conversation_origins`;
CREATE TABLE `conversation_origins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(75) COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `conversation_origins` WRITE;
INSERT INTO `conversation_origins` VALUES (1,'App'),(2,'Facebook'),(3,'Instagram'),(4,'Whatsapp');
UNLOCK TABLES;

DROP TABLE IF EXISTS `conversation_types`;
CREATE TABLE `conversation_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(75) COLLATE utf8mb4_bin NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `conversation_types` WRITE;
INSERT INTO `conversation_types` VALUES (1,'Incident','les conversations de suivi des incidents'),(2,'Sinistre','les conversations de suivi des sinistres'),(3,'Groupe','les conversations libres de groupe'),(4,'Message','les conversations avec un utilisateur précis'),(5,'Autre','les conversations internes ou intégrées');
UNLOCK TABLES;

DROP TABLE IF EXISTS `conversations`;
CREATE TABLE `conversations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `image_id` int unsigned DEFAULT NULL,
  `type` varchar(75) COLLATE utf8mb4_bin NOT NULL,
  `etat` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '1' COMMENT 'inactive/active',
  `origin` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'App',
  PRIMARY KEY (`id`),
  KEY `conversation_image_foreign_idx` (`image_id`),
  KEY `conversation_type_foreign_idx` (`type`),
  KEY `conversation_origin` (`origin`),
  CONSTRAINT `conversation_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `conversation_origin` FOREIGN KEY (`origin`) REFERENCES `conversation_origins` (`nom`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `conversation_type_foreign` FOREIGN KEY (`type`) REFERENCES `conversation_types` (`nom`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `sinistre_types`;
CREATE TABLE `sinistre_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) COLLATE utf8mb4_bin NOT NULL,
  `description` text COLLATE utf8mb4_bin,
  `statut` tinyint NOT NULL DEFAULT '1' COMMENT 'inactif/actif',
  `catProduit_id` int unsigned NOT NULL COMMENT 'actif/inactif',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom_UNIQUE` (`nom`),
  KEY `sinistra_catProd_id_foreign_idx` (`catProduit_id`),
  CONSTRAINT `sinistra_catProd_id_foreign` FOREIGN KEY (`catProduit_id`) REFERENCES `categorie_produits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `sinistre_types` WRITE;
INSERT INTO `sinistre_types` VALUES (1,'Décès',"La personne couverte par l'assurance est décédé.",1,3),(2,'Catastrophes naturelles','La garantie catastrophe naturelle couvre les dommages matériels subis par le véhicule assuré à la suite d’un événement naturel de forte ampleur.',1,4),(10,'Vandalisme','Un acte de vandalisme sur une voiture est une dégradation volontaire du véhicule. Le vandalisme a deux objectifs : provoquer le propriétaire de la voiture et lui occasionner des dépenses lourdes de réparations.',1,4),(11,'Stationnement','De façon générale, dès qu’une voiture occupe une place de façon ininterrompue au-delà de la durée légale de 7 jours, le stationnement est considéré comme abusif',1,4),(12,'Incendie','Comme pour le vol, la garantie incendie n’est effective qu’en cas de déclaration préalable aux autorités. L’assuré dispose de 5 jours pour déclarer le sinistre à son assureur en lui envoyant une attestation de dépôt de plainte',1,4),(13,'Vol','Un vol de voiture doit être déclaré dans les 24 heures à la police et dans les 48 heures à l’assurance. Les autorités remettent au propriétaire du véhicule une attestation de dépôt de plainte, qui doit ensuite être envoyée par lettre recommandée (ou remise en main propre) à l’assureur',1,4),(14,'Bris de glace','La garantie optionnelle bris de glace est utile en cas de vitre cassée ou fissurée. Si vous êtes victime d’un bris de glace, vous avez 5 jours pour déclarer le sinistre à votre assureur et obtenir un dédommagement',1,4),(15,'Accident','Un automobiliste victime d’un accident de voiture a 5 jours pour remplir un constat à l’amiable et l’envoyer à son assureur auto',1,4),(16,'Autre','',1,6);
UNLOCK TABLES;

DROP TABLE IF EXISTS `sinistres`;
CREATE TABLE `sinistres` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(25) COLLATE utf8mb4_bin NOT NULL,
  `titre` varchar(150) COLLATE utf8mb4_bin NOT NULL,
  `description` text COLLATE utf8mb4_bin NOT NULL,
  `etat` tinyint NOT NULL DEFAULT '1' COMMENT 'Termine/En cours',
  `auteur_id` int unsigned NOT NULL,
  `type_id` int unsigned NOT NULL,
  `souscription_id` int unsigned NOT NULL,
  `conversation_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sinistre_auteur_foreign_idx` (`auteur_id`),
  KEY `sinistre_type_foreign_idx` (`type_id`),
  KEY `sinistre_souscription_foreign_idx` (`souscription_id`),
  KEY `sinistre_conversation_foreign_idx` (`conversation_id`),
  CONSTRAINT `sinistre_auteur_foreign` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `sinistre_conversation_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `sinistre_souscription_foreign` FOREIGN KEY (`souscription_id`) REFERENCES `souscriptions` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `sinistre_type_foreign` FOREIGN KEY (`type_id`) REFERENCES `sinistre_types` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `sinistre_documents`;
CREATE TABLE `sinistre_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sinistre_id` int unsigned NOT NULL,
  `document_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `docSinistre_sinistre_foreign_idx` (`sinistre_id`),
  KEY `docSinistre_document_foreign_idx` (`document_id`),
  CONSTRAINT `docSinistre_document_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `docSinistre_sinistre_foreign` FOREIGN KEY (`sinistre_id`) REFERENCES `sinistres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `sinistre_images`;
CREATE TABLE `sinistre_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `sinistre_id` int unsigned NOT NULL,
  `image_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `imgSinstre_sinistre_foreign_idx` (`sinistre_id`),
  KEY `imgSinistre_image_foreign_idx` (`image_id`),
  CONSTRAINT `imgSinistre_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `imgSinstre_sinistre_foreign` FOREIGN KEY (`sinistre_id`) REFERENCES `sinistres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `conversation_membres`;
CREATE TABLE `conversation_membres` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` int unsigned NOT NULL,
  `membre_id` int unsigned NOT NULL,
  `isAdmin` tinyint NOT NULL DEFAULT '0' COMMENT 'Dans une conversation de groupe, seul un admin peut supprimer la conversation. Dans les autres cas, tout le monde est admin par défaut.',
  PRIMARY KEY (`id`,`conversation_id`),
  UNIQUE KEY `convMemb_conv_memb_uniq` (`conversation_id`,`membre_id`),
  KEY `convMemb_membre_foreign_idx` (`membre_id`),
  KEY `convMemb_conversation_foreign_idx` (`conversation_id`),
  CONSTRAINT `convMemb_conversation_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `convMemb_membre_foreign` FOREIGN KEY (`membre_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `incident_types`;
CREATE TABLE `incident_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(75) COLLATE utf8mb4_bin NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `statut` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
LOCK TABLES `incident_types` WRITE;
INSERT INTO `incident_types` VALUES (1,'problème de connexion','Vous faites face à un disfonctionnement du processus de connexion',1),(2,'problème de création de compte','Vous ne parvenez pas à créer votre compte pour un quelconque motif',1);
UNLOCK TABLES;

DROP TABLE IF EXISTS `incidents`;
CREATE TABLE `incidents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(75) COLLATE utf8mb4_bin NOT NULL,
  `titre` varchar(150) COLLATE utf8mb4_bin NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `etat` tinyint NOT NULL DEFAULT '1' COMMENT 'inactif/actif',
  `type_id` int unsigned NOT NULL,
  `auteur_id` int unsigned NOT NULL,
  `conversation_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `incidents_type_foreign_idx` (`type_id`),
  KEY `incidents_auteur_foreign_idx` (`auteur_id`),
  KEY `incidents_conversation_foreign_idx` (`conversation_id`),
  CONSTRAINT `incidents_auteur_foreign` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `incidents_conversation_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `incidents_type_foreign` FOREIGN KEY (`type_id`) REFERENCES `incident_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `incident_images`;
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

DROP TABLE IF EXISTS `categorie_mkps`;
CREATE TABLE `categorie_mkps` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `image_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mkpCat_image_foreign_idx` (`image_id`),
  CONSTRAINT `mkpCat_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `assurance_categoriesmkp`;
CREATE TABLE `assurance_categoriesmkp` (
  `id` int unsigned NOT NULL,
  `assurance_id` int unsigned NOT NULL,
  `categorie_mkp_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`assurance_id`,`categorie_mkp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `msg_text` tinytext COLLATE utf8mb4_bin NOT NULL,
  `from_user_id` int unsigned NOT NULL,
  `to_conversation_id` int unsigned NOT NULL,
  `statut` tinyint NOT NULL,
  `etat` tinyint NOT NULL DEFAULT '1',
  `dateCreation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `message_from_foreign_idx` (`from_user_id`),
  KEY `message_to_foreign_idx` (`to_conversation_id`),
  CONSTRAINT `message_from_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `message_to_foreign` FOREIGN KEY (`to_conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `message_documents`;
CREATE TABLE `message_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int unsigned NOT NULL,
  `document_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Unique` (`message_id`,`document_id`),
  KEY `message_docuemnt_foreign_idx` (`document_id`),
  KEY `message_image_foreign_idx` (`message_id`),
  CONSTRAINT `messageDoc_docuemnt_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `messageDoc_message_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `message_images`;
CREATE TABLE `message_images` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int unsigned NOT NULL,
  `image_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Unique` (`message_id`,`image_id`),
  KEY `messageImg_image_foreign_idx` (`image_id`),
  KEY `messageImg_message_foreign_idx` (`message_id`),
  CONSTRAINT `messageImg_image_foreign` FOREIGN KEY (`image_id`) REFERENCES `images` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `messageImg_message_foreign` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `assurance_pieces`;
CREATE TABLE `assurance_pieces` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `assurance_id` int unsigned NOT NULL,
  `piece_id` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Unique` (`assurance_id`,`piece_id`),
  KEY `AssurPiece_assur_foreign_idx` (`assurance_id`),
  KEY `AssurPiece_piece_foreign_idx` (`piece_id`),
  CONSTRAINT `AssurPiece_assur_foreign` FOREIGN KEY (`assurance_id`) REFERENCES `assurances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `AssurPiece_piece_foreign` FOREIGN KEY (`piece_id`) REFERENCES `document_titres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `auth_logins`;
CREATE TABLE `auth_logins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `id_type` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_type_identifier` (`id_type`,`identifier`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `auth_token_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_token_logins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(255) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `id_type` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `user_id` int unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  `success` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_type_identifier` (`id_type`,`identifier`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `auth_remember_tokens`;
CREATE TABLE `auth_remember_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `selector` varchar(255) NOT NULL,
  `hashedValidator` varchar(255) NOT NULL,
  `user_id` int unsigned NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selector` (`selector`),
  KEY `auth_remember_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `auth_remember_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `auth_groups_users`;
CREATE TABLE `auth_groups_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `group` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_groups_users_user_id_foreign` (`user_id`),
  CONSTRAINT `auth_groups_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `auth_permissions_users`;
CREATE TABLE `auth_permissions_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `permission` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_permissions_users_user_id_foreign` (`user_id`),
  CONSTRAINT `auth_permissions_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `class` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text,
  `type` varchar(31) NOT NULL DEFAULT 'string',
  `context` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
