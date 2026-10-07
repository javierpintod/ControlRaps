import * as XLSX from 'xlsx';
import { Aprendiz, IngestionRowError, JuicioEstado } from '../types';

export interface ParsedJuicioRow {
  fila: number;
  tipoDocumento: string;
  numeroDocumento: string;
  nombres: string;
  apellidos: string;
  codigoFicha: string;
  competenciaCodigo: string;
  rapCodigo: string;
  rapDescripcion: string;
  juicioEvaluativo: JuicioEstado | string;
  instructorEvaluador: string;
  fechaJuicio: string;
  valido: boolean;
  errores: string[];
}

export function parseExcelOrCsvFile(dataBuffer: ArrayBuffer | Uint8Array): {
  rows: ParsedJuicioRow[];
  errors: IngestionRowError[];
  totalFilas: number;
  filasValidas: number;
  filasConError: number;
  advertencias: number;
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
      advertencias: 0,
    };
  }

  // Header inspection
  const headers = (jsonData[0] as string[]).map(h => String(h || '').trim().toLowerCase());
  const rows: ParsedJuicioRow[] = [];
  const errors: IngestionRowError[] = [];
  let validCount = 0;
  let warnCount = 0;

  // Expected column matching
  const findCol = (possibleNames: string[]) => {
    return headers.findIndex(h => possibleNames.some(p => h.includes(p)));
  };

  const colTipoDoc = findCol(['tipo', 'tdoc', 'tipo_doc']);
  const colDoc = findCol(['documento', 'numero_doc', 'identificacion', 'cedula']);
  const colNombres = findCol(['nombre', 'nombres']);
  const colApellidos = findCol(['apellido', 'apellidos']);
  const colFicha = findCol(['ficha', 'codigo_ficha']);
  const colCompetencia = findCol(['competencia', 'comp_cod']);
  const colRap = findCol(['rap', 'resultado', 'codigo_rap']);
  const colDescripcion = findCol(['descripcion', 'desc_rap', 'detalle']);
  const colJuicio = findCol(['juicio', 'estado', 'evaluativo', 'calificacion']);
  const colInstructor = findCol(['instructor', 'evaluador', 'docente']);
  const colFecha = findCol(['fecha', 'fecha_juicio']);

  for (let i = 1; i < jsonData.length; i++) {
    const r = jsonData[i];
    if (!r || r.length === 0 || r.every((cell: any) => cell === undefined || cell === null || cell === '')) {
      continue;
    }

    const rowErrors: string[] = [];
    const tipoDoc = String(colTipoDoc >= 0 ? r[colTipoDoc] || 'CC' : 'CC').trim().toUpperCase();
    const doc = String(colDoc >= 0 ? r[colDoc] || '' : '').trim();
    const nombres = String(colNombres >= 0 ? r[colNombres] || '' : '').trim();
    const apellidos = String(colApellidos >= 0 ? r[colApellidos] || '' : '').trim();
    const ficha = String(colFicha >= 0 ? r[colFicha] || '2671982' : '2671982').trim();
    const competencia = String(colCompetencia >= 0 ? r[colCompetencia] || '220501096' : '220501096').trim();
    const rap = String(colRap >= 0 ? r[colRap] || '' : '').trim();
    const desc = String(colDescripcion >= 0 ? r[colDescripcion] || 'Resultado de Aprendizaje Técnico' : 'Resultado de Aprendizaje Técnico').trim();
    let juicioRaw = String(colJuicio >= 0 ? r[colJuicio] || 'Por Evaluar' : 'Por Evaluar').trim();
    const instructor = String(colInstructor >= 0 ? r[colInstructor] || 'Instructor Asignado' : 'Instructor Asignado').trim();
    const fecha = String(colFecha >= 0 ? r[colFecha] || new Date().toISOString().split('T')[0] : new Date().toISOString().split('T')[0]).trim();

    // Normalizing juicio evaluativo
    let juicio: JuicioEstado = 'Por Evaluar';
    const lowerJ = juicioRaw.toLowerCase();
    if (lowerJ.includes('aprob') || lowerJ === 'a' || lowerJ === 'aprobado') {
      juicio = 'Aprobado';
    } else if (lowerJ.includes('no') || lowerJ.includes('defic') || lowerJ === 'd' || lowerJ === 'no aprobado') {
      juicio = 'No Aprobado';
    } else {
      juicio = 'Por Evaluar';
    }

    // Validation rules
    if (!doc || doc.length < 5) {
      rowErrors.push('Número de documento inválido o ausente');
      errors.push({
        fila: i + 1,
        campo: 'numeroDocumento',
        valor: doc || '(vacío)',
        tipo: 'Error',
        descripcion: 'El documento debe contener al menos 5 dígitos numéricos.',
      });
    }

    if (!nombres) {
      rowErrors.push('Nombres del aprendiz requeridos');
      errors.push({
        fila: i + 1,
        campo: 'nombres',
        valor: '(vacío)',
        tipo: 'Error',
        descripcion: 'Campo obligatorio para identificación de aprendiz.',
      });
    }

    if (!rap) {
      rowErrors.push('Código de RAP ausente');
      errors.push({
        fila: i + 1,
        campo: 'rapCodigo',
        valor: '(vacío)',
        tipo: 'Error',
        descripcion: 'Debe especificarse el identificador único del Resultado de Aprendizaje.',
      });
    }

    if (juicio === 'Por Evaluar') {
      warnCount++;
      errors.push({
        fila: i + 1,
        campo: 'juicioEvaluativo',
        valor: juicioRaw,
        tipo: 'Advertencia',
        descripcion: 'El resultado permanece pendiente de calificación formal por el instructor.',
      });
    }

    const isValid = rowErrors.length === 0;
    if (isValid) validCount++;

    rows.push({
      fila: i + 1,
      tipoDocumento: tipoDoc,
      numeroDocumento: doc,
      nombres,
      apellidos,
      codigoFicha: ficha,
      competenciaCodigo: competencia,
      rapCodigo: rap || 'RAP-GEN-01',
      rapDescripcion: desc,
      juicioEvaluativo: juicio,
      instructorEvaluador: instructor,
      fechaJuicio: fecha,
      valido: isValid,
      errores: rowErrors,
    });
  }

  const errCount = rows.filter(r => !r.valido).length;

  return {
    rows,
    errors,
    totalFilas: rows.length,
    filasValidas: validCount,
    filasConError: errCount,
    advertencias: warnCount,
  };
}

