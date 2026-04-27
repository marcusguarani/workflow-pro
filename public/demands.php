<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

$status = trim($_GET['status'] ?? '');
$priority = trim($_GET['priority'] ?? '');
$search = trim($_GET['search'] ?? '');

$sql = 'SELECT d.*, od.name AS origin_name, dd.name AS destination_name, u.name AS responsible_name FROM demands d INNER JOIN departments od ON od.id = d.origin_department_id INNER JOIN departments dd ON dd.id = d.destination_department_id LEFT JOIN users u ON u.id = d.responsible_user_id WHERE 1=1';
$params = [];

if ($status !== '') {
    $sql .= ' AND d.status = ?';
    $params[] = $status;
}
if ($priority !== '') {
    $sql .= ' AND d.priority = ?';
    $params[] = $priority;
}
if ($search !== '') {
    $sql .= ' AND (d.title LIKE ? OR d.description LIKE ? OR d.requester_name LIKE ?)';
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= ' ORDER BY d.updated_at DESC, d.id DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="panel">
    <div class="filters-bar">
        <div>
            <h2 class="h4 mb-1">Central de demandas</h2>
            <div class="small-muted">Pesquisa rápida, filtros gerenciais e acesso ao detalhe completo</div>
        </div>
        <a href="demand_form.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Nova demanda</a>
    </div>

    <form class="row g-3 mb-3" method="get">
        <div class="col-md-4">
            <input type="text" class="form-control" name="search" value="<?= e($search) ?>" placeholder="Buscar por título, descrição ou solicitante">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="status">
                <option value="">Todos os status</option>
                <?php foreach (['Aberto','Em Andamento','Concluído','Atrasado'] as $option): ?>
                    <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="priority">
                <option value="">Todas as prioridades</option>
                <?php foreach (['Baixa','Média','Alta','Crítica'] as $option): ?>
                    <option value="<?= e($option) ?>" <?= $priority === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary">Filtrar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Responsável</th>
                    <th>Prioridade</th>
                    <th>Status</th>
                    <th>Prazo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="fw-bold">#<?= (int) $item['id'] ?></td>
                        <td>
                            <div class="fw-semibold"><?= e($item['title']) ?></div>
                            <div class="small-muted"><?= e($item['requester_name']) ?></div>
                        </td>
                        <td><?= e($item['origin_name']) ?></td>
                        <td><?= e($item['destination_name']) ?></td>
                        <td><?= e($item['responsible_name'] ?: '-') ?></td>
                        <td><span class="badge-priority <?= priority_badge_class($item['priority']) ?>"><?= e($item['priority']) ?></span></td>
                        <td><span class="badge-status <?= status_badge_class($item['status']) ?>"><?= e($item['status']) ?></span></td>
                        <td>
                            <?= e(date('d/m/Y', strtotime($item['due_date']))) ?>
                            <?php $days = days_remaining($item['due_date']); ?>
                            <div class="small-muted"><?= $days >= 0 ? $days . ' dia(s) restantes' : abs($days) . ' dia(s) em atraso' ?></div>
                        </td>
                        <td><a class="btn btn-sm btn-soft" href="demand_view.php?id=<?= (int) $item['id'] ?>">Detalhes</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$items): ?>
                    <tr><td colspan="9" class="text-center py-4 text-secondary">Nenhuma demanda encontrada.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
