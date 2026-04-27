<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
$app = config()['app'];
$user = current_user();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($app['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>

    <aside class="sidebar-glass" id="appSidebar">
        <div>
            <div class="sidebar-mobile-head d-lg-none">
                <div class="brand-wrap mb-0">
                    <div class="brand-icon"><i class="bi bi-buildings"></i></div>
                    <div>
                        <div class="brand-title">Workflow Pro</div>
                        <div class="brand-subtitle">Sede x Obras</div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-light sidebar-close-btn" type="button" data-sidebar-close>
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="brand-wrap mb-4 desktop-brand">
                <div class="brand-icon"><i class="bi bi-buildings"></i></div>
                <div>
                    <div class="brand-title">Workflow Pro</div>
                    <div class="brand-subtitle">Sede x Obras</div>
                </div>
            </div>

            <div class="user-card mb-4">
                <div class="avatar-circle"><?= strtoupper(substr((string)($user['name'] ?? 'U'), 0, 1)) ?></div>
                <div>
                    <div class="fw-semibold"><?= e($user['name'] ?? '') ?></div>
                    <div class="text-white-50 small"><?= e($user['role'] ?? 'Usuário') ?></div>
                </div>
            </div>

            <nav class="nav flex-column nav-pills gap-2 sidebar-nav">
                <a class="nav-link <?= active_nav('dashboard.php') ?>" href="dashboard.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                <a class="nav-link <?= active_nav('demands.php') ?>" href="demands.php"><i class="bi bi-list-task"></i> Demandas</a>
                <a class="nav-link <?= active_nav('demand_form.php') ?>" href="demand_form.php"><i class="bi bi-plus-circle-fill"></i> Nova Demanda</a>
                <a class="nav-link <?= active_nav('kanban.php') ?>" href="kanban.php"><i class="bi bi-kanban-fill"></i> Kanban</a>
                <a class="nav-link <?= active_nav('users.php') ?>" href="users.php"><i class="bi bi-people-fill"></i> Usuários</a>
                <a class="nav-link <?= active_nav('departments.php') ?>" href="departments.php"><i class="bi bi-diagram-3-fill"></i> Setores</a>
            </nav>
        </div>

        <a class="btn btn-outline-light w-100 rounded-4" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a>
    </aside>

    <main class="content-area">
        <div class="mobile-topbar d-lg-none">
            <button class="btn mobile-menu-btn" type="button" data-sidebar-open>
                <i class="bi bi-list"></i>
            </button>
            <div>
                <div class="mobile-title">Workflow Pro</div>
                <div class="mobile-subtitle">Sede x Obras</div>
            </div>
        </div>

        <div class="topbar">
            <div>
                <h1 class="page-title mb-1"><?= e($app['name']) ?></h1>
                <p class="page-subtitle mb-0">Gestão inteligente de demandas, SLA e produtividade operacional.</p>
            </div>
            <div class="topbar-chip">
                <i class="bi bi-activity"></i>
                Ambiente demonstrativo
            </div>
        </div>
