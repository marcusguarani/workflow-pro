<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = null;
$installHint = !table_exists('users');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (attempt_login($email, $password)) {
        redirect('dashboard.php');
    }

    $error = 'Usuário ou senha inválidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Workflow Pro</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-brand">
            <div class="brand-icon"><i class="bi bi-building-gear"></i></div>
            <div>
                <h1 class="h3 mb-1 fw-bold">Workflow Pro</h1>
                <div class="small-muted">Gestão profissional entre sede e obras</div>
            </div>
        </div>

        <p class="login-highlight">Dashboard analítico, controle de SLA, visual moderno em Bootstrap e estrutura pronta para demonstração.</p>

        <?php if ($installHint): ?>
            <div class="alert alert-warning">Sistema ainda não instalado. Acesse <strong>install.php</strong> antes do primeiro login.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">E-mail</label>
                <input type="email" name="email" class="form-control" placeholder="admin@workflow.local" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Senha</label>
                <input type="password" name="password" class="form-control" placeholder="Digite sua senha" required>
            </div>
            <div class="col-12 d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Entrar no sistema</button>
            </div>
        </form>

        <div class="mt-4 d-flex flex-wrap gap-3">
            <div class="kpi-box flex-fill"><strong>Kanban</strong><span>Fluxo visual das demandas</span></div>
            <div class="kpi-box flex-fill"><strong>Gráficos</strong><span>Indicadores gerenciais</span></div>
        </div>

        <div class="mt-4 small-muted">Acesso padrão após instalar: <strong>admin@workflow.local</strong> / <strong>123456</strong></div>
    </div>
</body>
</html>
