<?php
// Script para criar usuários de teste
// Acesse: http://localhost/seu_projeto/seed_usuarios.php para popular o banco

require_once 'config.php';

$usuarios = [
    [
        'nome' => 'Administrador',
        'email' => 'admin@gmail.com',
        'senha' => 'admin123',
        'tipo' => 'admin',
        'curso_id' => null
    ],
    [
        'nome' => 'Prof. João Silva',
        'email' => 'professor@gmail.com',
        'senha' => 'prof123',
        'tipo' => 'professor',
        'curso_id' => null
    ],
    [
        'nome' => 'Maria Santos',
        'email' => 'aluno@gmail.com',
        'senha' => 'aluno123',
        'tipo' => 'aluno',
        'curso_id' => 1
    ]
];

try {
    foreach ($usuarios as $usuario) {
        
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$usuario['email']]);
        
        if ($stmt->rowCount() > 0) {
            echo "⚠ Usuário {$usuario['email']} já existe.<br>";
            continue;
        }
        
       
        $senha_hash = hashPassword($usuario['senha']);
        
       
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, curso_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $usuario['nome'],
            $usuario['email'],
            $senha_hash,
            $usuario['tipo'],
            $usuario['curso_id']
        ]);
        
        echo "✓ Usuário {$usuario['email']} criado com sucesso!<br>";
    }
    
    echo "<br><hr>";
    echo "<h2>Usuários de Teste Criados:</h2>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Tipo</th><th>Email</th><th>Senha</th></tr>";
    echo "<tr><td>Admin</td><td>admin@gmail.com</td><td>admin123</td></tr>";
    echo "<tr><td>Professor</td><td>professor@gmail.com</td><td>prof123</td></tr>";
    echo "<tr><td>Aluno</td><td>aluno@gmail.com</td><td>aluno123</td></tr>";
    echo "</table>";
    echo "<br><a href='login.php'>Ir para Login</a>";
    
} catch (PDOException $e) {
    echo "Erro ao criar usuários: " . $e->getMessage();
}
?>
