import * as XLSX from 'xlsx';
import { FilaJuicio, FilaJuicioSchema, IngestionRowError, JuicioEstado } from '../types';

export interface ParsedJuicioRowResult {
  fila: number;
  data?: FilaJuicio;
  raw: Record<string, any>;
  valido: boolean;
  errores: string[];
}

export function parseExcelWithZod(dataBuffer: ArrayBuffer | Uint8Array): {
  rows: ParsedJuicioRowResult[];
  errors: IngestionRowError[];
  totalFilas: number;
  filasValidas: number;
  filasConError: number;
  aprendicesUnicos: number;
  programasUnicos: number;
} {
  const workbook = XLSX.read(dataBuffer, { type: 'array' });
  const firstSheetName = workbook.SheetNames[0];
  const worksheet = workbook.Sheets[firstSheetName];
  const jsonData: any[] = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

  if (jsonData.length <= 1) {
    return {
      rows: [],
      errors: [{
        fila: 1,
        campo: 'Archivo',
        valor: 'Vacío',
        tipo: 'Error',
        descripcion: 'El archivo no contiene registros o solo contiene encabezados.',
      }],
      totalFilas: 0,
      filasValidas: 0,
      filasConError: 1,
      aprendicesUnicos: 0,
      programasUnicos: 0,
    };
  }

  const rawHeaders = (jsonData[0] as string[]).map(h => String(h || '').trim().toLowerCase());
  const rows: ParsedJuicioRowResult[] = [];
  const errors: IngestionRowError[] = [];

  const findCol = (possibleNames: string[]) => {
    return rawHeaders.findIndex(h => possibleNames.some(p => h.includes(p)));
  };

  const colTipoId = findCol(['tipo_identificacion', 'tipo_doc', 'tipo', 'tdoc']);
  const colNumId = findCol(['numero_identificacion', 'documento', 'numero_doc', 'identificacion', 'cedula']);
  const colNombre = findCol(['nombre_completo', 'nombre_aprendiz', 'nombre', 'nombres']);
  const colEmail = findCol(['email', 'correo', 'correo_electronico']);
  const colCodProg = findCol(['codigo_programa', 'cod_programa', 'ficha', 'programa_codigo']);
  const colNomProg = findCol(['nombre_programa', 'programa', 'programa_formacion']);
  const colComp = findCol(['competencia', 'codigo_competencia', 'competencia_nombre']);
  const colRap = findCol(['resultado_aprendizaje', 'rap', 'resultado']);
  const colJuicio = findCol(['juicio', 'juicio_evaluativo', 'estado', 'calificacion']);
  const colFecha = findCol(['fecha_evaluacion', 'fecha', 'fecha_juicio']);

  const aprendicesSet = new Set<string>();
  const programasSet = new Set<string>();

  for (let i = 1; i < jsonData.length; i++) {
    const r = jsonData[i];
    if (!r || r.length === 0 || r.every((cell: any) => cell === undefined || cell === null || cell === '')) {
      continue;
    }

    const rowNumber = i + 1;
    const rawObj: Record<string, any> = {
      tipo_identificacion: String(colTipoId >= 0 ? r[colTipoId] || 'CC' : 'CC').trim().toUpperCase(),
      numero_identificacion: String(colNumId >= 0 ? r[colNumId] || '' : '').trim(),
      nombre_completo: String(colNombre >= 0 ? r[colNombre] || '' : '').trim(),
      email: String(colEmail >= 0 ? r[colEmail] || '' : '').trim(),
      codigo_programa: String(colCodProg >= 0 ? r[colCodProg] || '228106' : '228106').trim(),
      nombre_programa: String(colNomProg >= 0 ? r[colNomProg] || 'Análisis y Desarrollo de Software (ADSO)' : 'Análisis y Desarrollo de Software (ADSO)').trim(),
      competencia: String(colComp >= 0 ? r[colComp] || '' : '').trim(),
      resultado_aprendizaje: String(colRap >= 0 ? r[colRap] || '' : '').trim(),
      juicio: String(colJuicio >= 0 ? r[colJuicio] || 'POR_EVALUAR' : 'POR_EVALUAR').trim(),
      fecha_evaluacion: colFecha >= 0 && r[colFecha] ? r[colFecha] : undefined,
    };

    // Normalize Juicio Evaluativo to APROBADO, POR_EVALUAR, NO_APROBADO
    const rawJ = String(rawObj.juicio).toUpperCase();
    if (rawJ.includes('APROB') || rawJ === 'A') {
      rawObj.juicio = 'APROBADO';
    } else if (rawJ.includes('NO') || rawJ.includes('DEFIC') || rawJ === 'D') {
      rawObj.juicio = 'NO_APROBADO';
    } else {
      rawObj.juicio = 'POR_EVALUAR';
    }

    // Normalizing Tipo Identificacion enum
    const rawT = String(rawObj.tipo_identificacion).toUpperCase();
    if (['CC', 'TI', 'CE', 'PEP', 'PASAPORTE'].includes(rawT)) {
      rawObj.tipo_identificacion = rawT;
    } else {
      rawObj.tipo_identificacion = 'CC';
    }

    // Execute strict Zod Schema validation
    const parsed = FilaJuicioSchema.safeParse(rawObj);

    if (parsed.success) {
      if (parsed.data.numero_identificacion) {
        aprendicesSet.add(parsed.data.numero_identificacion);
      }
      if (parsed.data.codigo_programa) {
        programasSet.add(parsed.data.codigo_programa);
      }

      rows.push({
        fila: rowNumber,
        data: parsed.data,
        raw: rawObj,
        valido: true,
        errores: [],
      });
    } else {
      const issues = parsed.error.issues;
      const errorDescriptions: string[] = [];

      issues.forEach(issue => {
        const fieldName = issue.path.join('.');
        const description = issue.message;
        errorDescriptions.push(`${fieldName}: ${description}`);

        errors.push({
          fila: rowNumber,
          campo: fieldName,
          valor: String(rawObj[fieldName] || '(vacío)'),
          tipo: 'Error',
          descripcion: description,
        });
      });

      rows.push({
        fila: rowNumber,
        raw: rawObj,
        valido: false,
        errores: errorDescriptions,
      });
    }
  }

  const filasConError = rows.filter(r => !r.valido).length;
  const filasValidas = rows.filter(r => r.valido).length;

  return {
    rows,
    errors,
    totalFilas: rows.length,
    filasValidas,
    filasConError,
    aprendicesUnicos: aprendicesSet.size,
    programasUnicos: programasSet.size,
  };
}

