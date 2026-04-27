<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

$statuses = ['Aberto', 'Em Andamento', 'Concluído', 'Atrasado'];
$grouped = [];
foreach ($statuses as $status) {
    $stmt = db()->prepare('SELECT d.*, od.name AS origin_name, dd.name AS destination_name FROM demands d INNER JOIN departments od ON od.id = d.origin_department_id INNER JOIN departments dd ON dd.id = d.destination_department_id WHERE d.status = ? ORDER BY d.due_date ASC, d.id DESC');
    $stmt->execute([$status]);
    $grouped[$status] = $stmt->fetchAll();
}
require_once __DIR__ . '/../includes/header.php';
?>
<div class="filters-bar mb-3">
    <div>
        <h2 class="h4 mb-1 text-white">Kanban operacional</h2>
        <div class="page-subtitle">Acompanhamento visual do fluxo entre sede e obras</div>
    </div>
    <a href="demand_form.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Nova demanda</a>
</div>
<div class="kanban">
    <?php foreach ($statuses as $status): ?>
        <div class="kanban-column">
            <div class="kanban-head">
                <h3 class="kanban-title"><?= e($status) ?></h3>
                <span class="kanban-count"><?= count($grouped[$status]) ?></span>
            </div>
            <?php foreach ($grouped[$status] as $item): ?>
                <a class="kanban-card" href="demand_view.php?id=<?= (int) $item['id'] ?>">
                    <span class="title">#<?= (int) $item['id'] ?> • <?= e($item['title']) ?></span>
                    <span class="badge-priority <?= priority_badge_class($item['priority']) ?> mb-2"><?= e($item['priority']) ?></span>
                    <div class="meta">
                        <span><i class="bi bi-send"></i> <?= e($item['origin_name']) ?> → <?= e($item['destination_name']) ?></span>
                        <span><i class="bi bi-calendar-event"></i> <?= e(date('d/m/Y', strtotime($item['due_date']))) ?></span>
                        <span><i class="bi bi-tag"></i> <?= e($item['type']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if (!$grouped[$status]): ?>
                <div class="small-muted">Sem itens nesta etapa.</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
