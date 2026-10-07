<?php
/**
 * Módulo de Carga Masiva (Batch Ingestion) de Juicios Evaluativos
 * Protegido por RBAC: Solo ADMIN y LIDER_FORMACION
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data_helper.php';

$currentUser = requireAuth();
requireRole(['ADMIN', 'LIDER_FORMACION']);

$repo = new SenaRepository();
$cargasExistentes = $repo->getCargas();

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb & Header Title -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                <span>Ingesta ETL</span>
                <span>&bull;</span>
                <span class="text-[#0D7A53]">SofiaPlus PostgreSQL</span>
            </div>
            <h2 class="text-xl font-heading font-bold text-[#0C2340] mt-1">
                Carga Masiva de Juicios Evaluativos (Excel / CSV)
            </h2>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                Procesamiento por lotes estructurado con validación estricta de esquemas, trazabilidad atómica y sincronización directa hacia Supabase.
            </p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="<?= BASE_URL ?>/api/download_template.php" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition flex items-center space-x-1.5 border border-slate-300">
                <i data-lucide="download" class="w-4 h-4 text-[#0D7A53]"></i>
                <span>Descargar Plantilla Oficial</span>
            </a>
        </div>
    </div>

    <!-- Step 1: Fecha de Corte -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center space-x-2 text-xs font-bold text-[#0C2340] uppercase tracking-wide">
            <span class="w-5 h-5 rounded-full bg-[#0D7A53] text-white flex items-center justify-center text-[10px]">1</span>
            <span>Paso 1: Seleccionar Fecha de Corte del Snapshot</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Fecha de Corte Institucional:
                </label>
                <div class="relative">
                    <input 
                        type="date" 
                        id="snapshotDate" 
                        value="<?= date('Y-m-d') ?>"
                        onchange="checkCutoffDuplicate(this.value)"
                        class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 font-medium focus:outline-none focus:border-[#0D7A53]"
                    />
                </div>
                <p class="text-[11px] text-slate-400 mt-1">
                    Esta fecha define el snapshot temporal analizado en los tableros de Power BI.
                </p>
            </div>

            <!-- Warning for duplicate cutoff -->
            <div id="duplicate-cutoff-warning" class="hidden p-3 bg-amber-50 border border-amber-300 rounded-lg text-xs text-amber-800 flex items-start space-x-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
                <div>
                    <span class="font-bold">Aviso de Versión de Corte:</span> Ya existe un lote para esta fecha. Al procesar se creará una versión incremental para mantener trazabilidad histórica.
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Drag and drop dropzone -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center space-x-2 text-xs font-bold text-[#0C2340] uppercase tracking-wide">
            <span class="w-5 h-5 rounded-full bg-[#0D7A53] text-white flex items-center justify-center text-[10px]">2</span>
            <span>Paso 2: Subir Archivo Excel (.xlsx) o CSV</span>
        </div>

        <!-- Dropzone Container -->
        <div 
            id="dropzone"
            onclick="document.getElementById('fileInput').click()"
            ondragover="handleDragOver(event)"
            ondragleave="handleDragLeave(event)"
            ondrop="handleDrop(event)"
            class="border-2 border-dashed border-slate-300 hover:border-[#0D7A53] bg-slate-50 hover:bg-emerald-50/20 rounded-xl p-8 text-center cursor-pointer transition flex flex-col items-center justify-center space-y-3"
        >
            <input type="file" id="fileInput" accept=".xlsx,.xls,.csv" class="hidden" onchange="handleFileSelect(event)" />
            
            <div class="w-14 h-14 rounded-full bg-emerald-100 text-[#0D7A53] flex items-center justify-center shadow-inner">
                <i data-lucide="upload-cloud" class="w-7 h-7"></i>
            </div>

            <div>
                <h4 class="text-sm font-bold text-slate-800">
                    Haz clic para seleccionar o arrastra tu archivo aquí
                </h4>
                <p class="text-xs text-slate-500 mt-1">
                    Formatos soportados: <strong>.XLSX, .XLS, .CSV</strong> (Máximo 25 MB)
                </p>
            </div>

            <span class="text-[10px] text-slate-400 bg-white border border-slate-200 px-3 py-1 rounded-full">
                Estructura requerida: Documento, Aprendiz, Programa, Competencia, RAP, Juicio Evaluativo
            </span>
        </div>

        <!-- File Upload Status / Progress -->
        <div id="fileInfoBox" class="hidden bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-[#0D7A53] flex items-center justify-center">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                </div>
                <div>
                    <h5 id="fileNameDisplay" class="font-bold text-xs text-slate-800">archivo.xlsx</h5>
                    <p id="fileSizeDisplay" class="text-[11px] text-slate-500">0 KB</p>
                </div>
            </div>

            <button onclick="resetFileInput()" class="text-xs text-rose-600 hover:text-rose-800 font-semibold p-1">
                Remover
            </button>
        </div>
    </div>

    <!-- Step 3: Validation and Summary (Shown after parsing) -->
    <div id="validationSection" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-xs font-bold text-[#0C2340] uppercase tracking-wide">
                <span class="w-5 h-5 rounded-full bg-[#0D7A53] text-white flex items-center justify-center text-[10px]">3</span>
                <span>Paso 3: Diagnóstico y Previsualización de Datos</span>
            </div>

            <!-- Export Error Log if any -->
            <button id="downloadErrorsBtn" onclick="exportErrorLogCsv()" class="hidden px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 rounded-lg text-xs font-semibold transition flex items-center space-x-1.5">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                <span>Descargar Inconsistencias (.CSV)</span>
            </button>
        </div>

        <!-- Metrics Chips -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                <span class="text-slate-400 block text-[10px] font-semibold">TOTAL FILAS:</span>
                <span id="sumTotal" class="text-base font-bold font-heading text-slate-800">0</span>
            </div>
            <div class="bg-emerald-50 p-3 rounded-lg border border-emerald-200">
                <span class="text-emerald-700 block text-[10px] font-semibold">VÁLIDAS PARA INGESTA:</span>
                <span id="sumValidas" class="text-base font-bold font-heading text-[#0D7A53]">0</span>
            </div>
            <div class="bg-rose-50 p-3 rounded-lg border border-rose-200">
                <span class="text-rose-700 block text-[10px] font-semibold">INCONSISTENCIAS:</span>
                <span id="sumErrores" class="text-base font-bold font-heading text-rose-600">0</span>
            </div>
            <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                <span class="text-blue-700 block text-[10px] font-semibold">APRENDICES ÚNICOS:</span>
                <span id="sumAprendices" class="text-base font-bold font-heading text-blue-700">0</span>
            </div>
        </div>

        <!-- Preview Table of parsed records -->
        <div class="border border-slate-200 rounded-lg overflow-hidden">
            <div class="bg-slate-100 px-4 py-2 text-[11px] font-bold text-slate-600 flex items-center justify-between">
                <span>MUESTRA DE REGISTROS PROCESADOS (Primeros 8)</span>
                <span class="font-mono text-slate-500">Normalización Automática SENA</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-white border-b border-slate-200 text-slate-500 uppercase text-[10px]">
                        <tr>
                            <th class="py-2 px-3">Fila</th>
                            <th class="py-2 px-3">Documento</th>
                            <th class="py-2 px-3">Aprendiz</th>
                            <th class="py-2 px-3">Competencia</th>
                            <th class="py-2 px-3">Resultado de Aprendizaje (RAP)</th>
                            <th class="py-2 px-3 text-center">Juicio</th>
                            <th class="py-2 px-3 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="previewTableBody" class="divide-y divide-slate-200 bg-white">
                        <!-- Inyectado por JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Error details accordion if errors exist -->
        <div id="errorDetailsBox" class="hidden p-4 bg-rose-50/70 border border-rose-200 rounded-lg text-xs space-y-2">
            <div class="flex items-center space-x-2 text-rose-800 font-bold">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                <span>Reporte de Inconsistencias Detectadas:</span>
            </div>
            <ul id="errorListItems" class="list-disc list-inside text-rose-700 space-y-1 text-[11px] max-h-32 overflow-y-auto">
                <!-- Errores inyectados -->
            </ul>
        </div>

        <!-- Action / Commit Bar -->
        <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
            <a href="<?= BASE_URL ?>/index.php" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                Cancelar
            </a>

            <button 
                id="commitBtn"
                onclick="commitBatchToSupabase()" 
                class="px-5 py-2.5 bg-[#0D7A53] hover:bg-emerald-600 text-white rounded-lg text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-emerald-700/20 cursor-pointer"
            >
                <i data-lucide="database" class="w-4 h-4"></i>
                <span>Confirmar e Ingestar en Supabase</span>
            </button>
        </div>
    </div>
</div>

<script>
    const existingCargas = <?= json_encode($cargasExistentes) ?>;
    let parsedRows = [];
    let detectedErrors = [];
    let selectedFile = null;

    function checkCutoffDuplicate(dateStr) {
        const found = existingCargas.some(c => c.fecha_corte === dateStr && c.estado === 'EXITOSO');
        const warn = document.getElementById('duplicate-cutoff-warning');
        if (found) {
            warn.classList.remove('hidden');
        } else {
            warn.classList.add('hidden');
        }
    }

    // Drag and drop handlers
    function handleDragOver(e) {
        e.preventDefault();
        document.getElementById('dropzone').classList.add('border-[#0D7A53]', 'bg-emerald-50/40');
    }

    function handleDragLeave(e) {
        e.preventDefault();
        document.getElementById('dropzone').classList.remove('border-[#0D7A53]', 'bg-emerald-50/40');
    }

    function handleDrop(e) {
        e.preventDefault();
        handleDragLeave(e);
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            processFile(e.dataTransfer.files[0]);
        }
    }

    function handleFileSelect(e) {
        if (e.target.files && e.target.files[0]) {
            processFile(e.target.files[0]);
        }
    }

    function resetFileInput() {
        selectedFile = null;
        parsedRows = [];
        detectedErrors = [];
        document.getElementById('fileInput').value = '';
        document.getElementById('fileInfoBox').classList.add('hidden');
        document.getElementById('validationSection').classList.add('hidden');
    }

    // Procesar archivo con SheetJS en frontend
    function processFile(file) {
        selectedFile = file;
        document.getElementById('fileNameDisplay').textContent = file.name;
        document.getElementById('fileSizeDisplay').textContent = (file.size / 1024).toFixed(1) + ' KB';
        document.getElementById('fileInfoBox').classList.remove('hidden');

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                const jsonData = XLSX.utils.sheet_to_json(firstSheet, { header: 1 });

                validateAndDisplayData(jsonData);
            } catch (err) {
                alert('Error al leer el archivo. Asegúrate de que sea un archivo Excel (.xlsx) o CSV válido.');
                console.error(err);
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function validateAndDisplayData(rows) {
        parsedRows = [];
        detectedErrors = [];

        if (!rows || rows.length <= 1) {
            alert('El archivo no contiene registros o solo contiene encabezados.');
            return;
        }

        const headers = rows[0].map(h => String(h || '').trim().toLowerCase());
        const findCol = (keys) => headers.findIndex(h => keys.some(k => h.includes(k)));

        const cTipo = findCol(['tipo_id', 'tipo_doc', 'tdoc', 'tipo']);
        const cNum = findCol(['numero_id', 'documento', 'identificacion', 'cedula']);
        const cNom = findCol(['nombre_completo', 'nombre', 'aprendiz']);
        const cEmail = findCol(['email', 'correo']);
        const cCodP = findCol(['codigo_programa', 'cod_prog', 'ficha']);
        const cNomP = findCol(['nombre_programa', 'programa']);
        const cComp = findCol(['competencia', 'cod_competencia']);
        const cRap = findCol(['resultado_aprendizaje', 'rap', 'resultado']);
        const cJuicio = findCol(['juicio', 'estado', 'calificacion']);
        const cFecha = findCol(['fecha', 'fecha_evaluacion']);

        const uniqueAprendices = new Set();

        for (let i = 1; i < rows.length; i++) {
            const r = rows[i];
            if (!r || r.length === 0 || r.every(v => v === undefined || v === null || v === '')) continue;

            const rowNum = i + 1;
            const doc = String(cNum >= 0 ? r[cNum] || '' : '').trim();
            const nom = String(cNom >= 0 ? r[cNom] || '' : '').trim();
            const comp = String(cComp >= 0 ? r[cComp] || '' : '').trim();
            const rap = String(cRap >= 0 ? r[cRap] || '' : '').trim();
            let rawJ = String(cJuicio >= 0 ? r[cJuicio] || 'POR_EVALUAR' : 'POR_EVALUAR').trim().toUpperCase();

            // Normalización institucional de juicios SENA
            let normalizedJuicio = 'POR_EVALUAR';
            if (rawJ.includes('APROB') || rawJ === 'A') normalizedJuicio = 'APROBADO';
            else if (rawJ.includes('NO') || rawJ.includes('DEFIC') || rawJ === 'D') normalizedJuicio = 'NO_APROBADO';

            // Validar errores
            let rowErrors = [];
            if (!doc || doc.length < 5) rowErrors.push('Número de documento inválido o muy corto');
            if (!nom || nom.length < 3) rowErrors.push('Nombre de aprendiz incompleto');
            if (!comp) rowErrors.push('Competencia curricular ausente');
            if (!rap) rowErrors.push('Resultado de Aprendizaje ausente');

            const item = {
                fila: rowNum,
                tipo_identificacion: String(cTipo >= 0 ? r[cTipo] || 'CC' : 'CC').trim().toUpperCase(),
                numero_identificacion: doc,
                nombre_completo: nom,
                email: String(cEmail >= 0 ? r[cEmail] || '' : (doc ? doc + '@soy.sena.edu.co' : '')).trim(),
                codigo_programa: String(cCodP >= 0 ? r[cCodP] || '228106' : '228106').trim(),
                nombre_programa: String(cNomP >= 0 ? r[cNomP] || 'Análisis y Desarrollo de Software (ADSO)' : 'Análisis y Desarrollo de Software (ADSO)').trim(),
                competencia: comp,
                resultado_aprendizaje: rap,
                juicio: normalizedJuicio,
                fecha_evaluacion: cFecha >= 0 && r[cFecha] ? String(r[cFecha]) : new Date().toISOString().split('T')[0],
                es_valido: rowErrors.length === 0,
                errores: rowErrors
            };

            if (item.es_valido) {
                uniqueAprendices.add(doc);
            } else {
                rowErrors.forEach(err => {
                    detectedErrors.push({ fila: rowNum, documento: doc, error: err });
                });
            }

            parsedRows.push(item);
        }

        const validCount = parsedRows.filter(r => r.es_valido).length;
        const errorCount = parsedRows.filter(r => !r.es_valido).length;

        // Mostrar estadísticas
        document.getElementById('sumTotal').textContent = parsedRows.length;
        document.getElementById('sumValidas').textContent = validCount;
        document.getElementById('sumErrores').textContent = errorCount;
        document.getElementById('sumAprendices').textContent = uniqueAprendices.size;

        // Renderizar tabla de previsualización
        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';
        parsedRows.slice(0, 8).forEach(r => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition';
            tr.innerHTML = `
                <td class="py-2 px-3 font-mono text-slate-400">#${r.fila}</td>
                <td class="py-2 px-3 font-mono font-semibold">${r.tipo_identificacion} ${r.numero_identificacion}</td>
                <td class="py-2 px-3 font-medium text-slate-800">${r.nombre_completo}</td>
                <td class="py-2 px-3 text-slate-600 truncate max-w-xs">${r.competencia}</td>
                <td class="py-2 px-3 text-slate-600 truncate max-w-xs">${r.resultado_aprendizaje}</td>
                <td class="py-2 px-3 text-center">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold ${r.juicio === 'APROBADO' ? 'bg-emerald-100 text-emerald-800' : (r.juicio === 'NO_APROBADO' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800')}">
                        ${r.juicio}
                    </span>
                </td>
                <td class="py-2 px-3 text-center">
                    ${r.es_valido 
                        ? '<span class="text-emerald-600 font-bold">✓ Válido</span>' 
                        : '<span class="text-rose-600 font-bold">✕ Error</span>'}
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Mostrar u ocultar errores
        const errBox = document.getElementById('errorDetailsBox');
        const errBtn = document.getElementById('downloadErrorsBtn');
        if (detectedErrors.length > 0) {
            errBox.classList.remove('hidden');
            errBtn.classList.remove('hidden');
            const errList = document.getElementById('errorListItems');
            errList.innerHTML = '';
            detectedErrors.slice(0, 15).forEach(e => {
                const li = document.createElement('li');
                li.textContent = `Fila ${e.fila} [Doc: ${e.documento || 'Vacío'}]: ${e.error}`;
                errList.appendChild(li);
            });
        } else {
            errBox.classList.add('hidden');
            errBtn.classList.add('hidden');
        }

        document.getElementById('validationSection').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    // Exportar CSV de errores
    function exportErrorLogCsv() {
        if (detectedErrors.length === 0) return;
        let csv = 'Fila,Documento,Descripcion_Inconsistencia\n';
        detectedErrors.forEach(e => {
            csv += `${e.fila},"${e.documento || ''}","${e.error}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `INCONSISTENCIAS_${document.getElementById('snapshotDate').value}.csv`;
        a.click();
    }

    // Confirmar e Ingestar Lote en Supabase / Backend
    function commitBatchToSupabase() {
        const validRows = parsedRows.filter(r => r.es_valido);
        if (validRows.length === 0) {
            alert('No hay registros válidos para ingestar.');
            return;
        }

        const btn = document.getElementById('commitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-2">⟳</span> Ingestando en Supabase...';

        const payload = {
            nombre_archivo: selectedFile ? selectedFile.name : 'CARGA_MANUAL.xlsx',
            fecha_corte: document.getElementById('snapshotDate').value,
            total_registros: parsedRows.length,
            registros_exitosos: validRows.length,
            inconsistencias: detectedErrors.length,
            filas: validRows
        };

        fetch('<?= BASE_URL ?>/api/upload_batch.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(`✔ Ingesta finalizada con éxito en Supabase.\nSe procesaron ${data.registros} registros evaluativos para el corte ${payload.fecha_corte}.`);
                window.location.href = '<?= BASE_URL ?>/index.php?corte=' + payload.fecha_corte;
            } else {
                alert('Error al ingestar: ' + (data.error || 'Ocurrió un problema en el servidor.'));
                btn.disabled = false;
                btn.innerHTML = '<span>Confirmar e Ingestar en Supabase</span>';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de red al comunicarse con el backend de Supabase.');
            btn.disabled = false;
            btn.innerHTML = '<span>Confirmar e Ingestar en Supabase</span>';
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
