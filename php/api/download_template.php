<?php
/**
 * Generador y Descargador de Plantilla Oficial de Juicios Evaluativos SENA
 */

require_once __DIR__ . '/../config.php';

// Encabezados para forzar la descarga de archivo CSV compatible con Excel
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="PLANTILLA_JUICIOS_EVALUATIVOS_SENA.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

// BOM UTF-8 para que Microsoft Excel en Windows reconozca tildes y caracteres especiales automáticamente
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeceras oficiales
fputcsv($output, [
    'tipo_identificacion',
    'numero_identificacion',
    'nombre_completo',
    'email',
    'codigo_programa',
    'nombre_programa',
    'competencia',
    'resultado_aprendizaje',
    'juicio',
    'fecha_evaluacion'
]);

// Filas de ejemplo con datos institucionales del SENA
$ejemplos = [
    [
        'CC',
        '1014298102',
        'Valentina Ríos Cárdenas',
        'vrios@soy.sena.edu.co',
        '228106',
        'Análisis y Desarrollo de Software (ADSO)',
        'Especificación de Requisitos de Software (220501096)',
        'Caracterizar los procesos de la organización de acuerdo con el marco de referencia y estándares.',
        'APROBADO',
        date('Y-m-d')
    ],
    [
        'CC',
        '1014298102',
        'Valentina Ríos Cárdenas',
        'vrios@soy.sena.edu.co',
        '228106',
        'Análisis y Desarrollo de Software (ADSO)',
        'Modelado y Gestión de Bases de Datos (220501093)',
        'Construir bases de datos relacionales y no relacionales según especificaciones técnicas.',
        'APROBADO',
        date('Y-m-d')
    ],
    [
        'CC',
        '1020491820',
        'Mateo Alejandro Suárez Bermúdez',
        'msuarez@soy.sena.edu.co',
        '228106',
        'Análisis y Desarrollo de Software (ADSO)',
        'Desarrollo de Software Web Full-Stack (220501095)',
        'Implementar servicios web RESTful y microservicios seguros con autenticación JWT.',
        'POR_EVALUAR',
        date('Y-m-d')
    ],
    [
        'CC',
        '1032890145',
        'Daniel Fernando Gutiérrez Pinzón',
        'dgutierrez@soy.sena.edu.co',
        '228106',
        'Análisis y Desarrollo de Software (ADSO)',
        'Modelado y Gestión de Bases de Datos (220501093)',
        'Aplicar procedimientos de normalización y optimización SQL bajo estándares ACID.',
        'NO_APROBADO',
        date('Y-m-d')
    ],
    [
        'TI',
        '1077654321',
        'Camila Andrea Montoya Restrepo',
        'cmontoya@soy.sena.edu.co',
        '228118',
        'Gestión de Redes y Ciberseguridad',
        'Arquitectura y Seguridad en Redes WAN (220501098)',
        'Configurar enrutamiento seguro y listas de control de acceso ACL.',
        'APROBADO',
        date('Y-m-d')
    ],
    [
        'CC',
        '1011889922',
        'Andrés Felipe Pardo Caicedo',
        'apardo@soy.sena.edu.co',
        '228120',
        'Inteligencia Artificial Aplicada a Negocios',
        'Pipelines de Machine Learning y Datos (220501102)',
        'Entrenar y desplegar modelos supervisados para predicción de series temporales.',
        'APROBADO',
        date('Y-m-d')
    ]
];

foreach ($ejemplos as $fila) {
    fputcsv($output, $fila);
}

fclose($output);
exit;
