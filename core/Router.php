<?php

/**
 * Router sederhana.
 *
 * Mencocokkan method HTTP + URI ke pasangan [Controller, method],
 * mendukung parameter dinamis seperti /tickets/{id}.
 */
class Router
{
    private $routes = [];

    /**
     * Mendaftarkan route untuk method GET.
     *
     * @param string $path Pattern URI, boleh mengandung {param}
     * @param array $callback [NamaController, namaMethod]
     */
    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Mendaftarkan route untuk method POST.
     *
     * @param string $path Pattern URI, boleh mengandung {param}
     * @param array $callback [NamaController, namaMethod]
     */
    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Mencocokkan request saat ini ke route yang terdaftar,
     * lalu memanggil controller & method yang sesuai.
     */
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace(BASE_PATH, '', $uri);
        $uri = str_replace('/index.php', '', $uri);
        $uri = $uri === '' ? '/' : rtrim($uri, '/');
        $uri = $uri === '' ? '/' : $uri;

        if ($uri === '/') {
            header('Location: ' . url('/login'));
            exit;
        }

        if ($method === 'POST' && !verifyCsrf()) {
            http_response_code(419);
            echo 'Sesi form sudah tidak valid (CSRF token tidak cocok). Silakan kembali dan coba lagi.';
            return;
        }

        foreach ($this->routes[$method] ?? [] as $pattern => $callback) {
            $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';

            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                [$controllerName, $action] = $callback;
                $controller = new $controllerName();
                $controller->$action(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        echo '404 - Halaman tidak ditemukan';
    }
}
