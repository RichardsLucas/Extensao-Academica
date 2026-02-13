<?php
require_once 'fpdf/fpdf.php';
require_once 'config.php';
checkAuth('aluno');


$stmt = $pdo->prepare("
    SELECT 
        u.nome, c.nome as curso_nome, c.carga_horaria_necessaria,
        COALESCE(SUM(CASE WHEN p.status = 'concluido' THEN p.horas_contabilizadas ELSE 0 END), 0) as horas_totais
    FROM usuarios u
    JOIN cursos c ON u.curso_id = c.id
    LEFT JOIN participacoes p ON u.id = p.aluno_id
    WHERE u.id = ?
    GROUP BY u.id
");
$stmt->execute([$_SESSION['user_id']]);
$aluno = $stmt->fetch(PDO::FETCH_ASSOC);

if($aluno['horas_totais'] < $aluno['carga_horaria_necessaria']) {
    header("Location: dashboard_aluno.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado - Extensão Acadêmica</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <img src="img/icone.png" alt="" class="img">
        Extensão Acadêmica</a>
        <div class="navbar-menu">
            <a href="dashboard_aluno.php" class="logout-btn">Voltar ao Painel</a>
        </div>
    </nav>

    <main class="main-content" style="max-width: 100%; margin: 0 auto;">
        <div class="certificado-container">
            <div class="certificado-header">CERTIFICADO DE CONCLUSÃO</div>
            
            <div class="certificado-body">
                <p>Certificamos que</p>
                
                <div class="certificado-nome"><?php echo htmlspecialchars($aluno['nome']); ?></div>
                
                <p>completou com êxito o programa de extensão acadêmica do curso</p>
                
                <div class="curso-nome">
                    <?php echo htmlspecialchars($aluno['curso_nome']); ?>
                </div>

                <div class="certificado-info">
                    <strong>Carga Horária Cumprida:</strong> <?php echo $aluno['horas_totais']; ?> horas
                </div>

                <div class="certificado-footer">
                    <p style="margin-bottom: 1rem;">Emitido em <?php echo date('d \d\e F \d\e Y', time()); ?></p>
                    <p style="font-size: 0.8rem;">
                        Certificado válido conforme normas de extensão acadêmica.<br>
                        Documento gerado pelo Sistema de Extensão Acadêmica.
                    </p>
                </div>
            </div>

            <div class="download-btn">
                <a href='certificado-pdf.php' target='_blank' class="btn btn-primary">Baixar como PDF</a>
                <a href="dashboard_aluno.php" class="btn btn-secondary">Voltar</a>
            </div>
        </div>
    </main>

    <script src="js/script.js"></script>
</body>
</html>
