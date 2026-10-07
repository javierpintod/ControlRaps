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
  Sparkles
} from 'lucide-react';
import { 
  parseExcelOrCsvFile, 
  generateSenaSampleWorkbook, 
  ParsedJuicioRow 
} from '../utils/excelHelper';
import { IngestionBatch, IngestionRowError, JuicioEstado } from '../types';

interface BatchIngestionProps {
  onCommitBatch: (newBatch: IngestionBatch, parsedRows: ParsedJuicioRow[]) => void;
  selectedCutoff: string;
}

export const BatchIngestion: React.FC<BatchIngestionProps> = ({
  onCommitBatch,
  selectedCutoff,
}) => {
  const [isDragging, setIsDragging] = useState(false);
  const [fileName, setFileName] = useState<string>('');
  const [fileSizeKb, setFileSizeKb] = useState<number>(0);
  const [parsedRows, setParsedRows] = useState<ParsedJuicioRow[]>([]);
  const [validationErrors, setValidationErrors] = useState<IngestionRowError[]>([]);
  const [summary, setSummary] = useState<{
    total: number;
    validos: number;
    errores: number;
    advertencias: number;
  } | null>(null);
  const [isProcessing, setIsProcessing] = useState(false);
  const [isCommitting, setIsCommitting] = useState(false);
  const [commitSuccess, setCommitSuccess] = useState(false);
  const [filterRowsState, setFilterRowsState] = useState<'todos' | 'validos' | 'errores' | 'advertencias'>('todos');

  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleFile = (file: File) => {
    if (!file.name.endsWith('.xlsx') && !file.name.endsWith('.xls') && !file.name.endsWith('.csv')) {
      alert('Por favor selecciona un archivo con extensión .xlsx, .xls o .csv');
      return;
    }

    setIsProcessing(true);
    setFileName(file.name);
    setFileSizeKb(Math.round(file.size / 1024));
    setCommitSuccess(false);

    const reader = new FileReader();
    reader.onload = (e) => {
      try {
        const buffer = e.target?.result as ArrayBuffer;
        const result = parseExcelOrCsvFile(buffer);
        setParsedRows(result.rows);
        setValidationErrors(result.errors);
        setSummary({
          total: result.totalFilas,
          validos: result.filasValidas,
          errores: result.filasConError,
          advertencias: result.advertencias,
        });
      } catch (err) {
        console.error('Error parsing file:', err);
        alert('Error al analizar la estructura del archivo. Verifica que no esté corrupto.');
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

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(true);
  };

  const handleDragLeave = (e: React.DragEvent) => {
    e.preventDefault();
    setIsDragging(false);
  };

  const loadSampleData = () => {
    setIsProcessing(true);
    const sampleBytes = generateSenaSampleWorkbook();
    const result = parseExcelOrCsvFile(sampleBytes);
    setFileName('SOFIA_PLUS_MUESTRA_OFICIAL_ADSO_2026.xlsx');
    setFileSizeKb(124);
    setParsedRows(result.rows);
    setValidationErrors(result.errors);
    setSummary({
      total: result.totalFilas,
      validos: result.filasValidas,
      errores: result.filasConError,
      advertencias: result.advertencias,
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
    if (!summary || parsedRows.length === 0) return;

    setIsCommitting(true);
    setTimeout(() => {
      const now = new Date();
      const dateStr = now.toISOString().replace(/T/, ' ').replace(/\..+/, '');
      const batchCode = `BATCH_${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}_${Math.floor(1000 + Math.random() * 9000)}`;

      const newBatch: IngestionBatch = {
        id: `batch-${Date.now()}`,
        batchId: batchCode,
        nombreArchivo: fileName,
        tamanoKb: fileSizeKb,
        registrosProcesados: summary.total,
        registrosValidos: summary.validos,
        registrosConError: summary.errores,
        advertencias: summary.advertencias,
        fechaIngesta: dateStr,
        usuarioResponsable: 'Ing. Carlos Alberto Mendoza Silva',
        rolUsuario: 'Instructor Técnico Líder (CEET)',
        corteSnapshot: selectedCutoff,
        estado: 'Committed',
        sha256Hash: 'a7c9381f' + Math.random().toString(16).substring(2, 10) + '9f012bce8192a0e',
      };

      onCommitBatch(newBatch, parsedRows);
      setIsCommitting(false);
      setCommitSuccess(true);
    }, 1200);
  };

  // Filter rows for preview
  const displayedRows = parsedRows.filter(r => {
    if (filterRowsState === 'validos') return r.valido;
    if (filterRowsState === 'errores') return !r.valido;
    if (filterRowsState === 'advertencias') return r.juicioEvaluativo === 'Por Evaluar';
    return true;
  });

  return (
    <div className="space-y-6">
      {/* Title & Guidance Header */}
      <div className="bg-white p-5 rounded-[8px] border border-[#E2E8F0] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span className="text-[11px] font-bold text-[#0D7A53] uppercase tracking-wider bg-[#E6F4EA] px-2 py-0.5 rounded-[4px]">
            Módulo de Ingesta Institucional
          </span>
          <h2 className="text-xl font-heading font-bold text-[#0C2340] mt-1">
            Carga Masiva y Pre-validación de Juicios Evaluativos
          </h2>
          <p className="text-xs text-slate-500 mt-1 max-w-2xl">
            Sube archivos exportados de SofiaPlus en formato Excel (.xlsx) o CSV. El motor aplica validación estructural basada en esquemas de evaluación curricular SENA antes de persistir los registros.
          </p>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <button
            onClick={downloadSampleTemplate}
            className="px-3 py-2 bg-white border border-[#CBD5E1] hover:border-[#0C2340] text-[#0C2340] rounded-[4px] text-xs font-semibold flex items-center space-x-1.5 transition shadow-2xs"
            title="Descargar archivo modelo preformateado"
          >
            <Download size={14} />
            <span>Plantilla SofiaPlus (.xlsx)</span>
          </button>

          <button
            onClick={loadSampleData}
            className="px-3 py-2 bg-[#1B365D] hover:bg-[#0C2340] text-white rounded-[4px] text-xs font-semibold flex items-center space-x-1.5 transition shadow-2xs"
            title="Cargar conjunto de prueba interactivo"
          >
            <Sparkles size={14} className="text-amber-300" />
            <span>Cargar Archivo Muestra</span>
          </button>
        </div>
      </div>

      {/* Drag & Drop Area */}
      <div
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        onClick={() => fileInputRef.current?.click()}
        className={`border-2 border-dashed rounded-[8px] p-8 sm:p-10 text-center cursor-pointer transition duration-150 ${
          isDragging 
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
          <div className="w-14 h-14 bg-[#E8EDF5] text-[#0C2340] rounded-full flex items-center justify-center shadow-inner">
            <UploadCloud size={28} />
          </div>
          <div>
            <p className="text-sm font-semibold text-[#0C2340]">
              Arrastra y suelta tu archivo Excel o CSV aquí, o <span className="text-[#0D7A53] underline">examina tu equipo</span>
            </p>
            <p className="text-xs text-slate-500 mt-1">
              Archivos permitidos: XLSX o CSV hasta 25 MB • Estructura tabular SofiaPlus
            </p>
          </div>
        </div>
      </div>

      {/* Processing State */}
      {isProcessing && (
        <div className="bg-white p-6 rounded-[8px] border border-[#E2E8F0] text-center space-y-2">
          <div className="w-8 h-8 border-4 border-[#0C2340]/20 border-t-[#0C2340] rounded-full animate-spin mx-auto"></div>
          <p className="text-xs font-semibold text-[#0C2340]">
            Procesando hojas de cálculo y aplicando validaciones de esquema...
          </p>
        </div>
      )}

      {/* Success Ingested Banner */}
      {commitSuccess && (
        <div className="bg-[#E6F4EA] border border-[#A7F3D0] rounded-[6px] p-4 flex items-center justify-between text-xs text-[#0A6242]">
          <div className="flex items-center space-x-2">
            <CheckCircle2 size={18} className="text-[#0D7A53] flex-shrink-0" />
            <div>
              <span className="font-bold">¡Lote ingestado con éxito en la base de datos institucional!</span>
              <p className="text-[11px] text-emerald-800">
                Se han procesado {summary?.total} registros y se actualizó el modelo DirectQuery del cohorte.
              </p>
            </div>
          </div>
          <span className="text-[11px] font-mono font-semibold bg-white/70 px-2 py-1 rounded">
            Estado: Committed
          </span>
        </div>
      )}

      {/* Pre-validation Results & Summary Strip */}
      {summary && (
        <div className="space-y-4">
          <div className="bg-white p-4 sm:p-5 rounded-[8px] border border-[#E2E8F0] shadow-sm">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#E2E8F0] pb-3 mb-4">
              <div className="flex items-center space-x-2.5">
                <FileSpreadsheet size={20} className="text-[#0C2340]" />
                <div>
                  <h3 className="font-heading font-bold text-sm text-[#0C2340]">
                    {fileName}
                  </h3>
                  <p className="text-xs text-slate-500">
                    Tamaño: {fileSizeKb} KB • Corte de Ingesta: <strong className="text-slate-700">{selectedCutoff}</strong>
                  </p>
                </div>
              </div>

              {/* Action buttons */}
              <div className="flex items-center space-x-2">
                <button
                  onClick={() => {
                    setParsedRows([]);
                    setSummary(null);
                    setFileName('');
                  }}
                  className="px-3 py-1.5 text-xs text-slate-600 hover:text-slate-900 border border-slate-300 rounded-[4px] transition"
                >
                  Descartar
                </button>

                <button
                  onClick={handleCommit}
                  disabled={isCommitting || summary.errores > 0 || commitSuccess}
                  className={`px-4 py-1.5 text-xs font-semibold rounded-[4px] text-white flex items-center space-x-1.5 transition shadow-sm ${
                    summary.errores > 0
                      ? 'bg-slate-400 cursor-not-allowed'
                      : commitSuccess
                      ? 'bg-[#0D7A53] opacity-80 cursor-default'
                      : 'bg-[#0D7A53] hover:bg-[#0A6242]'
                  }`}
                >
                  <ShieldCheck size={14} />
                  <span>
                    {isCommitting ? 'Ingestando en BD...' : commitSuccess ? 'Lote Ingestado' : 'Confirmar e Ingestar en BD'}
                  </span>
                </button>
              </div>
            </div>

            {/* Validation Metrics Grid */}
            <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
              <div 
                onClick={() => setFilterRowsState('todos')}
                className={`p-3 rounded-[6px] border cursor-pointer transition ${
                  filterRowsState === 'todos' ? 'border-[#0C2340] bg-[#F1F5F9]' : 'border-[#E2E8F0] bg-white'
                }`}
              >
                <span className="text-[11px] font-semibold text-slate-500 uppercase">Total Registros</span>
                <div className="text-xl font-bold font-tabular text-[#0C2340] mt-0.5">{summary.total}</div>
              </div>

              <div 
                onClick={() => setFilterRowsState('validos')}
                className={`p-3 rounded-[6px] border cursor-pointer transition ${
                  filterRowsState === 'validos' ? 'border-[#0D7A53] bg-[#E6F4EA]' : 'border-[#E2E8F0] bg-white'
                }`}
              >
                <span className="text-[11px] font-semibold text-emerald-800 uppercase">Válidos</span>
                <div className="text-xl font-bold font-tabular text-[#0D7A53] mt-0.5">{summary.validos}</div>
              </div>

              <div 
                onClick={() => setFilterRowsState('advertencias')}
                className={`p-3 rounded-[6px] border cursor-pointer transition ${
                  filterRowsState === 'advertencias' ? 'border-[#D97706] bg-[#FEF3C7]' : 'border-[#E2E8F0] bg-white'
                }`}
              >
                <span className="text-[11px] font-semibold text-amber-800 uppercase">Advertencias</span>
                <div className="text-xl font-bold font-tabular text-[#D97706] mt-0.5">{summary.advertencias}</div>
              </div>

              <div 
                onClick={() => setFilterRowsState('errores')}
                className={`p-3 rounded-[6px] border cursor-pointer transition ${
                  filterRowsState === 'errores' ? 'border-[#DC2626] bg-[#FEE2E2]' : 'border-[#E2E8F0] bg-white'
                }`}
              >
                <span className="text-[11px] font-semibold text-rose-800 uppercase">Errores de Validación</span>
                <div className="text-xl font-bold font-tabular text-[#DC2626] mt-0.5">{summary.errores}</div>
              </div>
            </div>
          </div>

          {/* Validation Log if there are errors */}
          {validationErrors.length > 0 && (
            <div className="bg-white p-4 rounded-[8px] border border-[#E2E8F0]">
              <div className="flex items-center justify-between mb-2">
                <h4 className="text-xs font-bold text-[#0C2340] uppercase tracking-wider flex items-center gap-1.5">
                  <AlertTriangle size={14} className="text-amber-500" />
                  Registro de Observaciones y Advertencias de Esquema ({validationErrors.length})
                </h4>
              </div>
              <div className="max-h-40 overflow-y-auto space-y-1.5 pr-1">
                {validationErrors.slice(0, 10).map((err, idx) => (
                  <div 
                    key={idx}
                    className={`p-2 rounded text-xs flex items-start justify-between border ${
                      err.tipo === 'Error' 
                        ? 'bg-[#FEE2E2] border-[#FECACA] text-[#991B1B]' 
                        : 'bg-[#FEF3C7] border-[#FDE68A] text-[#92400E]'
                    }`}
                  >
                    <div>
                      <span className="font-mono font-bold mr-2">Fila {err.fila} [{err.campo}]:</span>
                      <span>{err.descripcion}</span>
                    </div>
                    <span className="font-mono text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-white/60">
                      {err.tipo}
                    </span>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Data Preview Table */}
          <div className="bg-white rounded-[8px] border border-[#E2E8F0] shadow-sm overflow-hidden">
            <div className="bg-[#F1F5F9] px-4 py-2.5 border-b border-[#E2E8F0] flex items-center justify-between">
              <span className="text-xs font-bold text-slate-700 uppercase tracking-wider">
                Previsualización de Filas Parseadas ({displayedRows.length} registros)
              </span>
              <span className="text-xs text-slate-500">
                Filtro activo: <strong className="capitalize">{filterRowsState}</strong>
              </span>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-xs text-left">
                <thead className="bg-[#F1F5F9] text-[#475569] uppercase font-bold text-[11px] border-b border-[#E2E8F0]">
                  <tr className="h-9">
                    <th className="py-2 px-3 text-center w-12">Fila</th>
                    <th className="py-2 px-3">Documento</th>
                    <th className="py-2 px-3">Aprendiz</th>
                    <th className="py-2 px-3">Ficha</th>
                    <th className="py-2 px-3">Cód. RAP</th>
                    <th className="py-2 px-3">Descripción RAP</th>
                    <th className="py-2 px-3">Juicio Evaluativo</th>
                    <th className="py-2 px-3">Instructor</th>
                    <th className="py-2 px-3 text-center">Estado Fila</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[#E2E8F0]">
                  {displayedRows.slice(0, 30).map((row, index) => {
                    const isZebra = index % 2 === 1;
                    return (
                      <tr 
                        key={index} 
                        className={`h-9 hover:bg-[#EDF2F7] transition ${isZebra ? 'bg-[#F8FAFC]' : 'bg-white'}`}
                      >
                        <td className="py-1.5 px-3 font-mono text-center text-slate-500">
                          {row.fila}
                        </td>
                        <td className="py-1.5 px-3 font-mono font-medium text-slate-800">
                          {row.tipoDocumento} {row.numeroDocumento}
                        </td>
                        <td className="py-1.5 px-3 font-medium text-slate-900">
                          {row.nombres} {row.apellidos}
                        </td>
                        <td className="py-1.5 px-3 font-mono text-slate-600">
                          {row.codigoFicha}
                        </td>
                        <td className="py-1.5 px-3 font-mono text-slate-700">
                          {row.rapCodigo}
                        </td>
                        <td className="py-1.5 px-3 text-slate-600 max-w-xs truncate" title={row.rapDescripcion}>
                          {row.rapDescripcion}
                        </td>
                        <td className="py-1.5 px-3">
                          {row.juicioEvaluativo === 'Aprobado' && (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#E6F4EA] text-[#0A6242] border border-[#A7F3D0]">
                              <span className="w-1.5 h-1.5 rounded-full bg-[#0D7A53]"></span> Aprobado
                            </span>
                          )}
                          {row.juicioEvaluativo === 'Por Evaluar' && (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]">
                              Por Evaluar
                            </span>
                          )}
                          {row.juicioEvaluativo === 'No Aprobado' && (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]">
                              No Aprobado
                            </span>
                          )}
                        </td>
                        <td className="py-1.5 px-3 text-slate-600 truncate max-w-[140px]">
                          {row.instructorEvaluador}
                        </td>
                        <td className="py-1.5 px-3 text-center">
                          {row.valido ? (
                            <span className="text-[#0D7A53] font-bold text-xs">✓ Válido</span>
                          ) : (
                            <span className="text-[#DC2626] font-bold text-xs" title={row.errores.join(', ')}>✕ Error</span>
                          )}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
            {displayedRows.length > 30 && (
              <div className="p-2.5 bg-[#F8FAFC] text-center text-xs text-slate-500 border-t border-[#E2E8F0]">
                Mostrando las primeras 30 de {displayedRows.length} filas
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};
