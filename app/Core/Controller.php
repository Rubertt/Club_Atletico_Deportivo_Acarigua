<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * Renderiza una vista dentro del layout indicado.
     */
    protected function view(string $view, array $data = [], ?string $layout = null): Response
    {
        $content = $this->renderView($view, $data);

        if ($layout !== null) {
            $data['_content'] = $content;
            $content = $this->renderView("layouts.$layout", $data);
        }

        return Response::html($content);
    }

    /**
     * Renderiza una vista sin layout y devuelve su HTML.
     */
    protected function renderView(string $view, array $data = []): string
    {
        $path = view_path($view);
        if (!is_file($path)) {
            throw new \RuntimeException("Vista no encontrada: $view ($path)");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $to, int $status = 302): Response
    {
        return Response::redirect($to, $status);
    }

    protected function back(): Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return Response::redirect($referer);
    }

    /**
     * Guarda los inputs actuales en sesión para mostrarlos tras redirect (método old()).
     */
    protected function withOld(array $input): static
    {
        $_SESSION['_old'] = $input;
        return $this;
    }

    /**
     * Guarda errores de validación en sesión.
     */
    protected function withErrors(array $errors): static
    {
        $_SESSION['_errors'] = $errors;
        return $this;
    }

    protected function flash(string $key, string $message): static
    {
        $_SESSION['_flash'][$key] = $message;
        return $this;
    }
}
