<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\AsigCategoria;
use App\Models\Categoria;
use App\Models\Atleta;
use App\Models\PosicionJuego;
use Throwable;

final class AsigCategoriasController extends Controller
{
    /**
     * Muestra la lista de atletas asignados a una categoría.
     */
    public function index(Request $request): Response
    {
        $categoriaId = (int) $request->param('id');
        $categoriaModel = new Categoria();
        $categoria = $categoriaModel->find($categoriaId);

        if (!$categoria) {
            flash('error', 'Categoría no encontrada.');
            return $this->redirect('/admin/categorias');
        }

        $asigModel = new AsigCategoria();
        $atletasAsignados = $asigModel->assignedAthletes($categoriaId);

        return $this->view('asig_categorias.index', [
            'title' => 'Detalles de Categoría: ' . $categoria['nombre_categoria'],
            'active' => 'categorias',
            'breadcrumb' => [
                'Inicio',
                ['label' => 'Categorías', 'url' => url('/admin/categorias')],
                $categoria['nombre_categoria']
            ],
            'categoria' => $categoria,
            'atletas' => $atletasAsignados,
        ], 'admin');
    }

    /**
     * Muestra la lista de atletas candidatos aptos para ser asignados de forma masiva.
     */
    public function create(Request $request): Response
    {
        $categoriaId = (int) $request->param('id');
        $categoriaModel = new Categoria();
        $categoria = $categoriaModel->find($categoriaId);

        if (!$categoria) {
            flash('error', 'Categoría no encontrada.');
            return $this->redirect('/admin/categorias');
        }

        // Obtener todos los atletas que no pertenezcan a ninguna categoría actualmente
        $db = \App\Core\Database::connection();
        $sql = "SELECT a.* FROM atletas a 
                WHERE a.atleta_id NOT IN (SELECT atleta_id FROM asig_categorias)";
        $atletasRaw = $db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        // Filtrar en PHP según la edad y género de la categoría destino
        $atletasAptos = [];
        $hoy = new \DateTime();
        $sexoCat = $categoria['sexo_categoria'];
        $edadMin = (int) $categoria['edad_min'];
        $edadMax = (int) $categoria['edad_max'];

        foreach ($atletasRaw as $a) {
            $nac = new \DateTime($a['fecha_nac']);
            $edad = $hoy->diff($nac)->y;

            // Filtro de edad
            if ($edad < $edadMin || $edad > $edadMax) {
                continue;
            }

            // Filtro de género (si es 'X' califica cualquiera)
            if ($sexoCat !== 'X' && $a['sexo'] !== $sexoCat) {
                continue;
            }

            $a['edad'] = $edad;
            $atletasAptos[] = $a;
        }

        $posicionesModel = new PosicionJuego();
        $posiciones = $posicionesModel->all('nombre_posicion');

        return $this->view('asig_categorias.form_assign', [
            'title' => 'Asignar Atletas a ' . $categoria['nombre_categoria'],
            'active' => 'categorias',
            'breadcrumb' => [
                'Inicio',
                ['label' => 'Categorías', 'url' => url('/admin/categorias')],
                ['label' => $categoria['nombre_categoria'], 'url' => url("/admin/categorias/{$categoria['categoria_id']}/detalles")],
                'Asignar'
            ],
            'categoria' => $categoria,
            'atletas' => $atletasAptos,
            'posiciones' => $posiciones,
            'action' => url("/admin/categorias/{$categoria['categoria_id']}/asignar"),
        ], 'admin');
    }

