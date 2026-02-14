# Controle de Atividades - Sistema de Extensao Academica

<p align="center">
  <img src="img/Redame.png" alt="Logo Extensao Academica" width="200" heigth="200">
</p>

<p align="center">
  Sistema web para gestao e controle de atividades complementares academicas, permitindo o gerenciamento de horas extracurriculares de alunos, professores e administradores.
</p>

<p align="center">
  <a href="#-comecando">Comecando</a> •
  <a href="#-pre-requisitos">Pre-requisitos</a> •
  <a href="#-instalacao">Instalacao</a> •
  <a href="#-funcionalidades">Funcionalidades</a> •
  <a href="#%EF%B8%8F-construido-com">Construido com</a> •
  <a href="#%EF%B8%8F-autores">Autores</a>
</p>

---


### Pre-requisitos

De que coisas voce precisa para instalar o software:

```
PHP >= 7.4
MySQL >= 5.7
Servidor Web (Apache ou Nginx)
XAMPP, WAMP ou LAMP (recomendado para ambiente local)
```

### Instalacao

Uma serie de exemplos passo-a-passo que informam o que voce deve executar para ter um ambiente de desenvolvimento em execucao.

**1. Clone o repositorio:**

```bash
git clone https://github.com/RichardsLucas/controle-atividades.git
```

**2. Copie os arquivos para o diretorio do servidor web:**

```bash
cp -r controle-atividades /var/www/html/
# Ou no XAMPP: copie para a pasta htdocs
```

**3. Crie o banco de dados executando o script SQL:**

```sql
-- Importe o arquivo sql/tabela.sql no seu MySQL
mysql -u root -p < sql/tabela.sql
```

**4. Configure a conexao com o banco de dados:**

Edite o arquivo `config.php` com as credenciais do seu banco:

```php
$host = 'localhost';
$dbname = 'extensao_academica';
$username = 'seu_usuario';
$password = 'sua_senha';
```

**5. Popule o banco com usuarios de teste (opcional):**

Acesse no navegador:

```
http://localhost/controle-atividades/seed_usuarios.php
```

**6. Acesse o sistema:**

```
http://localhost/controle-atividades/login.php
```

**Usuarios de teste:**

| Tipo       | Email               | Senha     |
|------------|---------------------|-----------|
| Admin      | admin@gmail.com     | admin123  |
| Professor  | professor@gmail.com | prof123   |
| Aluno      | aluno@gmail.com     | aluno123  |

## Funcionalidades

O sistema possui tres perfis de usuario, cada um com funcionalidades especificas:

### Administrador
- Dashboard com estatisticas gerais (alunos, professores, atividades, cursos)
- Criacao e gerenciamento de usuarios (alunos, professores, admins)
- Criacao, edicao e exclusao de cursos
- Definicao de carga horaria necessaria por curso

### Professor
- Dashboard com estatisticas das suas atividades
- Criacao de atividades de extensao (Evento, Curso, Projeto)
- Gerenciamento de inscricoes de alunos
- Validacao e atribuicao de horas para alunos participantes
- Edicao e exclusao de atividades criadas

### Aluno
- Dashboard com progresso pessoal (horas completadas e barra de progresso)
- Visualizacao de atividades disponiveis com filtros (pesquisa, tipo, curso)
- Inscricao em atividades de extensao
- Historico de participacoes
- Geracao de certificado em PDF ao completar as horas necessarias

## Estrutura do Projeto

```
controle-atividades/
├── config.php                 # Configuracao do banco e funcoes de autenticacao
├── index.php                  # Redirecionamento baseado no tipo de usuario
├── login.php                  # Tela de login
├── logout.php                 # Encerramento de sessao
├── registrar.php              # Registro de novos alunos
├── dashboard_admin.php        # Painel do administrador
├── dashboard_professor.php    # Painel do professor
├── dashboard_aluno.php        # Painel do aluno
├── criar.php                  # Criacao de atividades
├── inscrever.php              # Inscricao em atividades
├── gerenciar_horas.php        # Validacao de horas (professor)
├── certificado.php            # Visualizacao do certificado
├── certificado-pdf.php        # Geracao do certificado em PDF
├── fpdf.php                   # Biblioteca FPDF para geracao de PDFs
├── seed_usuarios.php          # Script para popular o banco com dados de teste
├── css/
│   └── style.css              # Estilos do sistema
├── js/
│   └── scripts.js             # Scripts JavaScript
├── img/
│   ├── logo.png               # Logo do sistema
│   ├── icone.png              # Icone da navbar
│   ├── fundo.png              # Imagem de fundo
│   └── fundo-certificado.png  # Fundo do certificado PDF
├── fpdf/                      # Biblioteca FPDF completa
├── sql/
│   └── tabela.sql             # Script de criacao do banco de dados
└── README.md                  # Este arquivo
```

## Banco de Dados

O sistema utiliza MySQL com as seguintes tabelas:

- **cursos** - Cursos disponiveis com carga horaria necessaria
- **usuarios** - Alunos, professores e administradores
- **atividades** - Atividades de extensao (Evento, Curso, Projeto)
- **participacoes** - Inscricoes e historico de participacao dos alunos



## Construido com

* [PHP](https://www.php.net/) - Linguagem de programacao backend
* [MySQL](https://www.mysql.com/) - Banco de dados relacional
* [FPDF](http://www.fpdf.org/) - Biblioteca para geracao de PDFs
* [HTML5/CSS3](https://developer.mozilla.org/pt-BR/) - Estrutura e estilizacao do frontend
* [JavaScript](https://developer.mozilla.org/pt-BR/docs/Web/JavaScript) - Interatividade no frontend

## Versao

Versao atual: **1.0.0**

## Autores

* **Desenvolvedor** - *Desenvolvimento completo* - (https://github.com/RichardsLucas)
