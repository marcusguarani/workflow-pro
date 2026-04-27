<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = db(false);
        $sql = file_get_contents(__DIR__ . '/../sql/install.sql');
        $pdo->exec($sql);

        $name = trim($_POST['name'] ?? 'Administrador');
        $email = trim($_POST['email'] ?? 'admin@workflow.local');
        $password = $_POST['password'] ?? '123456';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $pdoDb = db(true);
        $pdoDb->exec("INSERT IGNORE INTO departments (id, name, kind, created_at) VALUES
            (1, 'Administrador', 'Sede', NOW()),
            (2, 'Engenharia', 'Sede', NOW()),
            (3, 'PCP', 'Sede', NOW()),
            (4, 'Suprimentos', 'Apoio', NOW()),
            (5, 'Qualidade', 'Apoio', NOW()),
            (6, 'Obras', 'Obra', NOW())");

        $stmt = $pdoDb->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $existingId = $stmt->fetchColumn();

        if ($existingId) {
            $update = $pdoDb->prepare('UPDATE users SET name = ?, password_hash = ?, role = ?, is_active = 1 WHERE id = ?');
            $update->execute([$name, $hash, 'Administrador', $existingId]);
        } else {
            $insert = $pdoDb->prepare('INSERT INTO users (department_id, name, email, password_hash, role, is_active, created_at) VALUES (1, ?, ?, ?, ?, 1, NOW())');
            $insert->execute([$name, $email, $hash, 'Administrador']);
        }

        $count = (int) $pdoDb->query('SELECT COUNT(*) FROM demands')->fetchColumn();
        if ($count === 0) {
            $pdoDb->exec("INSERT INTO demands (origin_department_id, destination_department_id, responsible_user_id, created_by, title, description, requester_name, type, priority, status, due_date, completed_at, created_at, updated_at) VALUES
            (2, 6, 1, 1, 'Solicitação de material estrutural', 'Necessidade de envio de material para continuidade da obra.', 'Carlos Engenharia', 'Material', 'Alta', 'Aberto', DATE_ADD(CURDATE(), INTERVAL 3 DAY), NULL, NOW(), NOW()),
            (6, 2, 1, 1, 'Aprovação de projeto executivo', 'Projeto executivo aguardando validação da engenharia da sede.', 'Marina Obras', 'Projeto', 'Média', 'Em Andamento', DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, NOW(), NOW()),
            (6, 4, 1, 1, 'Compra emergencial de insumos', 'Compra urgente para manter cronograma de execução.', 'Marina Obras', 'Compra', 'Crítica', 'Atrasado', DATE_SUB(CURDATE(), INTERVAL 2 DAY), NULL, NOW(), NOW()),
            (3, 6, 1, 1, 'Ajuste de cronograma da frente B', 'Replanejamento solicitado devido a atraso de fornecedor.', 'PCP Central', 'Planejamento', 'Alta', 'Aberto', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL, NOW(), NOW()),
            (5, 6, 1, 1, 'Inspeção de qualidade da concretagem', 'Programar vistoria e registro fotográfico.', 'Equipe Qualidade', 'Qualidade', 'Média', 'Concluído', DATE_SUB(CURDATE(), INTERVAL 1 DAY), NOW(), NOW(), NOW())");
        }

        $message = 'Instalação concluída com sucesso. Entre no login com o usuário configurado.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalar Workflow Pro</title>
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
            <div class="brand-icon"><i class="bi bi-rocket-takeoff-fill"></i></div>
            <div>
                <h1 class="h3 mb-1 fw-bold">Instalação do Workflow Pro</h1>
                <div class="small-muted">Configuração automática do banco e do administrador</div>
            </div>
        </div>

        <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

        <form method="post" class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Nome do administrador</label>
                <input type="text" name="name" class="form-control" value="Administrador" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">E-mail</label>
                <input type="email" name="email" class="form-control" value="admin@workflow.local" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Senha</label>
                <input type="password" name="password" class="form-control" value="123456" required>
            </div>
            <div class="col-12 d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Instalar sistema</button>
            </div>
        </form>

        <div class="mt-4 small-muted">Após concluir, acesse a tela de login normal em <strong>index.php</strong>.</div>
    </div>
</body>
</html>
