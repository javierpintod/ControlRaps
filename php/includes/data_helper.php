<?php
/**
 * Capa de Datos y Lógica de Negocio Institucional SENA
 * Soporta Múltiples Centros, Múltiples Coordinaciones, Dashboard Subdirector y Supabase
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Supabase.php';

// Catálogo Nacional de Centros de Formación Profesional del SENA
const SENA_CENTROS_CATALOG = [
    [
        'id' => 'CENTRO-CEET',
        'codigo_centro' => '9201',
        'nombre_centro' => 'Centro de Electricidad, Electrónica y Telecomunicaciones (CEET)',
        'regional' => 'Distrito Capital',
        'ciudad' => 'Bogotá D.C.',
        'subdirector' => 'Dr. Jorge Eduardo Londoño Ulloa'
    ],
    [
        'id' => 'CENTRO-CGMLTI',
        'codigo_centro' => '9202',
        'nombre_centro' => 'Centro de Gestión de Mercados, Logística y TIC (CGMLTI)',
        'regional' => 'Distrito Capital',
        'ciudad' => 'Bogotá D.C.',
        'subdirector' => 'Dra. María Elena Restrepo'
    ],
    [
        'id' => 'CENTRO-CSF',
        'codigo_centro' => '9204',
        'nombre_centro' => 'Centro de Servicios Financieros (CSF)',
        'regional' => 'Distrito Capital',
        'ciudad' => 'Bogotá D.C.',
        'subdirector' => 'Dr. Wilson Andrés Pardo'
    ],
    [
        'id' => 'CENTRO-CMM',
        'codigo_centro' => '9205',
        'nombre_centro' => 'Centro Metalmecánico',
        'regional' => 'Distrito Capital',
        'ciudad' => 'Bogotá D.C.',
        'subdirector' => 'Ing. Rafael Antonio Mejía'
    ],
    [
        'id' => 'CENTRO-CTCM',
        'codigo_centro' => '9208',
        'nombre_centro' => 'Centro de Tecnologías para la Construcción y la Madera',
        'regional' => 'Distrito Capital',
        'ciudad' => 'Bogotá D.C.',
        'subdirector' => 'Arq. Germán Eduardo Silva'
    ],
    [
        'id' => 'CENTRO-CBA',
        'codigo_centro' => '9101',
        'nombre_centro' => 'Centro de Biotecnología Agropecuaria (CBA)',
        'regional' => 'Cundinamarca',
        'ciudad' => 'Mosquera',
        'subdirector' => 'Dr. José Vicente Morales'
    ],
    [
        'id' => 'CENTRO-CTM',
        'codigo_centro' => '9301',
        'nombre_centro' => 'Centro Tecnológico del Mobiliario (CTM)',
        'regional' => 'Antioquia',
        'ciudad' => 'Medellín / Itagüí',
        'subdirector' => 'Ing. Juan Carlos Montoya'
    ],
    [
        'id' => 'CENTRO-CPIC',
        'codigo_centro' => '9401',
        'nombre_centro' => 'Centro de Procesos Industriales y Construcción',
        'regional' => 'Caldas',
        'ciudad' => 'Manizales',
        'subdirector' => 'Dra. Claudia Ximena Zuluaga'
    ],
    [
        'id' => 'CENTRO-CNCA',
        'codigo_centro' => '9501',
        'nombre_centro' => 'Centro Nacional Colombo Alemán (CNCA)',
        'regional' => 'Atlántico',
        'ciudad' => 'Barranquilla',
        'subdirector' => 'Dr. Sergio Alejandro Orozco'
    ],
    [
        'id' => 'CENTRO-ASTIN',
        'codigo_centro' => '9601',
        'nombre_centro' => 'Centro Nacional de Asistencia Técnica a la Industria (ASTIN)',
        'regional' => 'Valle del Cauca',
        'ciudad' => 'Cali',
        'subdirector' => 'Ing. Rodrigo Henao Cadavid'
    ],
    [
        'id' => 'CENTRO-CDITI',
        'codigo_centro' => '9701',
        'nombre_centro' => 'Centro de Diseño e Innovación Tecnológica Industrial (CDITI)',
        'regional' => 'Risaralda',
        'ciudad' => 'Dosquebradas',
        'subdirector' => 'Dr. Andrés Mauricio Toro'
    ],
    [
        'id' => 'CENTRO-CIAA',
        'codigo_centro' => '9801',
        'nombre_centro' => 'Centro de la Industria, la Empresa y los Servicios (CIES)',
        'regional' => 'Norte de Santander',
        'ciudad' => 'Cúcuta',
        'subdirector' => 'Dr. Wilmar Alexis Pérez'
    ],
    [
        'id' => 'CENTRO-CDATH',
        'codigo_centro' => '9901',
        'nombre_centro' => 'Centro de Desarrollo Agroempresarial y Turístico del Huila',
        'regional' => 'Huila',
        'ciudad' => 'La Plata / Neiva',
        'subdirector' => 'Dr. Fabio Mauricio Ramírez'
    ],
    [
        'id' => 'CENTRO-CAB',
        'codigo_centro' => '8101',
        'nombre_centro' => 'Centro Agropecuario y de Biotecnología El Porvenir',
        'regional' => 'Córdoba',
        'ciudad' => 'Montería',
        'subdirector' => 'Dra. Carmen Cecilia Martínez'
    ],
    [
        'id' => 'CENTRO-CTP',
        'codigo_centro' => '8201',
        'nombre_centro' => 'Centro Tecnológico de la Amazonía (CTA)',
        'regional' => 'Caquetá',
        'ciudad' => 'Florencia',
        'subdirector' => 'Dr. Milton Javier Delgado'
    ]
];

// Catálogo de Coordinaciones Académicas asociadas por Centro
const SENA_COORDINACIONES_CATALOG = [
    // CEET
    [
        'id' => 'COORD-CEET-01',
        'centro_id' => 'CENTRO-CEET',
        'nombre_coordinacion' => 'Coordinación de Teleinformática y Desarrollo de Software',
        'coordinador_nombre' => 'Ing. Claudia Patricia Duarte Gómez',
        'coordinador_email' => 'coord.software@sena.edu.co',
        'programas_asociados' => ['228106', '228120'],
        'meta_tasa_aprobacion' => 90.0
    ],
    [
        'id' => 'COORD-CEET-02',
        'centro_id' => 'CENTRO-CEET',
        'nombre_coordinacion' => 'Coordinación de Redes, Telecomunicaciones y Ciberseguridad',
        'coordinador_nombre' => 'Ing. Harold Mauricio Morales Ruiz',
        'coordinador_email' => 'coord.redes@sena.edu.co',
        'programas_asociados' => ['228118'],
        'meta_tasa_aprobacion' => 85.0
    ],
    [
        'id' => 'COORD-CEET-03',
        'centro_id' => 'CENTRO-CEET',
        'nombre_coordinacion' => 'Coordinación de Electrónica, Automatización e Internet de las Cosas',
        'coordinador_nombre' => 'Ing. Roberto Carlos Peñaloza Rivas',
        'coordinador_email' => 'coord.electronica@sena.edu.co',
        'programas_asociados' => ['228130'],
        'meta_tasa_aprobacion' => 88.0
    ],
    [
        'id' => 'COORD-CEET-04',
        'centro_id' => 'CENTRO-CEET',
        'nombre_coordinacion' => 'Coordinación de Electricidad y Sistemas de Energía Solar Fotovoltaica',
        'coordinador_nombre' => 'Ing. Diana Marcela Rincón Torres',
        'coordinador_email' => 'coord.electricidad@sena.edu.co',
        'programas_asociados' => ['228140'],
        'meta_tasa_aprobacion' => 82.0
    ],

    // CGMLTI
    [
        'id' => 'COORD-CGMLTI-01',
        'centro_id' => 'CENTRO-CGMLTI',
        'nombre_coordinacion' => 'Coordinación de Logística, Transporte y Cadena de Suministro',
        'coordinador_nombre' => 'Lic. Javier Alberto Soler',
        'coordinador_email' => 'coord.logistica@sena.edu.co',
        'programas_asociados' => ['134101'],
        'meta_tasa_aprobacion' => 85.0
    ],
    [
        'id' => 'COORD-CGMLTI-02',
        'centro_id' => 'CENTRO-CGMLTI',
        'nombre_coordinacion' => 'Coordinación de Marketing Digital y Comercio Electrónico',
        'coordinador_nombre' => 'Dra. Andrea Castro Vélez',
        'coordinador_email' => 'coord.marketing@sena.edu.co',
        'programas_asociados' => ['134105'],
        'meta_tasa_aprobacion' => 87.0
    ],

    // CSF
    [
        'id' => 'COORD-CSF-01',
        'centro_id' => 'CENTRO-CSF',
        'nombre_coordinacion' => 'Coordinación de Contabilidad, Auditoría y Finanzas Públicas',
        'coordinador_nombre' => 'Dra. Sandra Milena Benavides',
        'coordinador_email' => 'coord.finanzas@sena.edu.co',
        'programas_asociados' => ['123112'],
        'meta_tasa_aprobacion' => 80.0
    ],
    [
        'id' => 'COORD-CSF-02',
        'centro_id' => 'CENTRO-CSF',
        'nombre_coordinacion' => 'Coordinación de Banca, Microfinanzas y Seguros',
        'coordinador_nombre' => 'Lic. Wilson Pardo Caicedo',
        'coordinador_email' => 'coord.banca@sena.edu.co',
        'programas_asociados' => ['123115'],
        'meta_tasa_aprobacion' => 84.0
    ]
];

function initSessionData(): void {
    if (isset($_SESSION['sena_mock_data'])) return;

    $_SESSION['sena_mock_data'] = [
        'programas' => [
            [
                'codigo_programa' => '228106',
                'nombre_programa' => 'Análisis y Desarrollo de Software (ADSO)',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-01',
                'total_aprendices' => 84,
                'tasa_aprobacion' => 88.5,
                'fichas_asociadas' => ['2671982', '2710493', '2810291']
            ],
            [
                'codigo_programa' => '228118',
                'nombre_programa' => 'Gestión de Redes y Ciberseguridad',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-02',
                'total_aprendices' => 48,
                'tasa_aprobacion' => 83.2,
                'fichas_asociadas' => ['2710493', '2799102']
            ],
            [
                'codigo_programa' => '228120',
                'nombre_programa' => 'Inteligencia Artificial Aplicada a Negocios',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-01',
                'total_aprendices' => 32,
                'tasa_aprobacion' => 92.0,
                'fichas_asociadas' => ['2821094']
            ],
            [
                'codigo_programa' => '228130',
                'nombre_programa' => 'Automatización Industrial e Internet de las Cosas',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-03',
                'total_aprendices' => 40,
                'tasa_aprobacion' => 86.0,
                'fichas_asociadas' => ['2845012']
            ],
            [
                'codigo_programa' => '123112',
                'nombre_programa' => 'Contabilidad y Finanzas Públicas',
                'centro_id' => 'CENTRO-CSF',
                'coordinacion_id' => 'COORD-CSF-01',
                'total_aprendices' => 60,
                'tasa_aprobacion' => 79.4,
                'fichas_asociadas' => ['2598301', '2610495']
            ]
        ],
        'cargas' => [
            [
                'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                'nombre_archivo' => 'SOFIA_PLUS_CORTE_OCTUBRE_2026.xlsx',
                'centro_id' => 'CENTRO-CEET',
                'usuario_id' => 'usr-lider-02',
                'usuario_nombre' => 'Ing. Carlos Alberto Mendoza Silva',
                'usuario_rol' => 'LIDER_FORMACION',
                'fecha_corte' => '2026-10-01',
                'total_registros' => 1450,
                'registros_exitosos' => 1450,
                'inconsistencias' => 0,
                'estado' => 'EXITOSO',
                'motivo_rollback' => null,
                'created_at' => '2026-10-01 09:14:22'
            ],
            [
                'batch_id' => 'b9c82345-d678-4f90-c123-efa456789012',
                'nombre_archivo' => 'JUICIOS_EVALUATIVOS_SEP_QUINCENA2.xlsx',
                'centro_id' => 'CENTRO-CEET',
                'usuario_id' => 'usr-coord-01',
                'usuario_nombre' => 'Ing. Claudia Patricia Duarte Gómez',
                'usuario_rol' => 'COORDINADOR',
                'fecha_corte' => '2026-09-15',
                'total_registros' => 1220,
                'registros_exitosos' => 1220,
                'inconsistencias' => 0,
                'estado' => 'EXITOSO',
                'motivo_rollback' => null,
                'created_at' => '2026-09-15 16:40:11'
            ],
            [
                'batch_id' => 'c0d93456-e789-4a01-d234-fab567890123',
                'nombre_archivo' => 'REPORTE_CONSOLIDADO_AGOSTO_2026.xlsx',
                'centro_id' => 'CENTRO-CEET',
                'usuario_id' => 'usr-admin-01',
                'usuario_nombre' => 'Dr. Fernando Arango Botero',
                'usuario_rol' => 'ADMIN',
                'fecha_corte' => '2026-08-30',
                'total_registros' => 1100,
                'registros_exitosos' => 1100,
                'inconsistencias' => 0,
                'estado' => 'EXITOSO',
                'motivo_rollback' => null,
                'created_at' => '2026-08-30 11:10:05'
            ],
            [
                'batch_id' => 'd1e04567-f890-4b12-e345-abc678901234',
                'nombre_archivo' => 'CORTE_ERRONEO_INCONSISTENCIAS.xlsx',
                'centro_id' => 'CENTRO-CEET',
                'usuario_id' => 'usr-coord-02',
                'usuario_nombre' => 'Ing. Harold Mauricio Morales Ruiz',
                'usuario_rol' => 'COORDINADOR',
                'fecha_corte' => '2026-08-15',
                'total_registros' => 420,
                'registros_exitosos' => 408,
                'inconsistencias' => 12,
                'estado' => 'REVOCADO',
                'motivo_rollback' => 'Códigos de competencia 220501096 con desfase de nomenclatura curricular. Reversión atómica aplicada por auditoría.',
                'created_at' => '2026-08-15 14:02:19'
            ]
        ],
        'aprendices' => [
            [
                'numero_identificacion' => '1014298102',
                'tipo_identificacion' => 'CC',
                'nombre_completo' => 'Valentina Ríos Cárdenas',
                'email' => 'vrios@soy.sena.edu.co',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-01',
                'codigo_programa' => '228106',
                'nombre_programa' => 'Análisis y Desarrollo de Software (ADSO)',
                'total_raps' => 8,
                'raps_aprobados' => 8,
                'raps_pendientes' => 0,
                'raps_no_aprobados' => 0,
                'porcentaje_avance' => 100,
                'estado_academico' => 'Por Certificar',
                'evaluaciones' => [
                    [
                        'id' => 'eval-val-1',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Especificación de Requisitos de Software (220501096)',
                        'resultado_aprendizaje' => 'Caracterizar los procesos de la organización de acuerdo con el marco de referencia y estándares.',
                        'juicio' => 'APROBADO',
                        'fecha_evaluacion' => '2026-09-18',
                        'instructor_evaluador' => 'Ing. Carlos Alberto Mendoza'
                    ],
                    [
                        'id' => 'eval-val-2',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Modelado y Gestión de Bases de Datos (220501093)',
                        'resultado_aprendizaje' => 'Construir bases de datos relacionales y no relacionales según especificaciones técnicas.',
                        'juicio' => 'APROBADO',
                        'fecha_evaluacion' => '2026-09-18',
                        'instructor_evaluador' => 'Ing. Carlos Alberto Mendoza'
                    ],
                    [
                        'id' => 'eval-val-3',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Desarrollo de Software Web Full-Stack (220501095)',
                        'resultado_aprendizaje' => 'Implementar servicios web RESTful y microservicios seguros con autenticación JWT.',
                        'juicio' => 'APROBADO',
                        'fecha_evaluacion' => '2026-09-20',
                        'instructor_evaluador' => 'Lic. Martha Gómez Restrepo'
                    ],
                    [
                        'id' => 'eval-val-4',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Comunicación en Segunda Lengua - Inglés (240201524)',
                        'resultado_aprendizaje' => 'Interactuar en lengua inglesa de forma oral y escrita en contextos laborales y técnicos.',
                        'juicio' => 'APROBADO',
                        'fecha_evaluacion' => '2026-09-22',
                        'instructor_evaluador' => 'Lic. Fernando Ospina'
                    ]
                ]
            ],
            [
                'numero_identificacion' => '1020491820',
                'tipo_identificacion' => 'CC',
                'nombre_completo' => 'Mateo Alejandro Suárez Bermúdez',
                'email' => 'msuarez@soy.sena.edu.co',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-01',
                'codigo_programa' => '228106',
                'nombre_programa' => 'Análisis y Desarrollo de Software (ADSO)',
                'total_raps' => 8,
                'raps_aprobados' => 6,
                'raps_pendientes' => 2,
                'raps_no_aprobados' => 0,
                'porcentaje_avance' => 75,
                'estado_academico' => 'Al Día',
                'evaluaciones' => [
                    [
                        'id' => 'eval-mat-1',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Especificación de Requisitos de Software (220501096)',
                        'resultado_aprendizaje' => 'Elaborar diagramas y modelos de arquitectura según requerimientos funcionales.',
                        'juicio' => 'APROBADO',
                        'fecha_evaluacion' => '2026-09-14',
                        'instructor_evaluador' => 'Ing. Carlos Alberto Mendoza'
                    ],
                    [
                        'id' => 'eval-mat-2',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Desarrollo de Software Web Full-Stack (220501095)',
                        'resultado_aprendizaje' => 'Implementar servicios web RESTful y microservicios seguros con autenticación JWT.',
                        'juicio' => 'POR_EVALUAR',
                        'instructor_evaluador' => 'Lic. Martha Gómez Restrepo'
                    ],
                    [
                        'id' => 'eval-mat-3',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Modelado y Gestión de Bases de Datos (220501093)',
                        'resultado_aprendizaje' => 'Aplicar procedimientos de normalización y optimización de consultas SQL.',
                        'juicio' => 'POR_EVALUAR',
                        'instructor_evaluador' => 'Ing. Carlos Alberto Mendoza'
                    ]
                ]
            ],
            [
                'numero_identificacion' => '1032890145',
                'tipo_identificacion' => 'CC',
                'nombre_completo' => 'Daniel Fernando Gutiérrez Pinzón',
                'email' => 'dgutierrez@soy.sena.edu.co',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-01',
                'codigo_programa' => '228106',
                'nombre_programa' => 'Análisis y Desarrollo de Software (ADSO)',
                'total_raps' => 8,
                'raps_aprobados' => 4,
                'raps_pendientes' => 3,
                'raps_no_aprobados' => 1,
                'porcentaje_avance' => 50,
                'estado_academico' => 'En Riesgo',
                'evaluaciones' => [
                    [
                        'id' => 'eval-dan-1',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Modelado y Gestión de Bases de Datos (220501093)',
                        'resultado_aprendizaje' => 'Aplicar procedimientos de normalización y optimización SQL bajo estándares ACID.',
                        'juicio' => 'NO_APROBADO',
                        'fecha_evaluacion' => '2026-09-10',
                        'instructor_evaluador' => 'Ing. Carlos Alberto Mendoza'
                    ],
                    [
                        'id' => 'eval-dan-2',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Desarrollo de Software Web Full-Stack (220501095)',
                        'resultado_aprendizaje' => 'Construir interfaces de usuario reactivas con diseño accesible.',
                        'juicio' => 'POR_EVALUAR',
                        'instructor_evaluador' => 'Lic. Martha Gómez Restrepo'
                    ]
                ]
            ],
            [
                'numero_identificacion' => '1077654321',
                'tipo_identificacion' => 'TI',
                'nombre_completo' => 'Camila Andrea Montoya Restrepo',
                'email' => 'cmontoya@soy.sena.edu.co',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-02',
                'codigo_programa' => '228118',
                'nombre_programa' => 'Gestión de Redes y Ciberseguridad',
                'total_raps' => 8,
                'raps_aprobados' => 7,
                'raps_pendientes' => 1,
                'raps_no_aprobados' => 0,
                'porcentaje_avance' => 87.5,
                'estado_academico' => 'Al Día',
                'evaluaciones' => [
                    [
                        'id' => 'eval-cam-1',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Arquitectura y Seguridad en Redes WAN (220501098)',
                        'resultado_aprendizaje' => 'Configurar enrutamiento seguro y listas de control de acceso ACL.',
                        'juicio' => 'APROBADO',
                        'fecha_evaluacion' => '2026-09-22',
                        'instructor_evaluador' => 'Ing. Diana Marcela Rincón'
                    ],
                    [
                        'id' => 'eval-cam-2',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Ciberseguridad y Análisis Forense (220501099)',
                        'resultado_aprendizaje' => 'Identificar vectores de ataque y vulnerabilidades en infraestructura perimetral.',
                        'juicio' => 'POR_EVALUAR',
                        'instructor_evaluador' => 'Ing. Diana Marcela Rincón'
                    ]
                ]
            ],
            [
                'numero_identificacion' => '1098456123',
                'tipo_identificacion' => 'CC',
                'nombre_completo' => 'Esteban José Herrera Morales',
                'email' => 'eherrera@soy.sena.edu.co',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-02',
                'codigo_programa' => '228118',
                'nombre_programa' => 'Gestión de Redes y Ciberseguridad',
                'total_raps' => 8,
                'raps_aprobados' => 8,
                'raps_pendientes' => 0,
                'raps_no_aprobados' => 0,
                'porcentaje_avance' => 100,
                'estado_academico' => 'Por Certificar',
                'evaluaciones' => [
                    [
                        'id' => 'eval-est-1',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Ciberseguridad y Análisis Forense (220501099)',
                        'resultado_aprendizaje' => 'Implementar contramedidas y políticas de seguridad bajo estándar ISO 27001.',
                        'juicio' => 'APROBADO',
                        'fecha_evaluacion' => '2026-09-25',
                        'instructor_evaluador' => 'Ing. Diana Marcela Rincón'
                    ]
                ]
            ],
            [
                'numero_identificacion' => '1011889922',
                'tipo_identificacion' => 'CC',
                'nombre_completo' => 'Andrés Felipe Pardo Caicedo',
                'email' => 'apardo@soy.sena.edu.co',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-01',
                'codigo_programa' => '228120',
                'nombre_programa' => 'Inteligencia Artificial Aplicada a Negocios',
                'total_raps' => 8,
                'raps_aprobados' => 8,
                'raps_pendientes' => 0,
                'raps_no_aprobados' => 0,
                'porcentaje_avance' => 100,
                'estado_academico' => 'Por Certificar',
                'evaluaciones' => [
                    [
                        'id' => 'eval-and-1',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Pipelines de Machine Learning y Datos (220501102)',
                        'resultado_aprendizaje' => 'Entrenar y desplegar modelos supervisados para predicción de series temporales.',
                        'juicio' => 'APROBADO',
                        'fecha_evaluacion' => '2026-09-02',
                        'instructor_evaluador' => 'Mag. Julián David Quintero'
                    ]
                ]
            ],
            [
                'numero_identificacion' => '1033221199',
                'tipo_identificacion' => 'CC',
                'nombre_completo' => 'Jessica Paola López Narváez',
                'email' => 'jlopez@soy.sena.edu.co',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-01',
                'codigo_programa' => '228120',
                'nombre_programa' => 'Inteligencia Artificial Aplicada a Negocios',
                'total_raps' => 8,
                'raps_aprobados' => 6,
                'raps_pendientes' => 1,
                'raps_no_aprobados' => 1,
                'porcentaje_avance' => 75,
                'estado_academico' => 'En Riesgo',
                'evaluaciones' => [
                    [
                        'id' => 'eval-jes-1',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Pipelines de Machine Learning y Datos (220501102)',
                        'resultado_aprendizaje' => 'Evaluar métricas de rendimiento y calibración de hiperparámetros.',
                        'juicio' => 'NO_APROBADO',
                        'fecha_evaluacion' => '2026-09-12',
                        'instructor_evaluador' => 'Mag. Julián David Quintero'
                    ],
                    [
                        'id' => 'eval-jes-2',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Gobierno y Ética en Modelos de IA (220501103)',
                        'resultado_aprendizaje' => 'Mitigar sesgos algorítmicos en datasets de entrenamiento.',
                        'juicio' => 'POR_EVALUAR',
                        'instructor_evaluador' => 'Mag. Julián David Quintero'
                    ]
                ]
            ],
            // Aprendiz de Electrónica (COORD-CEET-03)
            [
                'numero_identificacion' => '1044556677',
                'tipo_identificacion' => 'CC',
                'nombre_completo' => 'Brayan Steven Moreno Casas',
                'email' => 'bmoreno@soy.sena.edu.co',
                'centro_id' => 'CENTRO-CEET',
                'coordinacion_id' => 'COORD-CEET-03',
                'codigo_programa' => '228130',
                'nombre_programa' => 'Automatización Industrial e Internet de las Cosas',
                'total_raps' => 8,
                'raps_aprobados' => 7,
                'raps_pendientes' => 1,
                'raps_no_aprobados' => 0,
                'porcentaje_avance' => 87.5,
                'estado_academico' => 'Al Día',
                'evaluaciones' => [
                    [
                        'id' => 'eval-bra-1',
                        'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                        'competencia' => 'Sistemas Embebidos y Controladores PLC (220501110)',
                        'resultado_aprendizaje' => 'Programar rutinas de control en lógica de escaleras y Grafcet.',
                        'juicio' => 'APROBADO',
                        'fecha_evaluacion' => '2026-09-19',
                        'instructor_evaluador' => 'Ing. Roberto Carlos Peñaloza'
                    ]
                ]
            ]
        ]
    ];
}

class SenaRepository {
    private SupabaseClient $supabase;
    private bool $isLive = false;

    public function __construct() {
        $this->supabase = new SupabaseClient();
        $this->isLive = isSupabaseConfigured();
        initSessionData();
    }

    public function isLiveSupabase(): bool {
        return $this->isLive;
    }

    public function getLatency(): int {
        return $this->isLive ? $this->supabase->getLastLatencyMs() : rand(45, 95);
    }

    /**
     * Catálogo de Centros del SENA con opción de búsqueda / autocompletado
     */
    public function getCentros(?string $query = null): array {
        $centros = SENA_CENTROS_CATALOG;
        if (!empty($query)) {
            $q = mb_strtolower(trim($query), 'UTF-8');
            $centros = array_values(array_filter($centros, function($c) use ($q) {
                return strpos(mb_strtolower($c['nombre_centro'], 'UTF-8'), $q) !== false
                    || strpos(mb_strtolower($c['regional'], 'UTF-8'), $q) !== false
                    || strpos(mb_strtolower($c['ciudad'], 'UTF-8'), $q) !== false
                    || strpos(mb_strtolower($c['codigo_centro'], 'UTF-8'), $q) !== false
                    || strpos(mb_strtolower($c['id'], 'UTF-8'), $q) !== false;
            }));
        }
        return $centros;
    }

    public function getCentroById(string $centroId): ?array {
        foreach (SENA_CENTROS_CATALOG as $c) {
            if ($c['id'] === $centroId) return $c;
        }
        return SENA_CENTROS_CATALOG[0] ?? null;
    }

    /**
     * Catálogo de Coordinaciones filtradas por Centro
     */
    public function getCoordinacionesByCentro(string $centroId): array {
        return array_values(array_filter(SENA_COORDINACIONES_CATALOG, function($c) use ($centroId) {
            return $c['centro_id'] === $centroId;
        }));
    }

    public function getCoordinacionById(string $coordId): ?array {
        foreach (SENA_COORDINACIONES_CATALOG as $coord) {
            if ($coord['id'] === $coordId) return $coord;
        }
        return null;
    }

    /**
     * Obtiene programas de formación filtrados opcionalmente por Centro
     */
    public function getProgramas(?string $centroId = null): array {
        $programas = [];
        if ($this->isLive) {
            $params = ['select' => '*'];
            $data = $this->supabase->get('programas', $params);
            if (!empty($data)) {
                $programas = $data;
            }
        }

        if (empty($programas)) {
            $programas = $_SESSION['sena_mock_data']['programas'] ?? [];
        }

        if ($centroId && $centroId !== 'TODOS') {
            $programas = array_values(array_filter($programas, function($p) use ($centroId) {
                return ($p['centro_id'] ?? 'CENTRO-CEET') === $centroId;
            }));
        }

        return $programas;
    }

    /**
     * Obtiene las cargas/lotes de archivo
     */
    public function getCargas(?string $centroId = null): array {
        if ($this->isLive) {
            $data = $this->supabase->get('cargas_archivo', ['select' => '*', 'order' => 'created_at.desc']);
            if (!empty($data)) return $data;
        }
        $cargas = $_SESSION['sena_mock_data']['cargas'] ?? [];
        if ($centroId && $centroId !== 'TODOS') {
            $cargas = array_values(array_filter($cargas, function($c) use ($centroId) {
                return ($c['centro_id'] ?? 'CENTRO-CEET') === $centroId;
            }));
        }
        return $cargas;
    }

    /**
     * Obtiene aprendices filtrados por Centro, Coordinación y criterios adicionales
     */
    public function getAprendices(?string $centroId = null, ?string $coordinacionId = null): array {
        $aprendices = [];
        if ($this->isLive) {
            $aprendices = $this->supabase->get('aprendices', ['select' => '*', 'order' => 'nombre_completo.asc']);
            if (!empty($aprendices)) {
                $juicios = $this->supabase->get('juicios_evaluativos', ['select' => '*']);
                $juiciosPorDoc = [];
                foreach ($juicios as $j) {
                    $juiciosPorDoc[$j['numero_identificacion']][] = $j;
                }
                foreach ($aprendices as &$ap) {
                    $doc = $ap['numero_identificacion'];
                    $ap['evaluaciones'] = $juiciosPorDoc[$doc] ?? [];
                }
            }
        }

        if (empty($aprendices)) {
            $aprendices = $_SESSION['sena_mock_data']['aprendices'] ?? [];
        }

        // Asignar fallback de centro y coordinación si no existen en la BD antigua
        foreach ($aprendices as &$ap) {
            if (empty($ap['centro_id'])) $ap['centro_id'] = 'CENTRO-CEET';
            if (empty($ap['coordinacion_id'])) {
                if ($ap['codigo_programa'] === '228118') $ap['coordinacion_id'] = 'COORD-CEET-02';
                elseif ($ap['codigo_programa'] === '123112') $ap['coordinacion_id'] = 'COORD-CSF-01';
                else $ap['coordinacion_id'] = 'COORD-CEET-01';
            }
        }

        // Filtrar por centro
        if ($centroId && $centroId !== 'TODOS') {
            $aprendices = array_values(array_filter($aprendices, fn($a) => ($a['centro_id'] ?? 'CENTRO-CEET') === $centroId));
        }

        // Filtrar por coordinación
        if ($coordinacionId && $coordinacionId !== 'TODAS') {
            $aprendices = array_values(array_filter($aprendices, fn($a) => ($a['coordinacion_id'] ?? '') === $coordinacionId));
        }

        return $aprendices;
    }

    /**
     * Obtiene el detalle de un aprendiz por su número de identificación
     */
    public function getAprendizByDoc(string $documento): ?array {
        $aprendices = $this->getAprendices();
        foreach ($aprendices as $ap) {
            if ($ap['numero_identificacion'] === $documento) {
                return $ap;
            }
        }
        return null;
    }

    /**
     * MÉTRICAS CONSOLIDADAS POR COORDINACIÓN (Para el Dashboard del Subdirector)
     */
    public function getMetricasPorCoordinacion(string $centroId): array {
        $coordinaciones = $this->getCoordinacionesByCentro($centroId);
        $aprendicesCentro = $this->getAprendices($centroId, 'TODAS');

        $resultado = [];

        foreach ($coordinaciones as $coord) {
            $cId = $coord['id'];
            $aprendicesCoord = array_filter($aprendicesCentro, fn($a) => ($a['coordinacion_id'] ?? '') === $cId);

            $totalAprendices = count($aprendicesCoord);
            $totalRaps = 0;
            $rapsAprobados = 0;
            $rapsPendientes = 0;
            $rapsNoAprobados = 0;
            $enRiesgo = 0;
            $porCertificar = 0;
            $alDia = 0;

            foreach ($aprendicesCoord as $ap) {
                $totalRaps += (int)($ap['total_raps'] ?? 0);
                $rapsAprobados += (int)($ap['raps_aprobados'] ?? 0);
                $rapsPendientes += (int)($ap['raps_pendientes'] ?? 0);
                $rapsNoAprobados += (int)($ap['raps_no_aprobados'] ?? 0);

                if (($ap['estado_academico'] ?? '') === 'En Riesgo') $enRiesgo++;
                elseif (($ap['estado_academico'] ?? '') === 'Por Certificar') $porCertificar++;
                else $alDia++;
            }

            $tasaAprobacion = $totalRaps > 0 ? round(($rapsAprobados / $totalRaps) * 100, 1) : 0;

            // Semáforo gerencial
            $semaforo = 'VERDE';
            if ($enRiesgo > 0 || $tasaAprobacion < 70) {
                $semaforo = 'ROJO';
            } elseif ($tasaAprobacion < 85 || $rapsPendientes > 3) {
                $semaforo = 'AMARILLO';
            }

            $resultado[] = [
                'coordinacion' => $coord,
                'total_aprendices' => $totalAprendices,
                'total_raps' => $totalRaps,
                'raps_aprobados' => $rapsAprobados,
                'raps_pendientes' => $rapsPendientes,
                'raps_no_aprobados' => $rapsNoAprobados,
                'tasa_aprobacion' => $tasaAprobacion,
                'en_riesgo' => $enRiesgo,
                'por_certificar' => $porCertificar,
                'al_dia' => $alDia,
                'semaforo' => $semaforo,
                'programas_count' => count($coord['programas_asociados'])
            ];
        }

        return $resultado;
    }

    /**
     * Ingesta un nuevo lote de juicios evaluativos
     */
    public function commitBatch(array $cargaData, array $filasValidas): array {
        $batchId = $cargaData['batch_id'] ?? sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $cargaData['batch_id'] = $batchId;
        $cargaData['estado'] = 'EXITOSO';
        $cargaData['created_at'] = date('Y-m-d H:i:s');
        if (empty($cargaData['centro_id'])) {
            $cargaData['centro_id'] = getActiveCentroId();
        }

        if ($this->isLive) {
            $this->supabase->insert('cargas_archivo', $cargaData);

            $juiciosToInsert = [];
            foreach ($filasValidas as $f) {
                $juiciosToInsert[] = [
                    'id' => 'eval-' . uniqid(),
                    'batch_id' => $batchId,
                    'numero_identificacion' => $f['numero_identificacion'],
                    'competencia' => $f['competencia'],
                    'resultado_aprendizaje' => $f['resultado_aprendizaje'],
                    'juicio' => $f['juicio'],
                    'fecha_evaluacion' => $f['fecha_evaluacion'] ?? date('Y-m-d'),
                    'instructor_evaluador' => $cargaData['usuario_nombre'] ?? 'Instructor Evaluador'
                ];
            }
            if (!empty($juiciosToInsert)) {
                $this->supabase->insert('juicios_evaluativos', $juiciosToInsert);
            }
        }

        array_unshift($_SESSION['sena_mock_data']['cargas'], $cargaData);

        // Actualizar aprendices
        foreach ($filasValidas as $f) {
            $doc = $f['numero_identificacion'];
            $found = false;

            foreach ($_SESSION['sena_mock_data']['aprendices'] as &$ap) {
                if ($ap['numero_identificacion'] === $doc) {
                    $found = true;
                    $rapFound = false;
                    foreach ($ap['evaluaciones'] as &$ev) {
                        if ($ev['resultado_aprendizaje'] === $f['resultado_aprendizaje']) {
                            $ev['juicio'] = $f['juicio'];
                            $ev['fecha_evaluacion'] = $f['fecha_evaluacion'] ?? date('Y-m-d');
                            $ev['batch_id'] = $batchId;
                            $rapFound = true;
                            break;
                        }
                    }
                    if (!$rapFound) {
                        $ap['evaluaciones'][] = [
                            'id' => 'eval-' . uniqid(),
                            'batch_id' => $batchId,
                            'competencia' => $f['competencia'],
                            'resultado_aprendizaje' => $f['resultado_aprendizaje'],
                            'juicio' => $f['juicio'],
                            'fecha_evaluacion' => $f['fecha_evaluacion'] ?? date('Y-m-d'),
                            'instructor_evaluador' => $cargaData['usuario_nombre'] ?? 'Instructor Evaluador'
                        ];
                    }

                    $ap['total_raps'] = count($ap['evaluaciones']);
                    $ap['raps_aprobados'] = count(array_filter($ap['evaluaciones'], fn($e) => $e['juicio'] === 'APROBADO'));
                    $ap['raps_pendientes'] = count(array_filter($ap['evaluaciones'], fn($e) => $e['juicio'] === 'POR_EVALUAR'));
                    $ap['raps_no_aprobados'] = count(array_filter($ap['evaluaciones'], fn($e) => $e['juicio'] === 'NO_APROBADO'));
                    $ap['porcentaje_avance'] = $ap['total_raps'] > 0 ? (int)round(($ap['raps_aprobados'] / $ap['total_raps']) * 100) : 0;
                    $ap['estado_academico'] = $ap['raps_no_aprobados'] > 0 ? 'En Riesgo' : ($ap['porcentaje_avance'] === 100 ? 'Por Certificar' : 'Al Día');
                    break;
                }
            }

            if (!$found) {
                $nuevaEval = [
                    'id' => 'eval-' . uniqid(),
                    'batch_id' => $batchId,
                    'competencia' => $f['competencia'],
                    'resultado_aprendizaje' => $f['resultado_aprendizaje'],
                    'juicio' => $f['juicio'],
                    'fecha_evaluacion' => $f['fecha_evaluacion'] ?? date('Y-m-d'),
                    'instructor_evaluador' => $cargaData['usuario_nombre'] ?? 'Instructor Evaluador'
                ];
                $_SESSION['sena_mock_data']['aprendices'][] = [
                    'numero_identificacion' => $f['numero_identificacion'],
                    'tipo_identificacion' => $f['tipo_identificacion'] ?? 'CC',
                    'nombre_completo' => $f['nombre_completo'],
                    'email' => $f['email'] ?? ($f['numero_identificacion'] . '@soy.sena.edu.co'),
                    'centro_id' => $cargaData['centro_id'],
                    'coordinacion_id' => getActiveCoordinacionId() !== 'TODAS' ? getActiveCoordinacionId() : 'COORD-CEET-01',
                    'codigo_programa' => $f['codigo_programa'] ?? '228106',
                    'nombre_programa' => $f['nombre_programa'] ?? 'Análisis y Desarrollo de Software (ADSO)',
                    'total_raps' => 1,
                    'raps_aprobados' => $f['juicio'] === 'APROBADO' ? 1 : 0,
                    'raps_pendientes' => $f['juicio'] === 'POR_EVALUAR' ? 1 : 0,
                    'raps_no_aprobados' => $f['juicio'] === 'NO_APROBADO' ? 1 : 0,
                    'porcentaje_avance' => $f['juicio'] === 'APROBADO' ? 100 : 0,
                    'estado_academico' => $f['juicio'] === 'NO_APROBADO' ? 'En Riesgo' : ($f['juicio'] === 'APROBADO' ? 'Por Certificar' : 'Al Día'),
                    'evaluaciones' => [$nuevaEval]
                ];
            }
        }

        return ['success' => true, 'batch_id' => $batchId, 'registros' => count($filasValidas)];
    }

    /**
     * Reversión Atómica de un lote (Rollback)
     */
    public function rollbackBatch(string $batchId, string $motivo): array {
        if ($this->isLive) {
            $this->supabase->update(
                'cargas_archivo',
                ['estado' => 'REVOCADO', 'motivo_rollback' => $motivo],
                ['batch_id' => 'eq.' . $batchId]
            );
            $this->supabase->delete('juicios_evaluativos', ['batch_id' => 'eq.' . $batchId]);
        }

        foreach ($_SESSION['sena_mock_data']['cargas'] as &$c) {
            if ($c['batch_id'] === $batchId) {
                $c['estado'] = 'REVOCADO';
                $c['motivo_rollback'] = $motivo;
                break;
            }
        }

        foreach ($_SESSION['sena_mock_data']['aprendices'] as &$ap) {
            $ap['evaluaciones'] = array_values(array_filter($ap['evaluaciones'], fn($e) => ($e['batch_id'] ?? '') !== $batchId));
            $ap['total_raps'] = count($ap['evaluaciones']);
            $ap['raps_aprobados'] = count(array_filter($ap['evaluaciones'], fn($e) => $e['juicio'] === 'APROBADO'));
            $ap['raps_pendientes'] = count(array_filter($ap['evaluaciones'], fn($e) => $e['juicio'] === 'POR_EVALUAR'));
            $ap['raps_no_aprobados'] = count(array_filter($ap['evaluaciones'], fn($e) => $e['juicio'] === 'NO_APROBADO'));
            $ap['porcentaje_avance'] = $ap['total_raps'] > 0 ? (int)round(($ap['raps_aprobados'] / $ap['total_raps']) * 100) : 0;
            $ap['estado_academico'] = $ap['raps_no_aprobados'] > 0 ? 'En Riesgo' : ($ap['porcentaje_avance'] === 100 ? 'Por Certificar' : 'Al Día');
        }

        return ['success' => true, 'message' => "Lote {$batchId} revocado exitosamente."];
    }
}