export function generateSenaSampleWorkbook(): Uint8Array {
  const sampleHeaders = [
    'Tipo Documento',
    'Numero Documento',
    'Nombres',
    'Apellidos',
    'Codigo Ficha',
    'Codigo Competencia',
    'Codigo RAP',
    'Descripcion RAP',
    'Juicio Evaluativo',
    'Instructor Evaluador',
    'Fecha Juicio'
  ];

  const sampleRows = [
    ['CC', '1014298102', 'Valentina', 'Ríos Cárdenas', '2671982', '220501096', '220501096-01', 'Caracterizar procesos de software', 'Aprobado', 'Ing. Carlos Mendoza', '2026-09-18'],
    ['CC', '1014298102', 'Valentina', 'Ríos Cárdenas', '2671982', '220501093', '220501093-01', 'Construir bases de datos relacionales', 'Aprobado', 'Ing. Carlos Mendoza', '2026-09-18'],
    ['CC', '1020491820', 'Mateo Alejandro', 'Suárez Bermúdez', '2671982', '220501096', '220501096-02', 'Elaborar diagramas y modelos de arquitectura', 'Aprobado', 'Ing. Carlos Mendoza', '2026-09-14'],
    ['CC', '1020491820', 'Mateo Alejandro', 'Suárez Bermúdez', '2671982', '220501095', '220501095-02', 'Implementar servicios RESTful y microservicios', 'Por Evaluar', 'Ing. Carlos Mendoza', ''],
    ['CC', '1032890145', 'Daniel Fernando', 'Gutiérrez Pinzón', '2671982', '220501093', '220501093-02', 'Normalización y optimización SQL', 'No Aprobado', 'Ing. Carlos Mendoza', '2026-09-10'],
    ['TI', '1077654321', 'Camila Andrea', 'Montoya Restrepo', '2671982', '220501095', '220501095-01', 'Desarrollar componentes web frontend', 'Aprobado', 'Lic. Fernando Ospina', '2026-09-22'],
    ['CC', '1098456123', 'Esteban José', 'Herrera Morales', '2710493', '220501096', '220501096-01', 'Arquitectura de seguridad en redes', 'Aprobado', 'Ing. Diana Rincón', '2026-09-25'],
    ['CC', '1054321890', 'Sara Sofía', 'Zuluaga Henao', '2710493', '220501096', '220501096-02', 'Implementación de túneles VPN y TLS', 'Por Evaluar', 'Ing. Diana Rincón', ''],
  ];

  const ws = XLSX.utils.aoa_to_sheet([sampleHeaders, ...sampleRows]);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Juicios_Evaluativos');
  const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });
  return new Uint8Array(wbout);
}

export function exportConsolidatedReportToExcel(aprendices: Aprendiz[]): void {
  const flatData: any[] = [];

  aprendices.forEach(ap => {
    ap.raps.forEach(rap => {
      flatData.push({
        'Ficha': ap.fichaId.replace('f-', ''),
        'Tipo Doc': ap.tipoDocumento,
        'N° Documento': ap.numeroDocumento,
        'Aprendiz': `${ap.nombres} ${ap.apellidos}`,
        'Email': ap.email,
        'Estado Formación': ap.estadoFormacion,
        'Cód. Competencia': rap.competenciaCodigo,
        'Competencia': rap.competenciaNombre,
        'Cód. RAP': rap.codigo,
        'Descripción RAP': rap.descripcion,
        'Juicio Evaluativo': rap.estado,
        'Instructor Evaluador': rap.instructorEvaluador,
        'Fecha Juicio': rap.fechaJuicio || 'Pendiente',
        'Observaciones': rap.observaciones || 'Sin observaciones',
      });
    });
  });

  const ws = XLSX.utils.json_to_sheet(flatData);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Matriz_Juicios_SENA');
  XLSX.writeFile(wb, `SENA_Analytics_Matriz_Juicios_${new Date().toISOString().slice(0, 10)}.xlsx`);
}
