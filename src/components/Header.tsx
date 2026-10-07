import React from 'react';
import { 
  BarChart3, 
  UploadCloud, 
  FileCheck2, 
  Layers, 
  History, 
  Download, 
  RefreshCw, 
  Calendar, 
  ShieldCheck,
  CheckCircle2,
  Activity
} from 'lucide-react';

interface HeaderProps {
  currentTab: string;
  onTabChange: (tab: string) => void;
  selectedCutoff: string;
  onCutoffChange: (cutoff: string) => void;
  directQueryLatencyMs: number;
  isDirectQueryRefreshing: boolean;
  onRefreshDirectQuery: () => void;
  onExportGlobalReport: () => void;
  totalAprendices: number;
  totalAprobados: number;
}

export const Header: React.FC<HeaderProps> = ({
  currentTab,
  onTabChange,
  selectedCutoff,
  onCutoffChange,
  directQueryLatencyMs,
  isDirectQueryRefreshing,
  onRefreshDirectQuery,
  onExportGlobalReport,
  totalAprendices,
  totalAprobados,
}) => {
  const percentGlobal = totalAprendices > 0 ? Math.round((totalAprobados / (totalAprendices * 8)) * 100) : 88;

  const navItems = [
    { id: 'dashboard', label: 'Dashboard & Power BI DirectQuery', icon: BarChart3 },
    { id: 'ingestion', label: 'Ingesta de Lotes & Pre-validación', icon: UploadCloud },
    { id: 'juicios', label: 'Auditoría de Juicios (RAPs)', icon: FileCheck2 },
    { id: 'fichas', label: 'Fichas de Caracterización', icon: Layers },
    { id: 'lotes', label: 'Historial de Lotes & Rollback', icon: History },
  ];

  return (
    <header className="bg-[#0C2340] text-white border-b border-[#1B365D] sticky top-0 z-30 shadow-md">
      {/* Top Brand Bar */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between py-3 gap-3 border-b border-[#1B365D]/60">
          {/* Logo & Institutional Title */}
          <div className="flex items-center space-x-3.5">
            <div className="w-11 h-11 bg-white rounded-md p-1.5 flex items-center justify-center shadow-inner flex-shrink-0">
              {/* SENA Institutional Emblem Vector */}
              <svg viewBox="0 0 100 100" className="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="48" fill="#0C2340" />
                <path d="M50 18C42 18 36 24 36 32C36 39 42 44 49 45V55H32C26 55 22 59 22 65C22 71 27 75 33 75H49V84H53V75H69C75 75 80 71 80 65C80 59 76 55 70 55H53V45C60 44 66 39 66 32C66 24 60 18 50 18Z" fill="#0D7A53" />
                <circle cx="50" cy="32" r="7" fill="#FFFFFF" />
                <circle cx="34" cy="65" r="5" fill="#FFFFFF" />
                <circle cx="68" cy="65" r="5" fill="#FFFFFF" />
              </svg>
            </div>
            <div>
              <div className="flex items-center space-x-2">
                <span className="text-[11px] font-semibold tracking-wider uppercase text-[#0D7A53] bg-[#E6F4EA] px-2 py-0.5 rounded-[4px]">
                  SENA • SofiaPlus DirectQuery
                </span>
                <span className="text-xs text-slate-300 font-medium hidden sm:inline">
                  Dirección de Formación Profesional
                </span>
              </div>
              <h1 className="text-lg md:text-xl font-bold tracking-tight text-white flex items-center gap-2">
                Sena Analytics Institutional
                <span className="text-xs font-normal text-slate-300 bg-[#1B365D] px-2 py-0.5 rounded-[4px] border border-slate-700">
                  v3.8 Enterprise
                </span>
              </h1>
            </div>
          </div>

          {/* Quick Metrics & Actions */}
          <div className="flex flex-wrap items-center gap-2 sm:gap-3">
            {/* DirectQuery Live Status Pill */}
            <div 
              className="flex items-center space-x-2 bg-[#081628] border border-[#1B365D] px-2.5 py-1.5 rounded-[4px] text-xs font-medium cursor-pointer hover:bg-[#0c2340] transition"
              title="Monitoreo en tiempo real de conexión DirectQuery con almacén SofiaPlus"
              onClick={onRefreshDirectQuery}
            >
              <span className="relative flex h-2.5 w-2.5">
                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0D7A53] opacity-75"></span>
                <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#0D7A53]"></span>
              </span>
              <span className="text-slate-200">
                DirectQuery Activo
              </span>
              <span className="text-[#0D7A53] bg-[#E6F4EA]/15 px-1.5 py-0.5 rounded text-[11px] font-mono">
                {directQueryLatencyMs}ms
              </span>
              <button 
                className={`text-slate-400 hover:text-white transition ${isDirectQueryRefreshing ? 'animate-spin' : ''}`}
                title="Refrescar reporte DirectQuery"
              >
                <RefreshCw size={12} />
              </button>
            </div>

            {/* Snapshot Cut-off Switcher */}
            <div className="flex items-center space-x-1.5 bg-[#1B365D] border border-slate-600 px-2 py-1 rounded-[4px] text-xs">
              <Calendar size={13} className="text-slate-300" />
              <label htmlFor="cutoff-select" className="text-slate-300 text-[11px] hidden sm:inline">Corte:</label>
              <select
                id="cutoff-select"
                value={selectedCutoff}
                onChange={(e) => onCutoffChange(e.target.value)}
                className="bg-transparent text-white font-medium text-xs focus:outline-none cursor-pointer pr-1"
              >
                <option value="En Vivo (DirectQuery)" className="bg-[#0C2340] text-white">En Vivo (DirectQuery)</option>
                <option value="Corte Semanal W40-2026" className="bg-[#0C2340] text-white">Corte Semanal W40-2026</option>
                <option value="Corte Quincenal Q2-Sep" className="bg-[#0C2340] text-white">Corte Quincenal Q2-Sep</option>
                <option value="Corte Mensual Sep-2026" className="bg-[#0C2340] text-white">Corte Mensual Sep-2026</option>
                <option value="Consolidado Q3 2026" className="bg-[#0C2340] text-white">Consolidado Q3 2026</option>
              </select>
            </div>

            {/* Export Global Excel Button */}
            <button
              onClick={onExportGlobalReport}
              className="flex items-center space-x-1.5 bg-[#0D7A53] hover:bg-[#0A6242] text-white text-xs font-semibold px-3 py-1.5 rounded-[4px] transition shadow-sm"
              title="Descargar matriz consolidada de aprendices y juicios en formato Excel (.xlsx)"
            >
              <Download size={13} />
              <span className="hidden sm:inline">Exportar Matriz</span>
            </button>
          </div>
        </div>

        {/* Navigation Tabs Bar */}
        <nav className="flex space-x-1 sm:space-x-2 overflow-x-auto py-2 no-scrollbar">
          {navItems.map((item) => {
            const Icon = item.icon;
            const isActive = currentTab === item.id;
            return (
              <button
                key={item.id}
                onClick={() => onTabChange(item.id)}
                className={`flex items-center space-x-2 px-3 py-2 text-xs sm:text-sm font-semibold rounded-[4px] whitespace-nowrap transition-colors duration-150 ${
                  isActive
                    ? 'bg-white text-[#0C2340] shadow-sm font-bold border-b-2 border-[#0D7A53]'
                    : 'text-slate-200 hover:text-white hover:bg-[#1B365D]/60'
                }`}
              >
                <Icon size={15} className={isActive ? 'text-[#0D7A53]' : 'text-slate-400'} />
                <span>{item.label}</span>
              </button>
            );
          })}
        </nav>
      </div>
    </header>
  );
};
