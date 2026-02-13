<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $confirmar_senha = trim($_POST['confirmar_senha'] ?? '');
    $curso_id = intval($_POST['curso_id'] ?? 0);

    $erros = [];

    if (empty($nome)) $erros[] = "Nome é obrigatório";
    if (empty($email)) $erros[] = "Email é obrigatório";
    if (empty($senha)) $erros[] = "Senha é obrigatória";
    if ($senha !== $confirmar_senha) $erros[] = "As senhas não correspondem";
    if (strlen($senha) < 6) $erros[] = "A senha deve ter no mínimo 6 caracteres";
    if ($curso_id <= 0) $erros[] = "Selecione um curso";

    if (empty($erros)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erros[] = "Este email já está registrado";
        }
    }

    if (empty($erros)) {
        $senha_hash = hashPassword($senha);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, curso_id) VALUES (?, ?, ?, 'aluno', ?)");
        if ($stmt->execute([$nome, $email, $senha_hash, $curso_id])) {
            $sucesso = "Registro realizado com sucesso! Faça login para continuar.";
        } else {
            $erros[] = "Erro ao registrar. Tente novamente.";
        }
    }
}

$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar - Sistema de Extensão Acadêmica</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="register-page">
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1> Registre-se</h1>
                <p>Crie sua conta de aluno</p>
            </div>

            <?php if(isset($sucesso)): ?>
                <div class="alert alert-success"><?php echo $sucesso; ?></div>
                <a href="login.php" class="btn btn-primary" style="width: 100%; text-align: center;">Ir para Login</a>
            <?php endif; ?>

            <?php if(!empty($erros)): ?>
                <?php foreach($erros as $erro): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if(!isset($sucesso)): ?>
                <form method="POST" action="registrar.php">
                    <div class="form-group">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" required placeholder="Nome completo">
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required placeholder="exemplo@email.com">
                    </div>

                    <div class="form-group">
                        <label for="curso_id">Curso *</label>
                        <select id="curso_id" name="curso_id" required>
                            <option value="">Selecione um curso</option>
                            <?php foreach($cursos as $curso): ?>
                                <option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha *</label>
                        <input type="password" id="senha" name="senha" required placeholder="Mínimo 6 caracteres">
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Senha *</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required placeholder="Confirme sua senha">
                    </div>

                    <button type="submit" class="register-btn">Registrar</button>
                </form>

                <div class="login-link">
                    Já tem uma conta? <a href="login.php">Faça login aqui</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
