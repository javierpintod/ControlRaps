import React from 'react';
import { 
  X, 
  User, 
  CheckCircle2, 
  AlertTriangle, 
  FileCheck2, 
  Printer, 
  ShieldCheck, 
  Mail, 
  Phone,
  GraduationCap
} from 'lucide-react';
import { Aprendiz } from '../types';

interface LearnerDetailModalProps {
  aprendiz: Aprendiz | null;
  onClose: () => void;
}

export const LearnerDetailModal: React.FC<LearnerDetailModalProps> = ({
  aprendiz,
  onClose,
}) => {
  if (!aprendiz) return null;

  const isPazYSalvo = aprendiz.raps_aprobados === aprendiz.total_raps && aprendiz.total_raps > 0;

  const handlePrint = () => {
    window.print();
  };

  return (
    <div 
      className="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
      style={{ backgroundColor: 'rgba(8, 22, 40, 0.65)' }}
    >
      <div className="bg-white rounded-[12px] border border-[#CBD5E1] shadow-[0_20px_25px_-5px_rgba(8,22,40,0.2)] max-w-3xl w-full overflow-hidden max-h-[90vh] flex flex-col">
        {/* Header */}
        <div className="bg-[#0C2340] text-white p-4 sm:p-5 flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="w-10 h-10 rounded-full bg-white text-[#0C2340] flex items-center justify-center font-bold text-sm">
              {aprendiz.nombre_completo.split(' ')[0][0]}
              {aprendiz.nombre_completo.split(' ')[1]?.[0] || ''}
            </div>
            <div>
              <span className="text-[10px] font-bold uppercase tracking-wider text-[#0D7A53] bg-[#E6F4EA] px-2 py-0.5 rounded">
                Expediente del Aprendiz • SofiaPlus
              </span>
              <h3 className="text-base font-heading font-bold mt-1">
                {aprendiz.nombre_completo}
              </h3>
            </div>
          </div>

          <div className="flex items-center space-x-2">
            <button
              onClick={handlePrint}
              className="p-1.5 text-slate-300 hover:text-white hover:bg-slate-700 rounded-[4px] transition"
              title="Imprimir Paz y Salvo Académico"
            >
              <Printer size={16} />
            </button>
            <button
              onClick={onClose}
              className="text-slate-300 hover:text-white text-xl font-bold p-1"
            >
              ✕
            </button>
          </div>
        </div>

        {/* Content body */}
        <div className="p-5 overflow-y-auto space-y-5 text-xs">
          {/* Top info strip */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-[#F8FAFC] p-3.5 rounded-[8px] border border-[#E2E8F0]">
            <div>
              <span className="text-slate-400 block text-[10px]">Identificación:</span>
              <span className="font-mono font-bold text-slate-800 text-xs">
                {aprendiz.tipo_identificacion} {aprendiz.numero_identificacion}
              </span>
            </div>
            <div>
              <span className="text-slate-400 block text-[10px]">Correo Electrónico:</span>
              <span className="text-slate-800 block truncate">{aprendiz.email || 'Sin correo registrado'}</span>
            </div>
            <div>
              <span className="text-slate-400 block text-[10px]">Programa de Formación:</span>
              <span className="font-bold text-[#0C2340] block truncate">
                {aprendiz.codigo_programa} - {aprendiz.nombre_programa}
              </span>
            </div>
          </div>

          {/* Academic Peace and Safety Certificate Banner (Paz y Salvo) */}
          <div className={`p-4 rounded-[8px] border flex flex-col sm:flex-row sm:items-center justify-between gap-3 ${
            isPazYSalvo
              ? 'bg-[#E6F4EA] border-[#A7F3D0] text-[#0A6242]'
              : 'bg-[#FEF3C7] border-[#FCD34D] text-[#92400E]'
          }`}>
            <div className="flex items-start space-x-2.5">
              {isPazYSalvo ? (
                <ShieldCheck size={24} className="text-[#0D7A53] flex-shrink-0 mt-0.5" />
              ) : (
                <AlertTriangle size={24} className="text-[#D97706] flex-shrink-0 mt-0.5" />
              )}
              <div>
                <h4 className="font-bold text-sm">
                  {isPazYSalvo ? 'Concepto Pedagógico: Paz y Salvo Académico Aprobado' : 'Concepto Pedagógico: RAPs Pendientes de Evaluación'}
                </h4>
                <p className="text-[11px] mt-0.5 opacity-90">
                  {isPazYSalvo 
                    ? 'El aprendiz ha superado el 100% de los Resultados de Aprendizaje requeridos por la estructura curricular SENA. Habilitado para etapa productiva y certificación.'
                    : `El aprendiz registra ${aprendiz.raps_pendientes} RAPs pendientes y ${aprendiz.raps_no_aprobados} no aprobados. Debe concertar plan de mejoramiento con los instructores.`}
                </p>
              </div>
            </div>

            <div className="text-right flex-shrink-0">
              <span className="text-lg font-bold font-tabular">
                {aprendiz.raps_aprobados} / {aprendiz.total_raps}
              </span>
              <span className="block text-[10px] uppercase font-semibold">
                RAPs Superados ({aprendiz.porcentaje_avance}%)
              </span>
            </div>
          </div>

          {/* Evaluations / RAPs Table */}
          <div>
            <h4 className="font-heading font-bold text-xs uppercase tracking-wider text-[#0C2340] mb-2">
              Hoja de Registro de Juicios Evaluativos en PostgreSQL
            </h4>

            <div className="border border-[#E2E8F0] rounded-[6px] overflow-hidden">
              <table className="w-full text-xs text-left">
                <thead className="bg-[#F1F5F9] text-[#475569] uppercase font-bold text-[11px] border-b border-[#E2E8F0]">
                  <tr className="h-8">
                    <th className="py-2 px-3">Competencia</th>
                    <th className="py-2 px-3">Resultado de Aprendizaje</th>
                    <th className="py-2 px-3 text-center">Juicio</th>
                    <th className="py-2 px-3">Fecha / Evaluador</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-[#E2E8F0]">
                  {aprendiz.evaluaciones.map((item, idx) => {
                    const isZebra = idx % 2 === 1;
                    return (
                      <tr key={item.id} className={isZebra ? 'bg-[#F8FAFC]' : 'bg-white'}>
                        <td className="py-2 px-3 font-semibold text-slate-800 align-top max-w-[200px]">
                          {item.competencia}
                        </td>
                        <td className="py-2 px-3 align-top text-slate-600">
                          {item.resultado_aprendizaje}
                        </td>
                        <td className="py-2 px-3 text-center align-top">
                          {item.juicio === 'APROBADO' && (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#E6F4EA] text-[#0A6242] border border-[#A7F3D0]">
                              <span className="w-1.5 h-1.5 rounded-full bg-[#0D7A53]"></span> Aprobado
                            </span>
                          )}
                          {item.juicio === 'POR_EVALUAR' && (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]">
                              Por Evaluar
                            </span>
                          )}
                          {item.juicio === 'NO_APROBADO' && (
                            <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]">
                              No Aprobado
                            </span>
                          )}
                        </td>
                        <td className="py-2 px-3 align-top">
                          <div className="font-medium text-slate-800">{item.instructor_evaluador || 'Instructor Evaluador'}</div>
                          <div className="text-[10px] text-slate-400 font-mono">
                            {item.fecha_evaluacion || 'Pendiente'}
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        </div>

        {/* Footer */}
        <div className="bg-[#F8FAFC] p-4 border-t border-[#E2E8F0] flex items-center justify-between">
          <span className="text-[11px] text-slate-500">
            Registro Oficial SENA - Ley 119 de 1994
          </span>
          <button
            onClick={onClose}
            className="px-4 py-1.5 bg-[#0C2340] hover:bg-[#1B365D] text-white font-semibold rounded-[4px] text-xs transition"
          >
            Cerrar Expediente
          </button>
        </div>
      </div>
    </div>
  );
};
