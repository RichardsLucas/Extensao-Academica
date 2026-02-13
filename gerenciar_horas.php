<?php
require_once 'config.php';
checkAuth('professor');

$atividade_id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM atividades WHERE id = ? AND professor_id = ?");
$stmt->execute([$atividade_id, $_SESSION['user_id']]);
$atividade = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$atividade) {
    die("Atividade não encontrada ou você não tem permissão para acessá-la.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aluno_id'])) {
    $aluno_id = intval($_POST['aluno_id']);
    $horas = intval($_POST['horas'] ?? 0);

    if($horas > 0 && $horas <= $atividade['carga_horaria']) {
        $stmt = $pdo->prepare("
            UPDATE participacoes 
            SET status = 'concluido', horas_contabilizadas = ? 
            WHERE aluno_id = ? AND atividade_id = ? AND status = 'inscrito'
        ");
        $stmt->execute([$horas, $aluno_id, $atividade_id]);
        $sucesso = "Horas validadas com sucesso!";
    } else {
        $erro = "Horas inválidas. Deve ser entre 1 e " . $atividade['carga_horaria'];
    }
}

$stmt = $pdo->prepare("
    SELECT u.id, u.nome, p.status, p.horas_contabilizadas, p.data_inscricao
    FROM participacoes p
    JOIN usuarios u ON p.aluno_id = u.id
    WHERE p.atividade_id = ?
    ORDER BY p.data_inscricao DESC
");
$stmt->execute([$atividade_id]);
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Horas - Extensão Acadêmica</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand"> 
        <img src="img/icone.png" alt="" class="img">    
        Extensão Acadêmica</a>
        <div class="navbar-menu">
            <span><strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
            <a href="logout.php" class="logout-btn">Sair</a>
        </div>
    </nav>

    <div class="container-main">
        <aside class="sidebar">
            <ul>
                <li><a href="dashboard_professor.php">← Voltar</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="card">
                <div class="card-title"> Gerenciar Horas - <?php echo htmlspecialchars($atividade['titulo']); ?></div>
                
                <div class="stats-card" style="margin-bottom: 1.5rem;">
                    <div class="stats-label">Atividade</div>
                    <div style="font-size: 1rem; margin: 0.5rem 0;">
                        <strong><?php echo htmlspecialchars($atividade['titulo']); ?></strong><br>
                        <span style="color: var(--text-light);">Carga horária: <?php echo $atividade['carga_horaria']; ?>h</span>
                    </div>
                </div>

                <?php if(isset($sucesso)): ?>
                    <div class="alert alert-success"><?php echo $sucesso; ?></div>
                <?php endif; ?>
                
                <?php if(isset($erro)): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>

                <?php if(empty($alunos)): ?>
                    <div class="alert alert-info">Nenhum aluno inscrito nesta atividade ainda.</div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Status</th>
                                <th>Horas Creditadas</th>
                                <th>Data Inscrição</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($alunos as $aluno): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($aluno['nome']); ?></strong></td>
                                    <td>
                                        <?php if($aluno['status'] === 'concluido'): ?>
                                            <span class="badge badge-success">Concluído</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Aguardando</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $aluno['horas_contabilizadas'] > 0 ? $aluno['horas_contabilizadas'] . 'h' : '—'; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($aluno['data_inscricao'])); ?></td>
                                    <td>
                                        <?php if($aluno['status'] === 'inscrito'): ?>
                                            <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                                <input type="hidden" name="aluno_id" value="<?php echo $aluno['id']; ?>">
                                                <input type="number" name="horas" min="1" max="<?php echo $atividade['carga_horaria']; ?>" placeholder="Horas" required style="width: 80px; margin-bottom: 0;">
                                                <button type="submit" class="btn btn-success btn-sm">Validar</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: var(--success); font-weight: 600;">Validado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
