import React, { useState } from 'react';
import { 
  Layers, 
  MapPin, 
  User, 
  Users, 
  Calendar, 
  CheckCircle, 
  AlertTriangle, 
  Clock, 
  ArrowRight,
  PlusCircle,
  Building,
  GraduationCap
} from 'lucide-react';
import { Aprendiz, FichaCaracterizacion } from '../types';

interface FichasManagementProps {
  fichas: FichaCaracterizacion[];
  aprendices: Aprendiz[];
  onSelectAprendiz: (aprendiz: Aprendiz) => void;
  onFilterFichaInMatrix: (fichaId: string) => void;
}

export const FichasManagement: React.FC<FichasManagementProps> = ({
  fichas,
  aprendices,
  onSelectAprendiz,
  onFilterFichaInMatrix,
}) => {
  const [selectedRegional, setSelectedRegional] = useState('Todas');
  const [selectedNivel, setSelectedNivel] = useState('Todos');
  const [activeFichaDetail, setActiveFichaDetail] = useState<FichaCaracterizacion | null>(null);

  const filteredFichas = fichas.filter(f => {
    if (selectedRegional !== 'Todas' && f.regional !== selectedRegional) return false;
    if (selectedNivel !== 'Todos' && f.nivelFormacion !== selectedNivel) return false;
    return true;
  });

  const getAprendicesForFicha = (fichaId: string) => {
    return aprendices.filter(a => a.fichaId === fichaId);
  };

  return (
    <div className="space-y-6">
      {/* Title Header */}
      <div className="bg-white p-5 rounded-[8px] border border-[#E2E8F0] shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <span className="text-[11px] font-bold text-[#0C2340] uppercase tracking-wider bg-[#E8EDF5] px-2 py-0.5 rounded-[4px]">
            Estructura Curricular
          </span>
          <h2 className="text-xl font-heading font-bold text-[#0C2340] mt-1">
            Fichas de Caracterización y Centros de Formación
          </h2>
          <p className="text-xs text-slate-500 mt-1">
            Gestión institucional de cohortes de formación técnica y tecnológica articulados con el sistema SofiaPlus.
          </p>
        </div>

        {/* Filters */}
        <div className="flex flex-wrap items-center gap-2">
          <select
            value={selectedRegional}
            onChange={(e) => setSelectedRegional(e.target.value)}
            className="py-1.5 px-2.5 text-xs border border-[#CBD5E1] rounded-[4px] bg-white font-medium"
          >
            <option value="Todas">Todas las Regionales</option>
            <option value="Distrito Capital">Distrito Capital</option>
            <option value="Antioquia">Antioquia</option>
            <option value="Valle del Cauca">Valle del Cauca</option>
          </select>

          <select
            value={selectedNivel}
            onChange={(e) => setSelectedNivel(e.target.value)}
            className="py-1.5 px-2.5 text-xs border border-[#CBD5E1] rounded-[4px] bg-white font-medium"
          >
            <option value="Todos">Todos los Niveles</option>
            <option value="Tecnólogo">Tecnólogo</option>
            <option value="Técnico">Técnico</option>
            <option value="Especialización Tecnológica">Especialización Tecnológica</option>
          </select>
        </div>
      </div>

      {/* Cards Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-5">
        {filteredFichas.map(ficha => {
          const fichaAprendices = getAprendicesForFicha(ficha.id);
          const totalCohort = fichaAprendices.length;
          const alDia = fichaAprendices.filter(a => a.rapsNoAprobados === 0 && a.porcentajeAvance >= 75).length;
          const enRiesgo = fichaAprendices.filter(a => a.rapsNoAprobados > 0 || a.estadoFormacion === 'Condicionado').length;

          return (
            <div 
              key={ficha.id}
              className="bg-white rounded-[8px] border border-[#E2E8F0] shadow-sm hover:border-[#0C2340] transition overflow-hidden flex flex-col justify-between"
            >
              <div className="p-5 space-y-4">
                {/* Header of card */}
                <div className="flex items-start justify-between">
                  <div>
                    <div className="flex items-center space-x-2">
                      <span className="font-mono font-bold text-xs bg-[#0C2340] text-white px-2 py-0.5 rounded-[4px]">
                        Ficha {ficha.codigoFicha}
                      </span>
                      <span className="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-[4px]">
                        {ficha.nivelFormacion}
                      </span>
                    </div>
                    <h3 className="text-base font-heading font-bold text-[#0C2340] mt-1.5">
                      {ficha.programaFormacion}
                    </h3>
                  </div>

                  <span className={`text-[10px] font-bold px-2 py-0.5 rounded-[4px] uppercase ${
                    ficha.estado === 'Lectiva' ? 'bg-[#E6F4EA] text-[#0A6242] border border-[#A7F3D0]' : 'bg-blue-50 text-blue-700'
                  }`}>
                    Etapa {ficha.estado}
                  </span>
                </div>

                {/* Regional and Centro */}
                <div className="text-xs text-slate-600 space-y-1 bg-[#F8FAFC] p-3 rounded-[6px] border border-[#E2E8F0]">
                  <div className="flex items-center text-slate-700 font-medium">
                    <Building size={14} className="mr-1.5 text-[#0C2340] flex-shrink-0" />
                    <span className="truncate">{ficha.centroFormacion}</span>
                  </div>
                  <div className="flex items-center text-slate-500 text-[11px]">
                    <MapPin size={13} className="mr-1.5 text-slate-400 flex-shrink-0" />
                    <span>Regional {ficha.regional} • Modalidad {ficha.modalidad} ({ficha.jornada})</span>
                  </div>
                </div>

                {/* Leadership info */}
                <div className="grid grid-cols-2 gap-2 text-[11px] text-slate-600 pt-1">
                  <div>
                    <span className="text-slate-400 block text-[10px]">Instructor Técnico Líder:</span>
                    <span className="font-semibold text-slate-800 line-clamp-1">{ficha.instructorLider}</span>
                  </div>
                  <div>
                    <span className="text-slate-400 block text-[10px]">Coordinación Académica:</span>
                    <span className="font-semibold text-slate-800 line-clamp-1">{ficha.coordinadorAcademico}</span>
                  </div>
                </div>

                {/* Cohort Stats */}
                <div className="grid grid-cols-3 gap-2 pt-2 border-t border-[#E2E8F0] text-center">
                  <div className="p-2 bg-[#F1F5F9] rounded-[4px]">
                    <span className="text-[10px] text-slate-500 font-medium block">Total Cohorte</span>
                    <span className="text-base font-bold font-tabular text-[#0C2340]">{ficha.totalAprendices}</span>
                  </div>
                  <div className="p-2 bg-[#E6F4EA] rounded-[4px]">
                    <span className="text-[10px] text-emerald-800 font-medium block">Al Día</span>
                    <span className="text-base font-bold font-tabular text-[#0D7A53]">{ficha.aprendicesAlDia}</span>
                  </div>
                  <div className="p-2 bg-[#FEE2E2] rounded-[4px]">
                    <span className="text-[10px] text-rose-800 font-medium block">En Riesgo</span>
                    <span className="text-base font-bold font-tabular text-[#DC2626]">{ficha.aprendicesEnRiesgo}</span>
                  </div>
                </div>
              </div>

              {/* Action footer */}
              <div className="bg-[#F8FAFC] px-5 py-3 border-t border-[#E2E8F0] flex items-center justify-between">
                <span className="text-xs text-slate-500 font-mono">
                  {ficha.fechaInicio} al {ficha.fechaFin}
                </span>

                <div className="flex items-center space-x-2">
                  <button
                    onClick={() => setActiveFichaDetail(ficha)}
                    className="px-2.5 py-1 text-xs font-semibold text-[#0C2340] hover:bg-slate-200 rounded-[4px] border border-slate-300 transition"
                  >
                    Ver Aprendices ({fichaAprendices.length})
                  </button>
                  <button
                    onClick={() => onFilterFichaInMatrix(ficha.id)}
                    className="px-2.5 py-1 text-xs font-semibold bg-[#0C2340] hover:bg-[#1B365D] text-white rounded-[4px] transition"
                  >
                    Auditar en Matriz
                  </button>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      {/* Roster Modal for Ficha */}
      {activeFichaDetail && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#081628]/60 backdrop-blur-xs">
          <div className="bg-white rounded-[12px] border border-[#CBD5E1] shadow-[0_20px_25px_-5px_rgba(8,22,40,0.15)] max-w-2xl w-full overflow-hidden max-h-[85vh] flex flex-col">
            <div className="bg-[#0C2340] text-white p-4 flex items-center justify-between">
              <div>
                <span className="text-[10px] font-bold uppercase tracking-wider text-[#0D7A53] bg-[#E6F4EA] px-2 py-0.5 rounded">
                  Ficha {activeFichaDetail.codigoFicha}
                </span>
                <h3 className="text-base font-heading font-bold mt-1">
                  {activeFichaDetail.programaFormacion}
                </h3>
              </div>
              <button
                onClick={() => setActiveFichaDetail(null)}
                className="text-slate-300 hover:text-white text-lg font-bold"
              >
                ✕
              </button>
            </div>

            <div className="p-4 overflow-y-auto space-y-3">
              <p className="text-xs text-slate-500">
                Aprendices matriculados en la ficha. Haz clic en cualquiera para abrir su expediente académico completo.
              </p>

              <div className="space-y-2">
                {getAprendicesForFicha(activeFichaDetail.id).map(ap => (
                  <div 
                    key={ap.id}
                    onClick={() => {
                      onSelectAprendiz(ap);
                      setActiveFichaDetail(null);
                    }}
                    className="p-3 border border-[#E2E8F0] hover:border-[#0C2340] hover:bg-[#F8FAFC] rounded-[6px] flex items-center justify-between cursor-pointer transition"
                  >
                    <div>
                      <div className="font-bold text-xs text-[#0C2340]">
                        {ap.nombres} {ap.apellidos}
                      </div>
                      <div className="text-[11px] text-slate-500 font-mono">
                        {ap.tipoDocumento}: {ap.numeroDocumento} • {ap.email}
                      </div>
                    </div>

                    <div className="flex items-center space-x-3">
                      <div className="text-right">
                        <span className="text-xs font-bold font-tabular text-[#0D7A53]">
                          {ap.porcentajeAvance}%
                        </span>
                        <span className="text-[10px] text-slate-400 block">
                          {ap.rapsAprobados}/{ap.rapsTotales} RAPs
                        </span>
                      </div>
                      <ArrowRight size={14} className="text-slate-400" />
                    </div>
                  </div>
                ))}
              </div>
            </div>

            <div className="bg-[#F8FAFC] p-3 border-t border-[#E2E8F0] text-right">
              <button
                onClick={() => setActiveFichaDetail(null)}
                className="px-4 py-1.5 text-xs font-semibold bg-[#0C2340] text-white rounded-[4px]"
              >
                Cerrar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
