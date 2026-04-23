<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Direccion;

final class DireccionesApiController extends Controller
{
    public function paises(Request $request): Response
    {
        return $this->json((new Direccion())->paises());
    }

    public function estados(Request $request): Response
    {
        return $this->json((new Direccion())->estados((int) $request->param('paisId')));
    }

    public function municipios(Request $request): Response
    {
        return $this->json((new Direccion())->municipios((int) $request->param('estadoId')));
    }

    public function parroquias(Request $request): Response
    {
        return $this->json((new Direccion())->parroquias((int) $request->param('municipioId')));
    }
}
