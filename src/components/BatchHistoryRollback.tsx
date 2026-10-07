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
import { CargaArchivo, UsuarioSesion } from '../types';

interface BatchHistoryRollbackProps {
  currentUser: UsuarioSesion;
  cargas: CargaArchivo[];
  onRollbackBatch: (batchId: string, motivo: string) => void;
}

export const BatchHistoryRollback: React.FC<BatchHistoryRollbackProps> = ({
  currentUser,
  cargas,
  onRollbackBatch,
}) => {
  const [targetRollbackCarga, setTargetRollbackCarga] = useState<CargaArchivo | null>(null);
  const [rollbackReason, setRollbackReason] = useState<string>('');
  const [securityConfirmWord, setSecurityConfirmWord] = useState<string>('');
  const [isReverting, setIsReverting] = useState<boolean>(false);

  const canRollback = currentUser.rol === 'ADMIN' || currentUser.rol === 'LIDER_FORMACION';

  const handleConfirmRollback = (e: React.FormEvent) => {
    e.preventDefault();
    if (!targetRollbackCarga || !rollbackReason.trim()) return;

    if (securityConfirmWord.toUpperCase() !== 'ROLLBACK') {
      alert('Por favor escribe la palabra "ROLLBACK" para confirmar la reversión atómica.');
      return;
    }

    setIsReverting(true);
    setTimeout(() => {
      onRollbackBatch(targetRollbackCarga.batch_id, rollbackReason);
      setIsReverting(false);
      setTargetRollbackCarga(null);
      setRollbackReason('');
      setSecurityConfirmWord('');
    }, 1000);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white p-5 rounded-[8px] border border-[#E2E8F0] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span className="text-[11px] font-bold text-[#0C2340] uppercase tracking-wider bg-[#E8EDF5] px-2 py-0.5 rounded-[4px]">
            Tabla de Auditoría (cargas_archivo)
          </span>
          <h2 className="text-xl font-heading font-bold text-[#0C2340] mt-1">
            Auditoría de Lotes Ingestados y Reversión Atómica
          </h2>
          <p className="text-xs text-slate-500 mt-1 max-w-2xl">
            Trazabilidad histórica de todas las cargas de archivos procesadas en la base de datos PostgreSQL. Los administradores y líderes de formación pueden aplicar reversión atómica si se detectan inconsistencias.
          </p>
        </div>

        <div className="flex items-center space-x-2 text-xs text-slate-500 bg-[#F8FAFC] px-3 py-2 rounded border border-[#E2E8F0]">
          <Lock size={14} className="text-[#0C2340]" />
          <span>Políticas RBAC: {currentUser.rol}</span>
        </div>
      </div>

      {/* Batches Table */}
      <div className="bg-white rounded-[8px] border border-[#E2E8F0] shadow-sm overflow-hidden">
        <div className="bg-[#F1F5F9] px-4 py-3 border-b border-[#E2E8F0] flex items-center justify-between text-xs">
          <span className="font-bold text-slate-700 uppercase tracking-wider">
            Lotes Registrados ({cargas.length} transacciones históricas)
          </span>
          <span className="text-slate-500 font-mono text-[11px]">
            DirectQuery Target: juicios_evaluativos
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-xs text-left">
            <thead className="bg-[#F1F5F9] text-[#475569] uppercase font-bold text-[11px] border-b border-[#E2E8F0]">
              <tr className="h-9">
                <th className="py-2 px-3">Batch ID (UUID)</th>
                <th className="py-2 px-3">Archivo Origen</th>
                <th className="py-2 px-3">Fecha de Corte</th>
                <th className="py-2 px-3 text-center">Registros</th>
                <th className="py-2 px-3">Responsable</th>
                <th className="py-2 px-3">Estado</th>
                <th className="py-2 px-3 text-right">Acción</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[#E2E8F0]">
              {cargas.map((c, index) => {
                const isZebra = index % 2 === 1;
                const isRevoked = c.estado === 'REVOCADO';

                return (
                  <tr 
                    key={c.batch_id}
                    className={`h-9 hover:bg-[#EDF2F7] transition ${isZebra ? 'bg-[#F8FAFC]' : 'bg-white'} ${isRevoked ? 'opacity-70 bg-rose-50/40' : ''}`}
                  >
                    <td className="py-2 px-3">
                      <div className="font-mono font-bold text-[#0C2340]">
                        {c.batch_id.substring(0, 18)}...
                      </div>
                      <div className="text-[10px] text-slate-400 font-mono">
                        {c.created_at}
                      </div>
                    </td>

                    <td className="py-2 px-3">
                      <div className="font-medium text-slate-800 truncate max-w-[200px]" title={c.nombre_archivo}>
                        {c.nombre_archivo}
                      </div>
                      <div className="text-[10px] text-slate-400">
                        {c.metadata?.programas_count || 1} programas • {c.metadata?.aprendices_count || 0} aprendices
                      </div>
                    </td>

                    <td className="py-2 px-3 font-semibold text-slate-700">
                      {c.fecha_corte} {c.metadata?.version_corte ? `(v${c.metadata.version_corte})` : ''}
                    </td>

                    <td className="py-2 px-3 text-center">
                      <div className="font-tabular font-bold text-slate-800">
                        {c.total_registros}
                      </div>
                      <div className="text-[10px] text-slate-500">
                        {c.registros_exitosos} exitosos / {c.inconsistencias} err
                      </div>
                    </td>

                    <td className="py-2 px-3">
                      <div className="font-medium text-slate-800 truncate max-w-[160px]">
                        {c.usuario_nombre}
                      </div>
                      <div className="text-[10px] text-slate-400">
                        Rol: {c.usuario_rol}
                      </div>
                    </td>

                    <td className="py-2 px-3">
                      {c.estado === 'EXITOSO' && (
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#E6F4EA] text-[#0A6242] border border-[#A7F3D0]">
                          <span className="w-1.5 h-1.5 rounded-full bg-[#0D7A53]"></span> Exitoso
                        </span>
                      )}
                      {c.estado === 'REVOCADO' && (
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]" title={c.motivo_rollback}>
                          <RotateCcw size={11} /> Revocado
                        </span>
                      )}
                    </td>

                    <td className="py-2 px-3 text-right">
                      {c.estado === 'EXITOSO' ? (
                        canRollback ? (
                          <button
                            onClick={() => setTargetRollbackCarga(c)}
                            className="px-2.5 py-1 text-xs font-semibold bg-[#FEE2E2] hover:bg-rose-200 text-[#DC2626] border border-[#FECACA] rounded-[4px] transition inline-flex items-center gap-1 shadow-2xs"
                            title="Ejecutar revocación atómica del lote"
                          >
                            <RotateCcw size={12} /> Revocar Lote
                          </button>
                        ) : (
                          <span className="text-[11px] text-slate-400 italic">
                            Solo Admin/Líder
                          </span>
                        )
                      ) : (
                        <span className="text-[11px] text-slate-400 font-mono">
                          Revertido
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

      {/* Level 3 Rollback Confirmation Dialog */}
      {targetRollbackCarga && (
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
                onClick={() => setTargetRollbackCarga(null)}
                className="text-white hover:text-slate-200 text-lg font-bold"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleConfirmRollback} className="p-5 space-y-4 text-xs">
              <div className="p-3 bg-[#FEE2E2] border border-[#FECACA] rounded-[6px] text-rose-900 space-y-1">
                <div className="font-bold flex items-center gap-1">
                  <ShieldAlert size={14} /> TRANSACCIÓN ATÓMICA DE REVOCACIÓN:
                </div>
                <p className="text-[11px]">
                  Esta acción eliminará de forma atómica en cascada los <strong className="font-bold">{targetRollbackCarga.total_registros} juicios evaluativos</strong> asociados al <span className="font-mono font-bold">{targetRollbackCarga.batch_id}</span> para el corte {targetRollbackCarga.fecha_corte}.
                </p>
              </div>

              {/* Batch info */}
              <div className="bg-[#F8FAFC] p-3 rounded-[6px] border border-[#E2E8F0] space-y-1">
                <div className="flex justify-between">
                  <span className="text-slate-500">Lote (batch_id):</span>
                  <span className="font-mono font-bold text-slate-800 text-[11px] truncate max-w-[200px]">{targetRollbackCarga.batch_id}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-500">Archivo:</span>
                  <span className="font-medium text-slate-800 truncate max-w-[200px]">{targetRollbackCarga.nombre_archivo}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-slate-500">Fecha de Corte:</span>
                  <span className="font-semibold text-slate-800">{targetRollbackCarga.fecha_corte}</span>
                </div>
              </div>

              {/* Justification input */}
              <div>
                <label className="block text-slate-700 font-semibold mb-1">
                  Motivo o Justificación del Rollback (Registro de auditoría obligatorio):
                </label>
                <textarea
                  required
                  rows={3}
                  value={rollbackReason}
                  onChange={(e) => setRollbackReason(e.target.value)}
                  placeholder="Detalla la discrepancia observada o solicitud del comité académico..."
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
                  onClick={() => setTargetRollbackCarga(null)}
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
