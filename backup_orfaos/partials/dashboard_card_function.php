<?php
function renderDashboardCard(array $params) {
    extract($params); // Cria as variáveis a partir da array

    include __DIR__ . '/dashboard_card.php';
}

