import React, { useState } from 'react';
import { 
  History, 
  RotateCcw, 
  AlertOctagon, 
  CheckCircle2, 
  FileText, 
  Hash, 
  ShieldAlert, 
  Calendar, 
  User, 
  Lock,
  Download
} from 'lucide-react';
import { IngestionBatch } from '../types';

interface BatchHistoryRollbackProps {
  batches: IngestionBatch[];
  onRollbackBatch: (batchId: string, motivo: string) => void;
}

export const BatchHistoryRollback: React.FC<BatchHistoryRollbackProps> = ({
  batches,
  onRollbackBatch,
}) => {
  const [targetRollbackBatch, setTargetRollbackBatch] = useState<IngestionBatch | null>(null);
  const [rollbackReason, setRollbackReason] = useState<string>('');
  const [securityConfirmWord, setSecurityConfirmWord] = useState<string>('');
  const [isReverting, setIsReverting] = useState<boolean>(false);

  const handleConfirmRollback = (e: React.FormEvent) => {
    e.preventDefault();
    if (!targetRollbackBatch || !rollbackReason.trim()) return;

    if (securityConfirmWord.toUpperCase() !== 'ROLLBACK') {
      alert('Por favor escribe la palabra "ROLLBACK" para confirmar la operación atómica.');
      return;
    }

    setIsReverting(true);
    setTimeout(() => {
      onRollbackBatch(targetRollbackBatch.id, rollbackReason);
      setIsReverting(false);
      setTargetRollbackBatch(null);
      setRollbackReason('');
      setSecurityConfirmWord('');
    }, 1000);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white p-5 rounded-[8px] border border-[#E2E8F0] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span className="text-[11px] font-bold text-[#DC2626] uppercase tracking-wider bg-[#FEE2E2] px-2 py-0.5 rounded-[4px]">
            Auditoría de Transacciones Transaccionales
          </span>
          <h2 className="text-xl font-heading font-bold text-[#0C2340] mt-1">
            Historial de Lotes de Ingesta y Reversión Atómica (Rollback)
          </h2>
          <p className="text-xs text-slate-500 mt-1 max-w-2xl">
            Trazabilidad completa e inmutable de cargas masivas de SofiaPlus. Si un lote presenta corrupción o fue cargado con errores en los RAPs, los administradores pueden aplicar reversión atómica de estado.
          </p>
        </div>

        <div className="flex items-center space-x-2 text-xs text-slate-500">
          <Lock size={14} className="text-[#0C2340]" />
          <span>Políticas RBAC: Nivel Auditor SENA</span>
        </div>
      </div>

      {/* Batches Table */}
      <div className="bg-white rounded-[8px] border border-[#E2E8F0] shadow-sm overflow-hidden">
        <div className="bg-[#F1F5F9] px-4 py-3 border-b border-[#E2E8F0] flex items-center justify-between text-xs">
          <span className="font-bold text-slate-700 uppercase tracking-wider">
            Lotes Registrados ({batches.length} transacciones)
          </span>
          <span className="text-slate-500">
            Hash SHA-256 verificado en cada bloque
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-xs text-left">
            <thead className="bg-[#F1F5F9] text-[#475569] uppercase font-bold text-[11px] border-b border-[#E2E8F0]">
              <tr className="h-9">
                <th className="py-2 px-3">Batch ID / Fecha</th>
                <th className="py-2 px-3">Archivo Origen</th>
                <th className="py-2 px-3">Corte / Snapshot</th>
                <th className="py-2 px-3 text-center">Registros</th>
                <th className="py-2 px-3">Responsable</th>
                <th className="py-2 px-3">Estado Lote</th>
                <th className="py-2 px-3 text-right">Acción</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[#E2E8F0]">
              {batches.map((b, index) => {
                const isZebra = index % 2 === 1;
                const isRevoked = b.estado === 'Revocado (Rollback)';

                return (
                  <tr 
                    key={b.id}
                    className={`h-9 hover:bg-[#EDF2F7] transition ${isZebra ? 'bg-[#F8FAFC]' : 'bg-white'} ${isRevoked ? 'opacity-70 bg-rose-50/40' : ''}`}
                  >
                    <td className="py-2 px-3">
                      <div className="font-mono font-bold text-[#0C2340]">
                        {b.batchId}
                      </div>
                      <div className="text-[10px] text-slate-400 font-mono">
                        {b.fechaIngesta}
                      </div>
                    </td>

                    <td className="py-2 px-3">
                      <div className="font-medium text-slate-800 truncate max-w-[200px]" title={b.nombreArchivo}>
                        {b.nombreArchivo}
                      </div>
                      <div className="text-[10px] text-slate-400 font-mono truncate max-w-[180px]">
                        SHA: {b.sha256Hash.substring(0, 16)}...
                      </div>
                    </td>

                    <td className="py-2 px-3 font-medium text-slate-700">
                      {b.corteSnapshot}
                    </td>

                    <td className="py-2 px-3 text-center">
                      <div className="font-tabular font-bold text-slate-800">
                        {b.registrosProcesados}
                      </div>
                      <div className="text-[10px] text-slate-500">
                        {b.registrosValidos} válidos / {b.registrosConError} err
                      </div>
                    </td>

                    <td className="py-2 px-3">
                      <div className="font-medium text-slate-800 truncate max-w-[160px]">
                        {b.usuarioResponsable}
                      </div>
                      <div className="text-[10px] text-slate-400">
                        {b.rolUsuario}
                      </div>
                    </td>

                    <td className="py-2 px-3">
                      {b.estado === 'Committed' && (
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#E6F4EA] text-[#0A6242] border border-[#A7F3D0]">
                          <span className="w-1.5 h-1.5 rounded-full bg-[#0D7A53]"></span> Committed (Activo)
                        </span>
                      )}
                      {b.estado === 'Revocado (Rollback)' && (
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]" title={b.motivoRollback}>
                          <RotateCcw size={11} /> Revocado (Rollback)
                        </span>
                      )}
                    </td>

                    <td className="py-2 px-3 text-right">
                      {b.estado === 'Committed' ? (
                        <button
                          onClick={() => setTargetRollbackBatch(b)}
                          className="px-2.5 py-1 text-xs font-semibold bg-[#FEE2E2] hover:bg-rose-200 text-[#DC2626] border border-[#FECACA] rounded-[4px] transition inline-flex items-center gap-1 shadow-2xs"
                          title="Ejecutar revocación atómica del lote"
                        >
                          <RotateCcw size={12} /> Revocar Lote
                        </button>
                      ) : (
                        <span className="text-[11px] text-slate-400 font-mono">
                          {b.fechaRollback ? b.fechaRollback.split(' ')[0] : 'Inactivo'}
                        </span>
                      )}
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      </div>

      {/* Level 3 Ingestion Dialog & Rollback Scrim */}
      {targetRollbackBatch && (
        <div 
          className="fixed inset-0 z-50 flex items-center justify-center p-4"
          style={{ backgroundColor: 'rgba(8, 22, 40, 0.55)' }}
        >
          <div className="bg-white rounded-[12px] border border-[#CBD5E1] shadow-[0_20px_25px_-5px_rgba(8,22,40,0.15),0_8px_10px_-6px_rgba(8,22,40,0.10)] max-w-md w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            {/* Modal header with Crimson Alert */}
            <div className="bg-[#DC2626] text-white p-4 flex items-center justify-between">
              <div className="flex items-center space-x-2">
                <AlertOctagon size={20} />
                <h3 className="font-heading font-bold text-sm tracking-wide">
                  Confirmar Reversión Atómica (Rollback)
                </h3>
              </div>
              <button
                onClick={() => setTargetRollbackBatch(null)}
                className="text-white hover:text-slate-200 text-lg font-bold"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleConfirmRollback} className="p-5 space-y-4 text-xs">
              <div className="p-3 bg-[#FEE2E2] border border-[#FECACA] rounded-[6px] text-rose-900 space-y-1">
                <div className="font-bold flex items-center gap-1">
                  <ShieldAlert size={14} /> ADVERTENCIA CRÍTICA DE INTEGRIDAD:
                </div>
                <p className="text-[11px]">
                  Esta acción revertirá de forma atómica los <strong className="font-bold">{targetRollbackBatch.registrosProcesados} juicios evaluativos</strong> asentados en el lote <span className="font-mono font-bold">{targetRollbackBatch.batchId}</span>. Los estados de los aprendices volverán al punto de control anterior.
                </p>
              </div>

              {/* Batch info */}
              <div className="bg-[#F8FAFC] p-3 rounded-[6px] border border-[#E2E8F0] space-y-1">
                <div className="flex justify-between">
                  <span className="text-slate-500">Lote:</span>
                  <span className="font-mono font-bold text-slate-800">{targetRollbackBatch.batchId}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-500">Archivo:</span>
                  <span className="font-medium text-slate-800 truncate max-w-[220px]">{targetRollbackBatch.nombreArchivo}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-500">Fecha Ingesta:</span>
                  <span className="font-mono text-slate-800">{targetRollbackBatch.fechaIngesta}</span>
                </div>
              </div>

              {/* Justification input */}
              <div>
                <label className="block text-slate-700 font-semibold mb-1">
                  Motivo o Justificación del Rollback (Requerido para auditoría):
                </label>
                <textarea
                  required
                  rows={3}
                  value={rollbackReason}
                  onChange={(e) => setRollbackReason(e.target.value)}
                  placeholder="Ej: Inconsistencias en los códigos de RAP reportadas por coordinación académica..."
                  className="w-full p-2 border border-[#CBD5E1] rounded-[4px] focus:outline-none focus:ring-1 focus:ring-[#DC2626]"
                />
              </div>

              {/* Security confirmation keyword */}
              <div>
                <label className="block text-slate-700 font-semibold mb-1">
                  Escribe <span className="font-mono font-bold text-[#DC2626]">ROLLBACK</span> para confirmar:
                </label>
                <input
                  type="text"
                  required
                  value={securityConfirmWord}
                  onChange={(e) => setSecurityConfirmWord(e.target.value)}
                  placeholder="ROLLBACK"
                  className="w-full p-2 border border-[#CBD5E1] rounded-[4px] font-mono uppercase focus:outline-none focus:ring-1 focus:ring-[#DC2626]"
                />
              </div>

              {/* Buttons */}
              <div className="flex items-center justify-end space-x-2 pt-2 border-t border-[#E2E8F0]">
                <button
                  type="button"
                  onClick={() => setTargetRollbackBatch(null)}
                  className="px-3 py-1.5 text-slate-600 hover:text-slate-800 border border-slate-300 rounded-[4px]"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  disabled={isReverting || !rollbackReason.trim() || securityConfirmWord.toUpperCase() !== 'ROLLBACK'}
                  className="px-4 py-1.5 bg-[#DC2626] hover:bg-rose-700 text-white font-semibold rounded-[4px] shadow-sm disabled:opacity-50 flex items-center space-x-1.5"
                >
                  <RotateCcw size={13} className={isReverting ? 'animate-spin' : ''} />
                  <span>{isReverting ? 'Revirtiendo lote...' : 'Ejecutar Reversión Atómica'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
