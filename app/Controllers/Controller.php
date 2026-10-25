<?php

namespace App\Controllers;

class Controller
{
    // Nạp view từ thư mục app/views/ và truyền biến vào
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = ROOTDIR . 'app/views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewFile)) {
            http_response_code(500);
            exit("Không tìm thấy file view: " . htmlspecialchars($viewFile));
        }

        require $viewFile;
        exit();
    }
}
