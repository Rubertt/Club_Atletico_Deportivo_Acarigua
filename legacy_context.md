# Club Atlético Deportivo Acarigua (CADA) - Legacy Context & Architecture Guide

Este archivo contiene la documentación completa de arquitectura, convenciones de diseño, base de datos, estándares de código y el historial de cambios recientes del proyecto **Club Atlético Deportivo Acarigua (CADA)**. Ha sido diseñado para servir como "memoria del desarrollador" y dar un contexto técnico completo y detallado a cualquier agente de IA o desarrollador que trabaje en esta aplicación.

---

## 1. Arquitectura General del Sistema (MVC Nativo)

El sistema está construido sobre un framework **MVC nativo en PHP 8+**, sin utilizar frameworks comerciales (como Laravel, Symfony o CodeIgniter). 

### 1.1. Mapa de Directorios Clave
*   **`app/`**: Contiene la lógica del sistema.
    *   `Core/`: Clases fundamentales del framework (`Router`, `Request`, `Response`, `Controller`, `Model`, `Database`, `Auth`, `Validator`, `JWT`). No deben modificarse a menos que se trate de cambios estructurales en el framework.
    *   `Controllers/`:
        *   `Web/`: Controladores que manejan peticiones del navegador y renderizan vistas HTML completas usando layouts.
        *   `Api/`: Controladores REST que retornan respuestas JSON puras (utilizadas para selects dinámicos, gráficos y llamadas asíncronas).
    *   `Models/`: Clases que representan tablas de la base de datos, extendiendo de `App\Core\Model` y encapsulando consultas directas con PDO.
    *   `Helpers/`:
        *   `constants.php`: Definición de constantes del dominio (roles, estatus, tipos de actividad, etc.).
        *   `functions.php`: Helpers y funciones globales disponibles en toda la aplicación.
    *   `Middleware/`: Clases que interceptan el ciclo de vida de la petición (`AuthMiddleware`, `RoleMiddleware`, `CsrfMiddleware`).
    *   `Views/`: Vistas nativas `.php`.
        *   `layouts/`: Plantillas maestras de diseño (`admin.php` para panel de administración, `public.php` para landing page).
        *   `partials/`: Fragmentos HTML reutilizables (`sidebar.php`, `flash.php`, widgets).
    *   `bootstrap.php`: Inicialización de la aplicación, carga del autoloader, procesamiento del archivo de configuración `.env` y configuración del entorno global (timezones, cookies de sesión, etc.).
*   **`config/`**: Archivos de configuración centralizada (`app.php`, `auth.php`, `database.php`, `routes.php`).
*   **`public/`**: Directorio raíz web visible públicamente.
    *   `index.php`: Front Controller de la aplicación, intercepta y despacha todas las peticiones a través del `Router`.
    *   `assets/`: Archivos estáticos (`css/`, `js/`, `fonts/`, `img/`).
*   **`database/`**: Scripts SQL de migración y semillas, instalación de base de datos (`install.php`) y volcados limpios.
*   **`storage/`**: Almacenamiento de archivos locales y logs de errores de PHP.

---

## 2. Enrutamiento y Rutas del Sistema (config/routes.php)