    /**
     * Procesa la asignación masiva de atletas a una categoría.
     */
    public function store(Request $request): Response
    {
        $categoriaId = (int) $request->param('id');
        $categoriaModel = new Categoria();
        $categoria = $categoriaModel->find($categoriaId);

        if (!$categoria) {
            if ($request->isAjax() || $request->isJson()) {
                return $this->json(['success' => false, 'message' => 'Categoría no encontrada.'], 404);
            }
            flash('error', 'Categoría no encontrada.');
            return $this->redirect('/admin/categorias');
        }

        $selected = $request->input('selected_atletas', []);
        if (empty($selected)) {
            if ($request->isAjax() || $request->isJson()) {
                return $this->json(['success' => false, 'message' => 'Debe seleccionar al menos un atleta de la lista para proceder con la asignación.'], 400);
            }
            flash('error', 'Debe seleccionar al menos un atleta de la lista para proceder con la asignación.');
            return $this->redirect("/admin/categorias/$categoriaId/asignar");
        }

        $asigModel = new AsigCategoria();
        $errors = [];

        $posicionesPrincipal = $request->input('posicion_principal_id', []);
        $posicionesSecundaria = $request->input('posicion_secundaria_id', []);
        $dorsales = $request->input('nun_dorsal', []);

        // Validar que los atletas no estén suspendidos o inactivos
        foreach ($selected as $atletaId) {
            $atletaObj = (new Atleta())->findCompleto((int) $atletaId);
            if (!$atletaObj || in_array((int)$atletaObj['estatus'], [0, 3], true)) {
                $nombre = $atletaObj ? ($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) : "Atleta ID $atletaId";
                $errors[] = "El atleta $nombre no puede ser asignado porque está inactivo o suspendido.";
            }
        }
        $dorsalesEnviados = [];

        // Validar dorsales duplicados
        foreach ($selected as $atletaId) {
            $atletaId = (int) $atletaId;
            $dorsalVal = isset($dorsales[$atletaId]) && $dorsales[$atletaId] !== '' ? (int) $dorsales[$atletaId] : null;

            if ($dorsalVal !== null) {
                // Duplicado en el mismo envío
                if (in_array($dorsalVal, $dorsalesEnviados, true)) {
                    $errors[] = "El dorsal $dorsalVal está asignado a múltiples atletas en el formulario.";
                } else {
                    $dorsalesEnviados[] = $dorsalVal;
                }

                // Duplicado en base de datos para esta categoría
                if ($asigModel->checkDorsalExists($categoriaId, $dorsalVal)) {
                    $atletaObj = (new Atleta())->find($atletaId);
                    $atletaNombre = $atletaObj ? ($atletaObj['nombre'] . ' ' . $atletaObj['apellido']) : 'atleta';
                    $errors[] = "El dorsal $dorsalVal ya está ocupado por otro jugador en esta categoría (al intentar asignárselo a $atletaNombre).";
                }
            }
        }

        if (!empty($errors)) {
            if ($request->isAjax() || $request->isJson()) {
                return $this->json([
                    'success' => false,
                    'message' => implode("\n", array_unique($errors)),
                    'errors' => ['dorsales' => array_unique($errors)]
                ], 400);
            }
            $this->withOld($request->input())->withErrors(['dorsales' => implode('<br>', array_unique($errors))]);
            return $this->redirect("/admin/categorias/$categoriaId/asignar");
        }

        \App\Core\Database::beginTransaction();
        try {
            foreach ($selected as $atletaId) {
                $atletaId = (int) $atletaId;
                $posPrincipalId = isset($posicionesPrincipal[$atletaId]) && $posicionesPrincipal[$atletaId] !== '' ? (int) $posicionesPrincipal[$atletaId] : null;
                $posSecundariaId = isset($posicionesSecundaria[$atletaId]) && $posicionesSecundaria[$atletaId] !== '' ? (int) $posicionesSecundaria[$atletaId] : null;
                $dorsalVal = isset($dorsales[$atletaId]) && $dorsales[$atletaId] !== '' ? (int) $dorsales[$atletaId] : null;

                $asigModel->insert([
                    'categoria_id' => $categoriaId,
                    'atleta_id' => $atletaId,
                    'posicion_principal_id' => $posPrincipalId,
                    'posicion_secundaria_id' => $posSecundariaId,
                    'nun_dorsal' => $dorsalVal,
                ]);
            }
            \App\Core\Database::commit();
            if ($request->isAjax() || $request->isJson()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Atletas asignados correctamente.',
                    'redirect' => url("/admin/categorias/$categoriaId/detalles")
                ]);
            }
            flash('success', 'Atletas asignados correctamente.');
        } catch (Throwable $e) {
            \App\Core\Database::rollBack();
            if ($request->isAjax() || $request->isJson()) {
                return $this->json(['success' => false, 'message' => 'Ocurrió un error al guardar las asignaciones: ' . $e->getMessage()], 500);
            }
            flash('error', 'Ocurrió un error al guardar las asignaciones: ' . $e->getMessage());
            return $this->redirect("/admin/categorias/$categoriaId/asignar");
        }

        return $this->redirect("/admin/categorias/$categoriaId/detalles");
    }

    /**
     * Muestra el formulario de edición para una sola asignación.
     */
    public function edit(Request $request): Response
    {
        $asignacionId = (int) $request->param('id');
        $asigModel = new AsigCategoria();
        $asignacion = $asigModel->find($asignacionId);

        if (!$asignacion) {
            flash('error', 'Asignación no encontrada.');
            return $this->redirect('/admin/categorias');
        }

        $categoria = (new Categoria())->find((int) $asignacion['categoria_id']);
        $atleta = (new Atleta())->find((int) $asignacion['atleta_id']);
        $posiciones = (new PosicionJuego())->all('nombre_posicion');

        return $this->view('asig_categorias.form_edit', [
            'title' => 'Editar Asignación de ' . $atleta['nombre'],
            'active' => 'categorias',
            'breadcrumb' => [
                'Inicio',
                ['label' => 'Categorías', 'url' => url('/admin/categorias')],
                ['label' => $categoria['nombre_categoria'], 'url' => url("/admin/categorias/{$categoria['categoria_id']}/detalles")],
                'Editar Asignación'
            ],
            'item' => $asignacion,
            'categoria' => $categoria,
            'atleta' => $atleta,
            'posiciones' => $posiciones,
            'action' => url("/admin/asig-categorias/{$asignacionId}/editar"),
        ], 'admin');
    }

    /**
     * Procesa la actualización de una asignación individual.
     */
    public function update(Request $request): Response
    {
        $asignacionId = (int) $request->param('id');
        $asigModel = new AsigCategoria();
        $asignacion = $asigModel->find($asignacionId);

        if (!$asignacion) {
            if ($request->isAjax() || $request->isJson()) {
                return $this->json(['success' => false, 'message' => 'Asignación no encontrada.'], 404);
            }
            flash('error', 'Asignación no encontrada.');
            return $this->redirect('/admin/categorias');
        }

        $dorsalVal = $request->input('nun_dorsal') !== '' ? (int) $request->input('nun_dorsal') : null;
        $categoriaId = (int) $asignacion['categoria_id'];

        if ($dorsalVal !== null) {
            if ($asigModel->checkDorsalExists($categoriaId, $dorsalVal, $asignacionId)) {
                if ($request->isAjax() || $request->isJson()) {
                    return $this->json([
                        'success' => false,
                        'message' => "El dorsal $dorsalVal ya está ocupado por otro jugador en esta categoría.",
                        'errors' => ['nun_dorsal' => "El dorsal $dorsalVal ya está ocupado por otro jugador en esta categoría."]
                    ], 400);
                }
                $this->withOld($request->input())->withErrors(['nun_dorsal' => "El dorsal $dorsalVal ya está ocupado por otro jugador en esta categoría."]);
                return $this->redirect("/admin/asig-categorias/{$asignacionId}/editar");
            }
        }

        try {
            $asigModel->update($asignacionId, [
                'posicion_principal_id' => ($request->input('posicion_principal_id') !== null && $request->input('posicion_principal_id') !== '') ? (int) $request->input('posicion_principal_id') : null,
                'posicion_secundaria_id' => ($request->input('posicion_secundaria_id') !== null && $request->input('posicion_secundaria_id') !== '') ? (int) $request->input('posicion_secundaria_id') : null,
                'nun_dorsal' => $dorsalVal,
            ]);
            if ($request->isAjax() || $request->isJson()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Asignación actualizada correctamente.',
                    'redirect' => url("/admin/categorias/$categoriaId/detalles")
                ]);
            }
            flash('success', 'Asignación actualizada correctamente.');
        } catch (Throwable $e) {
            if ($request->isAjax() || $request->isJson()) {
                return $this->json(['success' => false, 'message' => 'Error al guardar los cambios: ' . $e->getMessage()], 500);
            }
            flash('error', 'Error al guardar los cambios: ' . $e->getMessage());
            return $this->redirect("/admin/asig-categorias/{$asignacionId}/editar");
        }

        return $this->redirect("/admin/categorias/$categoriaId/detalles");
    }

    /**
     * Elimina (desasigna) un atleta de una categoría.
     */
    public function destroy(Request $request): Response
    {
        $asignacionId = (int) $request->param('id');
        $asigModel = new AsigCategoria();
        $asignacion = $asigModel->find($asignacionId);

        if (!$asignacion) {
            flash('error', 'Asignación no encontrada.');
            return $this->redirect('/admin/categorias');
        }

        $categoriaId = (int) $asignacion['categoria_id'];

        try {
            $asigModel->delete($asignacionId);
            flash('success', 'El atleta ha sido desasignado de la categoría.');
        } catch (Throwable $e) {
            flash('error', 'No se pudo retirar el atleta de la categoría: ' . $e->getMessage());
        }

        return $this->redirect("/admin/categorias/$categoriaId/detalles");
    }
}
