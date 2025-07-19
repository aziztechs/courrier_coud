-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 05 juil. 2025 à 15:27
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
-- Structure de la table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `activity_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `activity_log`
--

INSERT INTO `activity_log` (`id`, `username`, `action`, `activity_date`) VALUES
(1, 'aziz', 'Tentative de connexion échouée', '2025-07-05 11:23:55'),
(2, 'aziz', 'Connexion réussie', '2025-07-05 11:24:54'),
(3, 'souleye', 'Connexion réussie', '2025-07-05 12:52:03');

-- --------------------------------------------------------

--
-- Structure de la table `archive`
--

CREATE TABLE `archive` (
  `id_archive` int(11) NOT NULL,
  `type_archivage` enum('manuel','automatique','annuel') NOT NULL,
  `num_correspondance` varchar(100) NOT NULL,
  `pdf_archive` varchar(255) NOT NULL,
  `date_archivage` datetime NOT NULL DEFAULT current_timestamp(),
  `commentaire` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `archive`
--

INSERT INTO `archive` (`id_archive`, `type_archivage`, `num_correspondance`, `pdf_archive`, `date_archivage`, `commentaire`) VALUES
(28, 'manuel', 'COR-2025-002', 'archives/685f4e07d9138_demolitions.pdf', '2025-06-28 02:05:59', 'FGYUYUYU'),
(29, 'automatique', 'COR-2025-003', 'archives/685f51d584244_buildings (1).pdf', '2025-06-28 02:06:52', 'SDQA'),
(31, 'automatique', 'COR-2025-040', 'archives/685ffa3677d84_2017_math.pdf', '2025-06-28 14:20:38', 'Dqd'),
(32, 'automatique', 'COR-2025-041', 'archives/685ffb413278f_2017_math.pdf', '2025-06-28 14:25:05', 'BJCBCKKK'),
(33, 'automatique', 'COR-2025-042', 'archives/68600a56bb369_demolitions.pdf', '2025-06-28 15:29:26', 'KLKLKLJHG'),
(34, 'automatique', 'COR-2025-043', 'archives/68601412797d0_anglais-5e.pdf', '2025-06-28 16:10:58', 'SSSS'),
(35, 'manuel', 'COR-2025-044', 'archives/686016612d28a_anglais-5e.pdf', '2025-06-28 16:20:49', 'SSQFQS'),
(37, 'annuel', 'COR-2025-046', 'archives/68604607172b4_Untitled.pdf', '2025-06-28 19:39:47', 'JJJ'),
(38, 'manuel', 'COR-2025-047', 'archives/686046738457e_constructions.pdf', '2025-06-28 19:45:55', 'FFFFF');

-- --------------------------------------------------------

--
-- Structure de la table `courrier`
--

CREATE TABLE `courrier` (
  `id_courrier` int(11) NOT NULL,
  `Numero_Courrier` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `Objet` text NOT NULL,
  `pdf` varchar(255) NOT NULL,
  `Nature` enum('arrive','depart') NOT NULL,
  `Type` enum('interne','externe') NOT NULL,
  `Expediteur` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `courrier`
--

INSERT INTO `courrier` (`id_courrier`, `Numero_Courrier`, `date`, `Objet`, `pdf`, `Nature`, `Type`, `Expediteur`) VALUES
(30, 'COUR-2025-001', '2025-07-05', 'OOOOOOOOOOOOOOOO', '../../uploads/courriers/6868f1d5b92cb_constructions.pdf', 'arrive', 'interne', 'Aziz'),
(31, 'COUR-2025-002', '2025-07-03', 'demande d\'aide', '../../uploads/courriers/6868f1bcf3782_anglais-5e.pdf', 'depart', 'interne', 'issa sarr'),
(32, 'COUR-2025-003', '2025-07-05', 'RECLASSEMENT', '../../uploads/courriers/6868f2a79fd2b_mails.pdf', 'depart', 'interne', 'ASSANE FALL'),
(33, 'COUR-2025-060', '2025-07-04', 'AAAAAAA', 'sdbbn.pdf', 'arrive', 'interne', 'BBBBBBBB'),
(34, 'COUR-2025-061', '2025-07-05', 'TTTTT', '../../uploads/courriers/6868f35db4764_ticket_caisse.pdf', 'arrive', 'interne', 'FFFFFF'),
(35, 'COUR-2025-062', '2025-07-05', 'remboursement', '../../uploads/courriers/686908b96fa7f_CORRIGES-CAHIER-EXERCICES-anglais.pdf', 'depart', 'interne', 'Abdou Aziz NDIAYE'),
(36, 'COUR-2025-063', '2025-07-05', 'BJHJHK', '../../uploads/courriers/686919083b2b5_Untitled.pdf', 'arrive', 'interne', 'Astou '),
(37, 'COUR-2025-064', '2025-07-05', 'SCSCS', '../../uploads/courriers/68691b5c6de10_constructions.pdf', 'arrive', 'interne', 'Aziz'),
(38, 'COUR-2025-065', '2025-07-05', 'jjddd', '../../uploads/courriers/68691c0b9df8a_buildings.pdf', 'arrive', 'externe', 'Aziz');

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
-- Structure de la table `facture`
--

CREATE TABLE `facture` (
  `id_facture` int(11) NOT NULL,
  `date_arrive` date NOT NULL,
  `numero_courrier` varchar(50) NOT NULL,
  `expediteur` varchar(100) NOT NULL,
  `numero_facture` varchar(50) NOT NULL,
  `decade` varchar(255) DEFAULT NULL,
  `montant_ttc` decimal(10,2) NOT NULL,
  `type_facture` varchar(30) NOT NULL,
  `facture_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `facture`
