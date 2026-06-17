-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 03-Jun-2026 às 13:03
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sistema_relatorios`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `tipo` enum('aluno','professor') DEFAULT 'aluno',
  `nome` varchar(255) NOT NULL,
  `nif` varchar(9) NOT NULL,
  `turma` varchar(50) DEFAULT NULL,
  `computador_id` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `alunos`
--

INSERT INTO `alunos` (`id`, `tipo`, `nome`, `nif`, `turma`, `computador_id`, `estado`) VALUES
(2, 'aluno', 'AA', '', '9ºA', 3, 1),
(3, 'aluno', 'fafasf', '', 'safasf', NULL, 1),
(4, 'aluno', 'Dias', '', '42342', NULL, 1),
(5, 'aluno', 'fafaf', '', 'asfafa', NULL, 1),
(6, 'aluno', 'jkghjghjgjghjg', '', 'fjgjfjfjf', NULL, 1),
(7, 'aluno', 'aaaaaa', '', 'aaa', NULL, 1),
(9, 'professor', 'Dias', '232131313', NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `computadores`
--

CREATE TABLE `computadores` (
  `id` int(11) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `aluno_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `computadores`
--

INSERT INTO `computadores` (`id`, `marca`, `serial_number`, `aluno_id`) VALUES
(3, 'HP', '5435345345345345345', NULL),
(4, 'aura', '534543456346346346', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `emprestimos`
--

CREATE TABLE `emprestimos` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `computador_id` int(11) NOT NULL,
  `data_entrega` date NOT NULL,
  `data_devolucao` date DEFAULT NULL,
  `tem_carregador` tinyint(1) DEFAULT 0,
  `observacoes` text DEFAULT NULL,
  `status` enum('ativo','devolvido') DEFAULT 'ativo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `emprestimos_computadores`
--

CREATE TABLE `emprestimos_computadores` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `computador_tag` varchar(50) NOT NULL,
  `data_entrega` datetime DEFAULT current_timestamp(),
  `data_devolucao` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'ATIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `emprestimos_computadores`
--

INSERT INTO `emprestimos_computadores` (`id`, `utente_id`, `computador_tag`, `data_entrega`, `data_devolucao`, `estado`) VALUES
(1, 2, 'HP', '2026-05-28 11:22:42', '2026-05-28 12:22:46', 'DEVOLVIDO'),
(2, 2, 'HP', '2026-05-28 11:24:40', NULL, 'ATIVO');

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos_calendario`
--

CREATE TABLE `eventos_calendario` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_evento` date NOT NULL,
  `utilizador` varchar(50) NOT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `eventos_calendario`
--

INSERT INTO `eventos_calendario` (`id`, `titulo`, `descricao`, `data_evento`, `utilizador`, `data_criacao`) VALUES
(4, 'Montagem computadores + auscultadores', 'http://localhost/sistema-relatorios/exportar_pdf.php', '2026-06-02', 'dias', '2026-05-29 13:25:55'),
(5, 'Montagem de computadores', '', '2026-06-05', 'user', '2026-06-03 10:49:47');

-- --------------------------------------------------------

--
-- Estrutura da tabela `links_uteis`
--

CREATE TABLE `links_uteis` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `links_uteis`
--

INSERT INTO `links_uteis` (`id`, `titulo`, `url`, `descricao`) VALUES
(1, 'Gmail', 'https://mail.google.com/mail/u/0/#inbox', ''),
(3, 'gemini', 'https://gemini.google.com/app/6cb7c7c819e6dd89?hl=pt-PT', ''),
(4, 'Drive', 'https://drive.google.com/drive/shared-with-me', ''),
(5, 'Exames Datas', 'https://terrasdelarus.edu.pt/nesta-pagina-podera-seguir-toda-a-informacao-relativa-as-provas-de-avaliacao-externa-e-provas-equivalencia-a-frequencia-24-25/', '');

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs_atividades`
--

CREATE TABLE `logs_atividades` (
  `id` int(11) NOT NULL,
  `utilizador` varchar(50) NOT NULL,
  `acao` varchar(20) NOT NULL,
  `pagina` varchar(50) NOT NULL,
  `descricao` text NOT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `logs_atividades`
--

INSERT INTO `logs_atividades` (`id`, `utilizador`, `acao`, `pagina`, `descricao`, `data_hora`) VALUES
(1, 'dias', 'REMOVEU', 'gerir_montagem.php', 'Eliminou permanentemente a sala \'45435\' que estava na ordem #1 (Registo ID: #1).', '2026-05-27 15:15:34'),
(2, 'dias', 'AGENDOU', 'calendario.php', 'Agendou: \'Exames\' para o dia 29/05/2026', '2026-05-27 15:50:51'),
(3, 'dias', 'AGENDOU', 'calendario.php', 'Agendou: \'exames\' para o dia 29/05/2026', '2026-05-27 16:04:09'),
(4, 'dias', 'REMOVEU', 'calendario.php', 'Removeu o agendamento: \'Exames\' (ID: #1)', '2026-05-28 08:21:27'),
(5, 'dias', 'REMOVEU', 'calendario.php', 'Removeu o agendamento: \'exames\' (ID: #2)', '2026-05-28 08:23:38'),
(6, 'dias', 'AGENDOU', 'calendario.php', 'Agendou: \'a\' para 28/05/2026', '2026-05-28 08:23:43'),
(7, 'dias', 'EDITOU', 'calendario.php', 'Modificou o evento ID #3 para: \'a\'', '2026-05-28 08:23:55'),
(8, 'dias', 'REMOVEU', 'calendario.php', 'Removeu o agendamento: \'a\' (ID: #3)', '2026-05-28 08:24:00'),
(9, 'dias', 'ALTEROU', 'emprestimos.php', 'Equipamento ID #1 marcado como entregue.', '2026-05-28 10:22:46'),
(10, 'dias', 'EMPRÉSTIMO', 'emprestimos.php', 'Alocou o S/N: 5435345345345345345 ao utente: AA', '2026-05-28 10:26:27'),
(11, 'dias', 'ENTREGUE', 'emprestimos.php', 'O utente AA devolveu o computador HP (S/N: 5435345345345345345). Equipamento guardado no armazém.', '2026-05-28 10:26:33'),
(12, 'dias', 'EMPRÉSTIMO', 'emprestimos.php', 'Alocou o S/N: 5435345345345345345 ao utente: AA', '2026-05-28 10:28:21'),
(13, 'dias', 'EMPRÉSTIMO', 'emprestimos.php', 'Alocou o S/N: 5435345345345345345 ao utente: AA', '2026-05-28 10:30:41'),
(14, 'dias', 'ADICIONOU', 'pc.php', 'Cadastrou novo hardware: aura (S/N: 534543456346346346)', '2026-05-28 14:36:46'),
(15, 'dias', 'AGENDOU', 'calendario.php', 'Agendou: \'Montagem computadores + auscultadores\' para 02/06/2026', '2026-05-29 13:25:55'),
(16, 'dias', 'EDITOU', 'calendario.php', 'Modificou o evento ID #4 para: \'Montagem computadores + auscultadores\'', '2026-05-29 13:26:30'),
(17, 'user', 'AGENDOU', 'calendario.php', 'Agendou: \'Montagem de computadores\' para 05/06/2026', '2026-06-03 10:49:47'),
(18, 'user', 'EDITOU', 'calendario.php', 'Modificou o evento ID #5 para: \'Montagem de computadores\'', '2026-06-03 10:49:57');

-- --------------------------------------------------------

--
-- Estrutura da tabela `ordem_montagem`
--

CREATE TABLE `ordem_montagem` (
  `id` int(11) NOT NULL,
  `ordem_montagem` int(11) NOT NULL,
  `salas` varchar(50) NOT NULL,
  `num_pc` int(11) NOT NULL,
  `observacoes` text DEFAULT NULL,
  `extensoes` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `ordem_montagem`
--

INSERT INTO `ordem_montagem` (`id`, `ordem_montagem`, `salas`, `num_pc`, `observacoes`, `extensoes`) VALUES
(2, 1, '26', 26, 'NOVOS', '2 PRETAS 1 BRANCA'),
(3, 5, '27', 18, NULL, '1 PRETA 2 BRANCAS'),
(4, 12, '28', 1, NULL, NULL),
(5, 3, '29', 21, NULL, '1 PRETA 2 BRANCAS'),
(6, 4, '30', 20, NULL, '1 PRETA 2 BRANCAS'),
(7, 6, '31', 13, '12 +1 NOVOS', '1 PRETA 1 BRANCA'),
(8, 9, '32', 1, NULL, NULL),
(9, 10, '33', 6, NULL, NULL),
(10, 7, '34', 7, NULL, '1 PRETAS 2 BRANCAS'),
(11, 8, '36', 1, 'SURDO', NULL),
(12, 2, '37', 22, 'NOVOS', '1 PRETA 2 BRANCAS'),
(13, 11, '39', 6, '5+1', '1 BRANCA');

-- --------------------------------------------------------

--
-- Estrutura da tabela `projetores`
--

CREATE TABLE `projetores` (
  `id` int(11) NOT NULL,
  `bloco` varchar(10) NOT NULL,
  `sala` varchar(50) NOT NULL,
  `equipamento` varchar(100) NOT NULL,
  `observacoes` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `projetores`
--

INSERT INTO `projetores` (`id`, `bloco`, `sala`, `equipamento`, `observacoes`) VALUES
(11, 'Bloco A', 'Biblioteca', 'Projetor Antigo', ''),
(12, 'Bloco B', '4', 'LEDTV65', ''),
(13, 'Bloco A', 'LED', 'quadro interativo', ''),
(14, 'Bloco B', '5', 'Projetor Novo', ''),
(15, 'Bloco B', '6', 'Projetor Novo', ''),
(16, 'Bloco B', '7', 'Projetor Novo', NULL),
(17, 'Bloco B', '8', 'Projetor Novo', ''),
(18, 'Bloco B', '9', 'Projetor Novo', ''),
(19, 'Bloco B', '10', 'Projetor Novo', ''),
(20, 'Bloco B', '11', 'Projetor Novo', ''),
(21, 'Bloco B', '12', 'Projetor Novo', ''),
(22, 'Bloco B', '13', 'Projetor Sala 36', ''),
(23, 'Bloco B', '14', 'Projetor Novo', ''),
(24, 'Bloco B', 'Sala Clubes', 'Sem Projetor', ''),
(25, 'Bloco C', '15', 'Projetor Novo', ''),
(26, 'Bloco C', '16', 'Projetor Novo', ''),
(27, 'Bloco C', '17', 'Projetor Novo', ''),
(28, 'Bloco C', '18', 'Projetor Novo', NULL),
(29, 'Bloco C', '19', 'Projetor Novo', ''),
(30, 'Bloco C', '20', 'LEDTv55', 'sala pequena'),
(31, 'Bloco C', '21A', 'LEDTv55', 'sala pequena'),
(32, 'Bloco C', '21B', 'LEDTv55', 'LEDTv66'),
(33, 'Bloco C', 'Sala 22', 'Projetor Novo', NULL),
(34, 'Bloco C', 'Sala 23', 'Projetor Novo', NULL),
(35, 'Bloco C', 'Sala 24', 'Projetor Novo', NULL),
(36, 'Bloco C', 'Sala 25', 'Projetor Novo', NULL),
(38, 'Bloco D', 'Sala 27', 'Projetor Novo', NULL),
(39, 'Bloco D', 'Sala 28', 'Projetor Antigo', 'sala pequena'),
(40, 'Bloco D', 'Sala 29', 'Projetor Novo', NULL),
(41, 'Bloco D', 'Sala 30', 'Projetor Novo', NULL),
(42, 'Bloco D', 'Sala 31', 'Projetor Novo', NULL),
(43, 'Bloco D', 'Sala 32', 'Projetor Antigo', 'sala pequena'),
(44, 'Bloco D', 'Sala 33', 'Projetor Novo', NULL),
(45, 'Bloco D', 'Sala 34', 'Projetor Novo', NULL),
(46, 'Bloco D', 'Sala 35', 'Projetor Novo', NULL),
(47, 'Bloco D', 'Sala 36', 'Quadro Interativo', NULL),
(58, 'Bloco D', '26', 'Projetor Novo', ''),
(59, 'Bloco D', 'Sala 37', 'Projetor Novo', NULL),
(60, 'Bloco D', 'Sala 39', 'Projetor Novo', NULL),
(61, 'Bloco D', '35 Sala APS', 'LEDTV55', NULL),
(62, 'Bloco D', 'Sala UES', 'LEDTV55', NULL),
(63, 'Bloco E', 'Sala EF', 'Projetor Antigo', NULL),
(64, 'Bloco E', 'Refeitório (sala reuniões)', 'Sem projetor', NULL),
(65, 'Bloco E', 'Sala Alunos (sala reuniões)', 'LEDTV65', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `relatorios`
--

CREATE TABLE `relatorios` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `autor` varchar(100) NOT NULL,
  `data_envio` date NOT NULL,
  `conteudo` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `semana_ano` varchar(7) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `relatorios`
--

INSERT INTO `relatorios` (`id`, `utilizador_id`, `autor`, `data_envio`, `conteudo`, `foto`, `semana_ano`, `criado_em`) VALUES
(12, 0, 'Dias', '2026-05-27', 'Au', NULL, '2026-W2', '2026-05-27 11:26:44'),
(13, 0, 'Diogo Simões', '2026-05-27', 'xbxzbxc', '', '2026-W2', '2026-05-27 11:42:03'),
(14, 0, 'Diogo', '2026-05-27', 'aaa', '', '2026-W2', '2026-05-27 11:58:31'),
(15, 0, 'dias', '2026-05-27', 'asfasdfasdgasdbasdbasdfbasdfbdfbdfbdfbdfbdfbdbdbadfbsdffbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfbfb', '', '2026-W2', '2026-05-27 14:19:40'),
(16, 0, 'dias', '2026-06-01', 'sadsadas', '12EBgyqP_lCXezPI_5PcpSURmwkyhELmF', '2026-W2', '2026-06-01 11:19:06'),
(17, 0, 'user', '2026-06-03', 'aura', '1Ze5EmLQRZCEZ61TxDiJVZoZ9FULDwjJD', '2026-W2', '2026-06-03 09:46:10'),
(18, 0, 'dias', '2026-06-03', 'gjdfjhedgawgawehrtfjdrtsdrtmkr', '', '2026-W2', '2026-06-03 10:54:53');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `concluida` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `tarefas`
--

INSERT INTO `tarefas` (`id`, `titulo`, `descricao`, `data_criacao`, `concluida`) VALUES
(3, 'sdgsdgsd', 'sdgsdgsdgsdg', '2026-05-26 11:24:18', 1),
(4, 'dsfsfs', 'dvsfsdfsdfsd', '2026-05-26 11:26:26', 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`id`, `username`, `password`, `role`) VALUES
(2, 'Diogo', '$2y$10$yAK1/93gPABkDA7npXClLeX/HpviXVDrtwLg8AIR8bXYH5hbqHt8S', 'user'),
(5, 'dias', 'd36a36c25ffe288126d40000c6c5714e', 'admin'),
(7, 'admin', '81dc9bdb52d04dc20036dbd8313ed055', 'admin'),
(8, 'user', '6dfe08eda761bd321f8a9b239f6f4ec3', 'user'),
(9, 'filipecabral', 'ba8b7ab7451e0b79e6f95d1472da1099', 'admin');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `computadores`
--
ALTER TABLE `computadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`);

--
-- Índices para tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `computador_id` (`computador_id`);

--
-- Índices para tabela `emprestimos_computadores`
--
ALTER TABLE `emprestimos_computadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Índices para tabela `eventos_calendario`
--
ALTER TABLE `eventos_calendario`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `links_uteis`
--
ALTER TABLE `links_uteis`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `logs_atividades`
--
ALTER TABLE `logs_atividades`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `ordem_montagem`
--
ALTER TABLE `ordem_montagem`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `projetores`
--
ALTER TABLE `projetores`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `relatorios`
--
ALTER TABLE `relatorios`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `tarefas`
--
ALTER TABLE `tarefas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `computadores`
--
ALTER TABLE `computadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `emprestimos_computadores`
--
ALTER TABLE `emprestimos_computadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `eventos_calendario`
--
ALTER TABLE `eventos_calendario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `links_uteis`
--
ALTER TABLE `links_uteis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `logs_atividades`
--
ALTER TABLE `logs_atividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `ordem_montagem`
--
ALTER TABLE `ordem_montagem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `projetores`
--
ALTER TABLE `projetores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT de tabela `relatorios`
--
ALTER TABLE `relatorios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `tarefas`
--
ALTER TABLE `tarefas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `emprestimos`
--
ALTER TABLE `emprestimos`
  ADD CONSTRAINT `emprestimos_ibfk_1` FOREIGN KEY (`computador_id`) REFERENCES `computadores` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `emprestimos_computadores`
--
ALTER TABLE `emprestimos_computadores`
  ADD CONSTRAINT `emprestimos_computadores_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
