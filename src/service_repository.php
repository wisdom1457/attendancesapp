<?php

require_once __DIR__ . '/db.php';

function fetch_services(): array
{
    $pdo = get_db();
    $stmt = $pdo->query('SELECT id, name, price, updated_at FROM services ORDER BY name ASC');

    return $stmt->fetchAll() ?: [];
}

function update_service_price(int $serviceId, float $price): void
{
    $pdo = get_db();
    $stmt = $pdo->prepare('UPDATE services SET price = :price, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute([
        'price' => $price,
        'id' => $serviceId,
    ]);
}

function fetch_total_expenses(): float
{
    $pdo = get_db();
    $stmt = $pdo->query('SELECT SUM(amount) as total FROM expenses');
    $total = $stmt->fetchColumn();

    return $total ? (float) $total : 0.0;
}

function fetch_recent_expenses(int $limit = 5): array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT e.id, e.amount, e.description, e.expense_date, s.name as service_name
        FROM expenses e
        INNER JOIN services s ON e.service_id = s.id
        ORDER BY e.expense_date DESC, e.id DESC
        LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll() ?: [];
}

