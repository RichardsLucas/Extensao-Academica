<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $coordenador = $_POST['coordenador'];
    $tipo = $_POST['tipo'];
    $data_inicio = $_POST['data_inicio'];
    $descricao = $_POST['descricao'];

    $sql = "INSERT INTO atividades (titulo, coordenador, tipo, data_inicio, descricao) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$titulo, $coordenador, $tipo, $data_inicio, $descricao])) {
        header('Location: index.php');
        exit;
    } else {
        $error = "Erro ao cadastrar atividade.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Nova Atividade</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header bg-white">
                <h4 class="mb-0">Nova Atividade de Extensão</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Título da Atividade</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Coordenador Responsável</label>
                        <input type="text" name="coordenador" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-select">
                                <option value="Evento">Evento</option>
                                <option value="Curso">Curso</option>
                                <option value="Projeto">Projeto</option>
                                <option value="Prestacao_Servico">Prestação de Serviço</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Início</label>
                            <input type="date" name="data_inicio" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Salvar Atividade</button>
                        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
