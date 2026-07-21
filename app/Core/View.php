<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $_view_file_path = dirname(__DIR__) . '/Views/' . $view . '.php';
        $_layout_file_path = dirname(__DIR__) . '/Views/layouts/' . $layout . '.php';
        extract($data, EXTR_SKIP);
        ob_start();
        require $_view_file_path;
        $content = ob_get_clean();
        require $_layout_file_path;
    }

    public static function partial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/' . $view . '.php';
    }
}

