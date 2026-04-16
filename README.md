# 🦅 Club Atlético Deportivo Acarigua - Sistema de Gestión Deportiva

Este proyecto es una aplicación web integral diseñada para la recopilación, monitoreo y análisis antropométrico del rendimiento deportivo de los atletas del **Club Atlético Deportivo Acarigua**.

## 📖 Descripción del Proyecto

El sistema centraliza la información técnica y médica del club, facilitando el seguimiento del progreso físico de los jugadores a través de mediciones periódicas, control de asistencias y generación de reportes técnicos detallados.

### 🌟 Características Principales

- **Gestión de Atletas:** Registro detallado de deportistas con información personal, técnica, médica y de contacto (incluyendo representante y dirección detallada).
- **Monitoreo Antropométrico:** Seguimiento de peso, altura, envergadura e índices de masa corporal.
- **Evaluación de Rendimiento:** Registro de tests físicos especializados (Fuerza, Resistencia, Velocidad, Coordinación y Reacción).
- **Ficha Médica Digital:** Historial de salud, alergias, condiciones crónicas y gestión de carnet de discapacidad.
- **Control de Asistencias:** Registro diario de presencia en los entrenamientos por categorías.
- **Gestión del Plantel:** Administración de entrenadores y personal del club con roles específicos.
- **Reportes Técnicos en PDF:** Generación e impresión de fichas técnicas individuales con gráficos y métricas de progreso.
- **Seguridad:** Sistema de permisos basado en roles (RBAC) y autenticación segura mediante **JSON Web Tokens (JWT)**.

### 🛡️ Seguridad y Roles (RBAC)

El sistema implementa un modelo de Control de Acceso Basado en Roles para garantizar la integridad y privacidad de la información:

- **Súper Usuario / Administrador:** Acceso total a todos los módulos, incluyendo la configuración del sistema, gestión de usuarios y personal. Debido a la ausencia de un médico de planta constante, el administrador tiene permisos para actualizar fichas médicas.
- **Entrenador:** Orientado al seguimiento técnico. Puede registrar asistencias y actualizar datos de **Rendimiento y Antropometría** de los atletas. Tiene acceso de solo lectura a los datos personales y médicos básicos. No tiene acceso a la configuración ni a la gestión de personal.

#### Matriz de Permisos

| Módulo | Súper / Admin | Entrenador |
| :--- | :---: | :---: |
| **Atletas (Datos Personales)** | Escritura | Lectura |
| **Ficha Médica** | Escritura | Lectura |
| **Rendimiento y Antropometría** | Escritura | **Escritura** |
| **Control de Asistencias** | Escritura | Escritura |
| **Gestión del Plantel** | Escritura | Sin Acceso |
| **Configuración del Sistema** | Escritura | Sin Acceso |
| **Reportes** | Todos | Todos |

---

## 🧰 Stack Técnico

- **Backend:** PHP 8.1+ (vanilla, arquitectura MVC propia, PDO, JWT HS256 implementado sin dependencias)
- **Frontend:** PHP templates renderizadas en servidor + JavaScript vanilla progresivo (fetch, Chart.js desde CDN)
- **Base de datos:** MySQL 8 / MariaDB 10.4+ (UTF-8 `utf8mb4`, esquema normalizado)
- **Reportes:** TCPDF (opcional vía Composer) con fallback a HTML imprimible
- **Deploy:** Apache (XAMPP/Laragon) o servidor embebido de PHP (`php -S`)

## 📁 Estructura del proyecto

```
├── app/                  # Código PHP (MVC)
│   ├── Core/             # Router, DB, JWT, Auth, Validator, etc.
│   ├── Middleware/       # Auth, Role (RBAC), CSRF
│   ├── Controllers/
│   │   ├── Web/          # Retornan vistas HTML
│   │   └── Api/          # Retornan JSON
│   ├── Models/           # PDO wrappers de cada tabla
│   ├── Services/         # Reglas de negocio (transacciones, uploads, PDF)
│   ├── Views/            # Templates PHP
│   └── Helpers/          # funciones globales y constantes
├── config/               # app.php, database.php, auth.php, routes.php
├── database/
│   ├── normalized_schema.sql
│   ├── seeds/            # 01_roles, 03_posiciones, 04_ubicaciones_vzla, etc.
│   └── install.php       # Instalador CLI
├── public/                # DocumentRoot
│   ├── index.php         # Front controller
│   ├── .htaccess
│   └── assets/           # CSS, JS, imágenes, uploads
└── storage/logs/
```

## 🐳 Despliegue con Docker (recomendado)

El proyecto incluye un stack Docker completo (Apache + PHP 8.2 + MariaDB 11) listo para usar.

### Requisitos
- Docker 20+ y Docker Compose v2

### Uso rápido

```bash
# Levantar el stack (construye la imagen la primera vez)
docker compose up -d --build

# La app queda disponible en:
#   http://localhost:8080
# Y MariaDB en:
#   localhost:3307 (usuario cada_user / cada_pass_2026)

# Credenciales iniciales:
#   admin@cada.com / Admin2026!
```

