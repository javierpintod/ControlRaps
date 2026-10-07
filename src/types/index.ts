import { z } from "zod";

export type RolUsuario = 'ADMIN' | 'LIDER_FORMACION' | 'INSTRUCTOR';

export interface UsuarioSesion {
  id: string;
  email: string;
  nombre: string;
  rol: RolUsuario;
  cargo: string;
}

export type JuicioEstado = 'APROBADO' | 'POR_EVALUAR' | 'NO_APROBADO';

export const FilaJuicioSchema = z.object({
  tipo_identificacion: z.enum(["CC", "TI", "CE", "PEP", "PASAPORTE"]),
  numero_identificacion: z.string().min(4).max(20).trim(),
  nombre_completo: z.string().min(3).max(255).trim(),
  email: z.string().email().optional().or(z.literal("")),
  codigo_programa: z.string().min(2).max(50).trim(),
  nombre_programa: z.string().min(3).max(255).trim(),
  competencia: z.string().min(5).trim(),
  resultado_aprendizaje: z.string().min(5).trim(),
  juicio: z.enum(["APROBADO", "POR_EVALUAR", "NO_APROBADO"]),
  fecha_evaluacion: z.coerce.date().optional()
});

export const LoteCargaSchema = z.object({
  fecha_corte: z.coerce.date(),
  filas: z.array(FilaJuicioSchema).min(1, "El archivo no contiene registros válidos")
});

export type FilaJuicio = z.infer<typeof FilaJuicioSchema>;

export interface IngestionRowError {
  fila: number;
  campo: string;
  valor: string;
  tipo: 'Error' | 'Advertencia';
  descripcion: string;
}

export interface CargaArchivo {
  batch_id: string;
  nombre_archivo: string;
  usuario_id: string;
  usuario_nombre: string;
  usuario_rol: RolUsuario;
  fecha_corte: string; // YYYY-MM-DD
  total_registros: number;
  registros_exitosos: number;
  inconsistencias: number;
  estado: 'PROCESANDO' | 'EXITOSO' | 'FALLIDO' | 'REVOCADO';
  created_at: string;
  motivo_rollback?: string;
  metadata?: {
    programas_count?: number;
    aprendices_count?: number;
    version_corte?: number;
  };
}

export interface Aprendiz {
  numero_identificacion: string;
  tipo_identificacion: 'CC' | 'TI' | 'CE' | 'PEP' | 'PASAPORTE';
  nombre_completo: string;
  email: string;
  codigo_programa: string;
  nombre_programa: string;
  total_raps: number;
  raps_aprobados: number;
  raps_pendientes: number;
  raps_no_aprobados: number;
  porcentaje_avance: number;
  estado_academico: 'Al Día' | 'En Riesgo' | 'Por Certificar';
  evaluaciones: EvaluacionItem[];
}

export interface EvaluacionItem {
  id: string;
  batch_id: string;
  competencia: string;
  resultado_aprendizaje: string;
  juicio: JuicioEstado;
  fecha_evaluacion?: string;
  instructor_evaluador?: string;
}

export interface ProgramaFormacion {
  codigo_programa: string;
  nombre_programa: string;
  total_aprendices: number;
  tasa_aprobacion: number;
  fichas_asociadas: string[];
}
