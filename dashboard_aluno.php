<?php
require_once 'config.php';
checkAuth('aluno');

$filtro_pesquisa = trim($_GET['pesquisa'] ?? '');
$filtro_tipo = $_GET['tipo'] ?? '';
$filtro_curso = $_GET['curso'] ?? '';

$stmt = $pdo->prepare("
    SELECT 
        u.nome, c.nome as curso_nome, c.carga_horaria_necessaria,
        COALESCE(SUM(CASE WHEN p.status = 'concluido' THEN p.horas_contabilizadas ELSE 0 END), 0) as horas_totais,
        COUNT(CASE WHEN p.status = 'concluido' THEN 1 END) as atividades_concluidas
    FROM usuarios u
    JOIN cursos c ON u.curso_id = c.id
    LEFT JOIN participacoes p ON u.id = p.aluno_id
    WHERE u.id = ?
    GROUP BY u.id
");
$stmt->execute([$_SESSION['user_id']]);
$progresso = $stmt->fetch(PDO::FETCH_ASSOC);

$query = "
    SELECT a.*, c.nome as nome_curso, p.status as inscricao_status, p.horas_contabilizadas
    FROM atividades a
    LEFT JOIN cursos c ON a.curso_relacionado_id = c.id
    LEFT JOIN participacoes p ON a.id = p.atividade_id AND p.aluno_id = ?
    WHERE a.status = 'aprovado'
";

$params = [$_SESSION['user_id']];

if(!empty($filtro_pesquisa)) {
    $query .= " AND (a.titulo LIKE ? OR a.descricao LIKE ?)";
    $params[] = "%$filtro_pesquisa%";
    $params[] = "%$filtro_pesquisa%";
}

if(!empty($filtro_tipo)) {
    $query .= " AND a.tipo = ?";
    $params[] = $filtro_tipo;
}

if(!empty($filtro_curso)) {
    $query .= " AND c.id = ?";
    $params[] = $filtro_curso;
}

$query .= " ORDER BY a.data_inicio DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$atividades_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT a.titulo, a.tipo, a.carga_horaria, c.nome as curso, p.status, p.horas_contabilizadas, p.data_inscricao
    FROM participacoes p
    JOIN atividades a ON p.atividade_id = a.id
    LEFT JOIN cursos c ON a.curso_relacionado_id = c.id
    WHERE p.aluno_id = ?
    ORDER BY p.data_inscricao DESC
");
$stmt->execute([$_SESSION['user_id']]);
$historico_participacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$porcentagem_progresso = ($progresso['horas_totais'] / $progresso['carga_horaria_necessaria']) * 100;
$porcentagem_progresso = min(round($porcentagem_progresso), 100);
$pode_certificado = $progresso['horas_totais'] >= $progresso['carga_horaria_necessaria'];

$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Aluno - Extensão Acadêmica</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">

        <a href="index.php" class="navbar-brand"> 
            <img src="img/icone.png" alt="" class="img">
            Extensão Acadêmica</a>
        <div class="navbar-menu">
            <span>Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
            <div class="user-info">
                <span><?php echo htmlspecialchars($progresso['curso_nome']); ?></span>
                <a href="logout.php" class="logout-btn">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container-main">
        <aside class="sidebar">
            <ul>
                <li class="active"><a href="dashboard_aluno.php">Dashboard</a></li>
                <li><a href="#atividades">Minhas Inscrições</a></li>
                <li><a href="#historico">Histórico</a></li>
                <li><a href="certificado.php">Certificado</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <section class="card">
                <div class="card-title"> Meu Progresso</div>
                
                <div class="grid grid-2">
                    <div class="stats-card">
                        <div class="stats-label">Horas Completadas</div>
                        <div class="stats-number"><?php echo $progresso['horas_totais']; ?></div>
                        <div class="stats-label">de <?php echo $progresso['carga_horaria_necessaria']; ?> horas</div>
                    </div>
                    
                    <div class="stats-card">
                        <div class="stats-label">Atividades Concluídas</div>
                        <div class="stats-number"><?php echo $progresso['atividades_concluidas']; ?></div>
                        <div class="stats-label">Atividades</div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: <?php echo $porcentagem_progresso; ?>%;">
                            <?php echo $porcentagem_progresso; ?>%
                        </div>
                    </div>
                    <p style="color: var(--text-light); font-size: 0.9rem;">
                        Faltam <strong><?php echo max(0, $progresso['carga_horaria_necessaria'] - $progresso['horas_totais']); ?> horas</strong> para completar seus requisitos.
                    </p>
                </div>

                <?php if($pode_certificado): ?>
                    <div class="alert alert-success" style="margin-top: 1rem;">
                        <strong> Parabéns!</strong> Você completou as horas necessárias. Clique no botão abaixo para gerar seu certificado.
                        <br><br>
                        <a href="certificado.php" class="btn btn-success">Baixar Certificado</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning" style="margin-top: 1rem;">
                        <strong> Quase lá!</strong> Continue participando de atividades para completar seus requisitos.
                    </div>
                <?php endif; ?>
            </section>

            <section class="card" id="atividades">
                <div class="card-title"> Atividades Disponíveis</div>

                <!-- Filtros de pesquisa -->
                <div class="filter-section" style="background-color: var(--light-gray); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                    <form method="GET" action="dashboard_aluno.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <input type="text" name="pesquisa" placeholder=" Pesquisar atividade..." value="<?php echo htmlspecialchars($filtro_pesquisa); ?>">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="tipo">
                                <option value="">Todos os tipos</option>
                                <option value="Evento" <?php if($filtro_tipo === 'Evento') echo 'selected'; ?>>Evento</option>
                                <option value="Curso" <?php if($filtro_tipo === 'Curso') echo 'selected'; ?>>Curso</option>
                                <option value="Projeto" <?php if($filtro_tipo === 'Projeto') echo 'selected'; ?>>Projeto</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <select name="curso">
                                <option value="">Todos os cursos</option>
                                <?php foreach($cursos as $curso): ?>
                                    <option value="<?php echo $curso['id']; ?>" <?php if($filtro_curso == $curso['id']) echo 'selected'; ?>><?php echo htmlspecialchars($curso['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">Filtrar</button>
                        <a href="dashboard_aluno.php" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; text-align: center;">Limpar</a>
                    </form>
                </div>

                <?php if(empty($atividades_disponiveis)): ?>
                    <div class="alert alert-info">Nenhuma atividade disponível com os filtros selecionados.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Atividade</th>
                                <th>Tipo</th>
                                <th>Horas</th>
                                <th>Status</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($atividades_disponiveis as $atividade): 
                                $inscrito = !empty($atividade['inscricao_status']);
                                $concluido = $atividade['inscricao_status'] === 'concluido';
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($atividade['titulo']); ?></strong></td>
                                    <td><span class="badge badge-info"><?php echo $atividade['tipo']; ?></span></td>
                                    <td><?php echo $atividade['carga_horaria']; ?>h</td>
                                    <td>
                                        <?php if($concluido): ?>
                                            <span class="badge badge-success">✓ Concluído</span>
                                        <?php elseif($inscrito): ?>
                                            <span class="badge badge-warning">Inscrito</span>
                                        <?php else: ?>
                                            <span class="badge badge-info">Disponível</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!$inscrito): ?>
                                            <form action="inscrever.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="atividade_id" value="<?php echo $atividade['id']; ?>">
                                                <button type="submit" class="btn btn-primary btn-sm">Inscrever-se</button>
                                            </form>
                                        <?php elseif($concluido): ?>
                                            <span class="badge badge-success">Realizado</span>
                                        <?php else: ?>
                                            <span style="color: var(--text-light); font-size: 0.9rem;">Aguardando validação</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

            <section class="card" id="historico">
                <div class="card-title"> Meu Histórico de Atividades</div>

                <?php if(empty($historico_participacoes)): ?>
                    <div class="alert alert-info">Você ainda não participou de nenhuma atividade.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Atividade</th>
                                <th>Tipo</th>
                                <th>Horas</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($historico_participacoes as $participacao): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($participacao['titulo']); ?></td>
                                    <td><?php echo $participacao['tipo']; ?></td>
                                    <td>
                                        <?php if($participacao['status'] === 'concluido'): ?>
                                            <strong><?php echo $participacao['horas_contabilizadas']; ?>h</strong>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($participacao['status'] === 'concluido'): ?>
                                            <span class="badge badge-success">Concluído</span>
                                        <?php elseif($participacao['status'] === 'inscrito'): ?>
                                            <span class="badge badge-warning">Inscrito</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Reprovado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($participacao['data_inscricao'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
