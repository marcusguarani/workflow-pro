<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

$departments = db()->query('SELECT * FROM departments ORDER BY name')->fetchAll();
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $departmentId = !empty($_POST['department_id']) ? (int) $_POST['department_id'] : null;
    $role = trim($_POST['role'] ?? 'Usuário');

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Preencha nome, e-mail e senha.';
    } else {
        $stmt = db()->prepare('INSERT INTO users (department_id, name, email, password_hash, role, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())');
        $stmt->execute([$departmentId, $name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
        redirect('users.php');
    }
}

$users = db()->query('SELECT u.*, d.name AS department_name FROM users u LEFT JOIN departments d ON d.id = u.department_id ORDER BY u.id DESC')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="panel h-100">
            <h2 class="h4 mb-3">Novo usuário</h2>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post" class="row g-3">
                <div class="col-12"><label class="form-label fw-semibold">Nome</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-12"><label class="form-label fw-semibold">E-mail</label><input type="email" name="email" class="form-control" required></div>
                <div class="col-12"><label class="form-label fw-semibold">Senha</label><input type="password" name="password" class="form-control" required></div>
                <div class="col-12"><label class="form-label fw-semibold">Setor</label>
                    <select name="department_id" class="form-select">
                        <option value="">Selecione</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= (int) $department['id'] ?>"><?= e($department['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12"><label class="form-label fw-semibold">Perfil</label><input type="text" name="role" class="form-control" value="Usuário" required></div>
                <div class="col-12 d-grid"><button type="submit" class="btn btn-primary">Cadastrar usuário</button></div>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="panel h-100">
            <h2 class="h4 mb-3">Usuários cadastrados</h2>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Setor</th><th>Perfil</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= (int) $user['id'] ?></td>
                                <td class="fw-semibold"><?= e($user['name']) ?></td>
                                <td><?= e($user['email']) ?></td>
                                <td><?= e($user['department_name'] ?: '-') ?></td>
                                <td><?= e($user['role']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