--

INSERT INTO `facture` (`id_facture`, `date_arrive`, `numero_courrier`, `expediteur`, `numero_facture`, `decade`, `montant_ttc`, `type_facture`, `facture_pdf`) VALUES
(29, '2025-06-27', 'COUR-2025-010', 'FATIMA', 'FACT-2025-010', 'Du 10-01-2023 au 10-06-2023', 4500000.00, 'Eau', 'facture_685f114df1b855.58631284.pdf'),
(30, '2024-01-25', 'COUR-2025-011', 'AA', 'FACT-2025-011', 'Du 10-01-2023 au 10-06-2023', 78000000.00, 'Restaurant', 'facture_685f128522a971.23061501.pdf'),
(32, '2025-06-28', 'COUR-2025-012', 'Abdou Aziz NDIAYE', 'FACT-2025-012', 'Du 10-01-2023 au 10-06-2023', 7890890.00, 'Internet', 'facture_685f1644b2cfb1.14671411.pdf'),
(33, '2025-06-28', 'COUR-2025-013', 'AA', 'FACT-2025-013', 'Du 10-01-2023 au 10-06-2023', 30000000.00, 'Restaurant', 'facture_685f1a9aa06dc2.28364388.pdf'),
(34, '2025-06-28', 'COUR-2025-014', 'ASSANE FALL', 'FACT-2025-01', 'Du 09-05-2022 au 10-01-2023', 25000000.00, 'Eau', 'facture_685f31866815d6.91557052.pdf'),
(36, '2025-06-28', 'COUR-2025-018', 'AA', 'FACT-2025-018', 'Du 09-03-2025 au 10-09-2025', 13000000.00, 'Téléphone', 'facture_685f36afba8247.56649032.pdf'),
(37, '2025-06-28', 'COUR-2025-019', 'Amadou NDIAYE', 'FACT-2025-019', 'Du 09-05-2022 au 10-01-2023', 1000000.00, 'Internet', 'facture_685fda45e7c538.64464758.pdf'),
(38, '2025-06-28', 'COUR-2025-020', 'Coumba diop', 'FACT-2025-020', 'Du 09-05-2022 au 10-01-2023', 38000000.00, 'Electricité', 'facture_685fe221afe2e5.50666131.pdf'),
(39, '2025-07-01', 'COUR-2025-598', 'Astou Seye', 'FACT-2025-598', 'Du 01-01-2025 au 31-01-2025', 12000000.00, 'Internet', 'facture_6864f663121be4.53405560.pdf'),
(40, '2025-07-02', 'COUR-2025-800', 'ASSANE FALL', 'FACT-2025-800', 'Du 01-01-2025 au 31-01-2025', 12000000.00, 'Téléphone', 'facture_686509d1bda3d6.32777059.pdf');

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
-- Structure de la table `suivi_courrier`
--

