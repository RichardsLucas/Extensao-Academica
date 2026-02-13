<?php
require_once 'config.php';
checkAuth('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if($acao === 'criar_usuario') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $tipo = $_POST['tipo'] ?? 'aluno';
        $curso_id = $tipo === 'aluno' ? intval($_POST['curso_id'] ?? 0) : null;

        if(!empty($nome) && !empty($email) && !empty($senha)) {
            $senha_hash = hashPassword($senha);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, curso_id) VALUES (?, ?, ?, ?, ?)");
            if($stmt->execute([$nome, $email, $senha_hash, $tipo, $curso_id])) {
                $sucesso = "Usuário criado com sucesso!";
            } else {
                $erro = "Erro ao criar usuário. Email pode estar duplicado.";
            }
        }
    }

    if($acao === 'editar_curso') {
        $curso_id = intval($_POST['curso_id'] ?? 0);
        $nome = trim($_POST['nome_curso'] ?? '');
        $horas = intval($_POST['horas_necessarias'] ?? 0);

        if($curso_id > 0 && !empty($nome) && $horas > 0) {
            $stmt = $pdo->prepare("UPDATE cursos SET nome = ?, carga_horaria_necessaria = ? WHERE id = ?");
            if($stmt->execute([$nome, $horas, $curso_id])) {
                $sucesso = "Curso atualizado com sucesso!";
            } else {
                $erro = "Erro ao atualizar curso.";
            }
        }
    }

    if($acao === 'deletar_curso') {
        $curso_id = intval($_POST['curso_id'] ?? 0);
        if($curso_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = ?");
            if($stmt->execute([$curso_id])) {
                $sucesso = "Curso deletado com sucesso!";
            } else {
                $erro = "Erro ao deletar curso.";
            }
        }
    }

    if($acao === 'criar_curso') {
        $nome = trim($_POST['nome_curso'] ?? '');
        $horas = intval($_POST['horas_necessarias'] ?? 0);

        if(!empty($nome) && $horas > 0) {
            $stmt = $pdo->prepare("INSERT INTO cursos (nome, carga_horaria_necessaria) VALUES (?, ?)");
            $stmt->execute([$nome, $horas]);
            $sucesso = "Curso criado com sucesso!";
        }
    }
}

$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'aluno'");
$total_alunos = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'professor'");
$total_professores = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM atividades");
$total_atividades = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM cursos");
$total_cursos = $stmt->fetch()['total'];

