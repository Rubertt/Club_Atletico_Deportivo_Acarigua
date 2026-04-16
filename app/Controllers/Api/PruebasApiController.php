<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\ResultadoPruebas;

final class PruebasApiController extends Controller
{
    public function historial(Request $request): Response
    {
        $id = (int) $request->param('id');
        return $this->json((new ResultadoPruebas())->historial($id));
    }
}
