<?php

declare(strict_types=1);

namespace App;

/**
 * Basit URI eşleştirmeli router
 */
class Router
{
    private string $basePath;
    private string $uri;
    private string $method;

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        $path = parse_url($uri, PHP_URL_PATH) ?: '';
        $path = trim(str_replace($this->basePath, '', $path), '/');
        $this->uri = $path === '' ? '/' : '/' . $path;
        $this->uri = rtrim($this->uri, '/') ?: '/';
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Tanımlı rotalara göre eşleşen [controller, method, middleware] döner veya null.
     * Pattern içinde :slug veya :id varsa dinamik eşleşir; yakalanan değer $_GET['_slug'] veya $_GET['_id'] olarak atanır.
     * @param array<string, array{0: string, 1: string, 2?: string[]}> $routes
     */
    public function match(array $routes): ?array
    {
        foreach ($routes as $pattern => $handler) {
            $hasPlaceholder = strpos($pattern, ':slug') !== false || strpos($pattern, ':id') !== false;
            if ($hasPlaceholder) {
                $quoted = preg_quote($pattern, '#');
                $quoted = str_replace(['\:slug', '\:id'], ['([^/]+)', '(\d+)'], $quoted);
                $regex = '#^' . $quoted . '$#';
                if (preg_match($regex, $this->uri, $m)) {
                    array_shift($m);
                    $i = 0;
                    if (preg_match_all('#:slug|:id#', $pattern, $placeholders)) {
                        foreach ($placeholders[0] as $p) {
                            if (isset($m[$i])) {
                                $_GET[$p === ':slug' ? '_slug' : '_id'] = $m[$i];
                                $i++;
                            }
                        }
                    }
                    return is_array($handler) ? $handler : [$handler, 'index', []];
                }
            } else {
                $regex = '#^' . preg_quote($pattern, '#') . '$#';
                if (preg_match($regex, $this->uri)) {
                    return is_array($handler) ? $handler : [$handler, 'index', []];
                }
            }
        }
        return null;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