El entrypoint detecta si la BD está vacía y la instala automáticamente (schema + seeds + admin) la primera vez.

### Comandos útiles

```bash
# Ver logs
docker compose logs -f app
docker compose logs -f db

# Entrar al contenedor
docker compose exec app bash

# Reinstalar BD desde cero
docker compose down -v         # borra volúmenes
docker compose up -d --build

# phpMyAdmin (opcional) en http://localhost:8081
docker compose --profile tools up -d

# Detener sin perder datos
docker compose down

# Detener y borrar volúmenes (pierde la BD)
docker compose down -v
```

### Personalizar puertos/credenciales

Por defecto el stack usa los puertos 8080 (app), 3307 (MariaDB) y 8081 (phpMyAdmin). Si alguno está ocupado o quieres cambiar credenciales:

```bash
cp .env.docker.example .env.docker
# edita .env.docker
docker compose --env-file .env.docker up -d
```

Todas las variables del stack Docker usan prefijo `CADA_` (ej. `CADA_APP_HOST_PORT=9000`).

### Archivos relevantes

- `Dockerfile` — imagen PHP 8.2 + Apache con extensiones (pdo_mysql, gd, mbstring, intl, opcache)
- `docker-compose.yml` — orquestación app + db + phpMyAdmin (opcional)
- `docker/apache/000-default.conf` — vhost Apache (DocumentRoot → /public, bloquea rutas internas)
- `docker/php/php.ini` — ajustes de producción (opcache, sesión segura, límites)
- `docker/entrypoint.sh` — espera a MariaDB, auto-instala BD, arranca Apache
- `.env.docker.example` — plantilla para personalizar el stack

---

## 🚀 Instalación local

### Requisitos
- PHP 8.1 o superior (con `pdo_mysql`, `mbstring`, `gd`, `json`)
- MySQL 8 / MariaDB 10.4+
- (Opcional) Composer para instalar TCPDF y generar PDFs binarios

### Pasos

1. **Clonar el repositorio** y entrar al directorio.

2. **Configurar variables de entorno:**
   ```bash
   cp .env.example .env
   # editar .env con tus credenciales MySQL y generar JWT_SECRET:
   php -r "echo bin2hex(random_bytes(32));"
   ```

3. **(Opcional) Instalar dependencias de Composer:**
   ```bash
   composer install
   ```
   Sin Composer, los reportes se entregan como HTML imprimible; el resto del sistema funciona completo.

4. **Crear base de datos + schema + seeds:**
   ```bash
   php database/install.php
   # o para recrear desde cero:
   php database/install.php --fresh
   ```
   Esto crea la base `club_atletico_db_normalized`, todas las tablas, ubicaciones (Venezuela → Portuguesa → Páez), roles, posiciones, categorías demo y un usuario admin.

5. **Levantar el servidor:**
   ```bash
   # Opción A: servidor embebido de PHP
   php -S localhost:8000 -t public

   # Opción B: Apache/XAMPP → apuntar DocumentRoot a /public
   ```

6. **Iniciar sesión** en `http://localhost:8000/login`:
   - **Email:** `admin@cada.com`
   - **Contraseña:** `Admin2026!`
   - ⚠ Cambia la contraseña al primer acceso desde Configuración → Usuarios.

### Scripts disponibles

```bash
composer run install-db    # Instala la BD (alias de database/install.php)
composer run serve         # Levanta servidor PHP en localhost:8000
```

## 🔐 Notas de seguridad

- Las contraseñas se almacenan con **bcrypt** (cost 12).
- El JWT viaja en cookie `httpOnly + SameSite=Lax` (mitiga XSS y CSRF).
- Todas las operaciones POST requieren token **CSRF** (`_csrf` inyectado en formularios vía `csrf_field()`).
- **Rate limit** en login: 5 intentos / 5 min por IP+email.
- **Prepared statements** en todas las consultas (PDO con `emulate_prepares = false`).
- Las rutas `/admin/*` requieren JWT válido; RBAC por rol en `/admin/plantel`, `/admin/configuracion` y escritura de ficha médica.
- Auditoría de eventos sensibles (`login`, `logout`, creación/edición/eliminación de atletas) en `storage/logs/app-YYYY-MM-DD.log`.

## 🧪 Smoke tests

Tras la instalación:
- `GET /` → landing con CTA "Acceder al Sistema"
- `GET /login` + credenciales admin → redirige a `/admin`
- `GET /admin/atletas` + crear atleta (foto, tutor, cascada de ubicación)
- `GET /admin/asistencia/pase` + categoría → marca asistencia y guarda
- `GET /admin/antropometria/atleta/{id}` → registra medición y ve gráfico
- `GET /admin/reportes/atleta/{id}` → descarga ficha técnica (PDF o HTML imprimible)

