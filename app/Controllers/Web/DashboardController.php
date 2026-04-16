<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::connection();

        $atletas   = (int) $db->query('SELECT COUNT(*) FROM atletas')->fetchColumn();
        $activos   = (int) $db->query("SELECT COUNT(*) FROM atletas WHERE estatus = 'Activo'")->fetchColumn();
        $categorias = (int) $db->query("SELECT COUNT(*) FROM categoria WHERE estatus = 'Activa'")->fetchColumn();
        $plantel    = (int) $db->query('SELECT COUNT(*) FROM plantel')->fetchColumn();

        return $this->view('dashboard.index', [
            'title'      => 'Inicio',
            'active'     => 'inicio',
            'breadcrumb' => ['Inicio'],
            'stats'      => compact('atletas', 'activos', 'categorias', 'plantel'),
        ], 'admin');
    }
}