El enrutamiento está centralizado en [routes.php](file:///c:/Users/Estudio/Documents/proyectos%20codex/Club_Atletico_Deportivo_Acarigua/config/routes.php). Soporta agrupamiento (`group`), middlewares encadenados y mapeo dinámico de parámetros mediante expresiones del tipo `{id}` o `{estadoId}`.

### 2.1. Catálogo de Rutas
A continuación se detallan las rutas principales del sistema, sus controladores y los middlewares requeridos:

| Ruta | Método | Controlador y Acción | Middlewares | Descripción |
| :--- | :--- | :--- | :--- | :--- |
| `/` | `GET` | `HomeController::index` | Ninguno | Página de inicio del club |
| `/login` | `GET`/`POST` | `AuthController::showLogin` / `login` | `CsrfMiddleware` (en POST) | Inicio de sesión de usuarios |
| `/logout` | `GET`/`POST` | `AuthController::logout` | `CsrfMiddleware` (en POST) | Cierre de sesión seguro |
| `/recuperar` | `GET`/`POST` | `AuthController::showRecuperar` / `recuperar` | `CsrfMiddleware` (en POST) | Ingresar cédula para recuperación |
| `/recuperar/preguntas`| `GET`/`POST` | `AuthController::showPreguntas` / `verificarPreguntas` | `CsrfMiddleware` (en POST) | Formulario de preguntas de seguridad |
| `/recuperar/nueva-clave`| `GET`/`POST` | `AuthController::showNuevaClave` / `cambiarClave` | `CsrfMiddleware` (en POST) | Guardar nueva contraseña restablecida |
| `/admin` / `/admin/`| `GET` | `DashboardController::index` | `AuthMiddleware` | Panel de control (Dashboard) |
| `/admin/setup` | `GET`/`POST` | `PerfilController::setup` / `saveSetup` | `AuthMiddleware`, `CsrfMiddleware` (POST) | Configuración obligatoria inicial |
| `/admin/atletas` | `GET` | `AtletasController::index` | `AuthMiddleware` | Listado general de atletas |
| `/admin/atletas/crear`| `GET`/`POST` | `AtletasController::create` / `store` | `AuthMiddleware`, `CsrfMiddleware` (POST), `RoleMiddleware(['admin', 'super_user'])` | Registro de nuevos atletas |
| `/admin/atletas/{id}` | `GET` | `AtletasController::show` | `AuthMiddleware` | Vista de detalle / Perfil del atleta |
| `/admin/atletas/{id}/editar`| `GET`/`POST` | `AtletasController::edit` / `update` | `AuthMiddleware`, `CsrfMiddleware` (POST), `RoleMiddleware(['admin', 'super_user'])` | Modificación de atleta |
| `/admin/categorias` | `GET` | `CategoriasController::index` | `AuthMiddleware` | Listado y filtros de categorías |
| `/admin/categorias/crear`| `GET`/`POST` | `CategoriasController::create` / `store` | `AuthMiddleware`, `CsrfMiddleware` (POST), `RoleMiddleware(['admin', 'super_user'])` | Registro de categoría |
| `/admin/categorias/{id}/detalles`| `GET` | `AsigCategoriasController::index` | `AuthMiddleware` | Detalle e historial de atletas en categoría |
| `/admin/categorias/{id}/asignar`| `GET`/`POST` | `AsigCategoriasController::create` / `store` | `AuthMiddleware`, `CsrfMiddleware` (POST), `RoleMiddleware(['admin', 'super_user'])` | Asignación masiva a categoría |
| `/admin/asistencias` | `GET` | `AsistenciasController::index` | `AuthMiddleware` | Registro histórico de asistencias |
| `/admin/asistencias/crear`| `GET`/`POST` | `AsistenciasController::crear` / `guardar` | `AuthMiddleware`, `CsrfMiddleware` (POST) | Registro diario de asistencia por categoría |
| `/admin/medidas` | `GET` | `MedidasAntropometricasController::index` | `AuthMiddleware` | Registro general de mediciones |
| `/admin/medidas/atleta/{id}`| `GET`/`POST` | `MedidasAntropometricasController::atleta` / `store` | `AuthMiddleware`, `CsrfMiddleware` (POST) | Registrar/Ver medidas de un atleta |
| `/admin/resultados-pruebas`| `GET` | `ResultadosPruebasController::index` | `AuthMiddleware` | Registro general de pruebas físicas |
| `/admin/resultados-pruebas/atleta/{id}`| `GET`/`POST` | `ResultadosPruebasController::atleta` / `store` | `AuthMiddleware`, `CsrfMiddleware` (POST) | Registrar/Ver pruebas de un atleta |
| `/admin/ficha-medica/{id}`| `GET`/`POST` | `FichaMedicaController::show` / `update` | `AuthMiddleware`, `CsrfMiddleware` (POST), `RoleMiddleware(['admin', 'super_user'])` | Ver/Actualizar ficha médica de un atleta |
| `/admin/ficha-medica/{id}/discapacidad`| `POST` | `FichaMedicaController::storeDiscapacidad` | `AuthMiddleware`, `CsrfMiddleware`, `RoleMiddleware(['admin', 'super_user'])` | Registrar discapacidad a atleta |
| `/admin/perfil` | `GET`/`POST` | `PerfilController::index` / `updatePerfil` | `AuthMiddleware`, `CsrfMiddleware` (POST) | Perfil del usuario logueado |
| `/admin/configuracion`| `GET`/`POST` | `ConfiguracionController::index` / `update` | `AuthMiddleware`, `CsrfMiddleware` (POST), `RoleMiddleware(['admin', 'super_user'])` | Ajustes de contacto, misión, visión |
| `/api/direcciones/estados/{paisId}`| `GET` | `DireccionesApiController::estados` | `AuthMiddleware` | Obtener estados asociados a un país |
| `/api/direcciones/municipios/{estadoId}`| `GET` | `DireccionesApiController::municipios` | `AuthMiddleware` | Obtener municipios asociados a un estado |
| `/api/direcciones/parroquias/{municipioId}`| `GET` | `DireccionesApiController::parroquias` | `AuthMiddleware` | Obtener parroquias de un municipio |
| `/api/asistencias/categoria/{id}`| `GET` | `AsistenciasApiController::atletasCategoria` | `AuthMiddleware` | Obtiene lista de atletas para toma de asistencia |

---

## 3. Modelo Base y Esquema de Base de Datos

### 3.1. Clase Base Model (app/Core/Model.php)
Todos los modelos de la aplicación extienden de la clase abstracta `App\Core\Model`. Esta clase expone métodos CRUD genéricos construidos sobre sentencias preparadas de PDO:
*   `find(int $id): ?array`: Devuelve el registro coincidente con la clave primaria o `null`.
*   `all(?string $orderBy = null): array`: Obtiene todos los registros de la tabla respectiva.
*   `insert(array $data): int`: Inserta un registro a partir de un array asociativo y retorna el `lastInsertId`.
*   `update(int $id, array $data): int`: Actualiza los campos especificados basándose en el ID y retorna la cantidad de filas afectadas.
*   `delete(int $id): int`: Elimina físicamente un registro por su clave primaria.
*   `count(string $where = '', array $bindings = []): int`: Retorna el conteo de registros bajo condiciones dadas.
*   `query(string $sql, array $bindings = []): array`: Ejecuta cualquier consulta SQL y retorna todas las filas.
*   `queryOne(string $sql, array $bindings = []): ?array`: Ejecuta consulta SQL y retorna un único registro o `null`.

### 3.2. Catálogo de Tablas en la Base de Datos (`cada_db`)
1.  **`actividades`**: Entrenamientos, partidos, pruebas físicas o eventos especiales agendados.
2.  **`asistencias`**: Detalle del estado de asistencia de cada atleta (presente, ausente, justificado) vinculado a una actividad.
3.  **`atletas`**: Almacena los datos personales, cédula (sin puntos), pierna dominante, posición de juego y enlace al tutor de los deportistas.
4.  **`categorias`**: Divisiones por edades de los atletas (ej: Sub-8, Sub-10, etc.) asociadas a un enlistador.
5.  **`configuraciones`**: Parámetros globales del sistema (visión, misión, contacto, tiempo de expiración de sesión).
6.  **`direcciones`**: Tabla pivote que vincula calles, tipo de vivienda y localidad con la tabla de `parroquias` (cascada geográfica).
7.  **`discapacidades`**: Tabla de relación que asocia discapacidades específicas con la ficha médica de un atleta.
8.  **`estados`**: Estados que componen el mapa político de Venezuela (ej: Portuguesa, Lara, Yaracuy).
9.  **`fichas_medicas`**: Ficha del atleta que registra tipo de sangre, alergias, condiciones crónicas y medicación.
10. **`historial_partidos`**: Registro e histórico de partidos jugados contra equipos rivales y goles.
11. **`medidas_antropometricas`**: Datos corporales del atleta medidos periódicamente (peso, altura, grasa, musculatura, envergadura).
12. **`municipios`**: Municipios asociados a cada `estado_id`.
13. **`parroquias`**: Parroquias asociadas a cada `municipio_id`.
14. **`posiciones_juegos`**: Catálogo de posiciones dentro del campo (Portero, Defensa, Mediocentro, Delantero).
15. **`preguntas_seguridad`**: Cuestionamientos precargados y agrupados en 4 bloques para recuperación de claves.
16. **`representantes`**: Datos de contacto, cédula (sin puntos) y relación (madre, padre, abuelo/a) del tutor legal del atleta.
17. **`respuestas_seguridad`**: Respuestas cifradas en BCrypt asociadas a un usuario y pregunta elegida.
18. **`resultados_pruebas`**: Calificaciones obtenidas por el atleta en los tests de fuerza, velocidad, resistencia, reacción y coordinación.
19. **`roles_usuarios`**: Roles permitidos en el sistema (`super_usuario` = 1, `administrador` = 2, `entrenador` = 3).
20. **`tipos_discapacidades`**: Catálogo estático de discapacidades (Física Motora, Visual, Auditiva, Intelectual, etc.).
21. **`usuarios`**: Datos de acceso y personales de administradores y entrenadores con credenciales en el sistema.

---

## 4. Helpers y Constantes Globales (PHP)

### 4.1. Funciones Globales (app/Helpers/functions.php)
*   `e($value)`: Atajo para `htmlspecialchars()`. **Su uso es estrictamente obligatorio al renderizar variables en las vistas para mitigar ataques XSS.**
*   `url($path)`: Retorna la dirección URL absoluta del sistema basada en la variable `APP_URL` del entorno.
*   `asset($path)`: Genera un enlace absoluto a los recursos estáticos ubicados en `/public/assets/`.
*   `old($key, $default)`: Obtiene los valores del último formulario fallido guardados en sesión.
*   `csrf_token()` / `csrf_field()`: Generación y pintado del campo oculto de seguridad contra ataques CSRF (`_csrf`).
*   `auth()`: Retorna un array con la información del usuario logueado en la sesión activa.
*   `can($role)`: Verifica si el usuario autenticado tiene asignado un rol específico (por nombre o ID de constante). El `super_usuario` (ID 1) siempre tiene permisos de bypass.
*   `flash($key, $message = null)`: Guarda o recupera (y limpia) un mensaje temporal flash de sesión (`success`, `error`, `warning`).
*   `redirect($to, $code = 302)`: Realiza una redirección HTTP directa y detiene la ejecución.
*   `request_method()`: Detecta el método real HTTP, interpretando campos ocultos de suplantación de método (`_method` para PUT, PATCH o DELETE).
*   `config_db($key, $default)`: Recupera un parámetro de configuración dinámica directamente desde la base de datos.
*   `errors()` / `has_errors()`: Retorna y verifica la existencia de errores de validación de formulario cargados en sesión.

### 4.2. Constantes (app/Helpers/constants.php)
*   **Roles**: `ROL_SUPERUSER = 1`, `ROL_ADMIN = 2`, `ROL_ENTRENADOR = 3`.
*   **Estatus Atleta**: `1` = Activo, `0` = Suspendido, `2` = Lesionado, `3` = Inactivo.
*   **Estatus Asistencia**: `0` = Ausente, `1` = Presente, `2` = Justificado.
*   **Tipo de Actividad**: `0` = Partido, `1` = Entrenamiento, `2` = Pruebas Físicas, `3` = Evento Especial.
*   **Clima**: `0` = Soleado, `1` = Nublado, `2` = Lluvioso, `3` = Viento, `4` = Tormenta.

---

## 5. Diseño CSS y Clases de UI Globales

La interfaz utiliza variables CSS personalizadas que facilitan el soporte nativo de **Modo Claro** y **Modo Oscuro** (usando la clase `html.dark` en el selector general).

### 5.1. Variables CSS y Colores (`main.css`)
*   `--color-primary`: Rojo principal vinotinto (`#BE123C`).
*   `--color-primary-dark`: Rojo oscuro vinotinto (`#9F1239`).
*   `--color-primary-light`: Fondo rosa claro para alertas/botones (`#FFE4E6`).
*   `--color-bg`: Fondo general de la página (Blanco / Modo Noche: `#0B1220`).
*   `--color-surface`: Tarjetas y elementos de UI (`#F8FAFC` / Modo Noche: `#111827`).
*   `--color-surface-2`: Fondos de contraste / cabeceras (`#F1F5F9` / Modo Noche: `#1F2937`).
*   `--color-sidebar-bg`: Color del menú lateral (`#0F172A` / Modo Noche: `#070B14`).
*   `--font-base`: Fuente para texto de lectura (`'Inter'`).
*   `--font-display`: Fuente premium para encabezados (`'Outfit'`).

### 5.2. Clases Estructurales y Componentes (`admin.css`)
*   `.admin-layout`: Contenedor principal en cuadrícula (`grid`) de dos columnas: `.sidebar` (260px) y `.admin-main` (1fr). Posee la clase `.is-collapsed` para colapsar a 80px en pantallas medianas.
*   `.sidebar` / `.topbar`: Menú lateral izquierdo y barra de navegación superior roja, respectivamente.
*   `.card`: Contenedor de tarjeta blanco con sombra sutil y bordes redondeados (`var(--radius)`).
*   `.stats-grid` / `.stat-card`: Contenedores para tarjetas de contadores de métricas en cuadrícula adaptable.
*   `.quick-grid` / `.quick-card`: Accesos directos ilustrados con iconos y colores temáticos para navegación rápida.
*   `.data-table-wrap` / `.data-table`: Envoltura responsiva y tabla semántica con fila de hover y bordes limpios.
*   `.table-filters`: Tarjeta de filtros flexibles que se coloca arriba de los listados para búsqueda instantánea.
*   `.btn`: Botón base. Variantes: `.btn-primary` (Rojo vinotinto), `.btn-outline` (Bordes rojos), `.btn-ghost` (Borde gris, fondo transparente), `.btn-danger` (Rojo alerta), `.btn-success` (Verde).
*   `.badge`: Etiquetas con esquinas totalmente redondeadas para estados. Variantes: `.badge-success` (Presente/Activo), `.badge-warning` (Justificado/Lesionado), `.badge-danger` (Ausente/Suspendido), `.badge-primary` (Inactivo).
*   `.form-row` (2 columnas) y `.form-row-3` (3 columnas): Cuadrículas para alinear inputs en formularios responsivos.
*   `.btn-help`: Botón de ayuda circular con el símbolo `?` que abre un modal con la guía visual del formulario.
*   `.af-file-upload`: Widget premium para subida de fotos (drag & drop virtual) con previsualización circular (`.af-file-preview`).

---

## 6. Estándar de Formularios y Validación

### 6.1. Estructura HTML de Formularios
El sistema promueve un estándar de diseño limpio e intuitivo. Ejemplo de formulario estandarizado:
```html
<form method="POST" action="<?= e(url('/admin/entidad')) ?>" class="card">
    <?= csrf_field() ?>
    
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Nombre Completo</label>
            <input type="text" name="nombre" class="form-control" value="<?= e(old('nombre')) ?>" required>
            <?php if (isset($errors['nombre'])): ?>
                <div class="form-error"><?= e($errors['nombre']) ?></div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="flex gap mt" style="justify-content: flex-end;">
        <a href="<?= e(url('/admin/entidad')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Guardar</button>
    </div>
</form>
```

### 6.2. Motor Frontend (FormValidator)
Definido de forma global en [form-validator.js](file:///c:/Users/Estudio/Documents/proyectos%20codex/Club_Atletico_Deportivo_Acarigua/public/assets/js/core/form-validator.js).
*   **Regla de Oro**: **NO se valida mientras el usuario escribe.**
*   La validación se ejecuta únicamente al presionar el botón de envío (`submit`).
*   Si hay errores, pinta un borde rojo en los inputs (`--color-danger`), previene el envío del formulario, genera scroll automático hacia el primer error y muestra una alerta centralizada con la lista de fallos vía `CadaModal.alert`.
*   Al hacer `focus` en un input con error, se limpia automáticamente su borde rojo para no entorpecer la interacción del usuario.
*   Soporta validaciones personalizadas inyectando una función callback en la propiedad `custom`:
    ```javascript
    FormValidator.init('#mi-formulario', {
        custom: function(container) {
            let errors = [];
            let input = container.querySelector('#campo_custom');
            if (input.value === 'invalido') {
                errors.push({ element: input, label: 'El campo custom no es válido' });
            }
            return errors;
        }
    });
    ```

### 6.3. Validación en el Backend
Los controladores realizan la validación de servidor para evitar bypasses de API. El controlador procesa la petición y si encuentra incongruencias en el objeto `App\Core\Validator`, almacena los campos enviados en sesión con `$this->withOld($data)`, guarda los errores con `$this->withErrors($errors)`, crea un mensaje flash y redirecciona al formulario:
```php
if (!$validator->isValid()) {
    $this->withOld($data)->withErrors($validator->getErrors())->flash('error', 'Revisa los campos obligatorios.');
    return $this->redirect('/admin/entidad/crear');
}
```

### 6.4. Flujo de Primer Login y Cambio de Contraseña (Setup)
*   Cuando un administrador crea un nuevo usuario, asigna la **cédula** como contraseña predeterminada (limpia, sin prefijo ni puntos, ej: `26987345`).
*   Al iniciar sesión por primera vez, el sistema detecta que la contraseña equivale a su cédula. El controlador de autenticación marca en la sesión la bandera `$_SESSION['must_change_password'] = true`.
*   El middleware `AuthMiddleware` intercepta esta bandera y **bloquea cualquier acceso al panel**, redirigiendo obligatoriamente al usuario a la ruta `/admin/setup`.
*   En la vista de Setup, el usuario está forzado a ingresar una nueva contraseña robusta y responder a 2 preguntas de seguridad (cifradas con BCrypt en la base de datos). Una vez completado, se limpia la bandera de sesión y se habilita el acceso completo al panel.

### 6.5. Selección de Direcciones en Cascada (AJAX)
En los formularios de Atletas, Representantes y Usuarios, la selección de la dirección geográfica se realiza de manera dinámica para no recargar la página.
*   El usuario selecciona un Estado. El evento de cambio (`change`) dispara una petición asíncrona usando `API.get('/api/direcciones/municipios/' + estadoId)`.
*   Los municipios retornados en JSON limpian y repueblan el select de Municipios.
*   Al cambiar el Municipio, se dispara una petición hacia `/api/direcciones/parroquias/' + municipioId` repoblando las Parroquias.
*   Todo este flujo se maneja en el frontend mediante funciones de renderizado de elementos DOM nativos.

---

## 7. Iconografía y Ventanas Emergentes (Modales)

### 7.1. Phosphor Icons
La iconografía del sistema está basada localmente en la librería **Phosphor Icons**, lo que asegura un funcionamiento offline óptimo.
*   **Clase base**: `ph ph-{nombre-del-icono}`.
*   **Iconos comunes**:
    *   Guardar / Registrar: `ph-floppy-disk` o `ph-check`.
    *   Editar / Modificar: `ph-pencil-simple` o `ph-note-pencil`.
    *   Eliminar / Rechazar: `ph-trash` o `ph-x-circle`.
    *   Volver / Cancelar: `ph-arrow-left` o `ph-arrow-u-up-left`.
    *   Atleta / Perfil: `ph-user` o `ph-gender-intersex`.
    *   Asistencia: `ph-calendar-check` o `ph-checks`.

### 7.2. Sistema de Modales Unificado (CadaModal)
Ubicado en [modal.js](file:///c:/Users/Estudio/Documents/proyectos%20codex/Club_Atletico_Deportivo_Acarigua/public/assets/js/core/modal.js).
*   **Alertas Estándar**: Abre una ventana modal de notificación que devuelve una Promesa resolviendo en verdadero al cerrarla.
    ```javascript
    CadaModal.alert({
        title: 'Título de la Alerta',
        text: 'Descripción en formato texto o HTML.',
        type: 'success', // success, error, warning, info
        confirmText: 'Entendido'
    });
    ```
*   **Ventanas de Confirmación**: Se utiliza para operaciones destructivas (como eliminar registros). Retorna una Promesa que resuelve en `true` (si confirma) o `false` (si cancela).
    ```javascript
    const confirmado = await CadaModal.confirm({
        title: '¿Eliminar Atleta?',
        text: 'Esta acción no se puede deshacer.',
        type: 'danger',
        confirmText: 'Sí, eliminar',
        cancelText: 'Cancelar'
    });
    if (confirmado) {
        // Ejecutar eliminación
    }
    ```

---

## 8. Reglas de Dominio y Lógica de Negocio

### 8.1. Formato de Cédulas
*   **Prefijos Admitidos**: `V-` (Venezolano), `E-` (Extranjero), `P-` (Pasaporte), `N-` (Partida de Nacimiento).
*   **Validación de Partida de Nacimiento (N-)**:
    *   La expresión regular que la valida en frontend y backend es: `/^N-\d{4}-[A-Z0-9]{1,5}$/i` (ejemplo: `N-2018-456A`). El número de acta tiene un límite de **5 caracteres**.
    *   El año especificado en la partida de nacimiento (ej: `2018`) **no puede ser menor** al año de nacimiento real del atleta ingresado en el campo de fecha de nacimiento.
*   **Formato de Cédula de Identidad en Base de Datos**:
    *   Tanto para atletas como para representantes o usuarios, la cédula se almacena en la base de datos de manera limpia, sin puntos y con el prefijo en mayúscula (ej: `V-12345678` o `E-9876543`).
    *   Al guardar los datos (creación o edición), el controlador limpia los puntos usando la función helper privada `cleanCedulaDots`.
*   **Formato de Cédula en Vistas (Frontend)**:
    *   Para la visualización, el modelo `Atleta` expone la función estática `Atleta::formatCedula($cedula)`. Esta función toma una cédula limpia y añade puntos cada 3 dígitos (ej: `V-12.345.678`).
    *   Al recuperar registros en las consultas, el modelo inyecta esta cadena formateada en las llaves virtuales `cedula_formateada` y `tutor_cedula_formateada`.
    *   En las vistas, se debe renderizar la variable formateada: `<?= e($atleta['cedula_formateada']) ?>`.
    *   En el formulario, al escribir en el input de cédula, una máscara JavaScript coloca los puntos visualmente en pantalla, pero sincroniza un input de tipo `hidden` que guarda el valor sin puntos para enviarlo en el POST de la petición.

### 8.2. Estatus del Atleta y Restricciones de Transacciones
Los atletas pueden tener 4 estatus posibles en base de datos: **1** (Activo), **2** (Lesionado), **0** (Suspendido) y **3** (Inactivo).
*   **Restricción Crítica**: Un atleta con estatus **Suspendido (0)** o **Inactivo (3)** tiene prohibido realizar cualquier transacción nueva en el sistema.
*   **Transacciones Bloqueadas**:
    *   Añadir medidas antropométricas.
    *   Registrar resultados de pruebas físicas.
    *   Toma de asistencias diarias.
    *   Asignación masiva a nuevas categorías.
    *   Registrar ficha médica o agregar nuevas discapacidades.
*   **Control en Frontend**: Los botones para registrar nuevos datos (ej: "Registrar Prueba", "Nueva Medición", etc.) en las pestañas del perfil del atleta se renderizan con el atributo `disabled`, opacidad reducida y cursor bloqueado si su estatus es inactivo o suspendido.
*   **Control en Listados**: En la toma de asistencias, los atletas inactivos/suspendidos se muestran en la lista con opacidad reducida y un badge descriptivo, con sus controles deshabilitados para evitar cambios en su asistencia. En la asignación masiva de categorías, sus casillas checkbox están bloqueadas.
*   **Control en Backend**: Los métodos `store` o `guardar` en sus respectivos controladores verifican el estatus actual del atleta en la base de datos antes de insertar. Si el estatus es `0` o `3`, rechazan la petición inmediatamente devolviendo un código HTTP 403 (en AJAX/JSON) o redireccionan con un mensaje flash de error en peticiones tradicionales.

### 8.3. Autogeneración del Nombre de Categoría
*   Los nombres de las categorías se autogeneran automáticamente basándose en la edad máxima permitida y el género.
*   **Sintaxis**: `sub-{edad_max}{genero}`.
    *   Géneros: `m` (Masculino), `f` (Femenino), `mix` (Mixto).
    *   Ejemplos: `sub-8m`, `sub-12f`, `sub-15mix`.

---

## 9. Historial de Cambios Recientes (Fase 2)

Durante esta fase de desarrollo se implementaron las siguientes mejoras y correcciones críticas:

### 9.1. Filtros de Búsqueda en el Listado de Categorías
*   **Model**: Se modificó `Categoria::allWithEntrenador(array $filters = [])` en [Categoria.php](file:///c:/Users/Estudio/Documents/proyectos%20codex/Club_Atletico_Deportivo_Acarigua/app/Models/Categoria.php) para concatenar dinámicamente cláusulas WHERE basadas en los parámetros de búsqueda (`q` para búsqueda parcial por nombre, `sexo` para filtro exacto de género y `entrenador_id` para filtrar por entrenador asignado).
*   **Controller**: Se modificó `CategoriasController::index` en [CategoriasController.php](file:///c:/Users/Estudio/Documents/proyectos%20codex/Club_Atletico_Deportivo_Acarigua/app/Controllers/Web/CategoriasController.php) para leer estos parámetros del GET, recuperar los entrenadores del sistema (`ROL_ENTRENADOR` = 3) usando `(new Usuario())->entrenadores()`, y enviarlos a la vista.
*   **View**: Se insertó una tarjeta `<form class="table-filters card">` en el listado de categorías [categorias/index.php](file:///c:/Users/Estudio/Documents/proyectos%20codex/Club_Atletico_Deportivo_Acarigua/app/Views/categorias/index.php) justo debajo de las métricas. Se rediseñó la tarjeta de categoría individual para alinear el nombre al centro y colocar el tag de género a la derecha en la fila correspondiente de metadatos.

### 9.2. Nueva Columna de Edad en Listado de Atletas
*   **View**: Se agregó la columna `Edad` en la cabecera y cuerpo de la tabla del listado de atletas [atletas/index.php](file:///c:/Users/Estudio/Documents/proyectos%20codex/Club_Atletico_Deportivo_Acarigua/app/Views/atletas/index.php).
*   **Lógica**: Se muestra la edad calculada de cada atleta reutilizando la función `calcularEdad` del modelo `ResultadoPrueba`, la cual utiliza la diferencia de fechas con `DateTime`. Se modificó el `colspan` de la fila de tabla vacía de 5 a 6 para evitar roturas de diseño.

### 9.3. Corrección de Inversión en Filtros de Estatus (Atletas)
*   **View**: Se corrigió un error en el select del filtro de estatus de [atletas/index.php](file:///c:/Users/Estudio/Documents/proyectos%20codex/Club_Atletico_Deportivo_Acarigua/app/Views/atletas/index.php). Las opciones estaban invertidas:
    *   La opción "Inactivo" apuntaba al valor `0` (que corresponde a Suspendido en base de datos).
    *   La opción "Suspendido" apuntaba al valor `3` (que corresponde a Inactivo en base de datos).
*   **Solución**: Se reasignaron los valores correctos: `3` para Inactivo y `0` para Suspendido, logrando que el filtrado de la base de datos coincida con la selección del usuario.

### 9.4. Configuración del Entorno de Composer (GD Extension php.ini)
*   **Problema**: Al intentar ejecutar `composer install`, el proceso fallaba debido a que las dependencias requerían de la librería gráfica de PHP `ext-gd`, la cual no estaba habilitada.
*   **Solución**:
    1.  Se debió localizar el archivo global `php.ini` activo en el computador (verificable con el comando `php --ini` en la consola).
    2.  Se buscó la línea `;extension=gd` (o `;extension=php_gd2.dll` en instalaciones antiguas de Windows).
    3.  Se eliminó el punto y coma inicial (`;`) para descomentar la línea y habilitar la extensión globalmente: `extension=gd`.
    4.  Tras guardar el archivo, se procedió a correr de forma exitosa `composer install` sin bloqueos de dependencias.
