<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$servicio = App\Models\Servicio::first();
if ($servicio) {
    try {
        echo "Categoria relationship loaded: " . ($servicio->Categoria ? $servicio->Categoria->nombre : 'NULL') . "\n";
    } catch (\Exception $e) {
        echo "Relation Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No service found.\n";
}
