<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $kind = trim($_POST['kind'] ?? 'Sede');
    if ($name === '') {
        $error = 'Informe o nome do setor.';
    } else {
        $stmt = db()->prepare('INSERT INTO departments (name, kind, created_at) VALUES (?, ?, NOW())');
        $stmt->execute([$name, $kind]);
        redirect('departments.php');
    }
}

$items = db()->query('SELECT * FROM departments ORDER BY name')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="panel h-100">
            <h2 class="h4 mb-3">Novo setor</h2>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post" class="row g-3">
                <div class="col-12"><label class="form-label fw-semibold">Nome</label><input type="text" name="name" class="form-control" required></div>
                <div class="col-12"><label class="form-label fw-semibold">Tipo</label>
                    <select name="kind" class="form-select">
                        <option value="Sede">Sede</option>
                        <option value="Obra">Obra</option>
                        <option value="Apoio">Apoio</option>
                    </select>
                </div>
                <div class="col-12 d-grid"><button type="submit" class="btn btn-primary">Cadastrar setor</button></div>
            </form>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="panel h-100">
            <h2 class="h4 mb-3">Estrutura organizacional</h2>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>ID</th><th>Nome</th><th>Tipo</th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>#<?= (int) $item['id'] ?></td>
                                <td class="fw-semibold"><?= e($item['name']) ?></td>
                                <td><?= e($item['kind']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
