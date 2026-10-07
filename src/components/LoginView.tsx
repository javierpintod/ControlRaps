import React, { useState } from 'react';
import { ShieldCheck, Lock, Mail, ArrowRight, UserCheck, CheckCircle2, AlertCircle } from 'lucide-react';
import { MOCK_USUARIOS } from '../data/mockSenaData';
import { UsuarioSesion } from '../types';

interface LoginViewProps {
  onLoginSuccess: (user: UsuarioSesion) => void;
  redirectUrl?: string;
}

export const LoginView: React.FC<LoginViewProps> = ({ onLoginSuccess, redirectUrl }) => {
  const [selectedUserEmail, setSelectedUserEmail] = useState<string>('gestor@sena.edu.co');
  const [password, setPassword] = useState<string>('********');
  const [loading, setLoading] = useState<boolean>(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setErrorMsg(null);

    setTimeout(() => {
      const user = MOCK_USUARIOS.find(u => u.email === selectedUserEmail);
      if (user) {
        onLoginSuccess(user);
      } else {
        setErrorMsg('Credenciales inválidas. Por favor verifica tu correo institucional.');
        setLoading(false);
      }
    }, 600);
  };

  const handleQuickSelect = (email: string) => {
    setSelectedUserEmail(email);
    const user = MOCK_USUARIOS.find(u => u.email === email);
    if (user) {
      setLoading(true);
      setTimeout(() => {
        onLoginSuccess(user);
      }, 400);
    }
  };

  return (
    <div className="min-h-screen bg-[#081628] flex flex-col justify-center items-center p-4 sm:p-6 text-slate-100">
      {/* Background Graphic Accent */}
      <div className="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
        <div className="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-[#0D7A53] blur-3xl"></div>
        <div className="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-[#1B365D] blur-3xl"></div>
      </div>

      <div className="max-w-md w-full relative z-10 space-y-6">
        {/* Brand Header */}
        <div className="text-center space-y-3">
          <div className="inline-flex w-16 h-16 bg-white rounded-xl p-2.5 items-center justify-center shadow-xl border border-slate-700 mx-auto">
            <svg viewBox="0 0 100 100" className="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
              <circle cx="50" cy="50" r="48" fill="#0C2340" />
              <path d="M50 18C42 18 36 24 36 32C36 39 42 44 49 45V55H32C26 55 22 59 22 65C22 71 27 75 33 75H49V84H53V75H69C75 75 80 71 80 65C80 59 76 55 70 55H53V45C60 44 66 39 66 32C66 24 60 18 50 18Z" fill="#0D7A53" />
              <circle cx="50" cy="32" r="7" fill="#FFFFFF" />
              <circle cx="34" cy="65" r="5" fill="#FFFFFF" />
              <circle cx="68" cy="65" r="5" fill="#FFFFFF" />
            </svg>
          </div>

          <div>
            <span className="text-[11px] font-bold tracking-widest text-[#0D7A53] uppercase bg-[#E6F4EA]/10 border border-[#0D7A53]/30 px-3 py-1 rounded-full">
              SENA • Sistema Integrado SofiaPlus
            </span>
            <h1 className="text-2xl font-bold font-heading text-white mt-2">
              Plataforma Analítica de Juicios Evaluativos
            </h1>
            <p className="text-xs text-slate-400 mt-1">
              Ingesta estructurada ETL, auditoría RBAC y analítica DirectQuery en Power BI Embedded
            </p>
          </div>
        </div>

        {/* Login Card */}
        <div className="bg-[#0C2340] border border-[#1B365D] rounded-[12px] p-6 shadow-2xl space-y-5">
          {redirectUrl && (
            <div className="p-3 bg-amber-500/10 border border-amber-500/30 rounded-[6px] text-xs text-amber-300 flex items-center gap-2">
              <AlertCircle size={15} />
              <span>Inicia sesión para acceder a la ruta protegida solicitada.</span>
            </div>
          )}

          {errorMsg && (
            <div className="p-3 bg-rose-500/10 border border-rose-500/30 rounded-[6px] text-xs text-rose-300 flex items-center gap-2">
              <AlertCircle size={15} />
              <span>{errorMsg}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4 text-xs">
            <div>
              <label className="block text-slate-300 font-semibold mb-1.5">
                Correo Electrónico Institucional
              </label>
              <div className="relative">
                <Mail size={15} className="absolute left-3 top-3 text-slate-400" />
                <input
                  type="email"
                  required
                  value={selectedUserEmail}
                  onChange={(e) => setSelectedUserEmail(e.target.value)}
                  placeholder="usuario@sena.edu.co"
                  className="w-full bg-[#081628] border border-slate-700 text-white pl-9 pr-3 py-2.5 rounded-[6px] focus:outline-none focus:ring-1 focus:ring-[#0D7A53] focus:border-[#0D7A53]"
                />
              </div>
            </div>

            <div>
              <label className="block text-slate-300 font-semibold mb-1.5">
                Contraseña de Acceso
              </label>
              <div className="relative">
                <Lock size={15} className="absolute left-3 top-3 text-slate-400" />
                <input
                  type="password"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full bg-[#081628] border border-slate-700 text-white pl-9 pr-3 py-2.5 rounded-[6px] focus:outline-none focus:ring-1 focus:ring-[#0D7A53] focus:border-[#0D7A53]"
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full py-2.5 bg-[#0D7A53] hover:bg-[#0A6242] text-white font-bold rounded-[6px] transition flex items-center justify-center space-x-2 shadow-lg disabled:opacity-50"
            >
              {loading ? (
                <div className="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
              ) : (
                <>
                  <span>Ingresar a la Plataforma</span>
                  <ArrowRight size={14} />
                </>
              )}
            </button>
          </form>

          {/* Quick RBAC Role Selectors (for demo / testing flow) */}
          <div className="pt-4 border-t border-slate-800 space-y-2.5">
            <span className="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
              Acceso Rápido por Perfil (Matriz RBAC):
            </span>

            <div className="space-y-1.5">
              {MOCK_USUARIOS.map((u) => (
                <button
                  key={u.id}
                  onClick={() => handleQuickSelect(u.email)}
                  className={`w-full p-2.5 rounded-[6px] text-left border transition flex items-center justify-between text-xs ${
                    selectedUserEmail === u.email 
                      ? 'bg-[#1B365D] border-[#0D7A53] text-white' 
                      : 'bg-[#081628]/60 border-slate-800 text-slate-300 hover:bg-[#081628] hover:border-slate-700'
                  }`}
                >
                  <div>
                    <div className="font-semibold text-white flex items-center gap-1.5">
                      <span>{u.nombre}</span>
                      <span className={`text-[10px] font-bold px-1.5 py-0.2 rounded uppercase ${
                        u.rol === 'ADMIN' ? 'bg-purple-900/60 text-purple-300 border border-purple-700' :
                        u.rol === 'LIDER_FORMACION' ? 'bg-emerald-900/60 text-emerald-300 border border-emerald-700' :
                        'bg-blue-900/60 text-blue-300 border border-blue-700'
                      }`}>
                        {u.rol}
                      </span>
                    </div>
                    <div className="text-[11px] text-slate-400 truncate max-w-xs">{u.cargo}</div>
                  </div>
                  <ArrowRight size={13} className="text-slate-400" />
                </button>
              ))}
            </div>
          </div>
        </div>

        {/* Security badge footer */}
        <div className="text-center text-[11px] text-slate-500 flex items-center justify-center gap-2">
          <ShieldCheck size={14} className="text-[#0D7A53]" />
          <span>Autenticación Cifrada TLS 1.3 • Cookies HttpOnly de Sesión</span>
        </div>
      </div>
    </div>
  );
};
