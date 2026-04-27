<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT d.*, od.name AS origin_name, dd.name AS destination_name, u.name AS responsible_name FROM demands d INNER JOIN departments od ON od.id = d.origin_department_id INNER JOIN departments dd ON dd.id = d.destination_department_id LEFT JOIN users u ON u.id = d.responsible_user_id WHERE d.id = ? LIMIT 1');
$stmt->execute([$id]);
$demand = $stmt->fetch();
if (!$demand) {
    redirect('demands.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? $demand['status'];
    $notes = trim($_POST['notes'] ?? '');
    $completedAt = $status === 'Concluído' ? date('Y-m-d H:i:s') : null;
    $up = db()->prepare('UPDATE demands SET status = ?, completed_at = ?, updated_at = NOW() WHERE id = ?');
    $up->execute([$status, $completedAt, $id]);
    $hist = db()->prepare('INSERT INTO demand_history (demand_id, user_id, action, notes, created_at) VALUES (?, ?, ?, ?, NOW())');
    $hist->execute([$id, current_user()['id'], 'Atualização de status', $notes !== '' ? $notes : 'Status alterado para ' . $status]);
    redirect('demand_view.php?id=' . $id);
}

$history = db()->prepare('SELECT h.*, u.name AS user_name FROM demand_history h INNER JOIN users u ON u.id = h.user_id WHERE h.demand_id = ? ORDER BY h.id DESC');
$history->execute([$id]);
$historyItems = $history->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<div class="panel">
    <div class="filters-bar">
        <div>
            <h2 class="h4 mb-1">Demanda #<?= (int) $demand['id'] ?></h2>
            <div class="small-muted">Painel detalhado da solicitação</div>
        </div>
        <span class="badge-status <?= status_badge_class($demand['status']) ?>"><?= e($demand['status']) ?></span>
    </div>

    <h3 class="fw-bold"><?= e($demand['title']) ?></h3>
    <p class="text-secondary mb-4"><?= nl2br(e($demand['description'])) ?></p>

    <div class="meta-grid mb-4">
        <div class="info-card"><strong>Origem</strong><div><?= e($demand['origin_name']) ?></div></div>
        <div class="info-card"><strong>Destino</strong><div><?= e($demand['destination_name']) ?></div></div>
        <div class="info-card"><strong>Responsável</strong><div><?= e($demand['responsible_name'] ?: '-') ?></div></div>
        <div class="info-card"><strong>Solicitante</strong><div><?= e($demand['requester_name']) ?></div></div>
        <div class="info-card"><strong>Tipo</strong><div><?= e($demand['type']) ?></div></div>
        <div class="info-card"><strong>Prioridade</strong><div><span class="badge-priority <?= priority_badge_class($demand['priority']) ?>"><?= e($demand['priority']) ?></span></div></div>
        <div class="info-card"><strong>Prazo</strong><div><?= e(date('d/m/Y', strtotime($demand['due_date']))) ?></div></div>
        <div class="info-card"><strong>Saldo</strong><div><?= days_remaining($demand['due_date']) >= 0 ? days_remaining($demand['due_date']) . ' dia(s) restantes' : abs(days_remaining($demand['due_date'])) . ' dia(s) de atraso' ?></div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="panel h-100">
            <h3 class="h5 fw-bold mb-3">Atualizar status</h3>
            <form method="post" class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Novo status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['Aberto','Em Andamento','Concluído','Atrasado'] as $status): ?>
                            <option value="<?= e($status) ?>" <?= $demand['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observação</label>
                    <textarea name="notes" rows="5" class="form-control" placeholder="Descreva a atualização realizada"></textarea>
                </div>
                <div class="col-12 d-grid">
                    <button type="submit" class="btn btn-primary">Salvar atualização</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="panel h-100">
            <h3 class="h5 fw-bold mb-3">Histórico da demanda</h3>
            <?php foreach ($historyItems as $item): ?>
                <div class="timeline-item">
                    <div class="fw-semibold"><?= e($item['action']) ?></div>
                    <div class="small-muted mb-1"><?= e($item['user_name']) ?> • <?= e(date('d/m/Y H:i', strtotime($item['created_at']))) ?></div>
                    <div><?= nl2br(e($item['notes'] ?? '')) ?></div>
                </div>
            <?php endforeach; ?>
            <?php if (!$historyItems): ?>
                <div class="text-secondary">Sem histórico registrado.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