export function downloadErrorLogCsv(errors: IngestionRowError[]): void {
  const headers = ['Numero_Fila', 'Columna_Afectada', 'Valor_Encontrado', 'Tipo_Fallo', 'Motivo_Rechazo'];
  const rows = errors.map(err => [
    err.fila,
    `"${err.campo}"`,
    `"${String(err.valor).replace(/"/g, '""')}"`,
    `"${err.tipo}"`,
    `"${err.descripcion.replace(/"/g, '""')}"`,
  ]);

  const csvContent = [headers.join(','), ...rows.map(r => r.join(','))].join('\r\n');
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `Reporte_Inconsistencias_Juicios_${new Date().toISOString().slice(0, 10)}.csv`;
  a.click();
  URL.revokeObjectURL(url);
}

export function generateSenaSampleWorkbook(): Uint8Array {
  const sampleHeaders = [
    'tipo_identificacion',
    'numero_identificacion',
    'nombre_completo',
    'email',
    'codigo_programa',
    'nombre_programa',
    'competencia',
    'resultado_aprendizaje',
    'juicio',
    'fecha_evaluacion'
  ];

  const sampleRows = [
    ['CC', '1014298102', 'Valentina Ríos Cárdenas', 'vrios@soy.sena.edu.co', '228106', 'Análisis y Desarrollo de Software (ADSO)', 'Especificación de Requisitos de Software', 'Caracterizar los procesos de la organización de acuerdo con el marco y estándares.', 'APROBADO', '2026-09-18'],
    ['CC', '1014298102', 'Valentina Ríos Cárdenas', 'vrios@soy.sena.edu.co', '228106', 'Análisis y Desarrollo de Software (ADSO)', 'Modelado y Gestión de Bases de Datos', 'Construir bases de datos relacionales y no relacionales según especificaciones.', 'APROBADO', '2026-09-18'],
    ['CC', '1020491820', 'Mateo Alejandro Suárez Bermúdez', 'msuarez@soy.sena.edu.co', '228106', 'Análisis y Desarrollo de Software (ADSO)', 'Especificación de Requisitos de Software', 'Elaborar diagramas y modelos de arquitectura según requerimientos funcionales.', 'APROBADO', '2026-09-14'],
    ['CC', '1020491820', 'Mateo Alejandro Suárez Bermúdez', 'msuarez@soy.sena.edu.co', '228106', 'Análisis y Desarrollo de Software (ADSO)', 'Desarrollo de Software Web Full-Stack', 'Implementar servicios web RESTful y microservicios seguros con autenticación JWT.', 'POR_EVALUAR', ''],
    ['CC', '1032890145', 'Daniel Fernando Gutiérrez Pinzón', 'dgutierrez@soy.sena.edu.co', '228106', 'Análisis y Desarrollo de Software (ADSO)', 'Modelado y Gestión de Bases de Datos', 'Aplicar procedimientos de normalización y optimización SQL bajo estándares ACID.', 'NO_APROBADO', '2026-09-10'],
    ['TI', '1077654321', 'Camila Andrea Montoya Restrepo', 'cmontoya@soy.sena.edu.co', '228118', 'Gestión de Redes y Ciberseguridad', 'Arquitectura y Seguridad en Redes WAN', 'Configurar enrutamiento seguro y listas de control de acceso ACL.', 'APROBADO', '2026-09-22'],
    ['CC', '1098456123', 'Esteban José Herrera Morales', 'eherrera@soy.sena.edu.co', '228118', 'Gestión de Redes y Ciberseguridad', 'Ciberseguridad y Análisis Forense', 'Implementar contramedidas y políticas de seguridad bajo estándar ISO 27001.', 'APROBADO', '2026-09-25'],
    ['CC', '1011889922', 'Andrés Felipe Pardo Caicedo', 'apardo@soy.sena.edu.co', '228120', 'Inteligencia Artificial Aplicada a Negocios', 'Pipelines de Machine Learning y Datos', 'Entrenar y desplegar modelos supervisados para predicción de series temporales.', 'APROBADO', '2026-09-02'],
  ];

  const ws = XLSX.utils.aoa_to_sheet([sampleHeaders, ...sampleRows]);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Juicios_Evaluativos');
  const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
  return new Uint8Array(wbout);
}
