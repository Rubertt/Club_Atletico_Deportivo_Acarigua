<?php
declare(strict_types=1);

const ROL_SUPERUSER   = 1;
const ROL_ADMIN       = 2;
const ROL_ENTRENADOR  = 3;
const ROL_MEDICO      = 4;

const ESTATUS_ATLETA = [
    1 => 'Activo',
    0 => 'Suspendido',
    2 => 'Lesionado',
    3 => 'Inactivo',
];

const PIERNA_DOMINANTE = ['derecha', 'izquierda', 'ambidiestro'];

const TIPO_RELACION_REPRESENTANTE = [
    'abuelo/a', 'padres', 'tio/a', 'hermano/a', 'primo/a', 'representante',
];

const TIPO_ACTIVIDAD = [0 => 'Partido', 1 => 'Entrenamiento', 2 => 'Pruebas Físicas'];

const ESTATUS_ASISTENCIA = [0 => 'Ausente', 1 => 'Presente', 2 => 'Justificado'];

const TIPO_EVENTO = ['Entrenamiento', 'Partido Oficial', 'Partido Amistoso', 'Torneo'];
