import React, { useState } from 'react';
import { 
  Users, 
  CheckCircle2, 
  Clock, 
  AlertTriangle, 
  RefreshCw, 
  Filter, 
  Maximize2, 
  BarChart, 
  TrendingUp, 
  ShieldAlert, 
  Eye, 
  Code2, 
  ArrowUpRight,
  Database,
  Layers,
  Upload,
  AlertOctagon,
  KeyRound,
  ExternalLink
} from 'lucide-react';
import { Aprendiz, CargaArchivo, ProgramaFormacion, UsuarioSesion } from '../types';

interface PowerBiDashboardProps {
  currentUser: UsuarioSesion;
  aprendices: Aprendiz[];
  programas: ProgramaFormacion[];
  cargas: CargaArchivo[];
  selectedCutoff: string;
  onCutoffChange: (cutoff: string) => void;
  directQueryLatencyMs: number;
  isRefreshing: boolean;
  onRefresh: () => void;
  onSelectAprendiz: (aprendiz: Aprendiz) => void;
  onNavigateToUpload: () => void;
}

export const PowerBiDashboard: React.FC<PowerBiDashboardProps> = ({
  currentUser,
  aprendices,
  programas,
  cargas,
  selectedCutoff,
  onCutoffChange,
  directQueryLatencyMs,
  isRefreshing,
  onRefresh,
  onSelectAprendiz,
  onNavigateToUpload,
}) => {
  const [selectedProgramaFilter, setSelectedProgramaFilter] = useState<string>('Todos');
  const [onlyPendingFilter, setOnlyPendingFilter] = useState<boolean>(false);
  const [showDaxInspector, setShowDaxInspector] = useState<boolean>(false);
  const [isFullScreen, setIsFullScreen] = useState<boolean>(false);

  // Caso D: Simulación de Expiración del EmbedToken de Power BI
  const [isTokenExpired, setIsTokenExpired] = useState<boolean>(false);
  const [isReconnectingToken, setIsReconnectingToken] = useState<boolean>(false);

  // Filtered apprentices based on program and pending filter
  const filteredAprendices = aprendices.filter(ap => {
    if (selectedProgramaFilter !== 'Todos' && ap.codigo_programa !== selectedProgramaFilter) {
      return false;
    }
    if (onlyPendingFilter && ap.raps_pendientes === 0 && ap.raps_no_aprobados === 0) {
      return false;
    }
    return true;
  });

  // Aggregate Metrics
  const totalAprendices = filteredAprendices.length;
  let totalEvaluaciones = 0;
  let evaluacionesAprobadas = 0;
  let evaluacionesPendientes = 0;
  let evaluacionesNoAprobadas = 0;

  filteredAprendices.forEach(ap => {
    totalEvaluaciones += ap.total_raps;
    evaluacionesAprobadas += ap.raps_aprobados;
    evaluacionesPendientes += ap.raps_pendientes;
    evaluacionesNoAprobadas += ap.raps_no_aprobados;
  });

  const tasaAprobacion = totalEvaluaciones > 0 
    ? ((evaluacionesAprobadas / totalEvaluaciones) * 100).toFixed(1) 
    : '0';

  const aprendicesEnRiesgo = filteredAprendices.filter(ap => ap.estado_academico === 'En Riesgo');

  const canUpload = currentUser.rol === 'ADMIN' || currentUser.rol === 'LIDER_FORMACION';

  // Handle Caso D token reconnection
  const handleReconnectToken = () => {
    setIsReconnectingToken(true);
    setTimeout(() => {
      setIsTokenExpired(false);
      setIsReconnectingToken(false);
      onRefresh();
    }, 800);
  };

  return (
    <div className={`space-y-6 ${isFullScreen ? 'fixed inset-0 z-50 bg-[#F8FAFC] overflow-y-auto p-4 sm:p-6' : ''}`}>
      {/* Power BI Embedded Container Wrapper */}
      <div className="bg-white rounded-[8px] border border-[#E2E8F0] shadow-[0_1px_3px_0_rgba(12,35,64,0.04),0_1px_2px_-1px_rgba(12,35,64,0.02)] overflow-hidden relative">
        {/* Power BI Embedded Header Bar */}
        <div className="bg-[#0C2340] px-4 py-3 flex flex-wrap items-center justify-between gap-3 text-white">
          <div className="flex items-center space-x-3">
            <div className="flex items-center space-x-2">
              <span className="w-2.5 h-2.5 rounded-full bg-[#0D7A53] inline-block animate-pulse"></span>
              <span className="font-heading font-semibold text-sm tracking-wide">
                Power BI Embedded • App Owns Data (DirectQuery)
              </span>
            </div>
            <span className="text-xs text-slate-300 hidden md:inline border-l border-slate-700 pl-3">
              Azure Service Principal • PostgreSQL Sync
            </span>
          </div>

          <div className="flex items-center space-x-2">
            {/* DirectQuery latency pill */}
            <div className="inline-flex items-center space-x-1.5 bg-[#081628] border border-[#1B365D] px-2.5 py-1 rounded-[4px] text-xs font-mono text-[#0D7A53]">
              <span className="w-2 h-2 rounded-full bg-[#0D7A53] inline-block"></span>
              <span>DirectQuery Activo • &lt; 2s ({directQueryLatencyMs} ms)</span>
            </div>

            {/* DAX Inspector Button */}
            <button
              onClick={() => setShowDaxInspector(!showDaxInspector)}
              className="px-2.5 py-1 text-xs font-medium rounded-[4px] bg-[#1B365D] hover:bg-slate-700 text-slate-200 transition flex items-center space-x-1 border border-slate-600"
              title="Inspeccionar consultas DAX / DirectQuery"
            >
              <Code2 size={13} />
              <span className="hidden sm:inline">DAX Query</span>
            </button>

            {/* Manual Refresh Trigger */}
            <button
              onClick={onRefresh}
              disabled={isRefreshing}
              className="px-2.5 py-1 text-xs font-semibold rounded-[4px] bg-[#0D7A53] hover:bg-[#0A6242] text-white transition flex items-center space-x-1.5 disabled:opacity-50"
              title="Invocar report.refresh()"
            >
              <RefreshCw size={13} className={isRefreshing ? 'animate-spin' : ''} />
              <span>report.refresh()</span>
            </button>

            {/* Toggle Token Expiry Test (Caso D Demo trigger) */}
            <button
              onClick={() => setIsTokenExpired(!isTokenExpired)}
              className="px-2 py-1 text-[11px] font-mono rounded-[4px] bg-slate-800 hover:bg-slate-700 text-slate-300 transition"
              title="Simular expiración de token de Power BI"
            >
              {isTokenExpired ? 'Token Vencido' : 'Token OK (60m)'}
            </button>

            {/* Fullscreen Toggle */}
            <button
              onClick={() => setIsFullScreen(!isFullScreen)}
              className="p-1 rounded-[4px] text-slate-300 hover:text-white hover:bg-slate-700 transition"
              title={isFullScreen ? 'Salir de pantalla completa' : 'Pantalla completa'}
            >
              <Maximize2 size={15} />
            </button>
          </div>
        </div>

        {/* Caso D: Overlay de Token Expirado */}
        {isTokenExpired && (
          <div className="absolute inset-x-0 top-12 z-40 bg-[#081628]/95 backdrop-blur-sm p-8 text-center text-white space-y-3 border-b border-amber-500/50">
            <div className="w-12 h-12 rounded-full bg-amber-500/20 border border-amber-500/40 text-amber-400 flex items-center justify-center mx-auto">
              <KeyRound size={24} />
            </div>
            <h3 className="font-heading font-bold text-base text-amber-300">
              La sesión de conexión con Power BI ha expirado (Embed Token Timeout)
            </h3>
            <p className="text-xs text-slate-300 max-w-md mx-auto">
              El tiempo límite de vida del token de lectura (60 minutos) ha concluido. Puedes renovar la conexión mediante el BFF sin perder tus filtros aplicados.
            </p>
            <div>
              <button
                onClick={handleReconnectToken}
                disabled={isReconnectingToken}
                className="px-4 py-2 bg-[#0D7A53] hover:bg-[#0A6242] text-white font-bold text-xs rounded-[4px] shadow-lg transition inline-flex items-center gap-1.5"
              >
                <RefreshCw size={14} className={isReconnectingToken ? 'animate-spin' : ''} />
                <span>{isReconnectingToken ? 'Solicitando nuevo token a Azure AD...' : 'Reconectar Reporte'}</span>
              </button>
            </div>
          </div>
        )}

        {/* DAX / DirectQuery Live Inspector Bar */}
        {showDaxInspector && (
          <div className="bg-[#081628] text-slate-200 p-3 text-xs font-mono border-b border-slate-800 space-y-1">
            <div className="flex items-center justify-between text-slate-400">
              <span className="font-semibold text-[#0D7A53]">DAX DIRECTQUERY EXECUTION ENGINE</span>
              <span>DirectQuery Latencia: {directQueryLatencyMs}ms • PostgreSQL 16</span>
            </div>
            <pre className="overflow-x-auto text-emerald-400 p-2 bg-[#050C16] rounded border border-slate-800">
{`EVALUATE 
SUMMARIZECOLUMNS(
  'aprendices'[numero_identificacion],
  'programas'[codigo_programa],
  FILTER('cargas_archivo', 'cargas_archivo'[fecha_corte] = "${selectedCutoff}"),
  "Total Evaluaciones", COUNT('juicios_evaluativos'[id]),
  "Aprobados", CALCULATE(COUNT('juicios_evaluativos'[id]), 'juicios_evaluativos'[juicio] = "APROBADO"),
  "Tasa Aprobacion", DIVIDE([Aprobados], [Total Evaluaciones], 0)
) 
ORDER BY [Tasa Aprobacion] ASC`}
            </pre>
          </div>
        )}

        {/* Global Toolbar and Slicers */}
        <div className="bg-[#F1F5F9] px-4 py-2.5 border-b border-[#E2E8F0] flex flex-wrap items-center justify-between gap-3 text-xs">
          <div className="flex flex-wrap items-center gap-3">
            {/* Snapshot Date Switcher */}
            <div className="flex items-center space-x-1.5">
              <span className="text-slate-600 font-bold">Corte Histórico (snapshot_date):</span>
              <select
                value={selectedCutoff}
                onChange={(e) => onCutoffChange(e.target.value)}
                className="bg-white border border-[#CBD5E1] rounded-[4px] px-2 py-1 text-slate-800 font-semibold focus:ring-1 focus:ring-[#0C2340]"
              >
                {cargas.map(c => (
                  <option key={c.batch_id} value={c.fecha_corte}>
                    {c.fecha_corte} ({c.nombre_archivo.substring(0, 24)}...)
                  </option>
                ))}
              </select>
            </div>

            {/* Program Slicer */}
            <div className="flex items-center space-x-1.5">
              <span className="text-slate-600 font-medium">Programa:</span>
              <select
                value={selectedProgramaFilter}
                onChange={(e) => setSelectedProgramaFilter(e.target.value)}
                className="bg-white border border-[#CBD5E1] rounded-[4px] px-2 py-1 text-slate-800 font-medium focus:ring-1 focus:ring-[#0C2340]"
              >
                <option value="Todos">Todos los Programas ({programas.length})</option>
                {programas.map(p => (
                  <option key={p.codigo_programa} value={p.codigo_programa}>
                    {p.codigo_programa} - {p.nombre_programa}
                  </option>
                ))}
              </select>
            </div>

            {/* Quick Filter: Only Pending / In Risk */}
            <button
              onClick={() => setOnlyPendingFilter(!onlyPendingFilter)}
              className={`px-2.5 py-1 rounded-[4px] text-xs font-semibold transition border ${
                onlyPendingFilter
                  ? 'bg-amber-100 border-amber-400 text-amber-900'
                  : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50'
              }`}
            >
              {onlyPendingFilter ? '✓ Filtrando Casos con Pendientes' : 'Filtrar Solo Pendientes/Riesgo'}
            </button>
          </div>

          {/* Upload CTA for Gestor/Admin */}
          {canUpload ? (
            <button
              onClick={onNavigateToUpload}
              className="px-3 py-1.5 bg-[#0C2340] hover:bg-[#1B365D] text-white font-semibold rounded-[4px] flex items-center space-x-1.5 transition shadow-sm"
              title="Cargar nuevo reporte de SofiaPlus"
            >
              <Upload size={13} />
              <span>Cargar Nuevo Reporte</span>
            </button>
          ) : (
            <span className="text-slate-400 text-[11px] italic bg-slate-200/60 px-2 py-1 rounded">
              Vista Solo Lectura (Rol Instructor)
            </span>
          )}
        </div>

        {/* Loading Spinner Skeleton State if refreshing */}
        {isRefreshing && (
          <div className="p-8 text-center bg-white/90 space-y-3">
            <div className="inline-block relative w-10 h-10">
              <div className="w-10 h-10 rounded-full border-4 border-[#0C2340]/20 border-t-[#0C2340] animate-spin"></div>
            </div>
            <p className="text-sm font-semibold text-[#0C2340]">
              Estableciendo conexión segura con Power BI DirectQuery...
            </p>
            <p className="text-xs text-slate-500">
              Actualizando tarjetas de KPI, gráficos de avance y listas de tareas pendientes sin recargar la página.
            </p>
          </div>
        )}

        {/* KPI Scorecards Strip */}
        <div className="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-[#F8FAFC]">
          {/* Card 1: Total Aprendices */}
          <div className="bg-white p-4 rounded-[6px] border border-[#E2E8F0] shadow-sm hover:border-slate-300 transition">
            <div className="flex items-center justify-between">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Aprendices en Cohorte</span>
              <span className="p-2 bg-[#E8EDF5] text-[#0C2340] rounded-[4px]">
                <Users size={16} />
              </span>
            </div>
            <div className="mt-2 flex items-baseline justify-between">
              <div className="text-2xl font-bold font-tabular text-[#0C2340]">
                {totalAprendices}
              </div>
              <span className="text-xs font-semibold text-emerald-700 bg-[#E6F4EA] px-1.5 py-0.5 rounded-[4px]">
                100% Auditados
              </span>
            </div>
            <p className="mt-1 text-xs text-slate-500">
              Corte activo: {selectedCutoff}
            </p>
          </div>

          {/* Card 2: Tasa de Aprobación Global */}
          <div className="bg-white p-4 rounded-[6px] border border-[#E2E8F0] shadow-sm hover:border-slate-300 transition">
            <div className="flex items-center justify-between">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Aprobación Global</span>
              <span className="p-2 bg-[#E6F4EA] text-[#0D7A53] rounded-[4px]">
                <CheckCircle2 size={16} />
              </span>
            </div>
            <div className="mt-2 flex items-baseline justify-between">
              <div className="text-2xl font-bold font-tabular text-[#0D7A53]">
                {tasaAprobacion}%
              </div>
              <span className="text-xs font-semibold text-emerald-800 bg-[#E6F4EA] px-1.5 py-0.5 rounded-[4px]">
                {evaluacionesAprobadas} Aprobados
              </span>
            </div>
            <div className="mt-2 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
              <div 
                className="bg-[#0D7A53] h-1.5 rounded-full transition-all duration-500" 
                style={{ width: `${Math.min(100, Number(tasaAprobacion))}%` }}
              ></div>
            </div>
          </div>

          {/* Card 3: RAPs Pendientes de Evaluación */}
          <div className="bg-white p-4 rounded-[6px] border border-[#E2E8F0] shadow-sm hover:border-slate-300 transition">
            <div className="flex items-center justify-between">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Juicios Pendientes</span>
              <span className="p-2 bg-[#FEF3C7] text-[#D97706] rounded-[4px]">
                <Clock size={16} />
              </span>
            </div>
            <div className="mt-2 flex items-baseline justify-between">
              <div className="text-2xl font-bold font-tabular text-[#D97706]">
                {evaluacionesPendientes}
              </div>
              <button
                onClick={() => setOnlyPendingFilter(true)}
                className="text-[11px] font-semibold text-amber-800 underline hover:text-amber-900"
              >
                Ver Lista
              </button>
            </div>
            <p className="mt-1 text-xs text-slate-500">
              Resultados pendientes de asentar en SofiaPlus
            </p>
          </div>

          {/* Card 4: Casos en Riesgo Pedagógico */}
          <div className="bg-white p-4 rounded-[6px] border border-[#E2E8F0] shadow-sm hover:border-slate-300 transition">
            <div className="flex items-center justify-between">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">En Riesgo / Condicionados</span>
              <span className="p-2 bg-[#FEE2E2] text-[#DC2626] rounded-[4px]">
                <ShieldAlert size={16} />
              </span>
            </div>
            <div className="mt-2 flex items-baseline justify-between">
              <div className="text-2xl font-bold font-tabular text-[#DC2626]">
                {aprendicesEnRiesgo.length}
              </div>
              <span className="text-xs font-semibold text-rose-800 bg-[#FEE2E2] px-1.5 py-0.5 rounded-[4px]">
                {evaluacionesNoAprobadas} No Aprobados
              </span>
            </div>
            <p className="mt-1 text-xs text-slate-500">
              Citados a plan de mejoramiento académico
            </p>
          </div>
        </div>

        {/* Analytical Visuals Grid */}
        <div className="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 bg-white border-t border-[#E2E8F0]">
          {/* Visual 1: Programas de Formación y Avance */}
          <div className="lg:col-span-2 space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="font-heading font-semibold text-sm text-[#0C2340]">
                  Cumplimiento y Aprobación por Programa de Formación
                </h3>
                <p className="text-xs text-slate-500">
                  Calculado mediante DirectQuery sobre la tabla maestra juicios_evaluativos
                </p>
              </div>

              {/* Paso 6.1 Action Button */}
              <button
                onClick={() => setOnlyPendingFilter(!onlyPendingFilter)}
                className="text-xs font-semibold text-[#0C2340] hover:text-[#0D7A53] flex items-center gap-1 transition"
              >
                <span>Inspeccionar pendientes en este corte</span>
                <ArrowUpRight size={13} />
              </button>
            </div>

            <div className="space-y-3">
              {programas.map((prog) => {
                const progAprendices = aprendices.filter(a => a.codigo_programa === prog.codigo_programa);
                const totalRapsProg = progAprendices.reduce((acc, a) => acc + a.total_raps, 0);
                const aprobadosProg = progAprendices.reduce((acc, a) => acc + a.raps_aprobados, 0);
                const pct = totalRapsProg > 0 ? Math.round((aprobadosProg / totalRapsProg) * 100) : 0;

                return (
                  <div key={prog.codigo_programa} className="p-3 bg-[#F8FAFC] rounded-[4px] border border-[#E2E8F0]">
                    <div className="flex justify-between items-center text-xs mb-1.5">
                      <span className="font-semibold text-slate-800">
                        {prog.codigo_programa} - {prog.nombre_programa}
                      </span>
                      <span className="font-tabular font-bold text-slate-700">
                        {pct}% ({aprobadosProg}/{totalRapsProg} RAPs)
                      </span>
                    </div>

                    <div className="w-full bg-slate-200 h-2 rounded-[2px] overflow-hidden">
                      <div 
                        className="bg-[#0D7A53] h-full transition-all duration-300"
                        style={{ width: `${pct}%` }}
                      />
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          {/* Visual 2: DirectQuery Operational Summary */}
          <div className="space-y-4">
            <div>
              <h3 className="font-heading font-semibold text-sm text-[#0C2340]">
                Estado de la Conexión DirectQuery
              </h3>
              <p className="text-xs text-slate-500">
                Parámetros de conexión del Azure Service Principal
              </p>
            </div>

            <div className="p-4 bg-[#F8FAFC] rounded-[6px] border border-[#E2E8F0] space-y-3 text-xs">
              <div className="flex justify-between pb-2 border-b border-slate-200">
                <span className="text-slate-500">Modo de Datos:</span>
                <span className="font-bold text-[#0D7A53]">DirectQuery (Push-Down)</span>
              </div>
              <div className="flex justify-between pb-2 border-b border-slate-200">
                <span className="text-slate-500">Base de Datos:</span>
                <span className="font-mono font-semibold text-slate-700">evaluative_db (PostgreSQL 16)</span>
              </div>
              <div className="flex justify-between pb-2 border-b border-slate-200">
                <span className="text-slate-500">Latencia Promedio:</span>
                <span className="font-mono text-[#0D7A53] font-bold">{directQueryLatencyMs} ms</span>
              </div>
              <div className="flex justify-between">
                <span className="text-slate-500">Token Status:</span>
                <span className="font-semibold text-emerald-700">EmbedToken Activo (60 min)</span>
              </div>
            </div>

            {canUpload && (
              <div className="p-3 bg-[#E8EDF5] rounded-[6px] border border-[#CBD5E1] text-xs">
                <div className="font-bold text-[#0C2340] flex items-center gap-1.5">
                  <Database size={14} className="text-[#0D7A53]" /> Nuevo Período Formativo
                </div>
                <p className="text-slate-600 mt-1 text-[11px]">
                  Carga un archivo Excel para crear un nuevo corte temporal sin sobrescribir auditorías históricas.
                </p>
                <button
                  onClick={onNavigateToUpload}
                  className="mt-2 w-full py-1.5 bg-[#0C2340] hover:bg-[#1B365D] text-white font-semibold rounded-[4px] text-xs transition"
                >
                  Subir Excel (.xlsx)
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Detailed Table of Learners */}
        <div className="p-4 sm:p-6 bg-[#F8FAFC] border-t border-[#E2E8F0]">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
            <div>
              <h3 className="font-heading font-semibold text-sm text-[#0C2340]">
                Listado Auditado de Aprendices (SofiaPlus Sync)
              </h3>
              <p className="text-xs text-slate-500">
                Haz clic en cualquier aprendiz para inspeccionar su trazabilidad de juicios evaluativos.
              </p>
            </div>
            <div className="text-xs text-slate-500">
              Mostrando <strong className="text-slate-800">{filteredAprendices.length}</strong> de {aprendices.length} aprendices
            </div>
          </div>

          <div className="overflow-x-auto bg-white rounded-[6px] border border-[#E2E8F0] shadow-sm">
            <table className="w-full text-xs text-left">
              <thead className="bg-[#F1F5F9] text-[#475569] uppercase font-bold text-[11px] border-b border-[#E2E8F0]">
                <tr>
                  <th className="py-2.5 px-3">Identificación</th>
                  <th className="py-2.5 px-3">Nombre Completo</th>
                  <th className="py-2.5 px-3">Programa</th>
                  <th className="py-2.5 px-3 text-center">Avance</th>
                  <th className="py-2.5 px-3 text-center">Aprobados</th>
                  <th className="py-2.5 px-3 text-center">Pendientes</th>
                  <th className="py-2.5 px-3 text-center">No Aprobados</th>
                  <th className="py-2.5 px-3">Estado Académico</th>
                  <th className="py-2.5 px-3 text-right">Acción</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-[#E2E8F0]">
                {filteredAprendices.map((ap, index) => (
                  <tr 
                    key={ap.numero_identificacion}
                    className={`hover:bg-[#EDF2F7] transition cursor-pointer ${index % 2 === 1 ? 'bg-[#F8FAFC]' : 'bg-white'}`}
                    onClick={() => onSelectAprendiz(ap)}
                  >
                    <td className="py-2 px-3 font-mono font-semibold text-slate-800">
                      {ap.tipo_identificacion} {ap.numero_identificacion}
                    </td>
                    <td className="py-2 px-3 font-medium text-slate-900">
                      {ap.nombre_completo}
                    </td>
                    <td className="py-2 px-3 text-slate-600 truncate max-w-[180px]">
                      {ap.nombre_programa}
                    </td>
                    <td className="py-2 px-3 text-center">
                      <span className="font-tabular font-bold text-slate-700">{ap.porcentaje_avance}%</span>
                    </td>
                    <td className="py-2 px-3 text-center font-tabular font-bold text-[#0A6242]">
                      {ap.raps_aprobados}
                    </td>
                    <td className="py-2 px-3 text-center font-tabular font-bold text-[#92400E]">
                      {ap.raps_pendientes}
                    </td>
                    <td className="py-2 px-3 text-center font-tabular font-bold text-[#991B1B]">
                      {ap.raps_no_aprobados}
                    </td>
                    <td className="py-2 px-3">
                      <span className={`inline-block px-2 py-0.5 text-[10px] font-semibold rounded-[4px] ${
                        ap.estado_academico === 'Por Certificar' ? 'bg-[#E6F4EA] text-[#0A6242] border border-[#A7F3D0]' :
                        ap.estado_academico === 'En Riesgo' ? 'bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]' :
                        'bg-blue-50 text-blue-700 border border-blue-200'
                      }`}>
                        {ap.estado_academico}
                      </span>
                    </td>
                    <td className="py-2 px-3 text-right">
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          onSelectAprendiz(ap);
                        }}
                        className="px-2 py-1 bg-white border border-[#CBD5E1] hover:border-[#0C2340] text-[#0C2340] rounded-[4px] font-medium text-[11px] inline-flex items-center gap-1"
                      >
                        <Eye size={12} /> Detalle
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
};
