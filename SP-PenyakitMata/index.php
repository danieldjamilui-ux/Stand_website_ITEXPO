<?php
// Redirect ke folder view
header('Location: view/index.php');
exit();
$router->get('/aturan', [AturanController::class, 'index']);