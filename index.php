<?php
require_once 'config.php';
checkAuth();

if ($_SESSION['user_role'] === 'aluno') {
    header("Location: dashboard_aluno.php");
} elseif ($_SESSION['user_role'] === 'professor') {
    header("Location: dashboard_professor.php");
} else {
    header("Location: dashboard_admin.php");
}
exit;
?>
