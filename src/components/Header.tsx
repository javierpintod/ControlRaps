import React, { useState } from 'react';
import { 
  BarChart3, 
  UploadCloud, 
  History, 
  Download, 
  RefreshCw, 
  Calendar, 
  LogOut, 
  User, 
  ShieldCheck,
  Lock,
  ChevronDown
} from 'lucide-react';
import { UsuarioSesion } from '../types';

interface HeaderProps {
  currentUser: UsuarioSesion;
  currentPath: string; // '/dashboard' | '/dashboard/upload' | '/dashboard/history'
  onNavigate: (path: string) => void;
  selectedCutoff: string;
  onCutoffChange: (cutoff: string) => void;
  directQueryLatencyMs: number;
  isDirectQueryRefreshing: boolean;
  onRefreshDirectQuery: () => void;
  onSignOut: () => void;
}

export const Header: React.FC<HeaderProps> = ({
  currentUser,
  currentPath,
  onNavigate,
  selectedCutoff,
  onCutoffChange,
  directQueryLatencyMs,
  isDirectQueryRefreshing,
  onRefreshDirectQuery,
  onSignOut,
}) => {
  const [showUserMenu, setShowUserMenu] = useState(false);

  const canAccessUpload = currentUser.rol === 'ADMIN' || currentUser.rol === 'LIDER_FORMACION';

  const navItems = [
    {
      path: '/dashboard',
      label: 'Visor Power BI (DirectQuery)',
      icon: BarChart3,
      allowed: true,
    },
    {
      path: '/dashboard/upload',
      label: 'Cargar Archivo Excel',
      icon: UploadCloud,
      allowed: canAccessUpload,
    },
    {
      path: '/dashboard/history',
      label: 'Auditoría de Lotes',
      icon: History,
      allowed: true,
    },
  ];

  return (
    <header className="bg-[#0C2340] text-white border-b border-[#1B365D] sticky top-0 z-30 shadow-md">
      {/* Top Brand Bar */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between py-3 gap-3 border-b border-[#1B365D]/60">
          {/* Logo & System Title */}
          <div className="flex items-center space-x-3.5">
            <div className="w-10 h-10 bg-white rounded-md p-1.5 flex items-center justify-center shadow-inner flex-shrink-0">
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
                <span className="text-[10px] font-semibold tracking-wider uppercase text-[#0D7A53] bg-[#E6F4EA] px-2 py-0.5 rounded-[4px]">
                  SENA • SofiaPlus DirectQuery
                </span>
                <span className="text-xs text-slate-300 font-medium hidden sm:inline">
                  Plataforma Analítica de Juicios Evaluativos
                </span>
              </div>
              <h1 className="text-lg font-bold tracking-tight text-white flex items-center gap-2">
                Sena Analytics Institutional
              </h1>
            </div>
          </div>

          {/* Quick Controls & User Profile */}
          <div className="flex flex-wrap items-center gap-2 sm:gap-3">
            {/* DirectQuery Live Status Pill */}
            <div 
              className="flex items-center space-x-2 bg-[#081628] border border-[#1B365D] px-2.5 py-1.5 rounded-[4px] text-xs font-medium cursor-pointer hover:bg-[#0c2340] transition"
              title="Monitoreo DirectQuery contra base de datos PostgreSQL"
              onClick={onRefreshDirectQuery}
            >
              <span className="relative flex h-2 w-2">
                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0D7A53] opacity-75"></span>
                <span className="relative inline-flex rounded-full h-2 w-2 bg-[#0D7A53]"></span>
              </span>
              <span className="text-slate-200 text-[11px]">DirectQuery</span>
              <span className="text-[#0D7A53] bg-[#E6F4EA]/15 px-1.5 py-0.5 rounded text-[11px] font-mono">
                {directQueryLatencyMs}ms
              </span>
              <button 
                className={`text-slate-400 hover:text-white transition ${isDirectQueryRefreshing ? 'animate-spin' : ''}`}
                title="Refrescar reporte DirectQuery"
              >
                <RefreshCw size={11} />
              </button>
            </div>

            {/* User Profile & Logout Dropdown */}
            <div className="relative">
              <button
                onClick={() => setShowUserMenu(!showUserMenu)}
                className="flex items-center space-x-2 bg-[#1B365D] hover:bg-slate-700 border border-slate-600 px-2.5 py-1.5 rounded-[4px] text-xs transition"
              >
                <div className="w-5 h-5 rounded-full bg-[#0D7A53] text-white flex items-center justify-center font-bold text-[10px]">
                  {currentUser.nombre[0]}
                </div>
                <div className="text-left hidden sm:block">
                  <span className="font-semibold text-white block text-[11px] truncate max-w-[120px]">{currentUser.nombre.split(' ')[0]}</span>
                </div>
                <span className={`text-[9px] font-bold px-1.5 py-0.2 rounded uppercase ${
                  currentUser.rol === 'ADMIN' ? 'bg-purple-900 text-purple-200' :
                  currentUser.rol === 'LIDER_FORMACION' ? 'bg-emerald-900 text-emerald-200' :
                  'bg-blue-900 text-blue-200'
                }`}>
                  {currentUser.rol}
                </span>
                <ChevronDown size={12} className="text-slate-400" />
              </button>

              {/* User Menu Dropdown */}
              {showUserMenu && (
                <div className="absolute right-0 mt-1.5 w-60 bg-white text-slate-800 rounded-[8px] border border-[#CBD5E1] shadow-xl z-50 py-2 text-xs">
                  <div className="px-3 py-2 border-b border-slate-100">
                    <p className="font-bold text-[#0C2340] truncate">{currentUser.nombre}</p>
                    <p className="text-[11px] text-slate-500 font-mono truncate">{currentUser.email}</p>
                    <p className="text-[10px] text-[#0D7A53] font-semibold mt-1">{currentUser.cargo}</p>
                  </div>

                  <div className="px-3 py-2 text-[11px] text-slate-500 border-b border-slate-100">
                    <span>Permiso RBAC: </span>
                    <strong className="text-slate-700">{currentUser.rol}</strong>
                  </div>

                  <button
                    onClick={() => {
                      setShowUserMenu(false);
                      onSignOut();
                    }}
                    className="w-full px-3 py-2 text-left hover:bg-rose-50 text-rose-700 font-semibold flex items-center space-x-2 transition"
                  >
                    <LogOut size={13} />
                    <span>Cerrar Sesión Segura</span>
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Navigation Tabs Bar */}
        <nav className="flex space-x-2 py-2 overflow-x-auto">
          {navItems.map((item) => {
            const Icon = item.icon;
            const isActive = currentPath === item.path;

            return (
              <button
                key={item.path}
                onClick={() => {
                  if (item.allowed) {
                    onNavigate(item.path);
                  } else {
                    onNavigate(item.path); // header/app will trigger RBAC Toast for forbidden routes
                  }
                }}
                className={`flex items-center space-x-2 px-3 py-1.5 text-xs font-semibold rounded-[4px] whitespace-nowrap transition-colors duration-150 ${
                  isActive
                    ? 'bg-white text-[#0C2340] shadow-sm font-bold border-b-2 border-[#0D7A53]'
                    : item.allowed
                    ? 'text-slate-200 hover:text-white hover:bg-[#1B365D]/60'
                    : 'text-slate-400 opacity-60 hover:text-slate-300'
                }`}
              >
                <Icon size={14} className={isActive ? 'text-[#0D7A53]' : 'text-slate-400'} />
                <span>{item.label}</span>
                {!item.allowed && (
                  <span className="text-[9px] bg-slate-800 text-slate-400 px-1 rounded flex items-center gap-0.5">
                    <Lock size={9} /> Restringido
                  </span>
                )}
              </button>
            );
          })}
        </nav>
      </div>
    </header>
  );
};
