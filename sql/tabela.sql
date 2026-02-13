-- Atualizando schema com data de término e melhorias
CREATE DATABASE IF NOT EXISTS extensao_academica;
USE extensao_academica;

-- Tabela de Cursos
CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    carga_horaria_necessaria INT DEFAULT 200,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de Usuários (Alunos, Professores, Admins)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('aluno', 'professor', 'admin') NOT NULL,
    curso_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE SET NULL
);

-- Tabela de Atividades
CREATE TABLE atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    professor_id INT NOT NULL,
    curso_relacionado_id INT,
    tipo ENUM('Evento', 'Curso', 'Projeto') NOT NULL,
    carga_horaria INT NOT NULL,
    data_inicio DATE NOT NULL,
    data_termino DATE,
    status ENUM('pendente', 'aprovado', 'concluido', 'cancelado') DEFAULT 'pendente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (professor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (curso_relacionado_id) REFERENCES cursos(id) ON DELETE CASCADE
);

-- Tabela de Inscrições / Participação
CREATE TABLE participacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    atividade_id INT NOT NULL,
    status ENUM('inscrito', 'concluido', 'reprovado') DEFAULT 'inscrito',
    horas_contabilizadas INT DEFAULT 0,
    data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (atividade_id) REFERENCES atividades(id) ON DELETE CASCADE
);

-- Dados iniciais para teste
INSERT INTO cursos (nome, carga_horaria_necessaria) VALUES 
('Engenharia de Software', 200),
('Direito', 150),
('Administração', 100);
