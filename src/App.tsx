/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { useState } from 'react';
import { Header } from './components/Header';
import { PowerBiDashboard } from './components/PowerBiDashboard';
import { BatchIngestion } from './components/BatchIngestion';
import { JuiciosEvaluativosMatrix } from './components/JuiciosEvaluativosMatrix';
import { FichasManagement } from './components/FichasManagement';
import { BatchHistoryRollback } from './components/BatchHistoryRollback';
import { LearnerDetailModal } from './components/LearnerDetailModal';
import { 
  MOCK_APRENDICES, 
  MOCK_BATCHES, 
  MOCK_FICHAS 
} from './data/mockSenaData';
import { Aprendiz, FichaCaracterizacion, IngestionBatch, JuicioEstado } from './types';
import { ParsedJuicioRow, exportConsolidatedReportToExcel } from './utils/excelHelper';
import { CheckCircle2, Shield, Info } from 'lucide-react';

export default function App() {
  const [currentTab, setCurrentTab] = useState<string>('dashboard');
  const [selectedCutoff, setSelectedCutoff] = useState<string>('En Vivo (DirectQuery)');
  const [directQueryLatencyMs, setDirectQueryLatencyMs] = useState<number>(142);
  const [isDirectQueryRefreshing, setIsDirectQueryRefreshing] = useState<boolean>(false);

  // Core reactive data state
  const [aprendices, setAprendices] = useState<Aprendiz[]>(MOCK_APRENDICES);
  const [fichas, setFichas] = useState<FichaCaracterizacion[]>(MOCK_FICHAS);
  const [batches, setBatches] = useState<IngestionBatch[]>(MOCK_BATCHES);

  // Selected learner for detail inspection
  const [selectedLearner, setSelectedLearner] = useState<Aprendiz | null>(null);

  // Toast notification state
  const [toastMessage, setToastMessage] = useState<string | null>(null);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => {
      setToastMessage(null);
    }, 4000);
  };

  // DirectQuery refresh simulation
  const handleRefreshDirectQuery = () => {
    setIsDirectQueryRefreshing(true);
    const randomLatency = Math.floor(120 + Math.random() * 95);
    setTimeout(() => {
      setDirectQueryLatencyMs(randomLatency);
      setIsDirectQueryRefreshing(false);
      showToast(`DirectQuery refrescado con éxito (${randomLatency} ms) • Modelo tabular actualizado`);
    }, 900);
  };

  // Batch commit handler
  const handleCommitBatch = (newBatch: IngestionBatch, parsedRows: ParsedJuicioRow[]) => {
    setBatches(prev => [newBatch, ...prev]);

    // Integrate or update records from the batch
    setAprendices(prev => {
      const updated = [...prev];

      parsedRows.forEach(row => {
        if (!row.valido) return;

        let ap = updated.find(a => a.numeroDocumento === row.numeroDocumento);
        if (ap) {
          // Find or add RAP
          const existingRap = ap.raps.find(r => r.codigo === row.rapCodigo);
          if (existingRap) {
            existingRap.estado = row.juicioEvaluativo as JuicioEstado;
            existingRap.instructorEvaluador = row.instructorEvaluador;
            existingRap.fechaJuicio = row.fechaJuicio;
          } else {
            ap.raps.push({
              id: `rap-new-${Date.now()}-${Math.random()}`,
              codigo: row.rapCodigo,
              descripcion: row.rapDescripcion,
              competenciaCodigo: row.competenciaCodigo,
              competenciaNombre: 'Competencia Técnica Específica',
              estado: row.juicioEvaluativo as JuicioEstado,
              instructorEvaluador: row.instructorEvaluador,
              fechaJuicio: row.fechaJuicio,
            });
          }

          // Recalculate metrics
          ap.rapsTotales = ap.raps.length;
          ap.rapsAprobados = ap.raps.filter(r => r.estado === 'Aprobado').length;
          ap.rapsPendientes = ap.raps.filter(r => r.estado === 'Por Evaluar').length;
          ap.rapsNoAprobados = ap.raps.filter(r => r.estado === 'No Aprobado').length;
          ap.porcentajeAvance = ap.rapsTotales > 0 ? Math.round((ap.rapsAprobados / ap.rapsTotales) * 100) : 0;
        }
      });

      return updated;
    });

    showToast(`Lote ${newBatch.batchId} ingestado exitosamente (${newBatch.registrosProcesados} registros).`);
  };

  // Rollback batch handler
  const handleRollbackBatch = (batchId: string, motivo: string) => {
    setBatches(prev => prev.map(b => {
      if (b.id === batchId) {
        return {
          ...b,
          estado: 'Revocado (Rollback)',
          motivoRollback: motivo,
          fechaRollback: new Date().toISOString().replace(/T/, ' ').replace(/\..+/, ''),
        };
      }
      return b;
    }));

    showToast(`Reversión atómica completada para el lote ${batchId}.`);
  };

  // Single RAP judgment update handler
  const handleUpdateJuicio = (
    aprendizId: string,
    rapId: string,
    nuevoEstado: JuicioEstado,
    instructor: string,
    observaciones: string
  ) => {
    setAprendices(prev => prev.map(ap => {
      if (ap.id === aprendizId) {
        const updatedRaps = ap.raps.map(r => {
          if (r.id === rapId) {
            return {
              ...r,
              estado: nuevoEstado,
              instructorEvaluador: instructor,
              fechaJuicio: new Date().toISOString().split('T')[0],
              observaciones,
            };
          }
          return r;
        });

        const aprobados = updatedRaps.filter(r => r.estado === 'Aprobado').length;
        const pendientes = updatedRaps.filter(r => r.estado === 'Por Evaluar').length;
        const noAprobados = updatedRaps.filter(r => r.estado === 'No Aprobado').length;
        const total = updatedRaps.length;

        return {
          ...ap,
          raps: updatedRaps,
          rapsAprobados: aprobados,
          rapsPendientes: pendientes,
          rapsNoAprobados: noAprobados,
          porcentajeAvance: total > 0 ? Math.round((aprobados / total) * 100) : 0,
        };
      }
      return ap;
    }));

    showToast(`Juicio evaluativo asentado como "${nuevoEstado}" satisfactoriamente.`);
  };

  // Global Excel Export
  const handleExportGlobalReport = () => {
    exportConsolidatedReportToExcel(aprendices);
    showToast('Archivo Excel (.xlsx) generado y descargado exitosamente.');
  };

  // Quick navigation from fichas to matrix
  const handleFilterFichaInMatrix = (fichaId: string) => {
    setCurrentTab('juicios');
  };

  // Calculate totals for header
  const totalAprendices = aprendices.length;
  const totalAprobados = aprendices.reduce((acc, a) => acc + a.rapsAprobados, 0);

  return (
    <div className="min-h-screen bg-[#F8FAFC] text-[#0F172A] flex flex-col font-sans">
      {/* Toast Notification */}
      {toastMessage && (
        <div className="fixed bottom-5 right-5 z-50 bg-[#0C2340] text-white px-4 py-3 rounded-[6px] shadow-lg border border-[#1B365D] flex items-center space-x-2 text-xs animate-in slide-in-from-bottom-5 duration-200">
          <CheckCircle2 size={16} className="text-[#0D7A53] flex-shrink-0" />
          <span>{toastMessage}</span>
        </div>
      )}

      {/* Institutional Global Header */}
      <Header
        currentTab={currentTab}
        onTabChange={setCurrentTab}
        selectedCutoff={selectedCutoff}
        onCutoffChange={setSelectedCutoff}
        directQueryLatencyMs={directQueryLatencyMs}
        isDirectQueryRefreshing={isDirectQueryRefreshing}
        onRefreshDirectQuery={handleRefreshDirectQuery}
        onExportGlobalReport={handleExportGlobalReport}
        totalAprendices={totalAprendices}
        totalAprobados={totalAprobados}
      />

      {/* Main Content Viewport */}
      <main className="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {currentTab === 'dashboard' && (
          <PowerBiDashboard
            aprendices={aprendices}
            fichas={fichas}
            selectedCutoff={selectedCutoff}
            onCutoffChange={setSelectedCutoff}
            directQueryLatencyMs={directQueryLatencyMs}
            isRefreshing={isDirectQueryRefreshing}
            onRefresh={handleRefreshDirectQuery}
            onSelectAprendiz={setSelectedLearner}
            onNavigateToIngestion={() => setCurrentTab('ingestion')}
            onNavigateToJuicios={() => setCurrentTab('juicios')}
          />
        )}

        {currentTab === 'ingestion' && (
          <BatchIngestion
            onCommitBatch={handleCommitBatch}
            selectedCutoff={selectedCutoff}
          />
        )}

        {currentTab === 'juicios' && (
          <JuiciosEvaluativosMatrix
            aprendices={aprendices}
            fichas={fichas}
            onUpdateJuicio={handleUpdateJuicio}
            onSelectAprendiz={setSelectedLearner}
          />
        )}

        {currentTab === 'fichas' && (
          <FichasManagement
            fichas={fichas}
            aprendices={aprendices}
            onSelectAprendiz={setSelectedLearner}
            onFilterFichaInMatrix={handleFilterFichaInMatrix}
          />
        )}

        {currentTab === 'lotes' && (
          <BatchHistoryRollback
            batches={batches}
            onRollbackBatch={handleRollbackBatch}
          />
        )}
      </main>

      {/* Learner Detail & Paz y Salvo Modal */}
      {selectedLearner && (
        <LearnerDetailModal
          aprendiz={selectedLearner}
          ficha={fichas.find(f => f.id === selectedLearner.fichaId)}
          onClose={() => setSelectedLearner(null)}
        />
      )}

      {/* Institutional Legal & Technical Footer */}
      <footer className="bg-white border-t border-[#E2E8F0] mt-auto py-5 text-xs text-slate-500">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-3">
          <div className="flex items-center space-x-2">
            <span className="font-bold text-[#0C2340]">SENA - SERVICIO NACIONAL DE APRENDIZAJE</span>
            <span>•</span>
            <span>Dirección General - Sistema Integrado de Gestión Académica SofiaPlus</span>
          </div>
          <div className="flex items-center space-x-4 text-[11px]">
            <span>DirectQuery Latencia: <strong className="font-mono text-[#0D7A53]">{directQueryLatencyMs}ms</strong></span>
            <span>•</span>
            <span>Seguridad: TLS 1.3 / RBAC Auditor</span>
            <span>•</span>
            <span>Colombia 2026</span>
          </div>
        </div>
      </footer>
    </div>
  );
}