$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Extensão Acadêmica</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand"> 
        <img src="img/icone.png" alt="" class="img">    
        Extensão Acadêmica</a>
        <div class="navbar-menu">
            <span>Painel Administrativo - <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
            <a href="logout.php" class="logout-btn">Sair</a>
        </div>
    </nav>

    <div class="container-main">
        <aside class="sidebar">
            <ul>
                <li class="active"><a href="dashboard_admin.php">Dashboard</a></li>
                <li><a href="#novo-usuario">Novo Usuário</a></li>
                <li><a href="#novo-curso">Novo Curso</a></li>
                <li><a href="#usuarios">Usuários</a></li>
                <li><a href="#cursos">Cursos</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <?php if(isset($sucesso)): ?>
                <div class="alert alert-success"><?php echo $sucesso; ?></div>
            <?php endif; ?>
            
            <?php if(isset($erro)): ?>
                <div class="alert alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>

            <section class="card">
                <div class="card-title"> Estatísticas Gerais</div>
                <div class="grid grid-2">
                    <div class="stats-card">
                        <div class="stats-label">Total de Alunos</div>
                        <div class="stats-number"><?php echo $total_alunos; ?></div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-label">Total de Professores</div>
                        <div class="stats-number"><?php echo $total_professores; ?></div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-label">Total de Atividades</div>
                        <div class="stats-number"><?php echo $total_atividades; ?></div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-label">Total de Cursos</div>
                        <div class="stats-number"><?php echo $total_cursos; ?></div>
                    </div>
                </div>
            </section>

            <section class="card" id="novo-usuario">
                <div class="card-title"> Criar Novo Usuário</div>
                
                <form method="POST">
                    <input type="hidden" name="acao" value="criar_usuario">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome Completo *</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipo">Tipo de Usuário *</label>
                            <select id="tipo" name="tipo" onchange="atualizarCurso()" required>
                                <option value="aluno">Aluno</option>
                                <option value="professor">Professor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <div class="form-group" id="grupo-curso">
                            <label for="curso_id">Curso *</label>
                            <select id="curso_id" name="curso_id">
                                <option value="">Selecione um curso</option>
                                <?php foreach($cursos as $curso): ?>
                                    <option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha *</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Criar Usuário</button>
                </form>
            </section>

            <section class="card" id="novo-curso">
                <div class="card-title"> Criar Novo Curso</div>
                
                <form method="POST">
                    <input type="hidden" name="acao" value="criar_curso">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome_curso">Nome do Curso *</label>
                            <input type="text" id="nome_curso" name="nome_curso" required placeholder="Ex: Engenharia de Software">
                        </div>

                        <div class="form-group">
                            <label for="horas_necessarias">Horas Necessárias de Extensão *</label>
                            <input type="number" id="horas_necessarias" name="horas_necessarias" min="1" required placeholder="Ex: 200">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Criar Curso</button>
                </form>
            </section>

            <section class="card" id="usuarios">
                <div class="card-title"> Últimos Usuários Criados</div>
                
                <?php if(empty($usuarios)): ?>
                    <div class="alert alert-info">Nenhum usuário cadastrado.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Curso</th>
                                <th>Data Criação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($usuarios as $usuario): 
                                $tipo_badge = match($usuario['tipo']) {
                                    'aluno' => 'badge-info',
                                    'professor' => 'badge-success',
                                    'admin' => 'badge-danger'
                                };
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><span class="badge <?php echo $tipo_badge; ?>"><?php echo ucfirst($usuario['tipo']); ?></span></td>
                                    <td>
                                        <?php 
                                        if($usuario['curso_id']) {
                                            $stmt = $pdo->prepare("SELECT nome FROM cursos WHERE id = ?");
                                            $stmt->execute([$usuario['curso_id']]);
                                            $curso = $stmt->fetch();
                                            echo htmlspecialchars($curso['nome'] ?? '—');
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($usuario['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <section class="card" id="cursos">
                <div class="card-title"> Cursos Cadastrados</div>
                
                <?php if(empty($cursos)): ?>
                    <div class="alert alert-info">Nenhum curso cadastrado.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Horas Necessárias</th>
                                <th>Alunos Inscritos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cursos as $curso): 
                                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios WHERE curso_id = ?");
                                $stmt->execute([$curso['id']]);
                                $total_alunos_curso = $stmt->fetch()['total'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($curso['nome']); ?></td>
                                    <td><?php echo $curso['carga_horaria_necessaria']; ?>h</td>
                                    <td><?php echo $total_alunos_curso; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="editarCurso(<?php echo $curso['id']; ?>, '<?php echo htmlspecialchars($curso['nome']); ?>', <?php echo $curso['carga_horaria_necessaria']; ?>)">Editar</button>
                                        <form action="#cursos" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja deletar este curso?');">
                                            <input type="hidden" name="acao" value="deletar_curso">
                                            <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Deletar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <div id="modalEditarCurso" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Curso</h2>
                <button type="button" class="close-modal" onclick="fecharModal()">&times;</button>
            </div>
            <form method="POST" action="#cursos">
                <input type="hidden" name="acao" value="editar_curso">
                <input type="hidden" name="curso_id" id="modal_curso_id">
                
                <div class="form-group">
                    <label for="modal_nome_curso">Nome do Curso *</label>
                    <input type="text" id="modal_nome_curso" name="nome_curso" required>
                </div>

                <div class="form-group">
                    <label for="modal_horas_necessarias">Horas Necessárias *</label>
                    <input type="number" id="modal_horas_necessarias" name="horas_necessarias" min="1" required>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        function atualizarCurso() {
            const tipo = document.getElementById('tipo').value;
            const grupoCurso = document.getElementById('grupo-curso');
            
            if(tipo === 'aluno') {
                grupoCurso.style.display = 'block';
            } else {
                grupoCurso.style.display = 'none';
            }
        }

        function editarCurso(id, nome, horas) {
            document.getElementById('modal_curso_id').value = id;
            document.getElementById('modal_nome_curso').value = nome;
            document.getElementById('modal_horas_necessarias').value = horas;
            document.getElementById('modalEditarCurso').classList.add('show');
        }

        function fecharModal() {
            document.getElementById('modalEditarCurso').classList.remove('show');
        }

        atualizarCurso();
    </script>
</body>
</html>
