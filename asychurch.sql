-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 03/12/2025 às 12:47
-- Versão do servidor: 8.0.44-0ubuntu0.24.04.1
-- Versão do PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `asychurch`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `asy_cursos`
--

CREATE TABLE `asy_cursos` (
  `idCurso` int NOT NULL,
  `nomeCurso` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `descricaoCurso` text COLLATE utf8mb4_general_ci,
  `dataCriacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `statusCurso` enum('ativo','finalizado','inativo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ativo',
  `idMinisterio` int DEFAULT NULL,
  `imagemCurso` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `asy_cursos`
--

INSERT INTO `asy_cursos` (`idCurso`, `nomeCurso`, `descricaoCurso`, `dataCriacao`, `statusCurso`, `idMinisterio`, `imagemCurso`) VALUES
(9, 'Graça', 'Estudo sobre a Graça', '2025-11-11 17:26:56', 'ativo', 8, 'http://srv880732.hstgr.cloud/asy/assets/img/cursos/9/CARD/Graça.webp'),
(10, 'Exodo', 'Estudo sobre Exodo', '2025-11-18 12:06:54', 'ativo', 1, 'http://srv880732.hstgr.cloud/asy/assets/img/cursos/10/CARD/Exodo.webp'),
(11, 'Levitas', 'Estudo para Levitas', '2025-11-18 12:08:34', 'ativo', 1, 'http://srv880732.hstgr.cloud/asy/assets/img/cursos/11/CARD/Levitas.jpeg');

-- --------------------------------------------------------

--
-- Estrutura para tabela `asy_eventos`
--

CREATE TABLE `asy_eventos` (
  `idEvento` int NOT NULL,
  `nomeEvento` varchar(255) NOT NULL,
  `tipoEvento` enum('festa','culto','estudo','célula') NOT NULL,
  `dataHoraInicio` datetime NOT NULL,
  `dataHoraFim` datetime NOT NULL,
  `idMinisterio` int DEFAULT NULL,
  `observacao` text,
  `criadoPor` int NOT NULL,
  `idResponsavel` int DEFAULT NULL,
  `dataCriacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statusEvento` enum('ativo','cancelado','finalizado','em andamento') DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `asy_eventos`
--

INSERT INTO `asy_eventos` (`idEvento`, `nomeEvento`, `tipoEvento`, `dataHoraInicio`, `dataHoraFim`, `idMinisterio`, `observacao`, `criadoPor`, `idResponsavel`, `dataCriacao`, `statusEvento`) VALUES
(11, 'Natal', 'festa', '2025-11-27 16:26:18', '2025-11-27 15:26:20', 8, 'asfasdfasfasf', 4, 4, '2025-11-27 16:26:55', 'ativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `asy_ministerios`
--

CREATE TABLE `asy_ministerios` (
  `idMinisterio` int NOT NULL,
  `descricaoMinisterio` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `asy_ministerios`
--

INSERT INTO `asy_ministerios` (`idMinisterio`, `descricaoMinisterio`) VALUES
(1, 'Levita'),
(2, 'Pastor(a)'),
(3, 'Intercessão'),
(4, 'Infantil'),
(5, 'Mídia / Comunicação'),
(6, 'Técnica'),
(7, 'Missões'),
(8, 'Membro'),
(9, 'Tesouraria / Finanças');

-- --------------------------------------------------------

--
-- Estrutura para tabela `asy_modulos`
--

CREATE TABLE `asy_modulos` (
  `idModulo` int NOT NULL,
  `idCurso` int NOT NULL,
  `nomeModulo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `ordemModulo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `asy_modulos`
--

INSERT INTO `asy_modulos` (`idModulo`, `idCurso`, `nomeModulo`, `ordemModulo`) VALUES
(1, 9, 'Introdução', 1),
(6, 10, 'Introdução', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `asy_PermissaoUsuario`
--

CREATE TABLE `asy_PermissaoUsuario` (
  `idPermissaoUsuario` int NOT NULL,
  `descricaoPermissaoUsuario` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `asy_PermissaoUsuario`
--

INSERT INTO `asy_PermissaoUsuario` (`idPermissaoUsuario`, `descricaoPermissaoUsuario`) VALUES
(1, 'Administrador'),
(2, 'Líder'),
(3, 'Aluno'),
(4, 'Padrão'),
(5, 'Suporte Técnico / Dev');

-- --------------------------------------------------------

--
-- Estrutura para tabela `asy_progresso_videos`
--

CREATE TABLE `asy_progresso_videos` (
  `idProgresso` int NOT NULL,
  `idUsuario` int NOT NULL,
  `idVideo` int NOT NULL,
  `tempoAssistido` int DEFAULT '0',
  `concluido` tinyint(1) DEFAULT '0',
  `dataConclusao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `asy_progresso_videos`
--

INSERT INTO `asy_progresso_videos` (`idProgresso`, `idUsuario`, `idVideo`, `tempoAssistido`, `concluido`, `dataConclusao`) VALUES
(181, 4, 1, 47, 1, '2025-11-20 13:22:49'),
(197, 7, 1, 4, 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `asy_questionarios`
--

CREATE TABLE `asy_questionarios` (
  `idQuest` int NOT NULL,
  `idVideo` int NOT NULL,
  `pergunta` text COLLATE utf8mb4_general_ci NOT NULL,
  `respostaCorreta` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `asy_usuarios`
--

CREATE TABLE `asy_usuarios` (
  `idUsuario` int NOT NULL,
  `nomeCompletoUsuario` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `dataNascimentoUsuario` date DEFAULT NULL,
  `emailUsuario` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `senhaUsuario` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `forcarTrocaSenha` tinyint(1) NOT NULL DEFAULT '0',
  `telefoneUsuario` varchar(11) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cepUsuario` varchar(8) COLLATE utf8mb4_general_ci NOT NULL,
  `enderecoUsuario` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `numeroEnderecoUsuario` int NOT NULL,
  `complementoEnderecoUsuario` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bairroEnderecoUsuario` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `cidadeUsuario` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `estadoEnderecoUsuario` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `idMinisterioUsuario` int DEFAULT '8',
  `tokenUsuario` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `horarioLogonUsuario` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `idPermissaoUsuario` int DEFAULT '4',
  `statusUsuario` enum('ativo','inativo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'ativo',
  `foto_perfil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dataCriacaoUsuario` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `asy_usuarios`
--

INSERT INTO `asy_usuarios` (`idUsuario`, `nomeCompletoUsuario`, `dataNascimentoUsuario`, `emailUsuario`, `senhaUsuario`, `forcarTrocaSenha`, `telefoneUsuario`, `cepUsuario`, `enderecoUsuario`, `numeroEnderecoUsuario`, `complementoEnderecoUsuario`, `bairroEnderecoUsuario`, `cidadeUsuario`, `estadoEnderecoUsuario`, `idMinisterioUsuario`, `tokenUsuario`, `horarioLogonUsuario`, `idPermissaoUsuario`, `statusUsuario`, `foto_perfil`, `dataCriacaoUsuario`) VALUES
(4, 'Mathews Barreto', '1997-12-25', 'mathews.sbarreto@gmail.com', '$2y$10$s5VCo9UzFoPGhdp2Fz6/bevJ6BbY6xNCnPwwtLuZJTeCggsXxltwm', 0, '11948666995', '07193020', 'Rua Fonte Boa', 320, '', 'Vila Barros', 'Guarulhos', 'SP', 3, NULL, '2025-12-03 11:48:23', 1, 'ativo', 'app/controllers/usuario/4/foto-perfil/4_user_icon.jpeg', '2025-11-11 11:14:13'),
(5, 'Beatriz Felix', '1999-01-06', 'beatriz@gmail.com', '$2y$10$dKd/foAxMj36U2A1rGyPaOwKLjTGxh97aNoakQpLn8zfSUgaQO8ky', 0, '11974175848', '09780900', 'Rua Tiradentes', 1837, 'Bloco 2 - APTO 1', 'Santa Terezinha', 'São Bernardo do Campo', 'SP', 8, NULL, '2025-10-22 05:24:29', 4, 'ativo', NULL, '2025-11-11 11:14:13'),
(6, 'Samuel Moura', '1976-03-30', 'samuelbsmoura@gmail.com', '$2y$10$U.xIOIT7JUpzCW0qz/dqfecHU8a6JfXgtUxa9C7Plp1ZRGXaLv3FO', 0, '11984203955', '07193000', 'Rua Eugênio Diamante', 471, '', 'Vila Barros', 'Guarulhos', 'SP', 2, NULL, '2025-11-19 03:54:14', 1, 'ativo', NULL, '2025-11-11 11:14:13'),
(7, 'Andressa Victoria', '2008-02-09', 'andressavictoria.sdo@gmail.com', '$2y$10$CeqUIC8vPTiRBGBd8OrCa.Xj6KuaM0ZmqjOL34p2bKJRWnCe6br5O', 0, '11981481570', '07133390', 'Rua Ana Alves dos Santos', 33, '', 'Jardim Almeida Prado', 'Guarulhos', 'SP', 1, NULL, '2025-11-27 14:49:59', 1, 'ativo', 'app/controllers/usuario/7/foto-perfil/7_user_icon.png', '2025-11-11 11:14:13'),
(8, 'Sueli Lucia da Silva', '1997-12-25', 'sueli@teste.com', '$2y$10$BcC20dXp.e0NS3Vy.V6JN.7veha6F9RIRguksVqR0Ihz4//NmkqMW', 1, '11948666990', '07193020', 'Rua Fonte Boa', 320, 'Casa', 'Vila Barros', 'Guarulhos', 'SP', 8, NULL, '2025-11-26 20:25:54', 4, 'ativo', NULL, '2025-11-11 12:45:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `asy_usuario_curso`
--

CREATE TABLE `asy_usuario_curso` (
  `id` int NOT NULL,
  `idUsuario` int NOT NULL,
  `idCurso` int NOT NULL,
  `dataInicio` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `asy_usuario_curso`
--

INSERT INTO `asy_usuario_curso` (`id`, `idUsuario`, `idCurso`, `dataInicio`) VALUES
(4, 7, 10, '2025-11-18 12:07:10'),
(5, 7, 11, '2025-11-18 12:09:06'),
(6, 4, 11, '2025-11-18 13:10:21'),
(7, 4, 10, '2025-11-18 13:10:29'),
(37, 5, 9, '2025-11-18 21:38:35'),
(38, 8, 9, '2025-11-18 21:38:35'),
(39, 4, 9, '2025-11-18 21:47:25'),
(40, 6, 9, '2025-11-18 21:54:36'),
(41, 7, 9, '2025-11-19 15:51:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `asy_videos`
--

CREATE TABLE `asy_videos` (
  `idVideo` int NOT NULL,
  `idModulo` int NOT NULL,
  `tituloVideo` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `urlVideo` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `duracaoSegundos` int NOT NULL,
  `ordemVideo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `asy_videos`
--

INSERT INTO `asy_videos` (`idVideo`, `idModulo`, `tituloVideo`, `urlVideo`, `duracaoSegundos`, `ordemVideo`) VALUES
(1, 1, 'O que é a graça?', 'assets/videos/Graça/Módulo 1/Aula 1.mp4', 51, 1),
(2, 1, 'Objetivo da graça', 'assets/videos/Graça/Módulo 1/Aula 2.mp4', 70, 2);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `asy_cursos`
--
ALTER TABLE `asy_cursos`
  ADD PRIMARY KEY (`idCurso`),
  ADD KEY `fk_cursos_ministerio` (`idMinisterio`);

--
-- Índices de tabela `asy_eventos`
--
ALTER TABLE `asy_eventos`
  ADD PRIMARY KEY (`idEvento`),
  ADD KEY `idMinisterio` (`idMinisterio`),
  ADD KEY `criadoPor` (`criadoPor`),
  ADD KEY `idResponsavel` (`idResponsavel`);

--
-- Índices de tabela `asy_ministerios`
--
ALTER TABLE `asy_ministerios`
  ADD PRIMARY KEY (`idMinisterio`);

--
-- Índices de tabela `asy_modulos`
--
ALTER TABLE `asy_modulos`
  ADD PRIMARY KEY (`idModulo`),
  ADD KEY `idCurso` (`idCurso`);

--
-- Índices de tabela `asy_PermissaoUsuario`
--
ALTER TABLE `asy_PermissaoUsuario`
  ADD PRIMARY KEY (`idPermissaoUsuario`);

--
-- Índices de tabela `asy_progresso_videos`
--
ALTER TABLE `asy_progresso_videos`
  ADD PRIMARY KEY (`idProgresso`),
  ADD UNIQUE KEY `unique_user_video` (`idUsuario`,`idVideo`),
  ADD KEY `idVideo` (`idVideo`);

--
-- Índices de tabela `asy_questionarios`
--
ALTER TABLE `asy_questionarios`
  ADD PRIMARY KEY (`idQuest`),
  ADD KEY `idVideo` (`idVideo`);

--
-- Índices de tabela `asy_usuarios`
--
ALTER TABLE `asy_usuarios`
  ADD PRIMARY KEY (`idUsuario`),
  ADD KEY `fk_usuarios_ministerios` (`idMinisterioUsuario`),
  ADD KEY `fk_usuario_permissao` (`idPermissaoUsuario`);

--
-- Índices de tabela `asy_usuario_curso`
--
ALTER TABLE `asy_usuario_curso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idCurso` (`idCurso`),
  ADD KEY `fk_usuario_curso_usuario` (`idUsuario`);

--
-- Índices de tabela `asy_videos`
--
ALTER TABLE `asy_videos`
  ADD PRIMARY KEY (`idVideo`),
  ADD KEY `idModulo` (`idModulo`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `asy_cursos`
--
ALTER TABLE `asy_cursos`
  MODIFY `idCurso` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `asy_eventos`
--
ALTER TABLE `asy_eventos`
  MODIFY `idEvento` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `asy_ministerios`
--
ALTER TABLE `asy_ministerios`
  MODIFY `idMinisterio` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `asy_modulos`
--
ALTER TABLE `asy_modulos`
  MODIFY `idModulo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `asy_PermissaoUsuario`
--
ALTER TABLE `asy_PermissaoUsuario`
  MODIFY `idPermissaoUsuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `asy_progresso_videos`
--
ALTER TABLE `asy_progresso_videos`
  MODIFY `idProgresso` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT de tabela `asy_questionarios`
--
ALTER TABLE `asy_questionarios`
  MODIFY `idQuest` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `asy_usuarios`
--
ALTER TABLE `asy_usuarios`
  MODIFY `idUsuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `asy_usuario_curso`
--
ALTER TABLE `asy_usuario_curso`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de tabela `asy_videos`
--
ALTER TABLE `asy_videos`
  MODIFY `idVideo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `asy_cursos`
--
ALTER TABLE `asy_cursos`
  ADD CONSTRAINT `fk_cursos_ministerio` FOREIGN KEY (`idMinisterio`) REFERENCES `asy_ministerios` (`idMinisterio`);

--
-- Restrições para tabelas `asy_eventos`
--
ALTER TABLE `asy_eventos`
  ADD CONSTRAINT `asy_eventos_ibfk_1` FOREIGN KEY (`idMinisterio`) REFERENCES `asy_ministerios` (`idMinisterio`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `asy_eventos_ibfk_2` FOREIGN KEY (`criadoPor`) REFERENCES `asy_usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asy_eventos_ibfk_3` FOREIGN KEY (`idResponsavel`) REFERENCES `asy_usuarios` (`idUsuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `asy_modulos`
--
ALTER TABLE `asy_modulos`
  ADD CONSTRAINT `asy_modulos_ibfk_1` FOREIGN KEY (`idCurso`) REFERENCES `asy_cursos` (`idCurso`) ON DELETE CASCADE;

--
-- Restrições para tabelas `asy_progresso_videos`
--
ALTER TABLE `asy_progresso_videos`
  ADD CONSTRAINT `asy_progresso_videos_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `asy_usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `asy_progresso_videos_ibfk_2` FOREIGN KEY (`idVideo`) REFERENCES `asy_videos` (`idVideo`) ON DELETE CASCADE;

--
-- Restrições para tabelas `asy_questionarios`
--
ALTER TABLE `asy_questionarios`
  ADD CONSTRAINT `asy_questionarios_ibfk_1` FOREIGN KEY (`idVideo`) REFERENCES `asy_videos` (`idVideo`) ON DELETE CASCADE;

--
-- Restrições para tabelas `asy_usuarios`
--
ALTER TABLE `asy_usuarios`
  ADD CONSTRAINT `fk_usuario_permissao` FOREIGN KEY (`idPermissaoUsuario`) REFERENCES `asy_PermissaoUsuario` (`idPermissaoUsuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_ministerios` FOREIGN KEY (`idMinisterioUsuario`) REFERENCES `asy_ministerios` (`idMinisterio`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `asy_usuario_curso`
--
ALTER TABLE `asy_usuario_curso`
  ADD CONSTRAINT `asy_usuario_curso_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `asy_usuarios` (`idUsuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `asy_usuario_curso_ibfk_2` FOREIGN KEY (`idCurso`) REFERENCES `asy_cursos` (`idCurso`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuario_curso_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `asy_usuarios` (`idUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `asy_videos`
--
ALTER TABLE `asy_videos`
  ADD CONSTRAINT `asy_videos_ibfk_1` FOREIGN KEY (`idModulo`) REFERENCES `asy_modulos` (`idModulo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
