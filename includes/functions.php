<?php
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'Aberto' => 'badge-open',
        'Em Andamento' => 'badge-progress',
        'Concluído' => 'badge-done',
        'Atrasado' => 'badge-late',
        default => 'badge-open',
    };
}

function priority_badge_class(string $priority): string
{
    return match ($priority) {
        'Baixa' => 'badge-soft',
        'Média' => 'badge-info',
        'Alta' => 'badge-warning',
        'Crítica' => 'badge-danger',
        default => 'badge-soft',
    };
}

function demand_is_late(array $demand): bool
{
    return $demand['status'] !== 'Concluído' && !empty($demand['due_date']) && strtotime($demand['due_date']) < strtotime(date('Y-m-d'));
}

function days_remaining(?string $date): ?int
{
    if (!$date) {
        return null;
    }

    $today = new DateTimeImmutable(date('Y-m-d'));
    $due = new DateTimeImmutable($date);
    return (int) $today->diff($due)->format('%r%a');
}

function active_nav(string $file): string
{
    return basename($_SERVER['PHP_SELF'] ?? '') === $file ? 'active' : '';
}
