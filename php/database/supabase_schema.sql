-- ============================================================================
-- SENA ANALYTICS INSTITUTIONAL - CONTROLRAPS
-- Script de Base de Datos para Supabase (PostgreSQL 15+)
-- Compatible con Múltiples Centros, Múltiples Coordinaciones y Subdirección
-- ============================================================================

-- 1. Extensiones necesarias
CREATE EXTENSION IF NOT EXISTS "pgcrypto";
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 2. Limpieza previa de tablas (opcional)
DROP TABLE IF EXISTS juicios_evaluativos CASCADE;
DROP TABLE IF EXISTS aprendices CASCADE;
DROP TABLE IF EXISTS cargas_archivo CASCADE;
DROP TABLE IF EXISTS programas CASCADE;
DROP TABLE IF EXISTS coordinaciones CASCADE;
DROP TABLE IF EXISTS centros CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;

-- ============================================================================
-- 3. TABLA: centros (Centros de Formación Profesional Integral del SENA)
-- ============================================================================
CREATE TABLE centros (
    id VARCHAR(50) PRIMARY KEY,
    codigo_centro VARCHAR(20) NOT NULL,
    nombre_centro VARCHAR(255) NOT NULL,
    regional VARCHAR(100) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    subdirector VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================================
-- 4. TABLA: coordinaciones (Coordinaciones Académicas adscritas a cada Centro)
-- ============================================================================
CREATE TABLE coordinaciones (
    id VARCHAR(50) PRIMARY KEY,
    centro_id VARCHAR(50) REFERENCES centros(id) ON DELETE CASCADE,
    nombre_coordinacion VARCHAR(255) NOT NULL,
    coordinador_nombre VARCHAR(255) NOT NULL,
    coordinador_email VARCHAR(255) NOT NULL,
    meta_tasa_aprobacion NUMERIC(5,2) DEFAULT 85.0,
    programas_asociados TEXT[] DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================================
-- 5. TABLA: usuarios (Autenticación y RBAC institucional con Centro y Coordinación)
-- ============================================================================
CREATE TABLE usuarios (
    id VARCHAR(50) PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL CHECK (rol IN ('ADMIN', 'SUBDIRECTOR', 'LIDER_FORMACION', 'COORDINADOR', 'INSTRUCTOR')),
    cargo VARCHAR(255),
    password VARCHAR(255) DEFAULT 'sena2026',
    centro_id VARCHAR(50) REFERENCES centros(id) ON DELETE SET NULL,
    coordinacion_id VARCHAR(50) REFERENCES coordinaciones(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================================
-- 6. TABLA: programas (Programas de formación profesional del SENA)
-- ============================================================================
CREATE TABLE programas (
    codigo_programa VARCHAR(50) PRIMARY KEY,
    nombre_programa VARCHAR(255) NOT NULL,
    centro_id VARCHAR(50) REFERENCES centros(id) ON DELETE SET NULL,
    coordinacion_id VARCHAR(50) REFERENCES coordinaciones(id) ON DELETE SET NULL,
    total_aprendices INT DEFAULT 0,
    tasa_aprobacion NUMERIC(5,2) DEFAULT 0.0,
    fichas_asociadas TEXT[] DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================================================
-- 7. TABLA: cargas_archivo (Auditoría de Ingesta por Lotes y Rollbacks)
-- ============================================================================
CREATE TABLE cargas_archivo (
    batch_id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    nombre_archivo VARCHAR(255) NOT NULL,
    centro_id VARCHAR(50) REFERENCES centros(id) ON DELETE SET NULL,
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
-- 8. TABLA: aprendices (Ficha académica y estado de certificación SofiaPlus)
-- ============================================================================
CREATE TABLE aprendices (
    numero_identificacion VARCHAR(20) PRIMARY KEY,
    tipo_identificacion VARCHAR(10) DEFAULT 'CC' CHECK (tipo_identificacion IN ('CC', 'TI', 'CE', 'PEP', 'PASAPORTE')),
    nombre_completo VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    centro_id VARCHAR(50) REFERENCES centros(id) ON DELETE SET NULL,
    coordinacion_id VARCHAR(50) REFERENCES coordinaciones(id) ON DELETE SET NULL,
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
-- 9. TABLA: juicios_evaluativos (Matriz de Resultados de Aprendizaje - RAPs)
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
-- 10. ÍNDICES DE RENDIMIENTO (DirectQuery & Multi-Tenant Centros)
-- ============================================================================
CREATE INDEX idx_centros_regional ON centros(regional);
CREATE INDEX idx_coordinaciones_centro ON coordinaciones(centro_id);
CREATE INDEX idx_aprendices_centro ON aprendices(centro_id);
CREATE INDEX idx_aprendices_coordinacion ON aprendices(coordinacion_id);
CREATE INDEX idx_juicios_aprendiz ON juicios_evaluativos(numero_identificacion);
CREATE INDEX idx_juicios_batch ON juicios_evaluativos(batch_id);

-- ============================================================================
-- 11. ROW LEVEL SECURITY (RLS)
-- ============================================================================
ALTER TABLE centros ENABLE ROW LEVEL SECURITY;
ALTER TABLE coordinaciones ENABLE ROW LEVEL SECURITY;
ALTER TABLE usuarios ENABLE ROW LEVEL SECURITY;
ALTER TABLE programas ENABLE ROW LEVEL SECURITY;
ALTER TABLE cargas_archivo ENABLE ROW LEVEL SECURITY;
ALTER TABLE aprendices ENABLE ROW LEVEL SECURITY;
ALTER TABLE juicios_evaluativos ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Permitir todo en centros" ON centros FOR ALL USING (true);
CREATE POLICY "Permitir todo en coordinaciones" ON coordinaciones FOR ALL USING (true);
CREATE POLICY "Permitir todo en usuarios" ON usuarios FOR ALL USING (true);
CREATE POLICY "Permitir todo en programas" ON programas FOR ALL USING (true);
CREATE POLICY "Permitir todo en cargas_archivo" ON cargas_archivo FOR ALL USING (true);
CREATE POLICY "Permitir todo en aprendices" ON aprendices FOR ALL USING (true);
CREATE POLICY "Permitir todo en juicios_evaluativos" ON juicios_evaluativos FOR ALL USING (true);

-- ============================================================================
-- 12. DATOS SEMILLA (CENTROS, COORDINACIONES Y USUARIOS)
-- ============================================================================

-- Centros del SENA
INSERT INTO centros (id, codigo_centro, nombre_centro, regional, ciudad, subdirector) VALUES
('CENTRO-CEET', '9201', 'Centro de Electricidad, Electrónica y Telecomunicaciones (CEET)', 'Distrito Capital', 'Bogotá D.C.', 'Dr. Jorge Eduardo Londoño Ulloa'),
('CENTRO-CGMLTI', '9202', 'Centro de Gestión de Mercados, Logística y TIC (CGMLTI)', 'Distrito Capital', 'Bogotá D.C.', 'Dra. María Elena Restrepo'),
('CENTRO-CSF', '9204', 'Centro de Servicios Financieros (CSF)', 'Distrito Capital', 'Bogotá D.C.', 'Dr. Wilson Andrés Pardo'),
('CENTRO-CMM', '9205', 'Centro Metalmecánico', 'Distrito Capital', 'Bogotá D.C.', 'Ing. Rafael Antonio Mejía'),
('CENTRO-CBA', '9101', 'Centro de Biotecnología Agropecuaria (CBA)', 'Cundinamarca', 'Mosquera', 'Dr. José Vicente Morales'),
('CENTRO-CTM', '9301', 'Centro Tecnológico del Mobiliario (CTM)', 'Antioquia', 'Medellín / Itagüí', 'Ing. Juan Carlos Montoya'),
('CENTRO-CPIC', '9401', 'Centro de Procesos Industriales y Construcción', 'Caldas', 'Manizales', 'Dra. Claudia Ximena Zuluaga'),
('CENTRO-CNCA', '9501', 'Centro Nacional Colombo Alemán (CNCA)', 'Atlántico', 'Barranquilla', 'Dr. Sergio Alejandro Orozco'),
('CENTRO-ASTIN', '9601', 'Centro Nacional de Asistencia Técnica a la Industria (ASTIN)', 'Valle del Cauca', 'Cali', 'Ing. Rodrigo Henao Cadavid');

-- Coordinaciones Académicas
INSERT INTO coordinaciones (id, centro_id, nombre_coordinacion, coordinador_nombre, coordinador_email, meta_tasa_aprobacion, programas_asociados) VALUES
('COORD-CEET-01', 'CENTRO-CEET', 'Coordinación de Teleinformática y Desarrollo de Software', 'Ing. Claudia Patricia Duarte Gómez', 'coord.software@sena.edu.co', 90.0, ARRAY['228106', '228120']),
('COORD-CEET-02', 'CENTRO-CEET', 'Coordinación de Redes, Telecomunicaciones y Ciberseguridad', 'Ing. Harold Mauricio Morales Ruiz', 'coord.redes@sena.edu.co', 85.0, ARRAY['228118']),
('COORD-CEET-03', 'CENTRO-CEET', 'Coordinación de Electrónica, Automatización e Internet de las Cosas', 'Ing. Roberto Carlos Peñaloza Rivas', 'coord.electronica@sena.edu.co', 88.0, ARRAY['228130']),
('COORD-CEET-04', 'CENTRO-CEET', 'Coordinación de Electricidad y Sistemas de Energía Solar Fotovoltaica', 'Ing. Diana Marcela Rincón Torres', 'coord.electricidad@sena.edu.co', 82.0, ARRAY['228140']),
('COORD-CSF-01', 'CENTRO-CSF', 'Coordinación de Contabilidad, Auditoría y Finanzas Públicas', 'Dra. Sandra Milena Benavides', 'coord.finanzas@sena.edu.co', 80.0, ARRAY['123112']);

-- Usuarios con Roles y Centros
INSERT INTO usuarios (id, email, nombre, rol, cargo, password, centro_id, coordinacion_id) VALUES
('usr-subdir-01', 'subdirector@sena.edu.co', 'Dr. Jorge Eduardo Londoño Ulloa', 'SUBDIRECTOR', 'Subdirector de Centro - CEET Regional Distrito Capital', 'subdirector123', 'CENTRO-CEET', NULL),
('usr-coord-01', 'coord.software@sena.edu.co', 'Ing. Claudia Patricia Duarte Gómez', 'COORDINADOR', 'Coordinadora Académica de Teleinformática y Desarrollo de Software', 'coord123', 'CENTRO-CEET', 'COORD-CEET-01'),
('usr-coord-02', 'coord.redes@sena.edu.co', 'Ing. Harold Mauricio Morales Ruiz', 'COORDINADOR', 'Coordinador Académico de Redes, Telecomunicaciones y Ciberseguridad', 'coord123', 'CENTRO-CEET', 'COORD-CEET-02'),
('usr-admin-01', 'admin@sena.edu.co', 'Dr. Fernando Arango Botero', 'ADMIN', 'Administrador Nacional del Sistema y Auditoría DirectQuery', 'admin123', NULL, NULL),
('usr-lider-02', 'gestor@sena.edu.co', 'Ing. Carlos Alberto Mendoza Silva', 'LIDER_FORMACION', 'Líder / Gestor de Formación Profesional Integral', 'gestor123', 'CENTRO-CEET', NULL),
('usr-inst-03', 'instructor@sena.edu.co', 'Lic. Martha Gómez Restrepo', 'INSTRUCTOR', 'Instructor Técnico - CEET Regional Distrito Capital', 'instructor123', 'CENTRO-CEET', 'COORD-CEET-01');

-- Programas
INSERT INTO programas (codigo_programa, nombre_programa, centro_id, coordinacion_id, total_aprendices, tasa_aprobacion, fichas_asociadas) VALUES
('228106', 'Análisis y Desarrollo de Software (ADSO)', 'CENTRO-CEET', 'COORD-CEET-01', 84, 88.5, ARRAY['2671982', '2710493', '2810291']),
('228118', 'Gestión de Redes y Ciberseguridad', 'CENTRO-CEET', 'COORD-CEET-02', 48, 83.2, ARRAY['2710493', '2799102']),
('228120', 'Inteligencia Artificial Aplicada a Negocios', 'CENTRO-CEET', 'COORD-CEET-01', 32, 92.0, ARRAY['2821094']),
('123112', 'Contabilidad y Finanzas Públicas', 'CENTRO-CSF', 'COORD-CSF-01', 60, 79.4, ARRAY['2598301', '2610495']);

-- Cargas históricas
INSERT INTO cargas_archivo (batch_id, nombre_archivo, centro_id, usuario_id, usuario_nombre, usuario_rol, fecha_corte, total_registros, registros_exitosos, inconsistencias, estado, created_at) VALUES
('a8b71234-c567-4e89-b012-def345678901', 'SOFIA_PLUS_CORTE_OCTUBRE_2026.xlsx', 'CENTRO-CEET', 'usr-lider-02', 'Ing. Carlos Alberto Mendoza Silva', 'LIDER_FORMACION', '2026-10-01', 1450, 1450, 0, 'EXITOSO', '2026-10-01 09:14:22'),
('b9c82345-d678-4f90-c123-efa456789012', 'JUICIOS_EVALUATIVOS_SEP_QUINCENA2.xlsx', 'CENTRO-CEET', 'usr-coord-01', 'Ing. Claudia Patricia Duarte Gómez', 'COORDINADOR', '2026-09-15', 1220, 1220, 0, 'EXITOSO', '2026-09-15 16:40:11');

-- Aprendices
INSERT INTO aprendices (numero_identificacion, tipo_identificacion, nombre_completo, email, centro_id, coordinacion_id, codigo_programa, nombre_programa, total_raps, raps_aprobados, raps_pendientes, raps_no_aprobados, porcentaje_avance, estado_academico) VALUES
('1014298102', 'CC', 'Valentina Ríos Cárdenas', 'vrios@soy.sena.edu.co', 'CENTRO-CEET', 'COORD-CEET-01', '228106', 'Análisis y Desarrollo de Software (ADSO)', 4, 4, 0, 0, 100, 'Por Certificar'),
('1020491820', 'CC', 'Mateo Alejandro Suárez Bermúdez', 'msuarez@soy.sena.edu.co', 'CENTRO-CEET', 'COORD-CEET-01', '228106', 'Análisis y Desarrollo de Software (ADSO)', 3, 1, 2, 0, 33, 'Al Día'),
('1032890145', 'CC', 'Daniel Fernando Gutiérrez Pinzón', 'dgutierrez@soy.sena.edu.co', 'CENTRO-CEET', 'COORD-CEET-01', '228106', 'Análisis y Desarrollo de Software (ADSO)', 2, 0, 1, 1, 0, 'En Riesgo'),
('1077654321', 'TI', 'Camila Andrea Montoya Restrepo', 'cmontoya@soy.sena.edu.co', 'CENTRO-CEET', 'COORD-CEET-02', '228118', 'Gestión de Redes y Ciberseguridad', 2, 1, 1, 0, 50, 'Al Día'),
('1098456123', 'CC', 'Esteban José Herrera Morales', 'eherrera@soy.sena.edu.co', 'CENTRO-CEET', 'COORD-CEET-02', '228118', 'Gestión de Redes y Ciberseguridad', 1, 1, 0, 0, 100, 'Por Certificar'),
('1011889922', 'CC', 'Andrés Felipe Pardo Caicedo', 'apardo@soy.sena.edu.co', 'CENTRO-CEET', 'COORD-CEET-01', '228120', 'Inteligencia Artificial Aplicada a Negocios', 1, 1, 0, 0, 100, 'Por Certificar'),
('1033221199', 'CC', 'Jessica Paola López Narváez', 'jlopez@soy.sena.edu.co', 'CENTRO-CEET', 'COORD-CEET-01', '228120', 'Inteligencia Artificial Aplicada a Negocios', 2, 0, 1, 1, 0, 'En Riesgo');

-- Juicios evaluativos
INSERT INTO juicios_evaluativos (id, batch_id, numero_identificacion, competencia, resultado_aprendizaje, juicio, fecha_evaluacion, instructor_evaluador) VALUES
('eval-val-1', 'a8b71234-c567-4e89-b012-def345678901', '1014298102', 'Especificación de Requisitos de Software (220501096)', 'Caracterizar los procesos de la organización de acuerdo con el marco de referencia y estándares.', 'APROBADO', '2026-09-18', 'Ing. Carlos Alberto Mendoza'),
('eval-val-2', 'a8b71234-c567-4e89-b012-def345678901', '1014298102', 'Modelado y Gestión de Bases de Datos (220501093)', 'Construir bases de datos relacionales y no relacionales según especificaciones técnicas.', 'APROBADO', '2026-09-18', 'Ing. Carlos Alberto Mendoza'),
('eval-cam-1', 'a8b71234-c567-4e89-b012-def345678901', '1077654321', 'Arquitectura y Seguridad en Redes WAN (220501098)', 'Configurar enrutamiento seguro y listas de control de acceso ACL.', 'APROBADO', '2026-09-22', 'Ing. Diana Marcela Rincón');
