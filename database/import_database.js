#!/usr/bin/env node
'use strict';

const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');

// ============================================================================
// CONFIGURACIÓN
// ============================================================================

const DB_NAME = 'cada_db';
const SQL_FILE = 'import_cada_sql.sql';
const CONNECTION_TIMEOUT = 10000; // 10 segundos

/**
 * Configuración de conexión a MySQL
 * @type {Object}
 */
const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  multipleStatements: true,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  enableKeepAlive: true,
  keepAliveInitialDelayMs: 0,
  charset: 'utf8mb4'
};

// ============================================================================
// UTILIDADES DE LOGGING
// ============================================================================

const Logger = {
  info: (msg) => console.log(`ℹ️  ${msg}`),
  success: (msg) => console.log(`✅ ${msg}`),
  warn: (msg) => console.warn(`⚠️  ${msg}`),
  error: (msg) => console.error(`❌ ${msg}`),
  section: (title) => console.log(`\n${'='.repeat(70)}\n📌 ${title}\n${'='.repeat(70)}`),
  step: (step, msg) => console.log(`\n📍 [${step}] ${msg}`)
};

// ============================================================================
// VALIDACIONES
// ============================================================================

/**
 * Valida que el archivo SQL existe y es accesible
 * @param {string} filePath - Ruta del archivo SQL
 * @throws {Error} Si el archivo no existe o no es válido
 */
function validateSQLFile(filePath) {
  try {
    // Validar existencia
    if (!fs.existsSync(filePath)) {
      throw new Error(`Archivo no encontrado: ${filePath}`);
    }

    // Validar que es un archivo
    const stats = fs.statSync(filePath);
    if (!stats.isFile()) {
      throw new Error(`No es un archivo válido: ${filePath}`);
    }

    // Validar permisos de lectura
    fs.accessSync(filePath, fs.constants.R_OK);

    // Validar tamaño
    if (stats.size === 0) {
      throw new Error('El archivo SQL está vacío');
    }

    Logger.success(`Archivo validado (${(stats.size / 1024).toFixed(2)} KB)`);
  } catch (err) {
    if (err.code === 'EACCES') {
      throw new Error(`Permiso denegado al leer: ${filePath}`);
    }
    throw err;
  }
}

/**
 * Lee el contenido del archivo SQL
 * @param {string} filePath - Ruta del archivo SQL
 * @returns {string} Contenido del archivo
 * @throws {Error} Si hay error en la lectura
 */
function readSQLFile(filePath) {
  try {
    const content = fs.readFileSync(filePath, { encoding: 'utf8' });

    if (!content || content.trim().length === 0) {
      throw new Error('El contenido del archivo SQL está vacío');
    }

    return content;
  } catch (err) {
    if (err.code === 'ENOENT') {
      throw new Error(`Archivo no encontrado: ${filePath}`);
    }
    if (err.code === 'EISDIR') {
      throw new Error(`La ruta es un directorio, no un archivo: ${filePath}`);
    }
    throw new Error(`Error al leer archivo: ${err.message}`);
  }
}

/**
 * Valida la configuración de la base de datos
 * @param {Object} config - Configuración de la BD
 * @throws {Error} Si la configuración es inválida
 */
function validateDBConfig(config) {
  if (!config.host) {
    throw new Error('Host no especificado en la configuración');
  }
  if (!config.user) {
    throw new Error('Usuario no especificado en la configuración');
  }
  if (typeof config.multipleStatements !== 'boolean') {
    throw new Error('multipleStatements debe ser booleano');
  }
}

// ============================================================================
// CONEXIÓN A LA BASE DE DATOS
// ============================================================================

/**
 * Establece conexión a MySQL con manejo de errores
 * @returns {Promise<Object>} Conexión a MySQL
 * @throws {Error} Si hay error en la conexión
 */
