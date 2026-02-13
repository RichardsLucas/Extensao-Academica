<?php
require_once 'config.php';
checkAuth('professor');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if($acao === 'criar_atividade') {
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $curso_id = $_POST['curso_id'] ?? null;
        $carga_horaria = (int)$_POST['carga_horaria'] ?? 0;
        $tipo = $_POST['tipo'] ?? 'Evento';
        $data_inicio = $_POST['data_inicio'] ?? date('Y-m-d');
        $data_termino = $_POST['data_termino'] ?? null;

        if(!empty($titulo) && $carga_horaria > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO atividades (titulo, descricao, professor_id, curso_relacionado_id, tipo, carga_horaria, data_inicio, data_termino, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'aprovado')
            ");
            $stmt->execute([$titulo, $descricao, $_SESSION['user_id'], $curso_id, $tipo, $carga_horaria, $data_inicio, $data_termino]);
            $sucesso = "Atividade criada com sucesso!";
        }
    }

    if($acao === 'editar_atividade') {
        $atividade_id = intval($_POST['atividade_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $data_termino = $_POST['data_termino'] ?? null;

        if($atividade_id > 0 && !empty($titulo)) {
            $stmt = $pdo->prepare("UPDATE atividades SET titulo = ?, descricao = ?, data_termino = ? WHERE id = ? AND professor_id = ?");
            if($stmt->execute([$titulo, $descricao, $data_termino, $atividade_id, $_SESSION['user_id']])) {
                $sucesso = "Atividade atualizada com sucesso!";
            }
        }
    }

    if($acao === 'deletar_atividade') {
        $atividade_id = intval($_POST['atividade_id'] ?? 0);
        if($atividade_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM atividades WHERE id = ? AND professor_id = ?");
            if($stmt->execute([$atividade_id, $_SESSION['user_id']])) {
                $sucesso = "Atividade deletada com sucesso!";
            }
        }
    }
}

$stmt = $pdo->prepare("
    SELECT a.*, c.nome as nome_curso,
        COUNT(DISTINCT p.id) as total_inscritos,
        COUNT(DISTINCT CASE WHEN p.status = 'concluido' THEN 1 END) as total_concluidos
    FROM atividades a
    LEFT JOIN cursos c ON a.curso_relacionado_id = c.id
    LEFT JOIN participacoes p ON a.id = p.atividade_id
    WHERE a.professor_id = ?
    GROUP BY a.id
    ORDER BY a.data_inicio DESC
");
$stmt->execute([$_SESSION['user_id']]);
$atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Professor - Extensão Acadêmica</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
        <img src="img/icone.png" alt="" class="img">    
        Extensão Acadêmica</a>
        <div class="navbar-menu">
            <span>Bem-vindo, Prof. <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
            <a href="logout.php" class="logout-btn">Sair</a>
        </div>
    </nav>

    <div class="container-main">
        <aside class="sidebar">
            <ul>
                <li class="active"><a href="dashboard_professor.php">Dashboard</a></li>
                <li><a href="#nova-atividade">Nova Atividade</a></li>
                <li><a href="#minhas-atividades">Minhas Atividades</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <?php if(isset($sucesso)): ?>
                <div class="alert alert-success"><?php echo $sucesso; ?></div>
            <?php endif; ?>

            <section class="card">
                <div class="card-title"> Estatísticas</div>
                <div class="grid grid-2">
                    <div class="stats-card">
                        <div class="stats-label">Total de Atividades</div>
                        <div class="stats-number"><?php echo count($atividades); ?></div>
                    </div>
                    <div class="stats-card">
                        <div class="stats-label">Total de Inscritos</div>
                        <div class="stats-number">
                            <?php 
                            $total_inscritos = array_sum(array_map(function($a) { return $a['total_inscritos']; }, $atividades));
                            echo $total_inscritos;
                            ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card" id="nova-atividade">
                <div class="card-title">Criar Nova Atividade</div>
                
                <form method="POST" action="dashboard_professor.php">
                    <input type="hidden" name="acao" value="criar_atividade">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="titulo">Título da Atividade *</label>
                            <input type="text" id="titulo" name="titulo" required placeholder="Ex: Workshop de PHP">
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo">Tipo de Atividade *</label>
                            <select id="tipo" name="tipo" required>
                                <option value="Evento">Evento</option>
                                <option value="Curso">Curso</option>
                                <option value="Projeto">Projeto</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="curso_id">Curso Relacionado</label>
                            <select id="curso_id" name="curso_id">
                                <option value="">Geral (todos os cursos)</option>
                                <?php foreach($cursos as $curso): ?>
                                    <option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="carga_horaria">Carga Horária (em horas) *</label>
                            <input type="number" id="carga_horaria" name="carga_horaria" min="1" required placeholder="Ex: 20">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_inicio">Data de Início *</label>
                            <input type="date" id="data_inicio" name="data_inicio" required value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="data_termino">Data de Término</label>
                            <input type="date" id="data_termino" name="data_termino">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="4" placeholder="Descreva os objetivos e conteúdo da atividade..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Criar Atividade</button>
                </form>
            </section>

            <section class="card" id="minhas-atividades">
                <div class="card-title"> Minhas Atividades</div>

                <?php if(empty($atividades)): ?>
                    <div class="alert alert-info">Você ainda não criou nenhuma atividade.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Atividade</th>
                                <th>Tipo</th>
                                <th>Horas</th>
                                <th>Inscritos</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($atividades as $atividade): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($atividade['titulo']); ?></strong></td>
                                    <td><?php echo $atividade['tipo']; ?></td>
                                    <td><?php echo $atividade['carga_horaria']; ?>h</td>
                                    <td><?php echo $atividade['total_inscritos']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($atividade['data_inicio'])); ?></td>
                                    <td>
                                        <a href="gerenciar_horas.php?id=<?php echo $atividade['id']; ?>" class="btn btn-primary btn-sm">Gerenciar</a>
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="editarAtividade(<?php echo $atividade['id']; ?>, '<?php echo htmlspecialchars($atividade['titulo']); ?>', '<?php echo htmlspecialchars($atividade['descricao'] ?? ''); ?>', '<?php echo $atividade['data_termino'] ?? ''; ?>')">Editar</button>
                                        <form action="#minhas-atividades" method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja deletar esta atividade?');">
                                            <input type="hidden" name="acao" value="deletar_atividade">
                                            <input type="hidden" name="atividade_id" value="<?php echo $atividade['id']; ?>">
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

    <div id="modalEditarAtividade" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Atividade</h2>
                <button type="button" class="close-modal" onclick="fecharModal()">&times;</button>
            </div>
            <form method="POST" action="#minhas-atividades">
                <input type="hidden" name="acao" value="editar_atividade">
                <input type="hidden" name="atividade_id" id="modal_atividade_id">
                
                <div class="form-group">
                    <label for="modal_titulo">Título *</label>
                    <input type="text" id="modal_titulo" name="titulo" required>
                </div>

                <div class="form-group">
                    <label for="modal_descricao">Descrição</label>
                    <textarea id="modal_descricao" name="descricao" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="modal_data_termino">Data de Término</label>
                    <input type="date" id="modal_data_termino" name="data_termino">
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
        function editarAtividade(id, titulo, descricao, dataTermino) {
            document.getElementById('modal_atividade_id').value = id;
            document.getElementById('modal_titulo').value = titulo;
            document.getElementById('modal_descricao').value = descricao;
            document.getElementById('modal_data_termino').value = dataTermino;
            document.getElementById('modalEditarAtividade').classList.add('show');
        }

        function fecharModal() {
            document.getElementById('modalEditarAtividade').classList.remove('show');
        }
    </script>
</body>
</html>
