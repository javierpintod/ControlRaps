import React, { useState } from 'react';
import { 
  Search, 
  Filter, 
  Download, 
  Edit3, 
  CheckCircle2, 
  Clock, 
  AlertCircle, 
  UserCheck, 
  SlidersHorizontal,
  ChevronRight,
  ShieldCheck,
  FileCheck2
} from 'lucide-react';
import { Aprendiz, FichaCaracterizacion, JuicioEstado, RAP } from '../types';
import { exportConsolidatedReportToExcel } from '../utils/excelHelper';

interface JuiciosEvaluativosMatrixProps {
  aprendices: Aprendiz[];
  fichas: FichaCaracterizacion[];
  onUpdateJuicio: (aprendizId: string, rapId: string, nuevoEstado: JuicioEstado, instructor: string, observaciones: string) => void;
  onSelectAprendiz: (aprendiz: Aprendiz) => void;
}

export const JuiciosEvaluativosMatrix: React.FC<JuiciosEvaluativosMatrixProps> = ({
  aprendices,
  fichas,
  onUpdateJuicio,
  onSelectAprendiz,
}) => {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedFicha, setSelectedFicha] = useState('Todas');
  const [selectedEstado, setSelectedEstado] = useState('Todos');
  const [editingItem, setEditingItem] = useState<{
    aprendiz: Aprendiz;
    rap: RAP;
  } | null>(null);

  const [editEstado, setEditEstado] = useState<JuicioEstado>('Aprobado');
  const [editInstructor, setEditInstructor] = useState('Ing. Carlos Alberto Mendoza Silva');
  const [editObservacion, setEditObservacion] = useState('');

  // Flatten apprentice + RAP pairs
  const flatRows: { aprendiz: Aprendiz; rap: RAP }[] = [];
  aprendices.forEach(ap => {
    ap.raps.forEach(rap => {
      flatRows.push({ aprendiz: ap, rap });
    });
  });

  // Filtered rows
  const filteredRows = flatRows.filter(({ aprendiz, rap }) => {
    if (selectedFicha !== 'Todas' && aprendiz.fichaId !== selectedFicha) return false;
    if (selectedEstado !== 'Todos' && rap.estado !== selectedEstado) return false;

    if (searchTerm.trim()) {
      const q = searchTerm.toLowerCase();
      const matchDoc = aprendiz.numeroDocumento.includes(q);
      const matchNombre = `${aprendiz.nombres} ${aprendiz.apellidos}`.toLowerCase().includes(q);
      const matchRap = rap.codigo.toLowerCase().includes(q) || rap.descripcion.toLowerCase().includes(q);
      const matchComp = rap.competenciaNombre.toLowerCase().includes(q);
      return matchDoc || matchNombre || matchRap || matchComp;
    }
    return true;
  });

  const handleOpenEdit = (aprendiz: Aprendiz, rap: RAP) => {
    setEditingItem({ aprendiz, rap });
    setEditEstado(rap.estado);
    setEditInstructor(rap.instructorEvaluador || 'Ing. Carlos Alberto Mendoza Silva');
    setEditObservacion(rap.observaciones || '');
  };

  const handleSaveEdit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingItem) return;

    onUpdateJuicio(
      editingItem.aprendiz.id,
      editingItem.rap.id,
      editEstado,
      editInstructor,
      editObservacion
    );

    setEditingItem(null);
  };

  return (
    <div className="space-y-6">
      {/* Header and Controls */}
      <div className="bg-white p-5 rounded-[8px] border border-[#E2E8F0] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span className="text-[11px] font-bold text-[#0C2340] uppercase tracking-wider bg-[#E8EDF5] px-2 py-0.5 rounded-[4px]">
            Auditoría Pedagógica DirectQuery
          </span>
          <h2 className="text-xl font-heading font-bold text-[#0C2340] mt-1">
            Matriz de Juicios Evaluativos por RAP
          </h2>
          <p className="text-xs text-slate-500 mt-1">
            Inspecciona y actualiza los Resultados de Aprendizaje (RAP) de cada aprendiz según los lineamientos de evaluación SENA.
          </p>
        </div>

        <div className="flex items-center space-x-2">
          <button
            onClick={() => exportConsolidatedReportToExcel(aprendices)}
            className="px-3.5 py-2 bg-[#0D7A53] hover:bg-[#0A6242] text-white rounded-[4px] text-xs font-semibold flex items-center space-x-1.5 transition shadow-sm"
          >
            <Download size={14} />
            <span>Descargar Matriz Excel</span>
          </button>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="bg-white p-4 rounded-[8px] border border-[#E2E8F0] shadow-sm grid grid-cols-1 sm:grid-cols-12 gap-3">
        {/* Search */}
        <div className="sm:col-span-5 relative">
          <Search size={15} className="absolute left-3 top-3 text-slate-400" />
          <input
            type="text"
            placeholder="Buscar por documento, aprendiz, código o descripción de RAP..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="w-full pl-9 pr-3 py-2 text-xs border border-[#CBD5E1] rounded-[4px] focus:outline-none focus:ring-1 focus:ring-[#0C2340] bg-[#F8FAFC]"
          />
        </div>

        {/* Ficha Selector */}
        <div className="sm:col-span-4 flex items-center space-x-2">
          <label className="text-xs text-slate-600 font-medium whitespace-nowrap">Ficha:</label>
          <select
            value={selectedFicha}
            onChange={(e) => setSelectedFicha(e.target.value)}
            className="w-full py-2 px-2.5 text-xs border border-[#CBD5E1] rounded-[4px] focus:outline-none focus:ring-1 focus:ring-[#0C2340] bg-white font-medium"
          >
            <option value="Todas">Todas las Fichas</option>
            {fichas.map(f => (
              <option key={f.id} value={f.id}>
                Ficha {f.codigoFicha} ({f.programaFormacion.substring(0, 24)}...)
              </option>
            ))}
          </select>
        </div>

        {/* Estado Filter */}
        <div className="sm:col-span-3 flex items-center space-x-2">
          <label className="text-xs text-slate-600 font-medium whitespace-nowrap">Estado:</label>
          <select
            value={selectedEstado}
            onChange={(e) => setSelectedEstado(e.target.value)}
            className="w-full py-2 px-2.5 text-xs border border-[#CBD5E1] rounded-[4px] focus:outline-none focus:ring-1 focus:ring-[#0C2340] bg-white font-medium"
          >
            <option value="Todos">Todos los Juicios</option>
            <option value="Aprobado">Aprobado</option>
            <option value="Por Evaluar">Por Evaluar</option>
            <option value="No Aprobado">No Aprobado</option>
          </select>
        </div>
      </div>

      {/* Main Matrix Table */}
      <div className="bg-white rounded-[8px] border border-[#E2E8F0] shadow-sm overflow-hidden">
        <div className="bg-[#F1F5F9] px-4 py-3 border-b border-[#E2E8F0] flex flex-wrap items-center justify-between gap-2 text-xs">
          <span className="font-bold text-slate-700 uppercase tracking-wider">
            Registros Filtrados ({filteredRows.length} juicios de aprendizaje)
          </span>
          <span className="text-slate-500">
            Fila compacta estándar SENA 36px
          </span>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-xs text-left">
            <thead className="bg-[#F1F5F9] text-[#475569] uppercase font-bold text-[11px] border-b border-[#E2E8F0]">
              <tr className="h-9">
                <th className="py-2 px-3">Aprendiz / Documento</th>
                <th className="py-2 px-3">Ficha</th>
                <th className="py-2 px-3">Competencia</th>
                <th className="py-2 px-3">Resultado de Aprendizaje (RAP)</th>
                <th className="py-2 px-3">Juicio Evaluativo</th>
                <th className="py-2 px-3">Evaluador & Fecha</th>
                <th className="py-2 px-3 text-right">Acción</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[#E2E8F0]">
              {filteredRows.length === 0 ? (
                <tr>
                  <td colSpan={7} className="text-center py-8 text-slate-500">
                    No se encontraron registros que coincidan con los filtros aplicados.
                  </td>
                </tr>
              ) : (
                filteredRows.slice(0, 50).map(({ aprendiz, rap }, index) => {
                  const isZebra = index % 2 === 1;
                  const fichaObj = fichas.find(f => f.id === aprendiz.fichaId);

                  return (
                    <tr 
                      key={`${aprendiz.id}-${rap.id}`}
                      className={`h-9 hover:bg-[#EDF2F7] transition ${isZebra ? 'bg-[#F8FAFC]' : 'bg-white'}`}
                    >
                      <td className="py-1.5 px-3">
                        <button
                          onClick={() => onSelectAprendiz(aprendiz)}
                          className="text-left group"
                        >
                          <div className="font-bold text-slate-900 group-hover:text-[#0C2340] group-hover:underline">
                            {aprendiz.nombres} {aprendiz.apellidos}
                          </div>
                          <div className="font-mono text-[10px] text-slate-500">
                            {aprendiz.tipoDocumento} {aprendiz.numeroDocumento}
                          </div>
                        </button>
                      </td>
                      <td className="py-1.5 px-3 font-mono text-slate-700">
                        {fichaObj?.codigoFicha || aprendiz.fichaId}
                      </td>
                      <td className="py-1.5 px-3 max-w-[180px] truncate" title={rap.competenciaNombre}>
                        <span className="font-medium text-slate-800">{rap.competenciaNombre}</span>
                        <div className="text-[10px] text-slate-400 font-mono">{rap.competenciaCodigo}</div>
                      </td>
                      <td className="py-1.5 px-3 max-w-xs">
                        <span className="font-mono font-semibold text-slate-700 mr-1.5">{rap.codigo}</span>
                        <span className="text-slate-600 line-clamp-1 text-[11px]" title={rap.descripcion}>
                          {rap.descripcion}
                        </span>
                      </td>
                      <td className="py-1.5 px-3">
                        {rap.estado === 'Aprobado' && (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#E6F4EA] text-[#0A6242] border border-[#A7F3D0]">
                            <span className="w-1.5 h-1.5 rounded-full bg-[#0D7A53]"></span> Aprobado
                          </span>
                        )}
                        {rap.estado === 'Por Evaluar' && (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#FEF3C7] text-[#92400E] border border-[#FCD34D]">
                            Por Evaluar
                          </span>
                        )}
                        {rap.estado === 'No Aprobado' && (
                          <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-[4px] text-[11px] font-semibold bg-[#FEE2E2] text-[#991B1B] border border-[#FCA5A5]">
                            No Aprobado
                          </span>
                        )}
                      </td>
                      <td className="py-1.5 px-3 text-[11px]">
                        <div className="text-slate-700 font-medium truncate max-w-[150px]">
                          {rap.instructorEvaluador}
                        </div>
                        <div className="text-slate-400 font-mono text-[10px]">
                          {rap.fechaJuicio || 'Pendiente de asentar'}
                        </div>
                      </td>
                      <td className="py-1.5 px-3 text-right">
                        <button
                          onClick={() => handleOpenEdit(aprendiz, rap)}
                          className="px-2 py-1 bg-white hover:bg-slate-100 text-[#0C2340] border border-[#CBD5E1] rounded-[4px] font-semibold text-[11px] inline-flex items-center gap-1 shadow-2xs"
                          title="Asentar o modificar juicio evaluativo"
                        >
                          <Edit3 size={12} /> Modificar
                        </button>
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>
        {filteredRows.length > 50 && (
          <div className="p-2.5 bg-[#F8FAFC] text-center text-xs text-slate-500 border-t border-[#E2E8F0]">
            Mostrando los primeros 50 registros para optimizar renderizado DirectQuery. Usa los filtros para refinar la consulta.
          </div>
        )}
      </div>

      {/* Evaluation Modification Modal (Level 2 dialog) */}
      {editingItem && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#081628]/60 backdrop-blur-xs">
          <div className="bg-white rounded-[12px] border border-[#CBD5E1] shadow-[0_20px_25px_-5px_rgba(8,22,40,0.15)] max-w-lg w-full overflow-hidden">
            <div className="bg-[#0C2340] text-white p-4 flex items-center justify-between">
              <div>
                <span className="text-[10px] font-bold uppercase tracking-wider text-[#0D7A53] bg-[#E6F4EA] px-2 py-0.5 rounded">
                  Juicio Evaluativo SENA
                </span>
                <h3 className="text-sm font-heading font-bold mt-1">
                  Asentar Juicio de RAP
                </h3>
              </div>
              <button
                onClick={() => setEditingItem(null)}
                className="text-slate-300 hover:text-white text-lg font-bold"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleSaveEdit} className="p-5 space-y-4 text-xs">
              {/* Apprentice info */}
              <div className="bg-[#F8FAFC] p-3 rounded-[6px] border border-[#E2E8F0] space-y-1">
                <div className="text-slate-500 font-medium">Aprendiz:</div>
                <div className="text-sm font-bold text-[#0C2340]">
                  {editingItem.aprendiz.nombres} {editingItem.aprendiz.apellidos}
                </div>
                <div className="text-slate-600 font-mono">
                  {editingItem.aprendiz.tipoDocumento}: {editingItem.aprendiz.numeroDocumento}
                </div>
              </div>

              {/* RAP info */}
              <div className="border border-[#E2E8F0] p-3 rounded-[6px] space-y-1">
                <div className="text-slate-500 font-medium">Resultado de Aprendizaje:</div>
                <div className="font-mono font-bold text-slate-800">{editingItem.rap.codigo}</div>
                <div className="text-slate-600">{editingItem.rap.descripcion}</div>
              </div>

              {/* State Selector */}
              <div>
                <label className="block text-slate-700 font-semibold mb-1">
                  Estado del Juicio:
                </label>
                <div className="grid grid-cols-3 gap-2">
                  <button
                    type="button"
                    onClick={() => setEditEstado('Aprobado')}
                    className={`p-2 rounded-[4px] border font-bold text-center transition ${
                      editEstado === 'Aprobado'
                        ? 'bg-[#E6F4EA] text-[#0A6242] border-[#A7F3D0] ring-2 ring-[#0D7A53]'
                        : 'bg-white text-slate-600 border-[#CBD5E1]'
                    }`}
                  >
                    ✓ Aprobado
                  </button>

                  <button
                    type="button"
                    onClick={() => setEditEstado('Por Evaluar')}
                    className={`p-2 rounded-[4px] border font-bold text-center transition ${
                      editEstado === 'Por Evaluar'
                        ? 'bg-[#FEF3C7] text-[#92400E] border-[#FCD34D] ring-2 ring-[#D97706]'
                        : 'bg-white text-slate-600 border-[#CBD5E1]'
                    }`}
                  >
                    ⏱ Por Evaluar
                  </button>

                  <button
                    type="button"
                    onClick={() => setEditEstado('No Aprobado')}
                    className={`p-2 rounded-[4px] border font-bold text-center transition ${
                      editEstado === 'No Aprobado'
                        ? 'bg-[#FEE2E2] text-[#991B1B] border-[#FCA5A5] ring-2 ring-[#DC2626]'
                        : 'bg-white text-slate-600 border-[#CBD5E1]'
                    }`}
                  >
                    ✕ No Aprobado
                  </button>
                </div>
              </div>

              {/* Instructor */}
              <div>
                <label className="block text-slate-700 font-semibold mb-1">
                  Instructor Evaluador Responsable:
                </label>
                <input
                  type="text"
                  value={editInstructor}
                  onChange={(e) => setEditInstructor(e.target.value)}
                  required
                  className="w-full p-2 border border-[#CBD5E1] rounded-[4px] focus:outline-none focus:ring-1 focus:ring-[#0C2340]"
                />
              </div>

              {/* Observaciones */}
              <div>
                <label className="block text-slate-700 font-semibold mb-1">
                  Observaciones Pedagógicas / Justificación:
                </label>
                <textarea
                  rows={3}
                  value={editObservacion}
                  onChange={(e) => setEditObservacion(e.target.value)}
                  placeholder="Detalla evidencias de desempeño, enlace a repositorio o motivo de no aprobación..."
                  className="w-full p-2 border border-[#CBD5E1] rounded-[4px] focus:outline-none focus:ring-1 focus:ring-[#0C2340]"
                />
              </div>

              <div className="flex items-center justify-end space-x-2 pt-2 border-t border-[#E2E8F0]">
                <button
                  type="button"
                  onClick={() => setEditingItem(null)}
                  className="px-3 py-1.5 text-slate-600 hover:text-slate-800 border border-slate-300 rounded-[4px]"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="px-4 py-1.5 bg-[#0D7A53] hover:bg-[#0A6242] text-white font-semibold rounded-[4px] shadow-sm"
                >
                  Guardar Juicio en BD
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