async function connectToMySQL() {
  try {
    validateDBConfig(dbConfig);

    Logger.step(1, 'Conectando a MySQL...');
    Logger.info(`Host: ${dbConfig.host}, Usuario: ${dbConfig.user}`);

    const connection = await Promise.race([
      mysql.createConnection(dbConfig),
      new Promise((_, reject) =>
        setTimeout(() => reject(new Error('Timeout de conexión')), CONNECTION_TIMEOUT)
      )
    ]);

    Logger.success('Conexión establecida correctamente');

    // Verificar conexión con ping
    await connection.ping();
    Logger.success('Conexión verificada');

    return connection;
  } catch (err) {
    if (err.code === 'PROTOCOL_CONNECTION_LOST') {
      throw new Error('Conexión perdida. MySQL podría no estar ejecutándose.');
    }
    if (err.code === 'ER_ACCESS_DENIED_ERROR') {
      throw new Error('Acceso denegado. Verifica usuario y contraseña.');
    }
    if (err.code === 'ER_BAD_DB_ERROR') {
      throw new Error('Base de datos no existe.');
    }
    if (err.message.includes('connect ECONNREFUSED')) {
      throw new Error('MySQL no está escuchando en localhost:3306. ¿Está XAMPP/MySQL en ejecución?');
    }
    throw new Error(`Error de conexión: ${err.message}`);
  }
}

// ============================================================================
// OPERACIONES DE BASE DE DATOS
// ============================================================================

/**
 * Limpia la base de datos anterior si existe
 * @param {Object} connection - Conexión a MySQL
 * @throws {Error} Si hay error en la operación
 */
async function cleanDatabase(connection) {
  try {
    Logger.step(2, `Limpiando base de datos "${DB_NAME}" si existe...`);

    await connection.query(`DROP DATABASE IF EXISTS \`${DB_NAME}\``);

    Logger.success('Base de datos anterior eliminada');
  } catch (err) {
    throw new Error(`Error al eliminar base de datos: ${err.message}`);
  }
}

/**
 * Importa el esquema SQL
 * @param {Object} connection - Conexión a MySQL
 * @param {string} sqlContent - Contenido del archivo SQL
 * @throws {Error} Si hay error en la ejecución
 */
async function importSchema(connection, sqlContent) {
  try {
    Logger.step(3, 'Preparando importación de esquema...');

    // Desactivar restricciones de integridad
    Logger.info('Desactivando restricciones de claves foráneas...');
    await connection.query('SET FOREIGN_KEY_CHECKS = 0');
    Logger.success('Restricciones desactivadas');

    // Ejecutar el script SQL
    Logger.step(4, 'Ejecutando script SQL...');
    Logger.info('Esto puede tomar algunos segundos...');

    const startTime = Date.now();
    await connection.query(sqlContent);
    const duration = ((Date.now() - startTime) / 1000).toFixed(2);

    Logger.success(`Script ejecutado (${duration}s)`);

    // Reactivar restricciones de integridad
    Logger.info('Reactivando restricciones de claves foráneas...');
    await connection.query('SET FOREIGN_KEY_CHECKS = 1');
    Logger.success('Restricciones reactivadas');
  } catch (err) {
    throw new Error(`Error al ejecutar SQL: ${err.message}`);
  }
}

/**
 * Verifica que la base de datos se creó correctamente
 * @param {Object} connection - Conexión a MySQL
 * @throws {Error} Si la verificación falla
 */
async function verifyDatabase(connection) {
  try {
    Logger.step(5, 'Verificando base de datos...');

    // Verificar que la base de datos existe
    const [databases] = await connection.query(
      "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?",
      [DB_NAME]
    );

    if (databases.length === 0) {
      throw new Error(`Base de datos "${DB_NAME}" no se creó correctamente`);
    }

    Logger.success(`Base de datos "${DB_NAME}" verificada`);

    // Seleccionar la base de datos
    await connection.query(`USE \`${DB_NAME}\``);
    Logger.success(`Base de datos seleccionada`);

    // Contar tablas
    const [tables] = await connection.query('SHOW TABLES');
    Logger.success(`${tables.length} tabla(s) creada(s)`);

    // Mostrar información de integridad
    const [constraints] = await connection.query(
      "SELECT COUNT(*) as total FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ?",
      [DB_NAME]
    );
    Logger.info(`Restricciones de integridad: ${constraints[0]?.total || 0}`);

    return tables.length;
  } catch (err) {
    throw new Error(`Error en verificación: ${err.message}`);
  }
}

/**
 * Obtiene información de usuarios
 * @param {Object} connection - Conexión a MySQL
 * @returns {Promise<Array>} Lista de usuarios
 */
