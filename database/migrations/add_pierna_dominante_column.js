const pool = require('./config/database');

async function addPiernaDominanteColumn() {
    try {
        // Verificar si la columna ya existe
        const [columns] = await pool.execute('SHOW COLUMNS FROM atletas LIKE "pierna_dominante"');

        if (columns.length === 0) {
            console.log('Agregando columna pierna_dominante a la tabla atletas...');
            await pool.execute('ALTER TABLE atletas ADD COLUMN pierna_dominante VARCHAR(20) DEFAULT "Derecha" AFTER posicion_de_juego');
            console.log('Columna agregada exitosamente.');
        } else {
            console.log('La columna pierna_dominante ya existe.');
        }
    } catch (error) {
        console.error('Error al agregar la columna:', error);
    } finally {
        process.exit();
    }
}

addPiernaDominanteColumn();
