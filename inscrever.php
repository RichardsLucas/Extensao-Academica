<?php
require_once 'config.php';
checkAuth('aluno');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atividade_id'])) {
    $atividade_id = intval($_POST['atividade_id']);
    $aluno_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM participacoes WHERE aluno_id = ? AND atividade_id = ?");
    $stmt->execute([$aluno_id, $atividade_id]);
    
    if($stmt->fetch()) {
        $_SESSION['erro'] = "Você já está inscrito nesta atividade!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO participacoes (aluno_id, atividade_id, status) VALUES (?, ?, 'inscrito')");
        if($stmt->execute([$aluno_id, $atividade_id])) {
            $_SESSION['sucesso'] = "Você foi inscrito com sucesso na atividade!";
        }
    }
}

header("Location: dashboard_aluno.php");
exit;
?>
