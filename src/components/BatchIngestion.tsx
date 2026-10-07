import React, { useState, useRef } from 'react';
import { 
  UploadCloud, 
  FileSpreadsheet, 
  CheckCircle2, 
  AlertTriangle, 
  XCircle, 
  Download, 
  FileText, 
  ArrowRight,
  Database,
  ShieldCheck,
  RotateCcw,
  Sparkles,
  Calendar,
  AlertOctagon
} from 'lucide-react';
import { 
  parseExcelWithZod, 
  generateSenaSampleWorkbook, 
  downloadErrorLogCsv,
  ParsedJuicioRowResult 
} from '../utils/excelHelper';
import { CargaArchivo, IngestionRowError, UsuarioSesion } from '../types';

interface BatchIngestionProps {
  currentUser: UsuarioSesion;
  existingCargas: CargaArchivo[];
  onCommitBatch: (newCarga: CargaArchivo, parsedRows: ParsedJuicioRowResult[]) => void;
  onCancel?: () => void;
  onTriggerPowerBiRefresh: () => void;
}

export const BatchIngestion: React.FC<BatchIngestionProps> = ({
  currentUser,
  existingCargas,
  onCommitBatch,
  onCancel,
  onTriggerPowerBiRefresh,
}) => {
  // Step A: Snapshot Date Selection
  const [snapshotDate, setSnapshotDate] = useState<string>(() => {
    return new Date().toISOString().split('T')[0];
  });
  const [showDuplicateCutoffModal, setShowDuplicateCutoffModal] = useState<boolean>(false);
  const [cutoffVersion, setCutoffVersion] = useState<number>(1);

  // Step B: Drag & Drop and File state
  const [isDragging, setIsDragging] = useState(false);
  const [fileName, setFileName] = useState<string>('');
  const [fileSizeKb, setFileSizeKb] = useState<number>(0);
  const [fileErrorCaseA, setFileErrorCaseA] = useState<string | null>(null);

  // Parsed and Validation State
  const [parsedRows, setParsedRows] = useState<ParsedJuicioRowResult[]>([]);
  const [validationErrors, setValidationErrors] = useState<IngestionRowError[]>([]);
  const [summary, setSummary] = useState<{
    total: number;
    validos: number;
    errores: number;
    aprendices: number;
    programas: number;
  } | null>(null);

  const [isProcessing, setIsProcessing] = useState(false);
  const [isCommitting, setIsCommitting] = useState(false);
  const [commitSuccess, setCommitSuccess] = useState(false);

  const fileInputRef = useRef<HTMLInputElement>(null);

  // Check if chosen snapshot date already exists (Caso C)
  const checkDuplicateCutoff = (dateStr: string) => {
    const existing = existingCargas.filter(c => c.fecha_corte === dateStr && c.estado === 'EXITOSO');
    if (existing.length > 0) {
      setCutoffVersion(existing.length + 1);
      setShowDuplicateCutoffModal(true);
    } else {
      setCutoffVersion(1);
    }
  };

  const handleDateChange = (newDate: string) => {
    setSnapshotDate(newDate);
    checkDuplicateCutoff(newDate);
  };

  // Handle uploaded file
  const handleFile = (file: File) => {
    setFileErrorCaseA(null);
    setCommitSuccess(false);

    // Caso A: Archivo Inválido o Dañado / Restricción de 25MB
    const isExtensionValid = file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv');
    const isSizeValid = file.size <= 25 * 1024 * 1024; // 25 MB

    if (!isExtensionValid || !isSizeValid) {
      setFileErrorCaseA('Formato no compatible. Sube un archivo Excel válido (.xlsx) de hasta 25 MB.');
      setFileName('');
      setParsedRows([]);
      setSummary(null);
      return;
    }

    setIsProcessing(true);
    setFileName(file.name);
    setFileSizeKb(Math.round(file.size / 1024));

    const reader = new FileReader();
    reader.onload = (e) => {
      try {
        const buffer = e.target?.result as ArrayBuffer;
        const result = parseExcelWithZod(buffer);

        setParsedRows(result.rows);
        setValidationErrors(result.errors);
        setSummary({
          total: result.totalFilas,
          validos: result.filasValidas,
          errores: result.filasConError,
          aprendices: result.aprendicesUnicos,
          programas: result.programasUnicos,
        });
      } catch (err) {
        console.error('Error parsing file:', err);
        setFileErrorCaseA('El archivo parece estar dañado o con formato incompatible.');
      } finally {
        setIsProcessing(false);
      }
    };
    reader.readAsArrayBuffer(file);
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFile(e.dataTransfer.files[0]);
    }
  };

  const loadSampleData = () => {
    setIsProcessing(true);
    setFileErrorCaseA(null);
    const sampleBytes = generateSenaSampleWorkbook();
    const result = parseExcelWithZod(sampleBytes);
    setFileName('SOFIA_PLUS_MUESTRA_OFICIAL_ADSO_2026.xlsx');
    setFileSizeKb(124);
    setParsedRows(result.rows);
    setValidationErrors(result.errors);
    setSummary({
      total: result.totalFilas,
      validos: result.filasValidas,
      errores: result.filasConError,
      aprendices: result.aprendicesUnicos,
      programas: result.programasUnicos,
    });
    setCommitSuccess(false);
    setIsProcessing(false);
  };

  const downloadSampleTemplate = () => {
    const bytes = generateSenaSampleWorkbook();
    const blob = new Blob([bytes as unknown as BlobPart], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'PLANTILLA_OFICIAL_SOFIA_PLUS_JUICIOS_EVALUATIVOS.xlsx';
    a.click();
    URL.revokeObjectURL(url);
  };

  const handleCommit = () => {
    if (!summary || parsedRows.length === 0 || summary.errores > 0) return;

    setIsCommitting(true);
    setTimeout(() => {
      // Generate UUID v4 for batch_id
      const uuidv4 = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        const r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
      });

      const now = new Date();
      const dateStr = now.toISOString().replace(/T/, ' ').replace(/\..+/, '');

      const newCarga: CargaArchivo = {
        batch_id: uuidv4,
        nombre_archivo: fileName,
        usuario_id: currentUser.id,
        usuario_nombre: currentUser.nombre,
        usuario_rol: currentUser.rol,
        fecha_corte: snapshotDate,
        total_registros: summary.total,
        registros_exitosos: summary.validos,
        inconsistencias: summary.errores,
        estado: 'EXITOSO',
        created_at: dateStr,
        metadata: {
          programas_count: summary.programas,
          aprendices_count: summary.aprendices,
          version_corte: cutoffVersion,
        },
      };

      onCommitBatch(newCarga, parsedRows);
      setIsCommitting(false);
      setCommitSuccess(true);
      onTriggerPowerBiRefresh();
    }, 1100);
  };

  const handleDownloadErrors = () => {
    downloadErrorLogCsv(validationErrors);
  };

  return (
    <div className="space-y-6">
      {/* Title & Guidance Header */}
      <div className="bg-white p-5 rounded-[8px] border border-[#E2E8F0] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div className="flex items-center space-x-2">
            <span className="text-[11px] font-bold text-[#0D7A53] uppercase tracking-wider bg-[#E6F4EA] px-2 py-0.5 rounded-[4px]">
              Pipeline ETL • Ingesta Zod DirectQuery
            </span>
            <span className="text-xs text-slate-400">•</span>
            <span className="text-xs text-slate-500 font-medium">
              Operador: {currentUser.nombre} ({currentUser.rol})
            </span>
          </div>
          <h2 className="text-xl font-heading font-bold text-[#0C2340] mt-1">
            Carga y Validación Estructural de Juicios Evaluativos
          </h2>
          <p className="text-xs text-slate-500 mt-1 max-w-2xl">
            Sube archivos Excel (.xlsx) generados desde SofiaPlus. El motor valida fila por fila con esquemas Zod garantizando atomicidad transaccional antes de persistir en PostgreSQL.
          </p>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <button
            onClick={downloadSampleTemplate}
            className="px-3 py-2 bg-white border border-[#CBD5E1] hover:border-[#0C2340] text-[#0C2340] rounded-[4px] text-xs font-semibold flex items-center space-x-1.5 transition shadow-2xs"
            title="Descargar archivo modelo con encabezados oficiales"
          >
            <Download size={14} />
            <span>Plantilla SofiaPlus (.xlsx)</span>
          </button>

          <button
            onClick={loadSampleData}
            className="px-3 py-2 bg-[#1B365D] hover:bg-[#0C2340] text-white rounded-[4px] text-xs font-semibold flex items-center space-x-1.5 transition shadow-2xs"
            title="Cargar conjunto de datos de prueba"
          >
            <Sparkles size={14} className="text-amber-300" />
            <span>Cargar Datos de Muestra</span>
          </button>
        </div>
      </div>

      {/* Step A & B Container */}
      <div className="bg-white p-5 rounded-[8px] border border-[#E2E8F0] shadow-sm space-y-5">
        {/* Paso A: Snapshot Date Selection */}
        <div className="p-4 bg-[#F8FAFC] rounded-[6px] border border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div className="flex items-center space-x-2.5">
            <span className="w-7 h-7 rounded-full bg-[#0C2340] text-white flex items-center justify-center font-bold text-xs">
              A
            </span>
            <div>
              <label htmlFor="snapshot-date" className="block text-xs font-bold text-[#0C2340]">
                Fecha Oficial de Corte Formativo (snapshot_date):
              </label>
              <p className="text-[11px] text-slate-500">
                Identifica la fecha temporal para segmentación histórica en Power BI.
              </p>
            </div>
          </div>

          <div className="flex items-center space-x-2">
            <Calendar size={16} className="text-[#0C2340]" />
            <input
              id="snapshot-date"
              type="date"
              value={snapshotDate}
              onChange={(e) => handleDateChange(e.target.value)}
              className="px-3 py-1.5 text-xs font-mono font-semibold bg-white border border-[#CBD5E1] rounded-[4px] focus:outline-none focus:ring-1 focus:ring-[#0C2340]"
            />
          </div>
        </div>

        {/* Paso B: Drag & Drop Area */}
        <div className="space-y-2">
          <div className="flex items-center space-x-2">
            <span className="w-7 h-7 rounded-full bg-[#0C2340] text-white flex items-center justify-center font-bold text-xs">
              B
            </span>
            <span className="text-xs font-bold text-[#0C2340]">
              Área de Carga de Archivo (.xlsx)
            </span>
          </div>

          <div
            onDrop={handleDrop}
            onDragOver={(e) => { e.preventDefault(); setIsDragging(true); }}
            onDragLeave={(e) => { e.preventDefault(); setIsDragging(false); }}
            onClick={() => fileInputRef.current?.click()}
            className={`border-2 border-dashed rounded-[8px] p-8 sm:p-10 text-center cursor-pointer transition duration-150 ${
              fileErrorCaseA 
                ? 'border-[#DC2626] bg-[#FEF2F2]' 
                : isDragging 
                ? 'border-[#0C2340] bg-[#F1F5F9]' 
                : 'border-[#CBD5E1] bg-white hover:border-[#0C2340] hover:bg-[#F8FAFC]'
            }`}
          >
            <input
              ref={fileInputRef}
              type="file"
              accept=".xlsx,.xls,.csv"
              onChange={(e) => {
                if (e.target.files && e.target.files[0]) {
                  handleFile(e.target.files[0]);
                }
              }}
              className="hidden"
            />

            <div className="flex flex-col items-center justify-center space-y-3">
              <div className={`w-14 h-14 rounded-full flex items-center justify-center shadow-inner ${
                fileErrorCaseA ? 'bg-[#FEE2E2] text-[#DC2626]' : 'bg-[#E8EDF5] text-[#0C2340]'
              }`}>
                <UploadCloud size={28} />
              </div>

              <div>
                <p className="text-sm font-semibold text-[#0C2340]">
                  Arrastra tu archivo Excel aquí o <span className="text-[#0D7A53] underline">haz clic para examinar</span>
                </p>
                <p className="text-xs text-slate-500 mt-1">
                  Formatos permitidos: XLSX o CSV hasta 25 MB • Estructura tabular de juicios
                </p>
              </div>

              {/* Caso A Error Message */}
              {fileErrorCaseA && (
                <div className="mt-2 text-xs font-semibold text-[#DC2626] bg-white px-3 py-1.5 rounded-[4px] border border-[#FECACA] flex items-center gap-1.5">
                  <AlertOctagon size={14} />
                  <span>{fileErrorCaseA}</span>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Case C: Duplicate Cutoff Confirmation Modal */}
      {showDuplicateCutoffModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#081628]/60 backdrop-blur-xs">
          <div className="bg-white rounded-[10px] border border-amber-300 shadow-2xl max-w-md w-full p-5 space-y-4">
            <div className="flex items-start space-x-3 text-amber-700">
              <AlertTriangle size={24} className="flex-shrink-0 mt-0.5" />
              <div>
                <h3 className="font-heading font-bold text-sm text-[#0C2340]">
                  Fecha de Corte Preexistente Detectada
                </h3>
                <p className="text-xs text-slate-600 mt-1">
                  Ya existen evaluaciones registradas para la fecha seleccionada ({snapshotDate}). ¿Deseas agregar esta carga como una nueva versión histórica del mismo corte (Versión {cutoffVersion})?
                </p>
              </div>
            </div>

            <div className="flex justify-end space-x-2 pt-2 border-t border-slate-200">
              <button
                onClick={() => {
                  setShowDuplicateCutoffModal(false);
                  setSnapshotDate(new Date().toISOString().split('T')[0]);
                }}
                className="px-3 py-1.5 text-xs text-slate-600 border border-slate-300 rounded-[4px] hover:bg-slate-50"
              >
                Cancelar Operación
              </button>
              <button
                onClick={() => setShowDuplicateCutoffModal(false)}
                className="px-4 py-1.5 text-xs font-semibold bg-[#0C2340] hover:bg-[#1B365D] text-white rounded-[4px]"
              >
                Continuar (Crear Versión {cutoffVersion})
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Processing Spinner State */}
      {isProcessing && (
        <div className="bg-white p-6 rounded-[8px] border border-[#E2E8F0] text-center space-y-2">
          <div className="w-8 h-8 border-4 border-[#0C2340]/20 border-t-[#0C2340] rounded-full animate-spin mx-auto"></div>
          <p className="text-xs font-semibold text-[#0C2340]">
            Leyendo y analizando filas del archivo con esquema Zod...
          </p>
        </div>
      )}

      {/* Success Notification Banner */}
      {commitSuccess && (
        <div className="bg-[#E6F4EA] border border-[#A7F3D0] rounded-[6px] p-4 flex items-center justify-between text-xs text-[#0A6242] animate-in fade-in">
          <div className="flex items-center space-x-2">
            <CheckCircle2 size={18} className="text-[#0D7A53] flex-shrink-0" />
            <div>
              <span className="font-bold">✔ Ingesta finalizada: Se incorporaron exitosamente {summary?.validos} registros al corte {snapshotDate}.</span>
              <p className="text-[11px] text-emerald-800">
                Se ejecutó la transacción ACID en PostgreSQL y se disparó report.refresh() en DirectQuery.
              </p>
            </div>
          </div>
          <span className="text-[11px] font-mono font-semibold bg-white/70 px-2.5 py-1 rounded">
            Estado: Committed
          </span>
        </div>
      )}

      {/* Summary and Validation Results */}
      {summary && (
        <div className="space-y-4">
          <div className="bg-white p-5 rounded-[8px] border border-[#E2E8F0] shadow-sm">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E2E8F0] pb-3 mb-4">
              <div className="flex items-center space-x-2.5">
                <FileSpreadsheet size={20} className="text-[#0C2340]" />
                <div>
                  <h3 className="font-heading font-bold text-sm text-[#0C2340]">
                    {fileName}
                  </h3>
                  <p className="text-xs text-slate-500">
                    Tamaño: {fileSizeKb} KB • Corte: <strong className="text-slate-800">{snapshotDate} (v{cutoffVersion})</strong>
                  </p>
                </div>
              </div>

              {/* Actions */}
              <div className="flex items-center space-x-2">
                <button
                  onClick={() => {
                    setParsedRows([]);
                    setSummary(null);
                    setFileName('');
                    setValidationErrors([]);
                  }}
                  className="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-900 border border-slate-300 rounded-[4px] transition"
                >
                  Descartar
                </button>

                {/* If errors exist (Caso B): button to download error report CSV */}
                {summary.errores > 0 ? (
                  <button
                    onClick={handleDownloadErrors}
                    className="px-3.5 py-1.5 text-xs font-semibold rounded-[4px] text-[#DC2626] bg-[#FEE2E2] hover:bg-rose-200 border border-[#FECACA] flex items-center space-x-1.5 transition"
                  >
                    <Download size={13} />
                    <span>Descargar Reporte de Errores (.csv)</span>
                  </button>
                ) : (
                  <button
                    onClick={handleCommit}
                    disabled={isCommitting || commitSuccess}
                    className={`px-4 py-1.5 text-xs font-semibold rounded-[4px] text-white flex items-center space-x-1.5 transition shadow-sm ${
                      commitSuccess ? 'bg-[#0D7A53] opacity-80 cursor-default' : 'bg-[#0D7A53] hover:bg-[#0A6242]'
                    }`}
                  >
                    <ShieldCheck size={14} />
                    <span>
                      {isCommitting ? 'Persistiendo en PostgreSQL...' : commitSuccess ? 'Lote Ingestado' : 'Confirmar e Ingestar en Base de Datos'}
                    </span>
                  </button>
                )}
              </div>
            </div>

            {/* Validation Metrics Cards */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
              <div className="p-3 rounded-[6px] border border-[#E2E8F0] bg-[#F8FAFC]">
                <span className="text-[11px] font-semibold text-slate-500 uppercase">Total Registros</span>
                <div className="text-xl font-bold font-tabular text-[#0C2340] mt-0.5">{summary.total}</div>
              </div>

              <div className="p-3 rounded-[6px] border border-[#E2E8F0] bg-[#E6F4EA]">
                <span className="text-[11px] font-semibold text-emerald-800 uppercase">Aprendices Únicos</span>
                <div className="text-xl font-bold font-tabular text-[#0D7A53] mt-0.5">{summary.aprendices}</div>
              </div>

              <div className="p-3 rounded-[6px] border border-[#E2E8F0] bg-blue-50">
                <span className="text-[11px] font-semibold text-blue-800 uppercase">Programas</span>
                <div className="text-xl font-bold font-tabular text-blue-900 mt-0.5">{summary.programas}</div>
              </div>

              <div className={`p-3 rounded-[6px] border ${
                summary.errores > 0 ? 'border-[#FECACA] bg-[#FEE2E2]' : 'border-[#E2E8F0] bg-white'
              }`}>
                <span className={`text-[11px] font-semibold uppercase ${summary.errores > 0 ? 'text-rose-800' : 'text-slate-500'}`}>
                  Inconsistencias
                </span>
                <div className={`text-xl font-bold font-tabular mt-0.5 ${summary.errores > 0 ? 'text-[#DC2626]' : 'text-slate-700'}`}>
                  {summary.errores}
                </div>
              </div>
            </div>
          </div>

          {/* Caso B: Filas con Inconsistencias Estructurales Panel */}
          {summary.errores > 0 && (
            <div className="bg-white p-5 rounded-[8px] border border-[#FECACA] shadow-sm space-y-3">
              <div className="flex items-start justify-between">
                <div className="flex items-center space-x-2 text-[#DC2626]">
                  <AlertOctagon size={18} />
                  <h4 className="text-xs font-bold uppercase tracking-wider">
                    Transacción Abortada: Se encontraron {summary.errores} registros con errores de validación
                  </h4>
                </div>
                <button
                  onClick={handleDownloadErrors}
                  className="text-xs font-bold text-[#DC2626] underline flex items-center gap-1"
                >
                  <Download size={12} /> Descargar .csv
                </button>
              </div>

              <p className="text-xs text-slate-600">
                Para preservar la integridad relacional de la base de datos, <strong>se han insertado 0 filas</strong>. Corrige las inconsistencias listadas a continuación o descarga el reporte detallado:
              </p>

              <div className="max-h-48 overflow-y-auto space-y-1.5 pr-1 border border-slate-200 rounded-[6px] p-2 bg-[#F8FAFC]">
                {validationErrors.slice(0, 15).map((err, idx) => (
                  <div key={idx} className="p-2 bg-white rounded border border-[#FECACA] text-xs flex justify-between items-start">
                    <div>
                      <span className="font-mono font-bold text-slate-800 mr-2">Fila {err.fila} [{err.campo}]:</span>
                      <span className="text-rose-700">{err.descripcion}</span>
                      <span className="text-slate-400 font-mono text-[11px] block mt-0.5">Valor recibido: {err.valor}</span>
                    </div>
                    <span className="text-[10px] uppercase font-bold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded">
                      Fallo Zod
                    </span>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Table Preview: First 5 rows for visual confirmation */}
          <div className="bg-white rounded-[8px] border border-[#E2E8F0] shadow-sm overflow-hidden">
            <div className="bg-[#F1F5F9] px-4 py-2.5 border-b border-[#E2E8F0] flex items-center justify-between">
              <span className="text-xs font-bold text-slate-700 uppercase tracking-wider">
                Previsualización de Primeras Filas Parseadas (Validación Visual)
              </span>
              <span className="text-xs text-slate-500">
                Mostrando {Math.min(5, parsedRows.length)} filas
              </span>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-xs text-left">
                <thead className="bg-[#F1F5F9] text-[#475569] uppercase font-bold text-[11px] border-b border-[#E2E8F0]">
                  <tr className="h-9">
                    <th className="py-2 px-3 text-center w-12">Fila</th>
                    <th className="py-2 px-3">Identificación</th>
                    <th className="py-2 px-3">Nombre Aprendiz</th>
                    <th className="py-2 px-3">Programa</th>
                    <th className="py-2 px-3">Competencia</th>
                    <th className="py-2 px-3">Resultado de Aprendizaje</th>
                    <th className="py-2 px-3">Juicio Evaluativo</th>
                    <th className="py-2 px-3 text-center">Estado</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[#E2E8F0]">
                  {parsedRows.slice(0, 5).map((row, index) => (
                    <tr key={index} className="h-9 hover:bg-[#EDF2F7] transition bg-white">
                      <td className="py-1.5 px-3 font-mono text-center text-slate-500">
                        {row.fila}
                      </td>
                      <td className="py-1.5 px-3 font-mono font-medium text-slate-800">
                        {row.raw.tipo_identificacion} {row.raw.numero_identificacion}
                      </td>
                      <td className="py-1.5 px-3 font-medium text-slate-900">
                        {row.raw.nombre_completo}
                      </td>
                      <td className="py-1.5 px-3 text-slate-600 truncate max-w-[140px]" title={row.raw.nombre_programa}>
                        {row.raw.codigo_programa}
                      </td>
                      <td className="py-1.5 px-3 text-slate-600 truncate max-w-[150px]" title={row.raw.competencia}>
                        {row.raw.competencia}
                      </td>
                      <td className="py-1.5 px-3 text-slate-600 truncate max-w-[160px]" title={row.raw.resultado_aprendizaje}>
                        {row.raw.resultado_aprendizaje}
                      </td>
                      <td className="py-1.5 px-3">
                        {row.raw.juicio === 'APROBADO' && (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#E6F4EA] text-[#0A6242] border border-[#A7F3D0]">
                            <span className="w-1.5 h-1.5 rounded-full bg-[#0D7A53]"></span> Aprobado
                          </span>
                        )}
                        {row.raw.juicio === 'POR_EVALUAR' && (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]">
                            Por Evaluar
                          </span>
                        )}
                        {row.raw.juicio === 'NO_APROBADO' && (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]">
                            No Aprobado
                          </span>
                        )}
                      </td>
                      <td className="py-1.5 px-3 text-center">
                        {row.valido ? (
                          <span className="text-[#0D7A53] font-bold text-xs">✓ Válido</span>
                        ) : (
                          <span className="text-[#DC2626] font-bold text-xs" title={row.errores.join(', ')}>✕ Error</span>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