CREATE TABLE `suivi_courrier` (
  `id_suivi` int(11) NOT NULL,
  `id_courrier` int(11) NOT NULL,
  `destinataire` varchar(100) NOT NULL,
  `statut_1` varchar(255) DEFAULT NULL,
  `statut_2` varchar(255) DEFAULT NULL,
  `statut_3` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `suivi_courrier`
--

INSERT INTO `suivi_courrier` (`id_suivi`, `id_courrier`, `destinataire`, `statut_1`, `statut_2`, `statut_3`) VALUES
(17, 34, 'DG', 'DA', '', ''),
(18, 35, 'CSA', '', '', ''),
(19, 33, 'CSA', 'CHRONO', '', ''),
(20, 32, 'Directeur', '', '', ''),
(21, 31, 'CSA', 'DST', '', ''),
(22, 30, 'Directeur', 'ACP', '', '');

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
  `Fonction` enum('assistant_courrier','chef_courrier','directeur','superadmin') DEFAULT NULL,
  `subrole` enum('AC','DI','CELL_S_C_Q','CELL_PASS_MAR','A_I','A_P','C_C_I','C_COOP','C_COM','CELL_JURI','U_S','B_C','BAD','BAP','DB','DE','DST','DACS','DCU','DRU','DSAS','DMG','DCH','CSA','CONSEILLER') DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `Matricule` varchar(255) DEFAULT NULL,
  `Tel` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `Nom`, `Prenom`, `Username`, `Actif`, `Password`, `Fonction`, `subrole`, `email`, `Matricule`, `Tel`) VALUES
(33, 'DIOUF', 'Souleymane', 'souleye', 1, '$2y$10$dteYIIk876UK0oDyz/YO6.07JWEvxzhQ.VHHE6nH6uWqJHHENoZ/m', 'chef_courrier', NULL, 'souleye@coud.sn', '126456/L', '338675656'),
(36, 'NDIAYE', 'Abdou Aziz', 'aziz', 1, '$2y$10$a3vHyb8U79H8zwMXg7Vd/u1V6Bx2EcVsFNtlSbcluyEpRui8.ZW0a', 'assistant_courrier', NULL, 'abdouazizn@esp.sn', '897979/M', '772612777'),
(39, 'FALL', ' Abdou Magib', 'magib', 1, '1ec3e0701fadc706301af58a9c02f6c425cfb00a', 'assistant_courrier', 'B_C', 'sonko@coud.sn', '123888/M', '774336677'),
(46, 'SARR', 'ISSA', 'isarr', 1, '$2y$10$vCVfIFmBq3DvK6JSfOxWr.x6Cdi02YZFOJKMQ7GMgqllco6KY7sRC', 'assistant_courrier', NULL, 'azizsdbbn@gmail.com', '132434/M', '772767887'),
(49, 'DIOP', 'Hameth', 'diophameth', 1, '85a08b7a74f6a33dddd397a114ce5fdc23369ead', 'chef_courrier', NULL, 'diophameth@coud.sn', '987609/M', '779616785'),
(51, 'FALL', 'Cheikh Ibra', 'cheikhibra', 1, '$2y$10$OgozQnR3oZJ2k.QS/HjVcevRHW5RKolYwwKI6WjSdUJQjsP3zKFGO', 'assistant_courrier', NULL, 'cheikhibra@coud.sn', '126786/L', '778976060'),
(52, 'NDIAYE', 'Abdou Aziz', 'aziz2', 1, '7f985cfbc5cf9b6d2dcc4f2f4bd5876ffdf3da33', 'chef_courrier', NULL, 'azizsdbbn2@gmail.com', '788988/F', '779512970'),
(54, 'NDIAYE', 'Abdou Aziz', 'aziz4', 1, '7f985cfbc5cf9b6d2dcc4f2f4bd5876ffdf3da33', 'assistant_courrier', NULL, 'ndiaye1@sdbbntech.com', '788989/H', '779809090'),
(56, 'AA', 'BB', 'aa', 1, '8639316725bece379d63338db8ba6fcece609819', 'assistant_courrier', NULL, 'aziz@sdbbntech.com', '788987/U', '789008789'),
(57, 'Sarr', 'Assane', 'assane', 1, '7f985cfbc5cf9b6d2dcc4f2f4bd5876ffdf3da33', 'assistant_courrier', NULL, 'assane@coud.sn', '788987/X', '789008089'),
(61, 'DIAGNE', 'MADIAGNE', 'madiagne', 1, '$2y$10$FuN1rsFty97yjTFcA6/8ROFdo34oqv56IntRhDlgL6DWTTA1bCZz2', 'superadmin', NULL, 'madiagne@coud.sn', '987908/A', '778988787'),
(62, 'DJIBA', 'SAM', 'sam', 1, '$2y$10$A7bnRDKsTdIrl1dKivENkunsZKPUEqSAtnVlIkP4rfkST57s.u6Uq', 'directeur', NULL, 'sam@coud.sn', '789987/Z', '778988799'),
(64, 'NDIAYE', 'KHADIJA', 'khadijarasoul', 1, '$2y$10$VuKiAzSMoyMEQKX.DlgJLOOumTIMtz8QESznI6IrY3JgrqH10RcjS', 'assistant_courrier', NULL, 'khadija@coud.sn', '123899/P', '709075789'),
(65, 'malack', 'mass', 'mass', 1, '$2y$10$DqkRN0YrD77n2I0hlGWMYOO8xaLtB/brlXNU3ZYNLLXWAfUNXdyI2', 'superadmin', NULL, 'mass@coud.sn', '788987/W', '709008789'),
(66, 'DIOP', 'COUMBA', 'coumba', 1, '$2y$10$Vd0Csosz5b53CbyJ0xayCOV1/FgDsq3le8/dxErNH6tcCAhGIHEdO', 'chef_courrier', NULL, 'coumba@coud.sn', '126456/G', '709028789');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `archive`
--
ALTER TABLE `archive`
  ADD PRIMARY KEY (`id_archive`),
  ADD UNIQUE KEY `num_correspondance` (`num_correspondance`);

