<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($email) || empty($senha)) {
        $erro = "Email e senha são obrigatórios!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && verifyPassword($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_role'] = $user['tipo'];
            $_SESSION['curso_id'] = $user['curso_id'];

            header("Location: index.php");
            exit;
        } else {
            $erro = "Credenciais inválidas!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Extensão Acadêmica</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1> Login</h1>
                <p>feito para te ajudar!!</p>
            </div>

            <?php if(isset($erro)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <input type="email" id="email" name="email" required placeholder="Email">
                </div>
                
                <div class="form-group">
                    <input type="password" id="senha" name="senha" required placeholder="Senha">
                </div>
                
                <button type="submit" class="login-btn">Entrar</button>
                <div class="login-link">
                    <a href="registrar.php">cadastrar</a>
                </div>
            </form>
            
        </div>
    </div>
</body>
</html>
