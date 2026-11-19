"""Mapeo de CU-02 a CU-20 basado en las HU aprobadas."""

SUPER = "Superadministrador"
GER = "Gerente"
ADM = "Administración"
ENF = "Enfermería autorizada"
MED = "Medicina / Geriatría autorizada"
PROF = "Profesional competente autorizado"
FAM = "Familiar vinculado autorizado"
TEC = "Responsable técnico de pruebas"
SAL = "Enfermería de turno saliente"
ENT = "Enfermería de turno entrante"


def goal(name, actors, includes=()):
    return name, tuple(actors), tuple(includes)


CATALOG = {
    2: ("GESTIONAR USUARIOS Y PERMISOS", "HU-02", [
        goal("Gestionar usuarios y permisos", [SUPER], ["Verificar facultad de administración"]),
        goal("Crear o actualizar cuentas institucionales", [SUPER]),
        goal("Activar o desactivar cuentas", [SUPER]),
        goal("Asignar roles y permisos aprobados", [SUPER]),
        goal("Consultar auditoría de acciones", [SUPER]),
    ], "La auditoría es de consulta. Los permisos se asignan dentro de las competencias aprobadas."),
    3: ("ORGANIZAR PERSONAL Y JORNADAS", "HU-03 + HU-04", [
        goal("Planificar personal por áreas y turnos", [GER]),
        goal("Consultar indicadores institucionales", [GER]),
        goal("Abrir jornada de trabajo", [ADM], ["Consultar la planificación general"]),
        goal("Asignar residentes y actividades", [ADM], ["Verificar personal habilitado y disponible"]),
    ], "Gerencia define la planificación general. Administración opera la jornada respetando el alcance de cada profesional."),
    4: ("GESTIONAR INGRESO DE RESIDENTES", "HU-05 + HU-06", [
        goal("Registrar solicitud de preadmisión", [ADM]),
        goal("Revisar preadmisión y registrar decisión", [ADM]),
        goal("Confirmar admisión del residente", [ADM], [
            "Verificar preadmisión aprobada",
            "Validar documentos y consentimiento",
            "Comprobar cama disponible",
            "Verificar que el ingreso sea único",
        ]),
    ], "La aprobación de una preadmisión habilita su evaluación para el ingreso. La admisión requiere confirmación independiente."),
    5: ("GESTIONAR RESIDENTES Y ALOJAMIENTO", "HU-07 + HU-08", [
        goal("Consultar expediente administrativo", [ADM]),
        goal("Actualizar datos, contactos y documentos", [ADM]),
        goal("Consultar disponibilidad de camas", [ADM]),
        goal("Registrar cambio de alojamiento", [ADM], [
            "Verificar disponibilidad y compatibilidad",
            "Conservar historial de ocupación",
        ]),
    ], "Administración gestiona el expediente administrativo. El cambio de cama conserva las asignaciones anteriores."),
    6: ("GESTIONAR ACTIVIDADES Y VISITAS", "HU-09", [
        goal("Registrar actividad institucional", [ADM], ["Identificar fecha y participantes"]),
        goal("Registrar visita permitida", [ADM], ["Comprobar condiciones de acceso"]),
    ], "Administración documenta la actividad y la visita. El visitante actúa como persona registrada en el proceso."),
    7: ("REGISTRAR SEGUIMIENTO DE ENFERMERÍA", "HU-10 + HU-15", [
        goal("Consultar mi jornada y residentes asignados", [ENF]),
        goal("Registrar seguimiento de Enfermería", [ENF], [
            "Verificar residente asignado y jornada válida",
            "Vincular el registro con residente, autor y fecha",
        ]),
    ], "Se registran hechos, mediciones y cambios observables. La consulta histórica se distingue del registro en una jornada activa."),
    8: ("REGISTRAR ATENCIONES MÉDICAS", "HU-11", [
        goal("Registrar atenciones médicas", [MED], [
            "Identificar residente, profesional y fecha",
            "Documentar motivo de atención",
            "Registrar hallazgos e indicaciones",
        ]),
    ], "La atención conserva la autoría del profesional y las indicaciones dentro de su competencia."),
    9: ("REGISTRAR SIGNOS VITALES", "HU-12", [
        goal("Registrar signos vitales", [ENF], [
            "Verificar residente y jornada autorizados",
            "Validar al menos una medición",
        ]),
        goal("Consultar historial de signos vitales", [ENF]),
    ], "Si se advierte un valor crítico, Enfermería confirma la lectura antes de guardar el registro. La vista previa es de consulta."),
    10: ("GESTIONAR PRESCRIPCIONES MÉDICAS", "HU-13", [
        goal("Registrar prescripción médica", [MED], ["Vincular prescripción con el residente"]),
        goal("Consultar prescripciones del residente", [MED, ENF]),
    ], "Medicina o Geriatría prescribe. Enfermería consulta las prescripciones autorizadas para su labor."),
    11: ("ADMINISTRAR MEDICAMENTOS", "HU-14", [
        goal("Consultar agenda y prescripciones vigentes", [ENF]),
        goal("Registrar administración u omisión", [ENF], [
            "Verificar prescripción, residente y jornada",
            "Verificar horario aplicable",
            "Identificar resultado y autor",
        ]),
    ], "Se documenta el motivo cuando corresponda. Los eventos PRN se registran solo cuando están permitidos."),
    12: ("REGISTRAR CUIDADOS", "HU-16", [
        goal("Consultar planes e intervenciones vigentes", [ENF]),
        goal("Registrar cuidados ejecutados u omitidos", [ENF], [
            "Verificar intervención vigente y residente asignado",
            "Identificar ejecución u omisión",
        ]),
    ], "Se deja constancia de los motivos cuando correspondan y de los cuidados pendientes."),
    13: ("REGISTRAR INCIDENTES Y HERIDAS", "HU-17", [
        goal("Registrar incidente o herida", [ENF], ["Identificar residente, autor y fecha"]),
        goal("Registrar curación o seguimiento", [ENF], ["Comprobar permiso específico"]),
    ], "Los registros describen hechos y acciones, y conservan el historial previo."),
    14: ("REALIZAR PASE DE TURNO", "HU-18", [
        goal("Preparar y entregar el pase de turno", [SAL], [
            "Verificar alcance de la jornada saliente",
            "Identificar pendientes, alertas y registros fuente",
        ]),
        goal("Recibir y confirmar el pase de turno", [ENT], ["Identificar al profesional receptor"]),
    ], "La entrega y la recepción quedan vinculadas a sus autores. Los cambios posteriores requieren autorización."),
    15: ("REGISTRAR EVALUACIONES COGNITIVAS Y FUNCIONALES", "HU-19 + HU-20", [
        goal("Registrar evaluación cognitiva", [PROF], [
            "Identificar instrumento, versión, profesional y fecha",
            "Vincular resultados con el residente",
        ]),
        goal("Registrar valoración funcional", [PROF], [
            "Identificar procedimiento, autor y fecha",
        ]),
    ], "Cada profesional registra dentro de su disciplina. Se usan instrumentos autorizados y se respetan sus límites metodológicos."),
    16: ("CONSULTAR EVOLUCIÓN COGNITIVA", "HU-21", [
        goal("Consultar evolución cognitiva", ["Profesional clínico autorizado"], [
            "Verificar acceso al residente",
            "Presentar evaluaciones por fecha y fuente",
            "Presentar observaciones y valoraciones funcionales",
        ]),
    ], "La consulta apoya el seguimiento documentado. Las comparaciones respetan la compatibilidad de los instrumentos."),
    17: ("GESTIONAR ALERTAS", "HU-22", [
        goal("Consultar y coordinar alertas administrativas", [ADM], [
            "Verificar alcance de coordinación",
        ]),
        goal("Consultar y atender alertas asignadas", [ENF], [
            "Verificar residente y jornada de su alcance",
            "Registrar reconocimiento o intervención",
        ]),
    ], "El cierre depende del permiso. Cada acción conserva el origen, el responsable y el cambio de estado."),
    18: ("CONSULTAR INFORMACIÓN DEL RESIDENTE", "HU-23", [
        goal("Consultar información del residente", [FAM], [
            "Verificar vínculo familiar",
            "Comprobar permiso vigente",
            "Mostrar información autorizada",
        ]),
    ], "El familiar ve solo la información expresamente habilitada. El expediente clínico se mantiene reservado."),
    19: ("CONSULTAR CONOCIMIENTO Y PRUEBAS EXPERTAS", "HU-24", [
        goal("Consultar conocimiento experto", [SUPER], [
            "Consultar conceptos, relaciones y reglas versionadas",
            "Consultar fuentes y estado de aprobación",
        ]),
        goal("Ejecutar escenarios de prueba", [TEC], [
            "Verificar entorno controlado y datos sintéticos",
        ]),
        goal("Revisar evidencias y trazas del motor", [TEC]),
    ], "La consulta y las pruebas tienen permisos diferenciados. Las pruebas son técnicas y usan escenarios artificiales."),
    20: ("CONSULTAR RESULTADOS EXPERTOS", "HU-25", [
        goal("Consultar resultados expertos", [MED, ENF], [
            "Verificar permiso y residente autorizado",
            "Mostrar evidencias y reglas de sustento",
            "Presentar el historial disponible",
        ]),
    ], "Se consultan resultados técnicos disponibles dentro del alcance profesional. La inferencia clínica permanece pendiente de habilitación."),
}
