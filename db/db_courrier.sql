-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 03 juin 2025 à 12:26
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `db_courrier`
--

-- --------------------------------------------------------

--
-- Structure de la table `courrier`
--

CREATE TABLE `courrier` (
  `id_courrier` int(11) NOT NULL,
  `Numero_Courrier` varchar(255) NOT NULL,
  `Date` datetime NOT NULL,
  `Objet` text DEFAULT NULL,
  `pdf` varchar(255) NOT NULL,
  `Nature` text NOT NULL,
  `Type` text NOT NULL,
  `Expediteur` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `courrier`
--

INSERT INTO `courrier` (`id_courrier`, `Numero_Courrier`, `Date`, `Objet`, `pdf`, `Nature`, `Type`, `Expediteur`) VALUES
(116, '123', '2024-11-01 15:13:00', 'Demande', '6724f064d3730_6721fcfd99043_SENUM_DEX_DSM_RAPPORT_CAMPUS_SOCIAL_ESP_COUD.pdf', 'arrivee', 'externe', 'Gibma'),
(117, '32', '2024-11-10 15:09:00', 'Regularisations', '6730ccb360507_672bcb0bae167_6727d3ad189da_6727d1222002e_6721fcfd99043_SENUM_DEX_DSM_RAPPORT_CAMPUS_SOCIAL_ESP_COUD.pdf', 'depart', 'interne', 'mage'),
(118, '00221', '2025-05-12 14:17:00', 'Demande d\'emploi', '6822032719d3b_codif_grand_campus.pdf', 'arrivee', 'interne', 'doudou'),
(119, '00222', '2025-05-13 13:28:00', 'Demande d\'emploi', '6823490e06173_codif_grand_campus.pdf', 'depart', 'externe', 'DOUDOU'),
(120, '00223', '2025-05-13 15:47:00', 'oooooooooo', '682369944daa1_codif_grand_campus.pdf', 'depart', 'interne', 'mage'),
(121, '00224', '2025-05-13 15:47:00', 'hhhhhhhhhhhhhh', '682369d92a27a_codif_grand_campus.pdf', 'depart', 'interne', 'Astou Seye'),
(122, '00225', '2025-05-19 10:44:00', 'HHHHHHHHHHHH', '682b0b9e6dcbf_SVT-5e.pdf', 'arrivee', 'externe', 'Astou '),
(123, '00233', '2025-06-03 10:23:00', 'Etude et avis', '683ecd52b5abf_codif_grand_campus.pdf', 'arrive', 'interne', 'Astou Seye');

-- --------------------------------------------------------

--
-- Structure de la table `departement`
--

CREATE TABLE `departement` (
  `id_departement` int(11) NOT NULL,
  `Nom_dept` enum('CSA','AC','DCH','DMG','DSAS','DRU','DCU','DACS','DE','DI','DST','DB','BAP','DA','CONSEILLER','A_P','A_I','C_C','C_C_I','CEL_S_C_Q','CELL_JURI','CELL_PASS_MAR','C_C','U_S','C_M','BAD','B_C') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `departement`
--

INSERT INTO `departement` (`id_departement`, `Nom_dept`) VALUES
(1, 'CSA'),
(2, 'AC'),
(3, 'DCH');

-- --------------------------------------------------------

--
-- Structure de la table `imputation`
--

CREATE TABLE `imputation` (
  `id_imputation` int(11) NOT NULL,
  `id_courrier` int(11) NOT NULL,
  `Instruction` enum('Accord','M''en parler','Etude et Avis','Pour réponse','Me voir avec','Pour suivi','Suite à donner','Transmission','Pour information','Pour traitement','Classement','Diffusion') DEFAULT NULL,
  `departement` varchar(255) DEFAULT NULL,
  `date_impu` date NOT NULL DEFAULT current_timestamp(),
  `instruction_personnalisee` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `imputation`
--

INSERT INTO `imputation` (`id_imputation`, `id_courrier`, `Instruction`, `departement`, `date_impu`, `instruction_personnalisee`) VALUES
(185, 116, '', 'DI', '2024-11-03', 'Veulliez Venir'),
(192, 116, 'Pour traitement', 'AC', '2024-11-06', NULL),
(194, 117, 'Transmission', 'AC', '2024-11-10', NULL),
(195, 117, '', 'C_C_I', '2024-11-10', 'YUP_YUP'),
(198, 117, 'Transmission', 'CONSEILLER', '2024-11-13', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `suivi`
--

CREATE TABLE `suivi` (
  `id_suivi` int(11) NOT NULL,
  `Instruction` varchar(255) DEFAULT NULL,
  `Statut` varchar(255) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_imputation` int(11) DEFAULT NULL,
  `date_suivi` date NOT NULL DEFAULT current_timestamp(),
  `pdf` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `suivi`
--

INSERT INTO `suivi` (`id_suivi`, `Instruction`, `Statut`, `id_user`, `id_imputation`, `date_suivi`, `pdf`) VALUES
(119, 'Veulliez Venir', 'rez', 4, 185, '2024-11-10', '672bcb0bae167_6727d3ad189da_6727d1222002e_6721fcfd99043_SENUM_DEX_DSM_RAPPORT_CAMPUS_SOCIAL_ESP_COUD.pdf'),
(130, 'YUP_YUP', 'C\'est moi', 3, 195, '2024-11-10', '67312dbba931e_6730ccb360507_672bcb0bae167_6727d3ad189da_6727d1222002e_6721fcfd99043_SENUM_DEX_DSM_RAPPORT_CAMPUS_SOCIAL_ESP_COUD.pdf'),
(131, 'YUP_YUP', 'encore', 3, 195, '2024-11-13', '67312dee786b7_67312dbba931e_6730ccb360507_672bcb0bae167_6727d3ad189da_6727d1222002e_6721fcfd99043_SENUM_DEX_DSM_RAPPORT_CAMPUS_SOCIAL_ESP_COUD.pdf');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `Nom` varchar(255) NOT NULL,
  `Prenom` varchar(255) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Actif` tinyint(1) NOT NULL DEFAULT 1,
  `Password` varchar(255) NOT NULL,
  `Fonction` enum('assistant_courrier','chef_courrier') DEFAULT NULL,
  `subrole` enum('AC','DI','DST','CELL_S_C_Q','AC','CELL_PASS_MAR','A_I','A_P','C_C_I','C_COOP','C_COM','CELL_JURI','U_S','B_C','BAD','BAP','DB','DE','DST','DACS','DCU','DRU','DSAS','DMG','DCH','CSA','CONSEILLER') DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `Matricule` varchar(255) DEFAULT NULL,
  `Tel` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `Nom`, `Prenom`, `Username`, `Actif`, `Password`, `Fonction`, `subrole`, `email`, `Matricule`, `Tel`) VALUES
(30, 'a', 'a', 'a', 1, '86f7e437faa5a7fce15d1ddcb9eaeaea377667b8', 'chef_courrier', NULL, 'a.fall42@gmail.com', '12', '2432'),
(33, 'DIOUF', 'Souleymane', 'souleye', 1, '1ec3e0701fadc706301af58a9c02f6c425cfb00a', 'chef_courrier', NULL, 'souleye@gmail.com', '123456/L', '338675656'),
(36, 'NDIAYE', 'Abdou Aziz', 'aziz', 1, '1ec3e0701fadc706301af58a9c02f6c425cfb00a', 'assistant_courrier', NULL, 'abdouazizn@esp.sn', '897979/M', '772612777'),
(39, 'SONKO', 'Ousmane', 'sonko', 1, '1ec3e0701fadc706301af58a9c02f6c425cfb00a', 'assistant_courrier', 'B_C', 'sonko@coud.sn', '123888/M', '774336677');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `courrier`
--
ALTER TABLE `courrier`
  ADD PRIMARY KEY (`id_courrier`),
  ADD UNIQUE KEY `unique_numero` (`Numero_Courrier`);

--
-- Index pour la table `departement`
--
ALTER TABLE `departement`
  ADD PRIMARY KEY (`id_departement`);

--
-- Index pour la table `imputation`
--
ALTER TABLE `imputation`
  ADD PRIMARY KEY (`id_imputation`),
  ADD UNIQUE KEY `id_imputation` (`id_imputation`),
  ADD KEY `id_courrier` (`id_courrier`);

--
-- Index pour la table `suivi`
--
ALTER TABLE `suivi`
  ADD PRIMARY KEY (`id_suivi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_imputation` (`id_imputation`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `Matricule` (`Matricule`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `courrier`
--
ALTER TABLE `courrier`
  MODIFY `id_courrier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT pour la table `departement`
--
ALTER TABLE `departement`
  MODIFY `id_departement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `imputation`
--
ALTER TABLE `imputation`
  MODIFY `id_imputation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=199;

--
-- AUTO_INCREMENT pour la table `suivi`
--
ALTER TABLE `suivi`
  MODIFY `id_suivi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
