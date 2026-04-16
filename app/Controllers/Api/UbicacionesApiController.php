<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Ubicacion;

final class UbicacionesApiController extends Controller
{
    public function paises(Request $request): Response
    {
        return $this->json((new Ubicacion())->paises());
    }

    public function estados(Request $request): Response
    {
        return $this->json((new Ubicacion())->estados((int) $request->param('paisId')));
    }

    public function municipios(Request $request): Response
    {
        return $this->json((new Ubicacion())->municipios((int) $request->param('estadoId')));
    }

    public function parroquias(Request $request): Response
    {
        return $this->json((new Ubicacion())->parroquias((int) $request->param('municipioId')));
    }
}
