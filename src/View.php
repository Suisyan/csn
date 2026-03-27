<?php

declare(strict_types=1);

namespace App;

final class View
{
    public static function render(string $template, array $data = []): string
    {
        $file = Config::get('base_path') . '/templates/' . $template . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException("Template not found: {$template}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;

        return (string) ob_get_clean();
    }
}
