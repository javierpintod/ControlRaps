/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { useState } from 'react';
import { Header } from './components/Header';
import { PowerBiDashboard } from './components/PowerBiDashboard';
import { BatchIngestion } from './components/BatchIngestion';
import { BatchHistoryRollback } from './components/BatchHistoryRollback';
import { LearnerDetailModal } from './components/LearnerDetailModal';
import { LoginView } from './components/LoginView';
import { 
  MOCK_APRENDICES, 
  MOCK_CARGAS, 
  MOCK_PROGRAMAS, 
  MOCK_USUARIOS 
} from './data/mockSenaData';
import { Aprendiz, CargaArchivo, EvaluacionItem, ProgramaFormacion, UsuarioSesion } from './types';
import { ParsedJuicioRowResult } from './utils/excelHelper';
import { CheckCircle2, ShieldAlert, AlertCircle } from 'lucide-react';

export default function App() {
  // Session & Authentication state (starts with Líder de Formación)
  const [currentUser, setCurrentUser] = useState<UsuarioSesion | null>(MOCK_USUARIOS[1]);

  // Routing State ('/dashboard' | '/dashboard/upload' | '/dashboard/history' | '/login')
  const [currentPath, setCurrentPath] = useState<string>('/dashboard');
  const [redirectPathAfterLogin, setRedirectPathAfterLogin] = useState<string>('/dashboard');

  // DirectQuery & Snapshot state
  const [selectedCutoff, setSelectedCutoff] = useState<string>('2026-10-01');
  const [directQueryLatencyMs, setDirectQueryLatencyMs] = useState<number>(142);
  const [isDirectQueryRefreshing, setIsDirectQueryRefreshing] = useState<boolean>(false);

  // Core Data models
  const [aprendices, setAprendices] = useState<Aprendiz[]>(MOCK_APRENDICES);
  const [programas, setProgramas] = useState<ProgramaFormacion[]>(MOCK_PROGRAMAS);
  const [cargas, setCargas] = useState<CargaArchivo[]>(MOCK_CARGAS);

  // Modal / Detail state
  const [selectedLearner, setSelectedLearner] = useState<Aprendiz | null>(null);

  // Toast notification
  const [toastNotification, setToastNotification] = useState<{
    text: string;
    type: 'success' | 'error' | 'info';
  } | null>(null);

  const showToast = (text: string, type: 'success' | 'error' | 'info' = 'success') => {
    setToastNotification({ text, type });
    setTimeout(() => {
      setToastNotification(null);
    }, 5000);
  };

  // Route navigation with Middleware RBAC enforcement (USER_FLOW.md 1.2)
  const handleNavigate = (targetPath: string) => {
    if (!currentUser) {
      setRedirectPathAfterLogin(targetPath);
      setCurrentPath('/login');
      return;
    }

    // RBAC Rule: Instructor cannot access /dashboard/upload
    if (targetPath === '/dashboard/upload' && currentUser.rol === 'INSTRUCTOR') {
      showToast('Acceso denegado: El rol Instructor tiene permisos de Solo Lectura. Se requiere rol de Gestor o Administrador.', 'error');
      setCurrentPath('/dashboard');
      return;
    }

    setCurrentPath(targetPath);
  };

  // Sign out (Paso 6.2)
  const handleSignOut = () => {
    setCurrentUser(null);
    setCurrentPath('/login');
    showToast('Sesión institucional cerrada de forma segura.', 'info');
  };

  // Login handler
  const handleLoginSuccess = (user: UsuarioSesion) => {
    setCurrentUser(user);
    const target = redirectPathAfterLogin && redirectPathAfterLogin !== '/login' ? redirectPathAfterLogin : '/dashboard';
    setCurrentPath(target);
    showToast(`Bienvenido, ${user.nombre}. Sesión iniciada con rol ${user.rol}.`, 'success');
  };

  // DirectQuery refresh trigger
  const handleRefreshDirectQuery = () => {
    setIsDirectQueryRefreshing(true);
    const randomLatency = Math.floor(120 + Math.random() * 90);
    setTimeout(() => {
      setDirectQueryLatencyMs(randomLatency);
      setIsDirectQueryRefreshing(false);
      showToast(`DirectQuery refrescado con éxito (${randomLatency} ms) • Modelo tabular actualizado.`, 'success');
    }, 750);
  };

  // Commit batch ingestion handler (Paso 6)
  const handleCommitBatch = (newCarga: CargaArchivo, parsedRows: ParsedJuicioRowResult[]) => {
    // 1. Prepend to cargas_archivo table
    setCargas(prev => [newCarga, ...prev]);

    // 2. Synchronize learners in PostgreSQL
    setAprendices(prev => {
      const updated = [...prev];

      parsedRows.forEach(r => {
        if (!r.valido || !r.data) return;

        const row = r.data;
        let ap = updated.find(a => a.numero_identificacion === row.numero_identificacion);

        if (ap) {
          // Check existing evaluation
          const existingEval = ap.evaluaciones.find((e: EvaluacionItem) => e.resultado_aprendizaje === row.resultado_aprendizaje);
          if (existingEval) {
            existingEval.juicio = row.juicio;
            existingEval.fecha_evaluacion = row.fecha_evaluacion ? new Date(row.fecha_evaluacion).toISOString().split('T')[0] : new Date().toISOString().split('T')[0];
            existingEval.batch_id = newCarga.batch_id;
          } else {
            ap.evaluaciones.push({
              id: `eval-${Date.now()}-${Math.random()}`,
              batch_id: newCarga.batch_id,
              competencia: row.competencia,
              resultado_aprendizaje: row.resultado_aprendizaje,
              juicio: row.juicio,
              fecha_evaluacion: row.fecha_evaluacion ? new Date(row.fecha_evaluacion).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
              instructor_evaluador: currentUser?.nombre || 'Instructor Evaluador',
            });
          }

          // Recalculate metrics
          ap.total_raps = ap.evaluaciones.length;
          ap.raps_aprobados = ap.evaluaciones.filter((e: EvaluacionItem) => e.juicio === 'APROBADO').length;
          ap.raps_pendientes = ap.evaluaciones.filter((e: EvaluacionItem) => e.juicio === 'POR_EVALUAR').length;
          ap.raps_no_aprobados = ap.evaluaciones.filter((e: EvaluacionItem) => e.juicio === 'NO_APROBADO').length;
          ap.porcentaje_avance = ap.total_raps > 0 ? Math.round((ap.raps_aprobados / ap.total_raps) * 100) : 0;
          ap.estado_academico = ap.raps_no_aprobados > 0 ? 'En Riesgo' : ap.porcentaje_avance === 100 ? 'Por Certificar' : 'Al Día';
        }
      });

      return updated;
    });

    // 3. Auto-select new snapshot date
    setSelectedCutoff(newCarga.fecha_corte);

    // 4. Toast notification matching USER_FLOW.md 6.1
    showToast(`✔ Ingesta finalizada: Se incorporaron exitosamente ${newCarga.registros_exitosos} registros al corte ${newCarga.fecha_corte}.`, 'success');

    // 5. Navigate back to dashboard with updated Power BI DirectQuery
    setCurrentPath('/dashboard');
  };

  // Rollback batch handler
  const handleRollbackBatch = (batchId: string, motivo: string) => {
    setCargas(prev => prev.map(c => {
      if (c.batch_id === batchId) {
        return {
          ...c,
          estado: 'REVOCADO',
          motivo_rollback: motivo,
        };
      }
      return c;
    }));

    showToast(`Reversión atómica completada para el lote ${batchId.substring(0, 18)}...`, 'info');
    handleRefreshDirectQuery();
  };

  // If unauthenticated or path is '/login', render LoginView
  if (!currentUser || currentPath === '/login') {
    return (
      <LoginView
        onLoginSuccess={handleLoginSuccess}
        redirectUrl={redirectPathAfterLogin}
      />
    );
  }

  return (
    <div className="min-h-screen bg-[#F8FAFC] text-[#0F172A] flex flex-col font-sans">
      {/* Toast Notification (Top Right fixed per USER_FLOW.md 6.1) */}
      {toastNotification && (
        <div className={`fixed top-4 right-4 z-50 px-4 py-3 rounded-[6px] shadow-2xl border text-xs flex items-center space-x-2 animate-in slide-in-from-top-4 duration-200 ${
          toastNotification.type === 'error'
            ? 'bg-[#DC2626] text-white border-rose-700'
            : toastNotification.type === 'info'
            ? 'bg-[#0C2340] text-white border-slate-700'
            : 'bg-[#0D7A53] text-white border-emerald-700'
        }`}>
          {toastNotification.type === 'error' ? (
            <ShieldAlert size={16} className="text-white flex-shrink-0" />
          ) : toastNotification.type === 'info' ? (
            <AlertCircle size={16} className="text-amber-300 flex-shrink-0" />
          ) : (
            <CheckCircle2 size={16} className="text-white flex-shrink-0" />
          )}
          <span className="font-medium">{toastNotification.text}</span>
        </div>
      )}

      {/* Institutional Global Header */}
      <Header
        currentUser={currentUser}
        currentPath={currentPath}
        onNavigate={handleNavigate}
        selectedCutoff={selectedCutoff}
        onCutoffChange={setSelectedCutoff}
        directQueryLatencyMs={directQueryLatencyMs}
        isDirectQueryRefreshing={isDirectQueryRefreshing}
        onRefreshDirectQuery={handleRefreshDirectQuery}
        onSignOut={handleSignOut}
      />

      {/* Main Content Viewport */}
      <main className="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {currentPath === '/dashboard' && (
          <PowerBiDashboard
            currentUser={currentUser}
            aprendices={aprendices}
            programas={programas}
            cargas={cargas}
            selectedCutoff={selectedCutoff}
            onCutoffChange={setSelectedCutoff}
            directQueryLatencyMs={directQueryLatencyMs}
            isRefreshing={isDirectQueryRefreshing}
            onRefresh={handleRefreshDirectQuery}
            onSelectAprendiz={setSelectedLearner}
            onNavigateToUpload={() => handleNavigate('/dashboard/upload')}
          />
        )}

        {currentPath === '/dashboard/upload' && (
          <BatchIngestion
            currentUser={currentUser}
            existingCargas={cargas}
            onCommitBatch={handleCommitBatch}
            onCancel={() => handleNavigate('/dashboard')}
            onTriggerPowerBiRefresh={handleRefreshDirectQuery}
          />
        )}

        {currentPath === '/dashboard/history' && (
          <BatchHistoryRollback
            currentUser={currentUser}
            cargas={cargas}
            onRollbackBatch={handleRollbackBatch}
          />
        )}
      </main>

      {/* Learner Detail & Paz y Salvo Modal */}
      {selectedLearner && (
        <LearnerDetailModal
          aprendiz={selectedLearner}
          onClose={() => setSelectedLearner(null)}
        />
      )}

      {/* Institutional Legal & Technical Footer */}
      <footer className="bg-white border-t border-[#E2E8F0] mt-auto py-4 text-xs text-slate-500">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-3">
          <div className="flex items-center space-x-2">
            <span className="font-bold text-[#0C2340]">SENA - SERVICIO NACIONAL DE APRENDIZAJE</span>
            <span>•</span>
            <span>Dirección de Formación Profesional • SofiaPlus DirectQuery</span>
          </div>
          <div className="flex items-center space-x-4 text-[11px]">
            <span>DirectQuery Latencia: <strong className="font-mono text-[#0D7A53]">{directQueryLatencyMs}ms</strong></span>
            <span>•</span>
            <span>Rol Activo: <strong className="text-slate-800">{currentUser.rol}</strong></span>
            <span>•</span>
            <span>TLS 1.3 / RBAC</span>
          </div>
        </div>
      </footer>
    </div>
  );
}
