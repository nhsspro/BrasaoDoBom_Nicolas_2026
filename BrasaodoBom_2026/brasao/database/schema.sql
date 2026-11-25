-- Churrascaria Brasão do Bom - esquema do banco de dados
-- Uso: mysql -u root -p < database/schema.sql
CREATE DATABASE IF NOT EXISTS brasao_do_bom
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE brasao_do_bom;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  telefone VARCHAR(11) NOT NULL,
  email VARCHAR(150) NULL,
  data_reserva DATE NOT NULL,
  horario TIME NOT NULL,
  pessoas TINYINT UNSIGNED NOT NULL,
  observacoes VARCHAR(300) NULL,
  status ENUM('pendente','confirmada','cancelada') NOT NULL DEFAULT 'pendente',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_data_horario (data_reserva, horario),
  INDEX idx_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS promocoes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(80) NOT NULL,
  descricao VARCHAR(255) NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS depoimentos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  autor VARCHAR(60) NOT NULL,
  texto VARCHAR(255) NOT NULL,
  aprovado TINYINT(1) NOT NULL DEFAULT 1,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Dados iniciais (conteúdo que antes estava fixo no HTML)
INSERT INTO promocoes (titulo, descricao) VALUES
 ("🎉 Promoção de Aniversário", "Na compra de dois rodízios, ganhe uma sobremesa especial."),
 ("🍻 Happy Hour", "Das 17h às 19h, 20% de desconto em bebidas e petiscos."),
 ("👨‍👩‍👧‍👦 Família Brasão", "Reservas acima de 10 pessoas com 15% de desconto.");

INSERT INTO depoimentos (autor, texto) VALUES
 ("João S.", "A melhor churrascaria que já fui! Atendimento impecável e carne suculenta!"),
 ("Maria P.", "Ambiente incrível e sabor inesquecível. Voltarei com certeza!"),
 ("Carlos T.", "Um verdadeiro espetáculo de churrasco!");

-- Administrador inicial. Senha: Admin@123  (TROQUE antes de publicar)
INSERT INTO usuarios (nome, email, senha_hash) VALUES
 ("Administrador", "admin@brasaodobom.com.br", "$2y$10$.zvpHFdD5pQ9EBWUF2E5yujGZ.75eLDlRDk3mDGJykgwxRPgj3Gei");
