export type JuicioEstado = 'Aprobado' | 'Por Evaluar' | 'No Aprobado';

export interface RAP {
  id: string;
  codigo: string;
  descripcion: string;
  competenciaCodigo: string;
  competenciaNombre: string;
  estado: JuicioEstado;
  instructorEvaluador: string;
  fechaJuicio?: string;
  observaciones?: string;
}

export interface Aprendiz {
  id: string;
  tipoDocumento: 'CC' | 'TI' | 'CE' | 'PEP';
  numeroDocumento: string;
  nombres: string;
  apellidos: string;
  email: string;
  telefono: string;
  fichaId: string;
  estadoFormacion: 'En Formación' | 'Condicionado' | 'Retiro Voluntario' | 'Por Certificar' | 'Certificado';
  rapsTotales: number;
  rapsAprobados: number;
  rapsPendientes: number;
  rapsNoAprobados: number;
  porcentajeAvance: number;
  raps: RAP[];
}

export interface FichaCaracterizacion {
  id: string;
  codigoFicha: string;
  programaFormacion: string;
  nivelFormacion: 'Tecnólogo' | 'Técnico' | 'Especialización Tecnológica';
  regional: string;
  centroFormacion: string;
  modalidad: 'Presencial' | 'Virtual' | 'Mixta';
  jornada: 'Diurna' | 'Nocturna' | 'Madrugada' | 'Fines de Semana';
  instructorLider: string;
  coordinadorAcademico: string;
  fechaInicio: string;
  fechaFin: string;
  totalAprendices: number;
  aprendicesAlDia: number;
  aprendicesEnRiesgo: number;
  estado: 'Lectiva' | 'Productiva' | 'Finalizada';
}

export interface IngestionBatch {
  id: string;
  batchId: string;
  nombreArchivo: string;
  tamanoKb: number;
  registrosProcesados: number;
  registrosValidos: number;
  registrosConError: number;
  advertencias: number;
  fechaIngesta: string;
  usuarioResponsable: string;
  rolUsuario: string;
  corteSnapshot: string;
  estado: 'Committed' | 'En Validación' | 'Revocado (Rollback)' | 'Rechazado';
  motivoRollback?: string;
  fechaRollback?: string;
  sha256Hash: string;
}

export interface IngestionRowError {
  fila: number;
  campo: string;
  valor: string;
  tipo: 'Error' | 'Advertencia';
  descripcion: string;
}

export interface FilterState {
  regional: string;
  centro: string;
  ficha: string;
  estadoJuicio: string;
  search: string;
}
