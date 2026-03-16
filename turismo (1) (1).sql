-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Tempo de geração: 15-Mar-2026 às 14:25
-- Versão do servidor: 11.4.9-MariaDB
-- versão do PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dados: `turismo`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `Nome` varchar(100) NOT NULL,
  `Cor` varchar(7) DEFAULT NULL,
  `Letra` char(2) DEFAULT NULL,
  PRIMARY KEY (`Nome`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `categorias`
--

INSERT INTO `categorias` (`Nome`, `Cor`, `Letra`) VALUES
('Andreia', '#d62eb7', 'AD');

-- --------------------------------------------------------

--
-- Estrutura da tabela `fotos`
--

DROP TABLE IF EXISTS `fotos`;
CREATE TABLE IF NOT EXISTS `fotos` (
  `FotoID` int(11) NOT NULL AUTO_INCREMENT,
  `Arquivos` varchar(255) NOT NULL,
  `Descricao` text DEFAULT NULL,
  `Local_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`FotoID`),
  KEY `fk_foto_local` (`Local_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `fotos`
--

INSERT INTO `fotos` (`FotoID`, `Arquivos`, `Descricao`, `Local_id`) VALUES
(2, 'https://fleetmagazine.pt/wp-content/uploads/2025/01/DS-n8-2025-04.jpg', NULL, 5);

-- --------------------------------------------------------

--
-- Estrutura da tabela `locais`
--

DROP TABLE IF EXISTS `locais`;
CREATE TABLE IF NOT EXISTS `locais` (
  `LocalID` int(11) NOT NULL AUTO_INCREMENT,
  `Nome` varchar(250) NOT NULL,
  `Categoria_Nome` varchar(100) DEFAULT NULL,
  `Criado_por` int(11) DEFAULT NULL,
  `Pais` varchar(100) DEFAULT 'Portugal',
  `Cidade` varchar(100) DEFAULT NULL,
  `Morada` text DEFAULT NULL,
  `Codigo_Postal` varchar(20) DEFAULT NULL,
  `Telefone` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Website` varchar(255) DEFAULT NULL,
  `Descricao` text DEFAULT NULL,
  `Latitude` decimal(10,8) NOT NULL,
  `Longitude` decimal(11,8) NOT NULL,
  `Data_Criacao` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`LocalID`),
  KEY `fk_local_categoria` (`Categoria_Nome`),
  KEY `fk_local_usuario` (`Criado_por`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `locais`
--

INSERT INTO `locais` (`LocalID`, `Nome`, `Categoria_Nome`, `Criado_por`, `Pais`, `Cidade`, `Morada`, `Codigo_Postal`, `Telefone`, `Email`, `Website`, `Descricao`, `Latitude`, `Longitude`, `Data_Criacao`) VALUES
(5, 'Andreia', 'Andreia', 1, 'PT', 'Sesimbra', '', NULL, '', '', '', '', 37.90467200, -8.75610400, '2026-03-08 10:49:53');

-- --------------------------------------------------------

--
-- Estrutura da tabela `rotas`
--

DROP TABLE IF EXISTS `rotas`;
CREATE TABLE IF NOT EXISTS `rotas` (
  `RotaID` int(11) NOT NULL AUTO_INCREMENT,
  `Nome` varchar(250) NOT NULL,
  `Descricao` text DEFAULT NULL,
  `Usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`RotaID`),
  KEY `fk_rota_usuario` (`Usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `rotas_locais`
--

DROP TABLE IF EXISTS `rotas_locais`;
CREATE TABLE IF NOT EXISTS `rotas_locais` (
  `RotaID` int(11) NOT NULL,
  `LocalID` int(11) NOT NULL,
  `Ordem` int(11) NOT NULL,
  PRIMARY KEY (`RotaID`,`LocalID`),
  KEY `fk_rl_local` (`LocalID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `UsuarioID` int(11) NOT NULL AUTO_INCREMENT,
  `Nome` varchar(250) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Tipo` enum('Admin','Normal') DEFAULT 'Normal',
  `Sexo` enum('M','F','T','Q','V','s') NOT NULL,
  `Data_Nascimento` date DEFAULT NULL,
  `Foto` varchar(255) DEFAULT NULL,
  `Criado_em` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`UsuarioID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`UsuarioID`, `Nome`, `Email`, `Password`, `Tipo`, `Sexo`, `Data_Nascimento`, `Foto`, `Criado_em`) VALUES
(1, 'JORGE#1', 'jorge.trinidade21@gmail.com', '$2y$10$C5Qb1lnXUl.njEf6cv.JWeTD.NOSjS6CYF.eZvD1umOT/ipTJdcRa', 'Normal', 'M', NULL, NULL, '2026-03-05 08:57:07');

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `fotos`
--
ALTER TABLE `fotos`
  ADD CONSTRAINT `fk_foto_local` FOREIGN KEY (`Local_id`) REFERENCES `locais` (`LocalID`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `locais`
--
ALTER TABLE `locais`
  ADD CONSTRAINT `fk_local_categoria_nome` FOREIGN KEY (`Categoria_Nome`) REFERENCES `categorias` (`Nome`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_local_usuario` FOREIGN KEY (`Criado_por`) REFERENCES `usuarios` (`UsuarioID`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `rotas`
--
ALTER TABLE `rotas`
  ADD CONSTRAINT `fk_rota_usuario` FOREIGN KEY (`Usuario_id`) REFERENCES `usuarios` (`UsuarioID`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `rotas_locais`
--
ALTER TABLE `rotas_locais`
  ADD CONSTRAINT `fk_rl_local` FOREIGN KEY (`LocalID`) REFERENCES `locais` (`LocalID`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rl_rota` FOREIGN KEY (`RotaID`) REFERENCES `rotas` (`RotaID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
