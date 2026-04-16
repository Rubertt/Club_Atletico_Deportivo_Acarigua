const pool = require('./config/database');

async function updateTable() {
    try {
        console.log('Iniciando actualización de tabla atletas...');

        // Verificar si la columna ya existe
        const [columns] = await pool.execute('SHOW COLUMNS FROM atletas LIKE "foto"');

        if (columns.length === 0) {
            await pool.execute('ALTER TABLE atletas ADD COLUMN foto VARCHAR(255) DEFAULT NULL AFTER tutor_id');
            console.log('✅ Columna "foto" agregada exitosamente.');
        } else {
            console.log('ℹ️ La columna "foto" ya existe en la tabla atletas.');
        }

        process.exit(0);
    } catch (error) {
        console.error('❌ Error actualizando la tabla:', error);
        process.exit(1);
    }
}

updateTable();