async function getUsers(connection) {
  try {
    Logger.step(6, 'Obteniendo información de usuarios...');

    const [usuarios] = await connection.query(
      'SELECT email, rol, estatus FROM usuarios ORDER BY email'
    );

    if (usuarios.length > 0) {
      Logger.success(`${usuarios.length} usuario(s) encontrado(s):`);
      console.log('');
      usuarios.forEach((user, idx) => {
        console.log(`   ${idx + 1}. ${user.email}`);
        console.log(`      └─ Rol: ${user.rol}, Estado: ${user.estatus}`);
      });
    } else {
      Logger.warn('No hay usuarios en la base de datos');
    }

    return usuarios;
  } catch (err) {
    Logger.warn(`No se pudo obtener usuarios: ${err.message}`);
    return [];
  }
}

/**
 * Obtiene estadísticas de la base de datos
 * @param {Object} connection - Conexión a MySQL
 * @throws {Error} Si hay error en la operación
 */
async function getStatistics(connection) {
  try {
    Logger.step(7, 'Recopilando estadísticas...');

    const [tables] = await connection.query(
      "SELECT COUNT(*) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?",
      [DB_NAME]
    );

    const [columns] = await connection.query(
      "SELECT COUNT(*) as count FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ?",
      [DB_NAME]
    );

    const [rows] = await connection.query(
      "SELECT SUM(TABLE_ROWS) as total FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?",
      [DB_NAME]
    );

    Logger.info(`Tablas: ${tables[0]?.count || 0}`);
    Logger.info(`Columnas: ${columns[0]?.count || 0}`);
    Logger.info(`Registros totales aproximados: ${rows[0]?.total || 0}`);
  } catch (err) {
    Logger.warn(`No se pudo obtener estadísticas: ${err.message}`);
  }
}

// ============================================================================
// FUNCIÓN PRINCIPAL
// ============================================================================

/**
 * Importa la base de datos completa
 * @returns {Promise<boolean>} True si fue exitoso
 */
async function importDatabase() {
  let connection = null;

  try {
    Logger.section('IMPORTACIÓN DE BASE DE DATOS');

    // Validación inicial
    Logger.step(0, 'Validaciones iniciales...');
    const sqlFilePath = path.join(__dirname, SQL_FILE);
    validateSQLFile(sqlFilePath);
    const sqlContent = readSQLFile(sqlFilePath);

    // Conectar
    connection = await connectToMySQL();

    // Limpiar
    await cleanDatabase(connection);

    // Importar
    await importSchema(connection, sqlContent);

    // Verificar
    const tableCount = await verifyDatabase(connection);

    // Obtener datos
    await getUsers(connection);

    // Estadísticas
    await getStatistics(connection);

    // Resumen final
    Logger.section('IMPORTACIÓN COMPLETADA');
    Logger.success('¡Base de datos importada correctamente!');
    console.log(`\n   📊 Tabla(s) creada(s): ${tableCount}`);
    console.log(`   📁 Base de datos: ${DB_NAME}`);
    console.log(`   🔗 Servidor: ${dbConfig.host}:3306\n`);

    return true;
  } catch (error) {
    Logger.section('ERROR EN IMPORTACIÓN');
    Logger.error(error.message);

    // Stack trace en desarrollo
    if (process.env.DEBUG) {
      console.error('\nStack trace:', error.stack);
    }

    // Sugerencias
    console.log('\n💡 Soluciones sugeridas:');
    console.log('   1. Verifica que XAMPP/MySQL esté en ejecución');
    console.log('   2. Verifica usuario y contraseña de MySQL');
    console.log('   3. Verifica que el archivo import_cada_sql.sql existe');
    console.log('   4. Intenta: mysql -u root -e "DROP DATABASE IF EXISTS cada_db;"');
    console.log('   5. Ejecuta en DEBUG: DEBUG=1 node import_database.js\n');

    return false;
  } finally {
    // Cerrar conexión
    if (connection) {
      try {
        await connection.end();
        Logger.info('Conexión cerrada');
      } catch (err) {
        Logger.warn(`Error al cerrar conexión: ${err.message}`);
      }
    }
  }
}

// ============================================================================
// PUNTO DE ENTRADA
// ============================================================================

// Ejecutar solo si es el módulo principal
if (require.main === module) {
  importDatabase()
    .then(success => {
      process.exit(success ? 0 : 1);
    })
    .catch(err => {
      Logger.error(`Error fatal: ${err.message}`);
      process.exit(1);
    });
}

module.exports = { importDatabase };
