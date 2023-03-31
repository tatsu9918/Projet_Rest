-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 31 mars 2023 à 13:24
-- Version du serveur : 8.0.27
-- Version de PHP : 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet_rest`
--

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE IF NOT EXISTS `articles` (
  `Id_Articles` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(50) DEFAULT NULL,
  `Contenu` text,
  `date_publi` date DEFAULT NULL,
  `Id_Utilisateur` int NOT NULL,
  PRIMARY KEY (`Id_Articles`),
  KEY `Id_Utilisateur` (`Id_Utilisateur`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`Id_Articles`, `titre`, `Contenu`, `date_publi`, `Id_Utilisateur`) VALUES
(1, 'Météo', 'Les prévisions d\'aujourd\'hui sont qu\'après la pluie il y aura du beau temps', '2023-03-23', 2),
(2, 'Contrôle', 'C\'est toujours trop dur', '2023-03-22', 1),
(3, 'Diner', 'Ce soir, pizza, tacos et kebab au dessert', NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `like_dislikearticles`
--

DROP TABLE IF EXISTS `like_dislikearticles`;
CREATE TABLE IF NOT EXISTS `like_dislikearticles` (
  `Id_Like_DislikeArticles` int NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) DEFAULT NULL,
  `Id_Utilisateur` int NOT NULL,
  `Id_Articles` int NOT NULL,
  PRIMARY KEY (`Id_Like_DislikeArticles`),
  KEY `Id_Utilisateur` (`Id_Utilisateur`),
  KEY `Id_Articles` (`Id_Articles`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `like_dislikearticles`
--

INSERT INTO `like_dislikearticles` (`Id_Like_DislikeArticles`, `type`, `Id_Utilisateur`, `Id_Articles`) VALUES
(18, 1, 2, 3),
(17, 2, 2, 3),
(16, 1, 2, 1),
(15, 2, 2, 1),
(14, 1, 2, 1),
(13, 2, 2, 1),
(12, 1, 2, 1),
(11, 1, 2, 1),
(10, 2, 2, 1),
(19, 2, 2, 3),
(20, 1, 2, 3);

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

DROP TABLE IF EXISTS `role`;
CREATE TABLE IF NOT EXISTS `role` (
  `Id_Role` int NOT NULL AUTO_INCREMENT,
  `Libellé` text,
  PRIMARY KEY (`Id_Role`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`Id_Role`, `Libellé`) VALUES
(1, 'Moderator'),
(2, 'Publisher');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `Id_Utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) DEFAULT NULL,
  `mdp` varchar(50) DEFAULT NULL,
  `Id_Role` int NOT NULL,
  PRIMARY KEY (`Id_Utilisateur`),
  KEY `Id_Role` (`Id_Role`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`Id_Utilisateur`, `nom`, `mdp`, `Id_Role`) VALUES
(1, 'Mr.Exemple', 'mdp', 1),
(2, 'Mr.Exemple Junior', 'mdpJunior', 2);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
