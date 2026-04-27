<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

$departments = db()->query('SELECT * FROM departments ORDER BY name')->fetchAll();
$users = db()->query('SELECT * FROM users WHERE is_active = 1 ORDER BY name')->fetchAll();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'origin_department_id' => (int) ($_POST['origin_department_id'] ?? 0),
        'destination_department_id' => (int) ($_POST['destination_department_id'] ?? 0),
        'responsible_user_id' => !empty($_POST['responsible_user_id']) ? (int) $_POST['responsible_user_id'] : null,
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'requester_name' => trim($_POST['requester_name'] ?? ''),
        'type' => trim($_POST['type'] ?? 'Solicitação'),
        'priority' => trim($_POST['priority'] ?? 'Média'),
        'status' => trim($_POST['status'] ?? 'Aberto'),
        'due_date' => $_POST['due_date'] ?? '',
    ];

    if (!$data['origin_department_id'] || !$data['destination_department_id'] || $data['title'] === '' || $data['description'] === '' || $data['requester_name'] === '' || $data['due_date'] === '') {
        $error = 'Preencha os campos obrigatórios.';
    } else {
        $stmt = db()->prepare('INSERT INTO demands (origin_department_id, destination_department_id, responsible_user_id, created_by, title, description, requester_name, type, priority, status, due_date, completed_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, NOW(), NOW())');
        $stmt->execute([
            $data['origin_department_id'],
            $data['destination_department_id'],
            $data['responsible_user_id'],
            current_user()['id'],
            $data['title'],
            $data['description'],
            $data['requester_name'],
            $data['type'],
            $data['priority'],
            $data['status'],
            $data['due_date'],
        ]);
        $demandId = (int) db()->lastInsertId();
        $hist = db()->prepare('INSERT INTO demand_history (demand_id, user_id, action, notes, created_at) VALUES (?, ?, ?, ?, NOW())');
        $hist->execute([$demandId, current_user()['id'], 'Criação da demanda', 'Demanda cadastrada no sistema.']);
        redirect('demand_view.php?id=' . $demandId);
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="panel">
    <div class="filters-bar">
        <div>
            <h2 class="h4 mb-1">Cadastrar nova demanda</h2>
            <div class="small-muted">Estrutura profissional para registro, prazo e responsabilidade</div>
        </div>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Origem</label>
            <select name="origin_department_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= (int) $department['id'] ?>"><?= e($department['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Destino</label>
            <select name="destination_department_id" class="form-select" required>
                <option value="">Selecione</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= (int) $department['id'] ?>"><?= e($department['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Responsável</label>
            <select name="responsible_user_id" class="form-select">
                <option value="">Não definido</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?= (int) $user['id'] ?>"><?= e($user['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Solicitante</label>
            <input type="text" name="requester_name" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Tipo</label>
            <input type="text" name="type" class="form-control" value="Solicitação" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Prioridade</label>
            <select name="priority" class="form-select">
                <?php foreach (['Baixa','Média','Alta','Crítica'] as $priority): ?>
                    <option value="<?= e($priority) ?>"><?= e($priority) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Status inicial</label>
            <select name="status" class="form-select">
                <?php foreach (['Aberto','Em Andamento','Concluído','Atrasado'] as $status): ?>
                    <option value="<?= e($status) ?>"><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Prazo</label>
            <input type="date" name="due_date" class="form-control" required>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Título</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Descrição</label>
            <textarea name="description" rows="6" class="form-control" required></textarea>
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Salvar demanda</button>
            <a href="demands.php" class="btn btn-light">Cancelar</a>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
