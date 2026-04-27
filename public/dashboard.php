<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/functions.php';

$total = (int) db()->query('SELECT COUNT(*) FROM demands')->fetchColumn();
$open = (int) db()->query("SELECT COUNT(*) FROM demands WHERE status = 'Aberto'")->fetchColumn();
$progress = (int) db()->query("SELECT COUNT(*) FROM demands WHERE status = 'Em Andamento'")->fetchColumn();
$done = (int) db()->query("SELECT COUNT(*) FROM demands WHERE status = 'Concluído'")->fetchColumn();
$late = (int) db()->query("SELECT COUNT(*) FROM demands WHERE status = 'Atrasado' OR (status <> 'Concluído' AND due_date < CURDATE())")->fetchColumn();
$critical = (int) db()->query("SELECT COUNT(*) FROM demands WHERE priority = 'Crítica'")->fetchColumn();
$slaRate = $total > 0 ? round((($total - $late) / $total) * 100) : 100;

$recent = db()->query('SELECT d.*, od.name AS origin_name, dd.name AS destination_name, u.name AS responsible_name FROM demands d INNER JOIN departments od ON od.id = d.origin_department_id INNER JOIN departments dd ON dd.id = d.destination_department_id LEFT JOIN users u ON u.id = d.responsible_user_id ORDER BY d.id DESC LIMIT 8')->fetchAll();

$statusRows = db()->query("SELECT status, COUNT(*) total FROM demands GROUP BY status ORDER BY total DESC")->fetchAll();
$statusLabels = [];
$statusValues = [];
foreach ($statusRows as $row) {
    $statusLabels[] = $row['status'];
    $statusValues[] = (int) $row['total'];
}

$priorityRows = db()->query("SELECT priority, COUNT(*) total FROM demands GROUP BY priority ORDER BY FIELD(priority, 'Baixa','Média','Alta','Crítica')")->fetchAll();
$priorityLabels = [];
$priorityValues = [];
foreach ($priorityRows as $row) {
    $priorityLabels[] = $row['priority'];
    $priorityValues[] = (int) $row['total'];
}

$departmentRows = db()->query('SELECT dd.name AS destination_name, COUNT(*) total FROM demands d INNER JOIN departments dd ON dd.id = d.destination_department_id GROUP BY dd.name ORDER BY total DESC LIMIT 6')->fetchAll();
$departmentLabels = [];
$departmentValues = [];
foreach ($departmentRows as $row) {
    $departmentLabels[] = $row['destination_name'];
    $departmentValues[] = (int) $row['total'];
}

