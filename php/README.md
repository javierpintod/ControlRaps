# 🎓 SENA Analytics Institutional - ControlRaps (Versión PHP + XAMPP + Supabase)

Plataforma institucional de auditoría y analítica de juicios evaluativos del SENA con soporte para ingesta por lotes (Excel/CSV), control DirectQuery, matrices de aprendizaje y base de datos relacional en **Supabase (PostgreSQL)** alojada y publicada en un entorno local **XAMPP**.

---

## 🚀 Despliegue Inmediato en XAMPP

La aplicación ya ha sido construida y desplegada en tu servidor web Apache de XAMPP:

- **Ruta de Despliegue en XAMPP:** `C:\xampp\htdocs\controlraps`
- **URL de Acceso Local:** [http://localhost/controlraps/](http://localhost/controlraps/)
- **Código Fuente en Proyecto:** Carpeta `php/`

Si realizas cambios en la carpeta `php/`, puedes sincronizarlos ejecutando en PowerShell:
```powershell
powershell -ExecutionPolicy Bypass -File "php/deploy_to_xampp.ps1"
```

---

## 🗄️ Conexión con Supabase (Paso a Paso)

La aplicación cuenta con un cliente cURL PostgREST (`includes/Supabase.php`) de alta velocidad y un **Modo Demostración Automático** que permite utilizar todas las pantallas interactivamente mientras configuras tus credenciales de Supabase.

### 1. Crear el Proyecto en Supabase
1. Ingresa a [https://supabase.com](https://supabase.com) e inicia sesión o crea una cuenta gratuita.
2. Crea un nuevo proyecto (por ejemplo: `sena-controlraps`).

### 2. Ejecutar el Script de Base de Datos
1. En el panel de Supabase, ve a la sección **SQL Editor**.
2. Abre o copia el contenido del archivo:
   [`php/database/supabase_schema.sql`](file:///c:/Users/pinto/Downloads/clase%207%20de%20octubre/ControlRaps/php/database/supabase_schema.sql)
3. Pega el script en el editor y presiona **Run**.
   - Creará las tablas: `usuarios`, `programas`, `cargas_archivo`, `aprendices`, `juicios_evaluativos`.
   - Creará los índices optimizados para DirectQuery.
   - Creará las políticas de Row Level Security (RLS).
   - Insertará los datos institucionales semilla.

### 3. Configurar tus Credenciales en PHP
1. En Supabase, dirígete a **Project Settings > API**.
2. Copia tu **Project URL** y tu **anon public key**.
3. Abre el archivo [`php/config.php`](file:///c:/Users/pinto/Downloads/clase%207%20de%20octubre/ControlRaps/php/config.php) (y en `C:\xampp\htdocs\controlraps\config.php`) y reemplaza:
   ```php
   define('SUPABASE_URL', 'https://tu-proyecto.supabase.co');
   define('SUPABASE_KEY', 'tu_anon_public_key_aqui');
   ```
4. Abre [http://localhost/controlraps/test_connection.php](http://localhost/controlraps/test_connection.php) para verificar que la latencia y la conexión estén en verde.

---

## 👥 Usuarios Institucionales y Roles (RBAC)

La aplicación cuenta con control de acceso basado en roles. En la pantalla de login ([http://localhost/controlraps/login.php](http://localhost/controlraps/login.php)) dispones de botones de acceso rápido con 1 solo clic:

| Usuario | Correo | Rol | Permisos |
|---|---|---|---|
| **Dr. Fernando Arango Botero** | `admin@sena.edu.co` | **ADMIN** | Control total, auditoría, rollbacks, carga masiva, diagnóstico. |
| **Ing. Carlos Alberto Mendoza** | `gestor@sena.edu.co` | **LIDER_FORMACION** | Gestión académica, carga masiva Excel/CSV, rollbacks de lote. |
| **Lic. Martha Gómez Restrepo** | `instructor@sena.edu.co` | **INSTRUCTOR** | Solo lectura del visor DirectQuery y expedientes (sin permisos de carga). |

*Contraseña por defecto para pruebas: `admin123` / `gestor123` / `instructor123` o `sena2026`.*

---

## 🛠️ Módulos y Características

### 1. Visor Power BI (DirectQuery) (`index.php`)
- **Tarjetas KPI**: Total Aprendices, Juicios Evaluativos, Tasa de Aprobación Global (%) y Aprendices en Riesgo.
- **Gráficos Interactivos (Chart.js)**: Distribución de Estados Académicos (Doughnut) y Tasa de Aprobación por Programa (Barras).
- **Filtros Dinámicos**: Filtro por Programa, Filtro "Solo Aprendices Pendientes o En Riesgo" y buscador en vivo.
- **Inspector DAX / SQL**: Muestra las consultas SQL enviadas a Supabase y fórmulas DAX Tabulares.
- **Medidor de Latencia DirectQuery**: Muestra en tiempo real la velocidad de respuesta (ms) hacia la base de datos.

### 2. Expediente del Aprendiz y Paz y Salvo Imprimible
- Visualización de la totalidad de RAPs evaluados con estado, fecha e instructor evaluador.
- **Sello Institucional de Paz y Salvo**: Se activa automáticamente cuando el aprendiz alcanza el 100% de RAPs aprobados.
- **Botón de Impresión Directa**: Optimizado con estilos `@media print` para exportar a PDF o impresora física con formato oficial SENA.

### 3. Carga Masiva (Batch Ingestion) (`upload.php`)
- Carga de archivos `.xlsx`, `.xls` y `.csv` de hasta 25 MB mediante Drag and Drop.
- Previsualización instantánea en cliente utilizando **SheetJS**.
- Normalización automática de juicios evaluativos (Aprobado, Por Evaluar, No Aprobado).
- Descarga de plantilla institucional oficial (`api/download_template.php`).
- Detección y reporte exportable en CSV de inconsistencias.
- Ingesta atómica hacia Supabase (`cargas_archivo` y `juicios_evaluativos`).

### 4. Auditoría de Lotes y Reversión Atómica (`history.php`)
- Historial completo de cargas con Batch ID (UUID), responsable, fecha de corte y estado.
- Mecanismo de **Rollback Atómico**: Requiere confirmación con la palabra clave `ROLLBACK` y registro de justificación técnica, actualizando el estado y recalculando las métricas de los aprendices.

### 5. Diagnóstico de Conexión (`test_connection.php`)
- Monitoreo de latencia y estado de la API de Supabase en vivo.
- Instrucciones detalladas de despliegue.

---

## 📁 Estructura del Proyecto PHP

```text
php/
├── api/
│   ├── download_template.php     # Generación de plantilla CSV oficial SENA
│   ├── rollback_batch.php        # API de reversión atómica de lotes
│   └── upload_batch.php          # API de ingesta estructurada a Supabase
├── database/
│   └── supabase_schema.sql       # Script DDL/DML completo para Supabase SQL Editor
├── includes/
│   ├── auth.php                  # Sesiones y middleware RBAC
│   ├── data_helper.php           # Capa de datos y repositorio SENA
│   ├── footer.php                # Pie de página institucional y scripts
│   ├── header.php                # Barra superior SENA, latencia y selector de corte
│   └── Supabase.php              # Cliente cURL PostgREST para Supabase
├── config.php                    # Constantes y credenciales de Supabase
├── deploy_to_xampp.ps1           # Script de despliegue automático hacia htdocs
├── history.php                   # Auditoría de lotes y rollback
├── index.php                     # Dashboard analítico Power BI DirectQuery
├── login.php                     # Inicio de sesión con perfiles de prueba rápida
├── logout.php                    # Cierre de sesión seguro
├── README.md                     # Documentación completa del proyecto
└── test_connection.php           # Diagnóstico y estado de conexión
```
