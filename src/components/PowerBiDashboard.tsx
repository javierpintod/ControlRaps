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
  Award
} from 'lucide-react';
import { Aprendiz, FichaCaracterizacion } from '../types';

interface PowerBiDashboardProps {
  aprendices: Aprendiz[];
  fichas: FichaCaracterizacion[];
  selectedCutoff: string;
  onCutoffChange: (cutoff: string) => void;
  directQueryLatencyMs: number;
  isRefreshing: boolean;
  onRefresh: () => void;
  onSelectAprendiz: (aprendiz: Aprendiz) => void;
  onNavigateToIngestion: () => void;
  onNavigateToJuicios: () => void;
}

export const PowerBiDashboard: React.FC<PowerBiDashboardProps> = ({
  aprendices,
  fichas,
  selectedCutoff,
  onCutoffChange,
  directQueryLatencyMs,
  isRefreshing,
  onRefresh,
  onSelectAprendiz,
  onNavigateToIngestion,
  onNavigateToJuicios,
}) => {
  const [selectedRegional, setSelectedRegional] = useState<string>('Todas');
  const [selectedFichaFilter, setSelectedFichaFilter] = useState<string>('Todas');
  const [showDaxInspector, setShowDaxInspector] = useState<boolean>(false);
  const [isFullScreen, setIsFullScreen] = useState<boolean>(false);

  // Filtered apprentices calculation
  const filteredAprendices = aprendices.filter(ap => {
    const ficha = fichas.find(f => f.id === ap.fichaId);
    if (selectedRegional !== 'Todas' && ficha?.regional !== selectedRegional) return false;
    if (selectedFichaFilter !== 'Todas' && ap.fichaId !== selectedFichaFilter) return false;
    return true;
  });

  // Calculate KPIs
  const totalAprendices = filteredAprendices.length;
  let totalRaps = 0;
  let rapsAprobados = 0;
  let rapsPendientes = 0;
  let rapsNoAprobados = 0;

  filteredAprendices.forEach(ap => {
    totalRaps += ap.rapsTotales;
    rapsAprobados += ap.rapsAprobados;
    rapsPendientes += ap.rapsPendientes;
    rapsNoAprobados += ap.rapsNoAprobados;
  });

  const tasaAprobacion = totalRaps > 0 ? ((rapsAprobados / totalRaps) * 100).toFixed(1) : '0';
  const aprendicesEnRiesgo = filteredAprendices.filter(ap => ap.rapsNoAprobados > 0 || ap.estadoFormacion === 'Condicionado');

  // Competencies breakdown
  const competenciasMap: { [key: string]: { total: number; aprobados: number; pendientes: number; noAprobados: number } } = {};
  filteredAprendices.forEach(ap => {
    ap.raps.forEach(rap => {
      if (!competenciasMap[rap.competenciaNombre]) {
        competenciasMap[rap.competenciaNombre] = { total: 0, aprobados: 0, pendientes: 0, noAprobados: 0 };
      }
      competenciasMap[rap.competenciaNombre].total += 1;
      if (rap.estado === 'Aprobado') competenciasMap[rap.competenciaNombre].aprobados += 1;
      else if (rap.estado === 'Por Evaluar') competenciasMap[rap.competenciaNombre].pendientes += 1;
      else competenciasMap[rap.competenciaNombre].noAprobados += 1;
    });
  });

  const competenciasArray = Object.entries(competenciasMap).map(([nombre, datos]) => ({
    nombre,
    total: datos.total,
    aprobados: datos.aprobados,
    pendientes: datos.pendientes,
    noAprobados: datos.noAprobados,
    porcentaje: datos.total > 0 ? Math.round((datos.aprobados / datos.total) * 100) : 0,
  }));

  return (
    <div className={`space-y-6 ${isFullScreen ? 'fixed inset-0 z-50 bg-[#F8FAFC] overflow-y-auto p-4 sm:p-6' : ''}`}>
      {/* Power BI Embedded Container Wrapper */}
      <div className="bg-white rounded-[8px] border border-[#E2E8F0] shadow-[0_1px_3px_0_rgba(12,35,64,0.04),0_1px_2px_-1px_rgba(12,35,64,0.02)] overflow-hidden">
        {/* Power BI Embedded Header Bar */}
        <div className="bg-[#0C2340] px-4 py-3 flex flex-wrap items-center justify-between gap-3 text-white">
          <div className="flex items-center space-x-3">
            <div className="flex items-center space-x-2">
              <span className="w-2.5 h-2.5 rounded-full bg-[#F59E0B] inline-block animate-pulse"></span>
              <span className="font-heading font-semibold text-sm tracking-wide">
                Power BI Embedded • Workspace SENA DirectQuery
              </span>
            </div>
            <span className="text-xs text-slate-300 hidden md:inline border-l border-slate-700 pl-3">
              Modelo Tabular DirectQuery • SofiaPlus Sync
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

        {/* DAX / DirectQuery Live Inspector Bar */}
        {showDaxInspector && (
          <div className="bg-[#081628] text-slate-200 p-3 text-xs font-mono border-b border-slate-800 space-y-1">
            <div className="flex items-center justify-between text-slate-400">
              <span className="font-semibold text-[#0D7A53]">DAX DIRECTQUERY EXECUTION ENGINE</span>
              <span>Latencia: {directQueryLatencyMs}ms • Modo: Push-Down DirectQuery</span>
            </div>
            <pre className="overflow-x-auto text-emerald-400 p-2 bg-[#050C16] rounded border border-slate-800">
{`EVALUATE 
SUMMARIZECOLUMNS(
  'DimAprendiz'[NumeroDocumento],
  'DimFicha'[CodigoFicha],
  'DimCompetencia'[NombreCompetencia],
  FILTER('DimCorte', 'DimCorte'[Snapshot] = "${selectedCutoff}"),
  "Total RAPs", COUNT('FactJuiciosEvaluativos'[IdRap]),
  "Aprobados", CALCULATE(COUNT('FactJuiciosEvaluativos'[IdRap]), 'FactJuiciosEvaluativos'[Juicio] = "Aprobado"),
  "TasaAprobacion", DIVIDE([Aprobados], [Total RAPs], 0)
) 
ORDER BY [TasaAprobacion] ASC`}
            </pre>
          </div>
        )}

        {/* Filter Controls Bar */}
        <div className="bg-[#F1F5F9] px-4 py-2.5 border-b border-[#E2E8F0] flex flex-wrap items-center justify-between gap-3 text-xs">
          <div className="flex flex-wrap items-center gap-3">
            <span className="font-semibold text-slate-700 flex items-center gap-1.5">
              <Filter size={13} className="text-[#0C2340]" /> Segmentadores Power BI:
            </span>

            {/* Regional Filter */}
            <div className="flex items-center space-x-1.5">
              <span className="text-slate-600">Regional:</span>
              <select
                value={selectedRegional}
                onChange={(e) => setSelectedRegional(e.target.value)}
                className="bg-white border border-[#CBD5E1] rounded-[4px] px-2 py-1 text-slate-800 font-medium focus:ring-1 focus:ring-[#0C2340]"
              >
                <option value="Todas">Todas las Regionales</option>
                <option value="Distrito Capital">Distrito Capital</option>
                <option value="Antioquia">Antioquia</option>
                <option value="Valle del Cauca">Valle del Cauca</option>
              </select>
            </div>

            {/* Ficha Filter */}
            <div className="flex items-center space-x-1.5">
              <span className="text-slate-600">Ficha:</span>
              <select
                value={selectedFichaFilter}
                onChange={(e) => setSelectedFichaFilter(e.target.value)}
                className="bg-white border border-[#CBD5E1] rounded-[4px] px-2 py-1 text-slate-800 font-medium focus:ring-1 focus:ring-[#0C2340]"
              >
                <option value="Todas">Todas las Fichas Activas</option>
                {fichas.map(f => (
                  <option key={f.id} value={f.id}>
                    {f.codigoFicha} - {f.programaFormacion.substring(0, 32)}...
                  </option>
                ))}
              </select>
            </div>
          </div>

          <div className="text-slate-500 font-tabular">
            Visualizando <span className="font-semibold text-slate-800">{filteredAprendices.length}</span> de {aprendices.length} aprendices
          </div>
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
              Sincronizando modelos tabulares, matrices de aprendizaje y cortes evaluativos.
            </p>
          </div>
        )}

        {/* KPI Scorecard Strip */}
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
                100% Cobertura
              </span>
            </div>
            <p className="mt-1 text-xs text-slate-500">
              {fichas.length} Fichas de formación activas
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
                Meta &ge; 85%
              </span>
            </div>
            <div className="mt-2 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
              <div 
                className="bg-[#0D7A53] h-1.5 rounded-full transition-all duration-500" 
                style={{ width: `${Math.min(100, Number(tasaAprobacion))}%` }}
              ></div>
            </div>
          </div>

          {/* Card 3: RAPs Por Evaluar */}
          <div className="bg-white p-4 rounded-[6px] border border-[#E2E8F0] shadow-sm hover:border-slate-300 transition">
            <div className="flex items-center justify-between">
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider">Por Evaluar (Pendientes)</span>
              <span className="p-2 bg-[#FEF3C7] text-[#D97706] rounded-[4px]">
                <Clock size={16} />
              </span>
            </div>
            <div className="mt-2 flex items-baseline justify-between">
              <div className="text-2xl font-bold font-tabular text-[#D97706]">
                {rapsPendientes}
              </div>
              <span className="text-xs font-semibold text-amber-800 bg-[#FEF3C7] px-1.5 py-0.5 rounded-[4px]">
                Revisión Instructor
              </span>
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
                Comité de Evaluación
              </span>
            </div>
            <p className="mt-1 text-xs text-slate-500">
              {rapsNoAprobados} RAPs no aprobados en cohorte
            </p>
          </div>
        </div>

        {/* Analytical Visuals Grid */}
        <div className="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 bg-white border-t border-[#E2E8F0]">
          {/* Visual 1: Competencies Performance Chart */}
          <div className="lg:col-span-2 space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="font-heading font-semibold text-sm text-[#0C2340]">
                  Cumplimiento de RAPs por Competencia Técnica
                </h3>
                <p className="text-xs text-slate-500">
                  Porcentaje de aprobación consolidado en la matriz curricular
                </p>
              </div>
              <button 
                onClick={onNavigateToJuicios}
                className="text-xs text-[#0C2340] hover:text-[#0D7A53] font-semibold flex items-center gap-1 transition"
              >
                Ver Matriz Completa <ArrowUpRight size={13} />
              </button>
            </div>

            <div className="space-y-3">
              {competenciasArray.slice(0, 5).map((comp, idx) => (
                <div key={idx} className="p-3 bg-[#F8FAFC] rounded-[4px] border border-[#E2E8F0]">
                  <div className="flex justify-between items-center text-xs mb-1.5">
                    <span className="font-semibold text-slate-800 truncate max-w-[70%]">
                      {comp.nombre}
                    </span>
                    <span className="font-tabular font-bold text-slate-700">
                      {comp.porcentaje}% ({comp.aprobados}/{comp.total} RAPs)
                    </span>
                  </div>
                  {/* Segmented Bar */}
                  <div className="w-full bg-slate-200 h-2.5 rounded-[2px] flex overflow-hidden">
                    <div 
                      className="bg-[#0D7A53] h-full transition-all duration-300" 
                      style={{ width: `${(comp.aprobados / comp.total) * 100}%` }}
                      title={`Aprobados: ${comp.aprobados}`}
                    />
                    <div 
                      className="bg-[#F59E0B] h-full transition-all duration-300" 
                      style={{ width: `${(comp.pendientes / comp.total) * 100}%` }}
                      title={`Por Evaluar: ${comp.pendientes}`}
                    />
                    <div 
                      className="bg-[#DC2626] h-full transition-all duration-300" 
                      style={{ width: `${(comp.noAprobados / comp.total) * 100}%` }}
                      title={`No Aprobados: ${comp.noAprobados}`}
                    />
                  </div>
                  <div className="flex items-center gap-4 mt-1.5 text-[10px] text-slate-500 font-medium">
                    <span className="flex items-center gap-1">
                      <span className="w-2 h-2 rounded-full bg-[#0D7A53]"></span> {comp.aprobados} Aprobados
                    </span>
                    <span className="flex items-center gap-1">
                      <span className="w-2 h-2 rounded-full bg-[#F59E0B]"></span> {comp.pendientes} Pendientes
                    </span>
                    {comp.noAprobados > 0 && (
                      <span className="flex items-center gap-1">
                        <span className="w-2 h-2 rounded-full bg-[#DC2626]"></span> {comp.noAprobados} No Aprobados
                      </span>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>

          {/* Visual 2: Fichas Status & Quick Actions */}
          <div className="space-y-4">
            <div>
              <h3 className="font-heading font-semibold text-sm text-[#0C2340]">
                Estado de Fichas de Formación
              </h3>
              <p className="text-xs text-slate-500">
                Monitoreo de avance y alertas de comités pedagógicos
              </p>
            </div>

            <div className="space-y-3">
              {fichas.map(ficha => (
                <div key={ficha.id} className="p-3 border border-[#E2E8F0] rounded-[6px] hover:border-[#0C2340] transition bg-white">
                  <div className="flex items-start justify-between">
                    <div>
                      <span className="text-[11px] font-mono font-bold bg-[#E8EDF5] text-[#0C2340] px-1.5 py-0.5 rounded">
                        Ficha {ficha.codigoFicha}
                      </span>
                      <h4 className="text-xs font-bold text-slate-800 mt-1 line-clamp-1">
                        {ficha.programaFormacion}
                      </h4>
                    </div>
                    <span className={`text-[10px] font-bold px-2 py-0.5 rounded-[4px] uppercase ${
                      ficha.estado === 'Lectiva' ? 'bg-[#E6F4EA] text-[#0A6242]' : 'bg-blue-50 text-blue-700'
                    }`}>
                      {ficha.estado}
                    </span>
                  </div>

                  <div className="mt-2 grid grid-cols-2 gap-2 text-[11px] text-slate-600">
                    <div>
                      <span className="text-slate-400 block text-[10px]">Instructor Líder:</span>
                      <span className="font-medium text-slate-700 truncate block">{ficha.instructorLider}</span>
                    </div>
                    <div>
                      <span className="text-slate-400 block text-[10px]">Aprendices:</span>
                      <span className="font-tabular font-semibold text-slate-800">
                        {ficha.aprendicesAlDia} al día / {ficha.aprendicesEnRiesgo} riesgo
                      </span>
                    </div>
                  </div>
                </div>
              ))}
            </div>

            {/* Ingestion Prompt Banner */}
            <div className="p-3 bg-[#E8EDF5] rounded-[6px] border border-[#CBD5E1] text-xs">
              <div className="font-bold text-[#0C2340] flex items-center gap-1.5">
                <Database size={14} className="text-[#0D7A53]" /> Carga Masiva de Juicios
              </div>
              <p className="text-slate-600 mt-1 text-[11px]">
                ¿Tienes un nuevo reporte descargado de SofiaPlus? Súbelo para auditarlo antes de asentar.
              </p>
              <button
                onClick={onNavigateToIngestion}
                className="mt-2 w-full py-1.5 bg-[#0C2340] hover:bg-[#1B365D] text-white font-semibold rounded-[4px] text-xs transition"
              >
                Cargar Archivo Excel / CSV
              </button>
            </div>
          </div>
        </div>

        {/* Drilldown Section: Learner Audit Roster */}
        <div className="p-4 sm:p-6 bg-[#F8FAFC] border-t border-[#E2E8F0]">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
            <div>
              <h3 className="font-heading font-semibold text-sm text-[#0C2340]">
                Listado Auditado de Aprendices (DirectQuery Drill-through)
              </h3>
              <p className="text-xs text-slate-500">
                Haz clic en cualquier aprendiz para inspeccionar su trazabilidad de RAPs y juicio evaluativo oficial.
              </p>
            </div>
            <div className="flex items-center space-x-2">
              <span className="text-xs text-slate-500">Filas activas:</span>
              <span className="text-xs font-bold text-[#0C2340] bg-white px-2 py-0.5 rounded border border-[#E2E8F0]">
                {filteredAprendices.length}
              </span>
            </div>
          </div>

          <div className="overflow-x-auto bg-white rounded-[6px] border border-[#E2E8F0] shadow-sm">
            <table className="w-full text-xs text-left">
              <thead className="bg-[#F1F5F9] text-[#475569] uppercase font-bold text-[11px] border-b border-[#E2E8F0]">
                <tr>
                  <th className="py-2.5 px-3">Documento</th>
                  <th className="py-2.5 px-3">Aprendiz</th>
                  <th className="py-2.5 px-3">Ficha</th>
                  <th className="py-2.5 px-3 text-center">Avance</th>
                  <th className="py-2.5 px-3 text-center">Aprobados</th>
                  <th className="py-2.5 px-3 text-center">Pendientes</th>
                  <th className="py-2.5 px-3 text-center">No Aprobados</th>
                  <th className="py-2.5 px-3">Estado</th>
                  <th className="py-2.5 px-3 text-right">Acción</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-[#E2E8F0]">
                {filteredAprendices.map((ap, index) => {
                  const fichaObj = fichas.find(f => f.id === ap.fichaId);
                  const isZebra = index % 2 === 1;

                  return (
                    <tr 
                      key={ap.id} 
                      className={`hover:bg-[#EDF2F7] transition cursor-pointer ${isZebra ? 'bg-[#F8FAFC]' : 'bg-white'}`}
                      onClick={() => onSelectAprendiz(ap)}
                    >
                      <td className="py-2.5 px-3 font-mono font-semibold text-slate-800">
                        {ap.tipoDocumento} {ap.numeroDocumento}
                      </td>
                      <td className="py-2.5 px-3 font-medium text-slate-900">
                        {ap.nombres} {ap.apellidos}
                      </td>
                      <td className="py-2.5 px-3 font-mono text-slate-600">
                        {fichaObj?.codigoFicha || ap.fichaId}
                      </td>
                      <td className="py-2.5 px-3 text-center">
                        <div className="flex items-center justify-center space-x-1.5">
                          <div className="w-12 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                            <div 
                              className={`h-1.5 rounded-full ${ap.porcentajeAvance === 100 ? 'bg-[#0D7A53]' : ap.porcentajeAvance < 60 ? 'bg-[#DC2626]' : 'bg-[#D97706]'}`}
                              style={{ width: `${ap.porcentajeAvance}%` }}
                            />
                          </div>
                          <span className="font-tabular font-semibold text-[11px] text-slate-700">{ap.porcentajeAvance}%</span>
                        </div>
                      </td>
                      <td className="py-2.5 px-3 text-center font-tabular font-bold text-[#0A6242]">
                        {ap.rapsAprobados}
                      </td>
                      <td className="py-2.5 px-3 text-center font-tabular font-bold text-[#92400E]">
                        {ap.rapsPendientes}
                      </td>
                      <td className="py-2.5 px-3 text-center font-tabular font-bold text-[#991B1B]">
                        {ap.rapsNoAprobados}
                      </td>
                      <td className="py-2.5 px-3">
                        <span className={`inline-block px-2 py-0.5 text-[10px] font-semibold rounded-[4px] ${
                          ap.estadoFormacion === 'En Formación' ? 'bg-[#E6F4EA] text-[#0A6242] border border-[#A7F3D0]' :
                          ap.estadoFormacion === 'Condicionado' ? 'bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]' :
                          'bg-blue-50 text-blue-700 border border-blue-200'
                        }`}>
                          {ap.estadoFormacion}
                        </span>
                      </td>
                      <td className="py-2.5 px-3 text-right">
                        <button
                          onClick={(e) => {
                            e.stopPropagation();
                            onSelectAprendiz(ap);
                          }}
                          className="px-2 py-1 bg-white border border-[#CBD5E1] hover:border-[#0C2340] text-[#0C2340] rounded-[4px] font-medium text-[11px] inline-flex items-center gap-1 shadow-2xs"
                        >
                          <Eye size={12} /> Detalle
                        </button>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  );
};
