CREATE TABLE `socios` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `nome_completo` varchar(255),
  `telefone` varchar(255),
  `cpf` varchar(255) UNIQUE,
  `foto` blob,
  `identidade` varchar(255),
  `endereco` text,
  `data_nascimento` date,
  `data_entrada` date,
  `categoria_id` integer,
  `status` ENUM ('Ativo', 'Inativo') DEFAULT 'Ativo',
  `dancarino` boolean DEFAULT false,
  `paga_instrutor` boolean DEFAULT false
);

CREATE TABLE `dependentes` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `socio_titular_id` integer NOT NULL,
  `nome_completo` varchar(255),
  `telefone` varchar(255),
  `cpf` varchar(255),
  `foto` blob,
  `identidade` varchar(255),
  `endereco` text,
  `data_nascimento` date,
  `data_entrada` date,
  `categoria_id` integer,
  `dancarino` boolean DEFAULT false,
  `paga_instrutor` boolean DEFAULT false
);

CREATE TABLE `categorias` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `nome` varchar(255) COMMENT 'Ex: Mirim, Juvenil, Adulta',
  `valor_sociedade` decimal,
  `valor_instrutor` decimal
);

CREATE TABLE `mensalidades` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `socio_id` integer NOT NULL,
  `dependente_id` integer,
  `mes` integer,
  `ano` integer,
  `valor` decimal,
  `status` ENUM ('Pendente', 'Pago', 'Atrasado') DEFAULT 'Pendente',
  `data_vencimento` date
);

CREATE TABLE `pagamentos` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `mensalidade_id` integer,
  `data_pagamento` date,
  `forma_pagamento` varchar(255),
  `valor_pago` decimal,
  `multa_juros_aplicados` decimal
);

CREATE TABLE `cartao_tradicionalista` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `socio_id` integer,
  `dependente_id` integer,
  `data_solicitacao` date,
  `pago` boolean,
  `valor` decimal
);

ALTER TABLE `socios` ADD FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

ALTER TABLE `dependentes` ADD FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

ALTER TABLE `dependentes` ADD FOREIGN KEY (`socio_titular_id`) REFERENCES `socios` (`id`) ON DELETE CASCADE;

ALTER TABLE `mensalidades` ADD FOREIGN KEY (`socio_id`) REFERENCES `socios` (`id`);

ALTER TABLE `mensalidades` ADD FOREIGN KEY (`dependente_id`) REFERENCES `dependentes` (`id`);

ALTER TABLE `pagamentos` ADD FOREIGN KEY (`mensalidade_id`) REFERENCES `mensalidades` (`id`);

ALTER TABLE `cartao_tradicionalista` ADD FOREIGN KEY (`socio_id`) REFERENCES `socios` (`id`);

ALTER TABLE `cartao_tradicionalista` ADD FOREIGN KEY (`dependente_id`) REFERENCES `dependentes` (`id`);
