<?php
file_put_contents('/tmp/access.log', date('Y-m-d H:i:s') . " ROOT - " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n", FILE_APPEND);

require 'vendor/autoload.php';

// Load configuration
require 'config/config.php';

use App\Core\App;

session_start();

$app = new App();
