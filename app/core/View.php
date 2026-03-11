<?php
class View
{
    public function render($view, $data = [])
    {
        extract($data, EXTR_SKIP);
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            http_response_code(404);
            echo 'View not found';
            return;
        }
        $layout = isset($layout) ? $layout : 'main';
        require __DIR__ . '/../views/layouts/' . $layout . '.php';
    }
}
