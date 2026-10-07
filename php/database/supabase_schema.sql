-- ============================================================================
-- SENA ANALYTICS INSTITUTIONAL - CONTROLRAPS
-- Script de Base de Datos para Supabase (PostgreSQL 15+)
-- Ejecutar este archivo completo en el SQL Editor de tu proyecto en Supabase
-- ============================================================================

-- 1. Extensiones necesarias
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 2. Limpieza previa de tablas (opcional si ya existían)
DROP TABLE IF EXISTS juicios_evaluativos CASCADE;
DROP TABLE IF EXISTS aprendices CASCADE;
DROP TABLE IF EXISTS cargas_archivo CASCADE;
DROP TABLE IF EXISTS programas CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;

-- ============================================================================
-- 3. TABLA: usuarios (Autenticación y RBAC institucional)
-- ============================================================================
CREATE TABLE usuarios (
    id VARCHAR(50) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL CHECK (rol IN ('ADMIN', 'LIDER_FORMACION', 'INSTRUCTOR')),
    cargo VARCHAR(255),
    password VARCHAR(255) DEFAULT 'sena2026',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================================
-- 4. TABLA: programas (Programas de formación profesional del SENA)
-- ============================================================================
CREATE TABLE programas (
    codigo_programa VARCHAR(50) PRIMARY KEY,
    nombre_programa VARCHAR(255) NOT NULL,
    total_aprendices INT DEFAULT 0,
    tasa_aprobacion NUMERIC(5,2) DEFAULT 0.0,
    fichas_asociadas TEXT[] DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================================
-- 5. TABLA: cargas_archivo (Auditoría de Ingesta por Lotes y Rollbacks)
-- ============================================================================
CREATE TABLE cargas_archivo (
    batch_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nombre_archivo VARCHAR(255) NOT NULL,
    usuario_id VARCHAR(50),
    usuario_nombre VARCHAR(255),
    usuario_rol VARCHAR(50),
    fecha_corte DATE NOT NULL,
    total_registros INT DEFAULT 0,
    registros_exitosos INT DEFAULT 0,
    inconsistencias INT DEFAULT 0,
    estado VARCHAR(20) DEFAULT 'EXITOSO' CHECK (estado IN ('PROCESANDO', 'EXITOSO', 'FALLIDO', 'REVOCADO')),
    motivo_rollback TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================================
-- 6. TABLA: aprendices (Ficha académica y estado de certificación SofiaPlus)
-- ============================================================================
CREATE TABLE aprendices (
    numero_identificacion VARCHAR(20) PRIMARY KEY,
    tipo_identificacion VARCHAR(10) DEFAULT 'CC' CHECK (tipo_identificacion IN ('CC', 'TI', 'CE', 'PEP', 'PASAPORTE')),
    nombre_completo VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    codigo_programa VARCHAR(50) REFERENCES programas(codigo_programa) ON DELETE SET NULL,
    nombre_programa VARCHAR(255),
    total_raps INT DEFAULT 0,
    raps_aprobados INT DEFAULT 0,
    raps_pendientes INT DEFAULT 0,
    raps_no_aprobados INT DEFAULT 0,
    porcentaje_avance INT DEFAULT 0,
    estado_academico VARCHAR(30) DEFAULT 'Al Día' CHECK (estado_academico IN ('Al Día', 'En Riesgo', 'Por Certificar')),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================================
-- 7. TABLA: juicios_evaluativos (Matriz de Resultados de Aprendizaje - RAPs)
-- ============================================================================
CREATE TABLE juicios_evaluativos (
    id VARCHAR(100) PRIMARY KEY,
    batch_id UUID REFERENCES cargas_archivo(batch_id) ON DELETE CASCADE,
    numero_identificacion VARCHAR(20) REFERENCES aprendices(numero_identificacion) ON DELETE CASCADE,
    competencia TEXT NOT NULL,
    resultado_aprendizaje TEXT NOT NULL,
    juicio VARCHAR(20) NOT NULL CHECK (juicio IN ('APROBADO', 'POR_EVALUAR', 'NO_APROBADO')),
    fecha_evaluacion DATE,
    instructor_evaluador VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================================
-- 8. ÍNDICES DE RENDIMIENTO (Optimización para DirectQuery)
-- ============================================================================
CREATE INDEX idx_juicios_aprendiz ON juicios_evaluativos(numero_identificacion);
CREATE INDEX idx_juicios_batch ON juicios_evaluativos(batch_id);
CREATE INDEX idx_cargas_fecha_corte ON cargas_archivo(fecha_corte);
CREATE INDEX idx_aprendices_programa ON aprendices(codigo_programa);

-- ============================================================================
-- 9. HABILITAR ROW LEVEL SECURITY (RLS) Y POLÍTICAS DE ACCESO
-- ============================================================================
ALTER TABLE usuarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE programas ENABLE ROW LEVEL SECURITY;
ALTER TABLE cargas_archivo ENABLE ROW LEVEL SECURITY;
ALTER TABLE aprendices ENABLE ROW LEVEL SECURITY;
ALTER TABLE juicios_evaluativos ENABLE ROW LEVEL SECURITY;

-- Políticas de lectura y escritura para el servicio web (rol anon y authenticated)
CREATE POLICY "Permitir lectura a clientes autorizados en usuarios" ON usuarios FOR SELECT USING (true);
CREATE POLICY "Permitir todo en programas" ON programas FOR ALL USING (true);
CREATE POLICY "Permitir todo en cargas_archivo" ON cargas_archivo FOR ALL USING (true);
CREATE POLICY "Permitir todo en aprendices" ON aprendices FOR ALL USING (true);
CREATE POLICY "Permitir todo en juicios_evaluativos" ON juicios_evaluativos FOR ALL USING (true);

-- ============================================================================
-- 10. DATOS SEMILLA (SEED DATA INSTITUCIONAL SENA)
-- ============================================================================

-- Usuarios
INSERT INTO usuarios (id, email, nombre, rol, cargo, password) VALUES
('usr-admin-01', 'admin@sena.edu.co', 'Dr. Fernando Arango Botero', 'ADMIN', 'Administrador del Sistema y Auditoría DirectQuery', 'admin123'),
('usr-lider-02', 'gestor@sena.edu.co', 'Ing. Carlos Alberto Mendoza Silva', 'LIDER_FORMACION', 'Líder / Gestor de Formación Profesional Integral', 'gestor123'),
('usr-inst-03', 'instructor@sena.edu.co', 'Lic. Martha Gómez Restrepo', 'INSTRUCTOR', 'Instructor Técnico - CEET Regional Distrito Capital', 'instructor123');

-- Programas
INSERT INTO programas (codigo_programa, nombre_programa, total_aprendices, tasa_aprobacion, fichas_asociadas) VALUES
('228106', 'Análisis y Desarrollo de Software (ADSO)', 84, 88.5, ARRAY['2671982', '2710493', '2810291']),
('228118', 'Gestión de Redes y Ciberseguridad', 48, 83.2, ARRAY['2710493', '2799102']),
('228120', 'Inteligencia Artificial Aplicada a Negocios', 32, 92.0, ARRAY['2821094']),
('123112', 'Contabilidad y Finanzas Públicas', 60, 79.4, ARRAY['2598301', '2610495']);

-- Cargas históricas
INSERT INTO cargas_archivo (batch_id, nombre_archivo, usuario_id, usuario_nombre, usuario_rol, fecha_corte, total_registros, registros_exitosos, inconsistencias, estado, created_at) VALUES
('a8b71234-c567-4e89-b012-def345678901', 'SOFIA_PLUS_CORTE_OCTUBRE_2026.xlsx', 'usr-lider-02', 'Ing. Carlos Alberto Mendoza Silva', 'LIDER_FORMACION', '2026-10-01', 1450, 1450, 0, 'EXITOSO', '2026-10-01 09:14:22'),
('b9c82345-d678-4f90-c123-efa456789012', 'JUICIOS_EVALUATIVOS_SEP_QUINCENA2.xlsx', 'usr-lider-02', 'Ing. Carlos Alberto Mendoza Silva', 'LIDER_FORMACION', '2026-09-15', 1220, 1220, 0, 'EXITOSO', '2026-09-15 16:40:11'),
('c0d93456-e789-4a01-d234-fab567890123', 'REPORTE_CONSOLIDADO_AGOSTO_2026.xlsx', 'usr-admin-01', 'Dr. Fernando Arango Botero', 'ADMIN', '2026-08-30', 1100, 1100, 0, 'EXITOSO', '2026-08-30 11:10:05'),
('d1e04567-f890-4b12-e345-abc678901234', 'CORTE_ERRONEO_INCONSISTENCIAS.xlsx', 'usr-lider-02', 'Ing. Carlos Alberto Mendoza Silva', 'LIDER_FORMACION', '2026-08-15', 420, 408, 12, 'REVOCADO', '2026-08-15 14:02:19');

-- Aprendices iniciales
INSERT INTO aprendices (numero_identificacion, tipo_identificacion, nombre_completo, email, codigo_programa, nombre_programa, total_raps, raps_aprobados, raps_pendientes, raps_no_aprobados, porcentaje_avance, estado_academico) VALUES
('1014298102', 'CC', 'Valentina Ríos Cárdenas', 'vrios@soy.sena.edu.co', '228106', 'Análisis y Desarrollo de Software (ADSO)', 4, 4, 0, 0, 100, 'Por Certificar'),
('1020491820', 'CC', 'Mateo Alejandro Suárez Bermúdez', 'msuarez@soy.sena.edu.co', '228106', 'Análisis y Desarrollo de Software (ADSO)', 3, 1, 2, 0, 33, 'Al Día'),
('1032890145', 'CC', 'Daniel Fernando Gutiérrez Pinzón', 'dgutierrez@soy.sena.edu.co', '228106', 'Análisis y Desarrollo de Software (ADSO)', 2, 0, 1, 1, 0, 'En Riesgo'),
('1077654321', 'TI', 'Camila Andrea Montoya Restrepo', 'cmontoya@soy.sena.edu.co', '228118', 'Gestión de Redes y Ciberseguridad', 2, 1, 1, 0, 50, 'Al Día'),
('1098456123', 'CC', 'Esteban José Herrera Morales', 'eherrera@soy.sena.edu.co', '228118', 'Gestión de Redes y Ciberseguridad', 1, 1, 0, 0, 100, 'Por Certificar'),
('1011889922', 'CC', 'Andrés Felipe Pardo Caicedo', 'apardo@soy.sena.edu.co', '228120', 'Inteligencia Artificial Aplicada a Negocios', 1, 1, 0, 0, 100, 'Por Certificar'),
('1033221199', 'CC', 'Jessica Paola López Narváez', 'jlopez@soy.sena.edu.co', '228120', 'Inteligencia Artificial Aplicada a Negocios', 2, 0, 1, 1, 0, 'En Riesgo');

-- Juicios evaluativos de ejemplo
INSERT INTO juicios_evaluativos (id, batch_id, numero_identificacion, competencia, resultado_aprendizaje, juicio, fecha_evaluacion, instructor_evaluador) VALUES
('eval-val-1', 'a8b71234-c567-4e89-b012-def345678901', '1014298102', 'Especificación de Requisitos de Software (220501096)', 'Caracterizar los procesos de la organización de acuerdo con el marco de referencia y estándares.', 'APROBADO', '2026-09-18', 'Ing. Carlos Alberto Mendoza'),
('eval-val-2', 'a8b71234-c567-4e89-b012-def345678901', '1014298102', 'Modelado y Gestión de Bases de Datos (220501093)', 'Construir bases de datos relacionales y no relacionales según especificaciones técnicas.', 'APROBADO', '2026-09-18', 'Ing. Carlos Alberto Mendoza'),
('eval-val-3', 'a8b71234-c567-4e89-b012-def345678901', '1014298102', 'Desarrollo de Software Web Full-Stack (220501095)', 'Implementar servicios web RESTful y microservicios seguros con autenticación JWT.', 'APROBADO', '2026-09-20', 'Lic. Martha Gómez Restrepo'),
('eval-val-4', 'a8b71234-c567-4e89-b012-def345678901', '1014298102', 'Comunicación en Segunda Lengua - Inglés (240201524)', 'Interactuar en lengua inglesa de forma oral y escrita en contextos laborales y técnicos.', 'APROBADO', '2026-09-22', 'Lic. Fernando Ospina'),

('eval-mat-1', 'a8b71234-c567-4e89-b012-def345678901', '1020491820', 'Especificación de Requisitos de Software (220501096)', 'Elaborar diagramas y modelos de arquitectura según requerimientos funcionales.', 'APROBADO', '2026-09-14', 'Ing. Carlos Alberto Mendoza'),
('eval-mat-2', 'a8b71234-c567-4e89-b012-def345678901', '1020491820', 'Desarrollo de Software Web Full-Stack (220501095)', 'Implementar servicios web RESTful y microservicios seguros con autenticación JWT.', 'POR_EVALUAR', '2026-09-15', 'Lic. Martha Gómez Restrepo'),
('eval-mat-3', 'a8b71234-c567-4e89-b012-def345678901', '1020491820', 'Modelado y Gestión de Bases de Datos (220501093)', 'Aplicar procedimientos de normalización y optimización de consultas SQL.', 'POR_EVALUAR', '2026-09-15', 'Ing. Carlos Alberto Mendoza'),

('eval-dan-1', 'a8b71234-c567-4e89-b012-def345678901', '1032890145', 'Modelado y Gestión de Bases de Datos (220501093)', 'Aplicar procedimientos de normalización y optimización SQL bajo estándares ACID.', 'NO_APROBADO', '2026-09-10', 'Ing. Carlos Alberto Mendoza'),
('eval-dan-2', 'a8b71234-c567-4e89-b012-def345678901', '1032890145', 'Desarrollo de Software Web Full-Stack (220501095)', 'Construir interfaces de usuario reactivas con diseño accesible.', 'POR_EVALUAR', '2026-09-11', 'Lic. Martha Gómez Restrepo'),

('eval-cam-1', 'a8b71234-c567-4e89-b012-def345678901', '1077654321', 'Arquitectura y Seguridad en Redes WAN (220501098)', 'Configurar enrutamiento seguro y listas de control de acceso ACL.', 'APROBADO', '2026-09-22', 'Ing. Diana Marcela Rincón'),
('eval-cam-2', 'a8b71234-c567-4e89-b012-def345678901', '1077654321', 'Ciberseguridad y Análisis Forense (220501099)', 'Identificar vectores de ataque y vulnerabilidades en infraestructura perimetral.', 'POR_EVALUAR', '2026-09-23', 'Ing. Diana Marcela Rincón'),

('eval-est-1', 'a8b71234-c567-4e89-b012-def345678901', '1098456123', 'Ciberseguridad y Análisis Forense (220501099)', 'Implementar contramedidas y políticas de seguridad bajo estándar ISO 27001.', 'APROBADO', '2026-09-25', 'Ing. Diana Marcela Rincón'),

('eval-and-1', 'a8b71234-c567-4e89-b012-def345678901', '1011889922', 'Pipelines de Machine Learning y Datos (220501102)', 'Entrenar y desplegar modelos supervisados para predicción de series temporales.', 'APROBADO', '2026-09-02', 'Mag. Julián David Quintero'),

('eval-jes-1', 'a8b71234-c567-4e89-b012-def345678901', '1033221199', 'Pipelines de Machine Learning y Datos (220501102)', 'Evaluar métricas de rendimiento y calibración de hiperparámetros.', 'NO_APROBADO', '2026-09-12', 'Mag. Julián David Quintero'),
('eval-jes-2', 'a8b71234-c567-4e89-b012-def345678901', '1033221199', 'Gobierno y Ética en Modelos de IA (220501103)', 'Mitigar sesgos algorítmicos en datasets de entrenamiento.', 'POR_EVALUAR', '2026-09-13', 'Mag. Julián David Quintero');
