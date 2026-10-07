<?php
/**
 * Capa de Datos y Lógica de Negocio Institucional SENA
 * Soporta Supabase PostgreSQL con fallback a almacenamiento en sesión para pruebas
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Supabase.php';

// Inicializar datos semilla en la sesión si está en modo fallback
function initSessionData(): void {
    if (isset($_SESSION['sena_mock_data'])) return;

    $_SESSION['sena_mock_data'] = [
        'programas' => [
            [
                'codigo_programa' => '228106',
                'nombre_programa' => 'Análisis y Desarrollo de Software (ADSO)',
                'total_aprendices' => 84,
                'tasa_aprobacion' => 88.5,
                'fichas_asociadas' => ['2671982', '2710493', '2810291']
            ],
            [
                'codigo_programa' => '228118',
                'nombre_programa' => 'Gestión de Redes y Ciberseguridad',
                'total_aprendices' => 48,
                'tasa_aprobacion' => 83.2,
                'fichas_asociadas' => ['2710493', '2799102']
            ],
            [
                'codigo_programa' => '228120',
                'nombre_programa' => 'Inteligencia Artificial Aplicada a Negocios',
                'total_aprendices' => 32,
                'tasa_aprobacion' => 92.0,
                'fichas_asociadas' => ['2821094']
            ],
            [
                'codigo_programa' => '123112',
                'nombre_programa' => 'Contabilidad y Finanzas Públicas',
                'total_aprendices' => 60,
                'tasa_aprobacion' => 79.4,
                'fichas_asociadas' => ['2598301', '2610495']
            ]
        ],
        'cargas' => [
            [
                'batch_id' => 'a8b71234-c567-4e89-b012-def345678901',
                'nombre_archivo' => 'SOFIA_PLUS_CORTE_OCTUBRE_2026.xlsx',
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
                'usuario_id' => 'usr-lider-02',
                'usuario_nombre' => 'Ing. Carlos Alberto Mendoza Silva',
                'usuario_rol' => 'LIDER_FORMACION',
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
                'usuario_id' => 'usr-lider-02',
                'usuario_nombre' => 'Ing. Carlos Alberto Mendoza Silva',
                'usuario_rol' => 'LIDER_FORMACION',
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
     * Obtiene programas de formación
     */
    public function getProgramas(): array {
        if ($this->isLive) {
            $data = $this->supabase->get('programas', ['select' => '*']);
            if (!empty($data)) return $data;
        }
        return $_SESSION['sena_mock_data']['programas'] ?? [];
    }

    /**
     * Obtiene las cargas/lotes de archivo
     */
    public function getCargas(): array {
        if ($this->isLive) {
            $data = $this->supabase->get('cargas_archivo', ['select' => '*', 'order' => 'created_at.desc']);
            if (!empty($data)) return $data;
        }
        return $_SESSION['sena_mock_data']['cargas'] ?? [];
    }

    /**
     * Obtiene todos los aprendices y sus juicios consolidados
     */
    public function getAprendices(): array {
        if ($this->isLive) {
            // Traer aprendices de Supabase
            $aprendices = $this->supabase->get('aprendices', ['select' => '*', 'order' => 'nombre_completo.asc']);
            if (!empty($aprendices)) {
                // Traer evaluaciones asociadas
                $juicios = $this->supabase->get('juicios_evaluativos', ['select' => '*']);
                $juiciosPorDoc = [];
                foreach ($juicios as $j) {
                    $juiciosPorDoc[$j['numero_identificacion']][] = $j;
                }

                foreach ($aprendices as &$ap) {
                    $doc = $ap['numero_identificacion'];
                    $ap['evaluaciones'] = $juiciosPorDoc[$doc] ?? [];
                }
                return $aprendices;
            }
        }
        return $_SESSION['sena_mock_data']['aprendices'] ?? [];
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

        if ($this->isLive) {
            // 1. Guardar en tabla cargas_archivo de Supabase
            $this->supabase->insert('cargas_archivo', $cargaData);

            // 2. Insertar registros en juicios_evaluativos
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

        // También sincronizar en sesión local
        array_unshift($_SESSION['sena_mock_data']['cargas'], $cargaData);

        // Actualizar métricas de los aprendices en memoria
        foreach ($filasValidas as $f) {
            $doc = $f['numero_identificacion'];
            $found = false;

            foreach ($_SESSION['sena_mock_data']['aprendices'] as &$ap) {
                if ($ap['numero_identificacion'] === $doc) {
                    $found = true;
                    // Buscar si la competencia y RAP ya existen
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

                    // Recalcular métricas
                    $ap['total_raps'] = count($ap['evaluaciones']);
                    $ap['raps_aprobados'] = count(array_filter($ap['evaluaciones'], fn($e) => $e['juicio'] === 'APROBADO'));
                    $ap['raps_pendientes'] = count(array_filter($ap['evaluaciones'], fn($e) => $e['juicio'] === 'POR_EVALUAR'));
                    $ap['raps_no_aprobados'] = count(array_filter($ap['evaluaciones'], fn($e) => $e['juicio'] === 'NO_APROBADO'));
                    $ap['porcentaje_avance'] = $ap['total_raps'] > 0 ? (int)round(($ap['raps_aprobados'] / $ap['total_raps']) * 100) : 0;
                    $ap['estado_academico'] = $ap['raps_no_aprobados'] > 0 ? 'En Riesgo' : ($ap['porcentaje_avance'] === 100 ? 'Por Certificar' : 'Al Día');
                    break;
                }
            }

            // Si el aprendiz es nuevo, agregarlo
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
            // Actualizar estado en cargas_archivo
            $this->supabase->update(
                'cargas_archivo',
                ['estado' => 'REVOCADO', 'motivo_rollback' => $motivo],
                ['batch_id' => 'eq.' . $batchId]
            );
            // Marcar evaluaciones o borrarlas
            $this->supabase->delete('juicios_evaluativos', ['batch_id' => 'eq.' . $batchId]);
        }

        // Actualizar en sesión local
        foreach ($_SESSION['sena_mock_data']['cargas'] as &$c) {
            if ($c['batch_id'] === $batchId) {
                $c['estado'] = 'REVOCADO';
                $c['motivo_rollback'] = $motivo;
                break;
            }
        }

        // Quitar las evaluaciones pertenecientes a este batch
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
