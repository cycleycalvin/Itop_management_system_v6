<?php
require __DIR__ . '/../vendor/autoload.php';
try {
    $d = new \Dompdf\Dompdf();
    echo "Dompdf available. OK\n";
} catch (Throwable $e) {
    echo "Dompdf instantiation failed: " . $e->getMessage() . "\n";
}