--
-- Index pour la table `courrier`
--
ALTER TABLE `courrier`
  ADD PRIMARY KEY (`id_courrier`),
  ADD UNIQUE KEY `unique_numero` (`Numero_Courrier`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_nature` (`Nature`),
  ADD KEY `idx_type` (`Type`);

--
-- Index pour la table `departement`
--
ALTER TABLE `departement`
  ADD PRIMARY KEY (`id_departement`);

--
-- Index pour la table `facture`
--
ALTER TABLE `facture`
  ADD PRIMARY KEY (`id_facture`),
  ADD UNIQUE KEY `numero_courrier` (`numero_courrier`),
  ADD UNIQUE KEY `numero_facture` (`numero_facture`),
  ADD UNIQUE KEY `uk_numero_facture` (`numero_facture`),
  ADD UNIQUE KEY `uk_numero_courrier` (`numero_courrier`);

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
-- Index pour la table `suivi_courrier`
--
ALTER TABLE `suivi_courrier`
  ADD PRIMARY KEY (`id_suivi`),
  ADD KEY `idx_id_courrier` (`id_courrier`);

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
-- AUTO_INCREMENT pour la table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `archive`
--
ALTER TABLE `archive`
  MODIFY `id_archive` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT pour la table `courrier`
--
ALTER TABLE `courrier`
  MODIFY `id_courrier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pour la table `departement`
--
ALTER TABLE `departement`
  MODIFY `id_departement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `facture`
--
ALTER TABLE `facture`
  MODIFY `id_facture` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

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
-- AUTO_INCREMENT pour la table `suivi_courrier`
--
ALTER TABLE `suivi_courrier`
  MODIFY `id_suivi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `suivi_courrier`
--
ALTER TABLE `suivi_courrier`
  ADD CONSTRAINT `fk_suivi_courrier` FOREIGN KEY (`id_courrier`) REFERENCES `courrier` (`id_courrier`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
