<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(1);
if (!$user) {
    echo "User 1 not found.\n";
    exit;
}

echo "Migrating Emergency Fund for User: " . $user->name . "\n";

$service = app(App\Services\EmergencyFundService::class);

try {
    // Initialize with 5,000,000 expense base, 6 months target = 30,000,000 target
    $fund = $service->initialize($user, 5000000, 6);
    echo "Emergency Fund Initialized/Updated:\n";
    echo "Target: " . number_format($fund->target_amount) . "\n";
    echo "Current: " . number_format($fund->current_amount) . "\n";
    echo "Progress: " . $fund->progress_percentage . "%\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