$monthlyRows = db()->query("SELECT YEAR(created_at) AS year_num, MONTH(created_at) AS month_num, DATE_FORMAT(MIN(created_at), '%m/%Y') AS month_label, COUNT(*) total FROM demands GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY year_num, month_num")->fetchAll();
$monthLabels = [];
$monthValues = [];
foreach ($monthlyRows as $row) {
    $monthLabels[] = $row['month_label'];
    $monthValues[] = (int) $row['total'];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="metric-grid">
    <div class="glass-card metric-card">
        <div class="metric-icon icon-primary"><i class="bi bi-list-task"></i></div>
        <div class="metric-label">Demandas Totais</div>
        <div class="metric-value"><?= $total ?></div>
        <div class="metric-trend">Visão consolidada da operação</div>
    </div>
    <div class="glass-card metric-card">
        <div class="metric-icon icon-warning"><i class="bi bi-hourglass-split"></i></div>
        <div class="metric-label">Em Andamento</div>
        <div class="metric-value"><?= $progress ?></div>
        <div class="metric-trend">Fluxos ativos no momento</div>
    </div>
    <div class="glass-card metric-card">
        <div class="metric-icon icon-success"><i class="bi bi-check2-circle"></i></div>
        <div class="metric-label">Concluídas</div>
        <div class="metric-value"><?= $done ?></div>
        <div class="metric-trend">Itens finalizados com sucesso</div>
    </div>
    <div class="glass-card metric-card">
        <div class="metric-icon icon-danger"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="metric-label">Atrasadas</div>
        <div class="metric-value"><?= $late ?></div>
        <div class="metric-trend">Demandas fora do prazo</div>
    </div>
    <div class="glass-card metric-card">
        <div class="metric-icon icon-purple"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="metric-label">SLA Cumprido</div>
        <div class="metric-value"><?= $slaRate ?>%</div>
        <div class="metric-trend">Baseado no volume atual</div>
    </div>
</div>

<div class="chart-grid">
    <div class="glass-card chart-card">
        <div class="chart-title">Evolução de demandas</div>
        <div class="chart-subtitle">Acompanhamento mensal do volume cadastrado</div>
        <div class="chart-wrap"><canvas id="volumeChart"></canvas></div>
    </div>
    <div class="glass-card chart-card">
        <div class="chart-title">Distribuição por status</div>
        <div class="chart-subtitle">Panorama operacional do workflow</div>
        <div class="chart-wrap small"><canvas id="statusChart"></canvas></div>
    </div>
</div>

<div class="chart-grid">
    <div class="glass-card chart-card">
        <div class="chart-title">Demanda por setor de destino</div>
        <div class="chart-subtitle">Quais áreas recebem mais solicitações</div>
        <div class="chart-wrap small"><canvas id="departmentChart"></canvas></div>
    </div>
    <div class="glass-card chart-card">
        <div class="chart-title">Criticidade por prioridade</div>
        <div class="chart-subtitle">Leitura rápida para tomada de decisão</div>
        <div class="chart-wrap small"><canvas id="priorityChart"></canvas></div>

        <div class="kpi-strip">
            <div class="kpi-box"><strong><?= $open ?></strong><span>Abertas</span></div>
            <div class="kpi-box"><strong><?= $critical ?></strong><span>Críticas</span></div>
            <div class="kpi-box"><strong><?= max($total - $done, 0) ?></strong><span>Pendentes</span></div>
            <div class="kpi-box"><strong><?= $slaRate ?>%</strong><span>Eficiência atual</span></div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="filters-bar">
        <div>
            <h2 class="h4 mb-1">Últimas demandas</h2>
            <div class="small-muted">Tabela analítica com prazo, responsável e prioridade</div>
        </div>
        <a href="demand_form.php" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Nova demanda</a>
    </div>

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
                <?php foreach ($recent as $item): ?>
                    <tr>
                        <td class="fw-bold">#<?= (int) $item['id'] ?></td>
                        <td>
                            <div class="fw-semibold"><?= e($item['title']) ?></div>
                            <div class="small-muted"><?= e($item['type']) ?></div>
                        </td>
                        <td><?= e($item['origin_name']) ?></td>
                        <td><?= e($item['destination_name']) ?></td>
                        <td><?= e($item['responsible_name'] ?: '-') ?></td>
                        <td><span class="badge-priority <?= priority_badge_class($item['priority']) ?>"><?= e($item['priority']) ?></span></td>
                        <td><span class="badge-status <?= status_badge_class($item['status']) ?>"><?= e($item['status']) ?></span></td>
                        <td>
                            <?= e(date('d/m/Y', strtotime($item['due_date']))) ?>
                            <?php if (demand_is_late($item)): ?><div class="small text-danger fw-semibold">Atrasada</div><?php endif; ?>
                        </td>
                        <td><a class="btn btn-sm btn-soft" href="demand_view.php?id=<?= (int) $item['id'] ?>">Abrir</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { labels: { usePointStyle: true, boxWidth: 8 } } },
    scales: {
        x: { grid: { display: false } },
        y: { beginAtZero: true, ticks: { precision: 0 } }
    }
};

new Chart(document.getElementById('volumeChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($monthLabels, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            label: 'Demandas',
            data: <?= json_encode($monthValues) ?>,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.16)',
            tension: 0.35,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#2563eb'
        }]
    },
    options: commonOptions
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($statusLabels, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            data: <?= json_encode($statusValues) ?>,
            backgroundColor: ['#3b82f6', '#f59e0b', '#22c55e', '#ef4444'],
            borderWidth: 0
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%' }
});

new Chart(document.getElementById('departmentChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($departmentLabels, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            label: 'Demandas',
            data: <?= json_encode($departmentValues) ?>,
            backgroundColor: '#7c3aed',
            borderRadius: 12
        }]
    },
    options: commonOptions
});

new Chart(document.getElementById('priorityChart'), {
    type: 'polarArea',
    data: {
        labels: <?= json_encode($priorityLabels, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [{
            data: <?= json_encode($priorityValues) ?>,
            backgroundColor: ['rgba(148,163,184,.85)','rgba(14,165,233,.85)','rgba(245,158,11,.85)','rgba(239,68,68,.85)'],
            borderWidth: 0
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { r: { ticks: { precision: 0 } } } }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
