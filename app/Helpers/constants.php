<?php
declare(strict_types=1);

const ROL_ADMIN      = 1;
const ROL_ENTRENADOR = 2;

const ESTATUS_ATLETA = [
    'Activo'     => 'Activo',
    'Inactivo'   => 'Inactivo',
    'Lesionado'  => 'Lesionado',
    'Suspendido' => 'Suspendido',
];

const PIERNA_DOMINANTE = ['Derecha', 'Izquierda', 'Ambidiestro'];

const TIPO_RELACION_TUTOR = [
    'Padre', 'Madre', 'Abuelo/a', 'Tío/a', 'Hermano/a', 'Tutor Legal', 'Otro',
];

const TIPO_EVENTO = ['Entrenamiento', 'Partido', 'Pruebas', 'Evento especial'];

const ESTATUS_ASISTENCIA = ['Presente', 'Ausente', 'Justificado'];
