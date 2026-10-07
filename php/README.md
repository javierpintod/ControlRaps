# 🎓 SENA Analytics Institutional - ControlRaps (PHP + XAMPP + Supabase)

Plataforma institucional de auditoría y analítica de juicios evaluativos del SENA con arquitectura multi-centro y multi-coordinación, soporte para ingesta por lotes (Excel/CSV), control DirectQuery, dashboard para el Subdirector y base de datos relacional en **Supabase (PostgreSQL)** desplegada en **XAMPP**.

---

## 🌟 Novedades: Múltiples Centros, Coordinaciones y Subdirección

1. **Catálogo Nacional Multi-Centro con Autocompletado Predictivo:**
   * Selector con buscador en vivo en la cabecera: al escribir (ej. `CEET`, `Antioquia`, `Cundinamarca`, `Agro`, etc.) despliega instantáneamente los centros coincidentes con su regional, código y subdirector.
   * Persistencia en sesión del centro activo (`$_SESSION['active_centro_id']`).
   * Catálogo de 15+ centros oficiales del SENA (Distrito Capital, Antioquia, Cundinamarca, Atlántico, Caldas, Valle, Risaralda, etc.).

2. **Múltiples Coordinaciones Académicas:**
   * Cada centro agrupa sus coordinaciones (ej. Teleinformática y Software, Redes y Ciberseguridad, Electrónica e IoT, Electricidad y Energía Solar, etc.).
   * Selector dinámico de coordinación en la cabecera.
   * Filtro y reportes exportables en CSV/Excel por coordinación.
   * Nuevo rol `COORDINADOR` con permisos para gestionar sus fichas, cargar lotes y descargar reportes.

3. **Dashboard Estratégico del Subdirector (`subdirector.php`):**
   * Panel de control gerencial para el Subdirector de Centro.
   * **Tarjetas KPI Consolidadas:** Población activa, total coordinaciones, tasa de certificación y aprendices en riesgo del centro.
   * **Semáforo de Rendimiento por Coordinación:** Indicador visual de cumplimiento (<span style="color:green">Verde &ge;85%</span>, <span style="color:orange">Amarillo 70%-84%</span>, <span style="color:red">Rojo &lt;70% o riesgo</span>).
   * **Gráficos Comparativos (Chart.js):** Comparativa de tasas de aprobación y distribución de población por coordinación.
   * **Matriz Tabular Gerencial:** Tabla con metas vs tasa real, brecha y exportación de informe consolidado.

---

## 🚀 Despliegue en XAMPP

- **Ubicación en XAMPP:** `C:\xampp\htdocs\controlraps`
- **URL Local:** [http://localhost/controlraps/](http://localhost/controlraps/)
- **Dashboard del Subdirector:** [http://localhost/controlraps/subdirector.php](http://localhost/controlraps/subdirector.php)
- **Diagnóstico Supabase:** [http://localhost/controlraps/test_connection.php](http://localhost/controlraps/test_connection.php)

Para sincronizar cualquier cambio hacia XAMPP:
```powershell
powershell -ExecutionPolicy Bypass -File "php/deploy_to_xampp.ps1"
```

---

## 👥 Usuarios y Perfiles Institucionales (RBAC)

En la pantalla de acceso ([http://localhost/controlraps/login.php](http://localhost/controlraps/login.php)) dispones de accesos rápidos con 1 solo clic:

| Perfil | Correo | Rol | Centro / Coordinación |
|---|---|---|---|
| **Dr. Jorge Eduardo Londoño** | `subdirector@sena.edu.co` | **SUBDIRECTOR** | CEET - Despacho de Subdirección (Visión Gerencial) |
| **Ing. Claudia Patricia Duarte** | `coord.software@sena.edu.co` | **COORDINADOR** | CEET - Teleinformática y Desarrollo de Software |
| **Ing. Harold Mauricio Morales** | `coord.redes@sena.edu.co` | **COORDINADOR** | CEET - Redes y Ciberseguridad |
| **Dr. Fernando Arango Botero** | `admin@sena.edu.co` | **ADMIN** | Dirección General (Acceso Nacional) |
| **Ing. Carlos Alberto Mendoza** | `gestor@sena.edu.co` | **LIDER_FORMACION** | CEET - Gestión y Auditoría de Lotes |
| **Lic. Martha Gómez Restrepo** | `instructor@sena.edu.co` | **INSTRUCTOR** | CEET - Solo Lectura de Aprendices |

*Clave por defecto: `subdirector123` / `coord123` / `admin123` / `gestor123` / `instructor123`.*

---

## 🗄️ Supabase (PostgreSQL 15+)

El script DDL actualizado con soporte para múltiples centros y coordinaciones se encuentra en:
[`php/database/supabase_schema.sql`](file:///c:/Users/pinto/Downloads/clase%207%20de%20octubre/ControlRaps/php/database/supabase_schema.sql)
