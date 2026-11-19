"""Comportamientos y variantes trazables a las 25 historias de RememberMind."""

from casos_catalogo import ADM, ENF, ENT, FAM, GER, MED, PROF, SAL, SUPER, TEC


STAFF = "Personal institucional"
CLIN = "Profesional clínico autorizado"


def block(title, actors, required, options=()):
    return title, tuple(actors), tuple(required), tuple(options)


DETAIL = {
    1: ([
        block("Iniciar sesión", [STAFF, FAM], [
            "Validar correo y contraseña",
            "Comprobar que la cuenta esté activa",
            "Determinar el acceso según el rol",
        ], [
            ("Recuperar contraseña", "Cuando el usuario solicita recuperarla desde la pantalla de acceso."),
            ("Rechazar el ingreso", "Si las credenciales son incorrectas o la cuenta está inactiva."),
        ]),
        block("Cerrar sesión", [STAFF, FAM], [
            "Invalidar el acceso de la sesión activa",
        ]),
    ], "HU-01. Las cuentas son preexistentes. El acceso y las operaciones se limitan al permiso de cada cuenta. El canal de recuperación está por confirmar."),
    2: ([
        block("Gestionar cuentas institucionales", [SUPER], [
            "Verificar autorización administrativa",
            "Identificar al titular de la cuenta",
        ], [
            ("Crear cuenta institucional", "Si se autoriza el alta de una cuenta nueva."),
            ("Actualizar datos de una cuenta", "Si se solicita corregir una cuenta existente."),
            ("Activar o desactivar cuenta", "Si corresponde cambiar la habilitación de la cuenta."),
        ]),
        block("Asignar roles y permisos", [SUPER], [
            "Comprobar que los permisos estén aprobados",
            "Respetar las competencias del perfil",
        ]),
        block("Consultar auditoría de acciones", [SUPER], [
            "Mostrar la trazabilidad de las acciones registradas",
        ]),
    ], "HU-02. La auditoría se consulta en modo de solo lectura. La asignación de permisos sigue las competencias aprobadas."),
    3: ([
        block("Planificar personal y turnos", [GER], [
            "Organizar personal por áreas",
            "Definir la planificación general de turnos",
        ]),
        block("Consultar indicadores institucionales", [GER], [
            "Verificar acceso al indicador solicitado",
        ]),
        block("Abrir jornada de trabajo", [ADM], [
            "Consultar la planificación de Gerencia",
        ]),
        block("Asignar residentes y actividades", [ADM], [
            "Verificar disponibilidad del personal",
            "Comprobar habilitación y alcance del perfil",
        ], [
            ("Impedir una asignación incompatible", "Si el personal no está disponible o carece de habilitación."),
        ]),
    ], "HU-03 + HU-04. Gerencia planifica y consulta indicadores. Administración abre la jornada y efectúa las asignaciones autorizadas."),
    4: ([
        block("Registrar y revisar la preadmisión", [ADM], [
            "Registrar los datos requeridos de la solicitud",
            "Revisar la información presentada",
            "Conservar la decisión sobre la solicitud",
        ], [
            ("Aprobar la preadmisión", "Si la revisión determina que corresponde continuar."),
            ("Rechazar la preadmisión", "Si la revisión determina que no corresponde continuar."),
        ]),
        block("Confirmar admisión del residente", [ADM], [
            "Comprobar preadmisión aprobada",
            "Validar documentos y consentimiento",
            "Comprobar cama y ocupación compatibles",
            "Verificar que el ingreso no esté duplicado",
        ], [
            ("Impedir la confirmación del ingreso", "Si falla alguna condición de admisión."),
        ]),
    ], "HU-05 + HU-06. Preadmisión y admisión son decisiones distintas. La segunda exige una preadmisión aprobada y comprobaciones propias."),
    5: ([
        block("Consultar expediente administrativo", [ADM], [
            "Identificar al residente",
            "Consultar datos, contactos y documentos administrativos",
        ], [
            ("Actualizar el expediente administrativo", "Si Administración requiere corregir o incorporar información permitida."),
        ]),
        block("Consultar camas disponibles", [ADM], [
            "Consultar la ocupación vigente",
        ]),
        block("Registrar un cambio de alojamiento", [ADM], [
            "Verificar que la nueva cama esté disponible",
            "Comprobar compatibilidad con ocupaciones activas",
            "Conservar el historial de asignaciones",
        ], [
            ("Rechazar la asignación de cama", "Si la cama no está disponible o la ocupación es incompatible."),
        ]),
    ], "HU-07 + HU-08. Los datos administrativos y el historial de alojamiento se vinculan al mismo residente. La actualización corresponde al ámbito administrativo."),
    6: ([
        block("Registrar actividad institucional", [ADM], [
            "Identificar la actividad y su fecha",
            "Registrar a los participantes",
            "Conservar trazabilidad de la actividad",
        ]),
        block("Registrar visita permitida", [ADM], [
            "Comprobar condiciones de acceso del visitante",
            "Registrar el ingreso autorizado",
            "Conservar trazabilidad de la visita",
        ], [
            ("Impedir un ingreso no permitido", "Si el visitante no cumple las condiciones de acceso."),
        ]),
    ], "HU-09. Administración registra actividades y visitas. La comprobación de acceso se realiza antes de registrar un ingreso permitido."),
    7: ([
        block("Consultar jornada y residentes asignados", [ENF], [
            "Consultar la jornada de Enfermería",
            "Mostrar residentes dentro del alcance asignado",
            "Consultar tareas y registros disponibles",
        ], [
            ("Consultar registros históricos autorizados", "Si se elige una consulta histórica permitida."),
        ]),
        block("Registrar seguimiento diario de Enfermería", [ENF], [
            "Comprobar residente asignado y jornada válida",
            "Documentar hechos y mediciones observadas",
            "Vincular fecha, autor y jornada al residente",
        ], [
            ("Registrar cambios cognitivos observables", "Si se observan cambios de memoria, atención, orientación o comunicación."),
        ]),
    ], "HU-10 + HU-15. Se distingue la consulta histórica del registro durante la jornada. El seguimiento cotidiano documenta observaciones y mediciones."),
    8: ([
        block("Registrar una atención médica", [MED], [
            "Identificar al residente y al profesional",
            "Registrar fecha y motivo de atención",
            "Documentar los hallazgos",
            "Vincular la atención a la historia clínica",
        ], [
            ("Registrar indicaciones médicas", "Si se emiten indicaciones dentro de la competencia médica."),
            ("Impedir el registro sin autorización", "Si la cuenta no está autorizada para registrar una atención médica."),
        ]),
    ], "HU-11. La atención identifica al residente, al profesional y el motivo. Las indicaciones se registran dentro de la competencia médica."),
    9: ([
        block("Registrar signos vitales", [ENF], [
            "Verificar residente y jornada autorizados",
            "Capturar y validar al menos una medición",
            "Revisar la vista previa antes de guardar",
            "Guardar el registro en el historial",
        ], [
            ("Confirmar lectura crítica", "Si la vista previa señala un valor crítico."),
        ]),
        block("Consultar historial de signos vitales", [ENF], [
            "Consultar mediciones del residente autorizado",
        ]),
    ], "HU-12. Una lectura crítica requiere confirmación antes del guardado. La vista previa permite revisar los datos."),
    10: ([
        block("Registrar prescripción médica", [MED], [
            "Verificar habilitación para prescribir",
            "Identificar al residente",
            "Registrar la indicación farmacológica",
            "Vincular la prescripción al residente",
        ], [
            ("Impedir la prescripción sin habilitación", "Si el profesional carece de atribución para prescribir."),
        ]),
        block("Consultar prescripciones del residente", [MED, ENF], [
            "Comprobar acceso al residente y su prescripción",
            "Mostrar las prescripciones registradas y permitidas",
        ]),
    ], "HU-13. Medicina y Geriatría prescriben. Enfermería participa en la consulta dentro de su alcance autorizado."),
    11: ([
        block("Consultar agenda de medicación", [ENF], [
            "Consultar prescripciones vigentes",
        ]),
        block("Registrar administración de medicamentos", [ENF], [
            "Verificar prescripción y residente",
            "Comprobar horario y jornada vigentes",
            "Registrar el resultado y el autor",
        ], [
            ("Documentar omisión del medicamento", "Si el medicamento no se administra y corresponde registrar el motivo."),
            ("Registrar evento PRN permitido", "Si corresponde un evento PRN previsto por la orden vigente."),
        ]),
    ], "HU-14. El registro deja evidencia de la administración, la omisión o el evento PRN autorizado y del motivo cuando corresponde."),
    12: ([
        block("Consultar planes e intervenciones", [ENF], [
            "Identificar el plan vigente del residente",
            "Revisar las intervenciones y acciones pendientes",
        ]),
        block("Registrar cuidados durante la jornada", [ENF], [
            "Verificar residente asignado e intervención válida",
            "Registrar la ejecución u omisión",
            "Vincular el resultado a la jornada",
        ], [
            ("Documentar el motivo de la omisión", "Si se omitió el cuidado y corresponde consignar el motivo."),
        ]),
    ], "HU-16. Enfermería revisa intervenciones vigentes y documenta su ejecución u omisión dentro de las competencias autorizadas."),
    13: ([
        block("Registrar incidente o herida", [ENF], [
            "Identificar residente, fecha y autor",
            "Describir el hecho observado",
            "Vincular el registro al historial del residente",
        ]),
        block("Registrar curación o seguimiento", [ENF], [
            "Verificar permiso específico",
            "Identificar el incidente o herida de origen",
            "Documentar la acción y conservar los registros previos",
        ], [
            ("Impedir el registro de curación o seguimiento", "Si falta el permiso específico para esa acción."),
        ]),
    ], "HU-17. El historial preserva el incidente y los registros posteriores. Las curaciones y el seguimiento requieren permisos específicos."),
    14: ([
        block("Preparar y entregar el pase de turno", [SAL], [
            "Verificar residentes de la jornada saliente",
            "Resumir estado, cuidados y medicación",
            "Identificar alertas, pendientes y registros fuente",
            "Registrar la autoría de la entrega",
        ], [
            ("Impedir incluir un residente fuera de alcance", "Si el residente no pertenece al alcance de la jornada saliente."),
        ]),
        block("Recibir y confirmar el pase de turno", [ENT], [
            "Consultar el pase entregado",
            "Registrar confirmación y autoría de la recepción",
        ]),
    ], "HU-18. El turno saliente entrega y el entrante confirma. Se conservan ambas autorías. La edición posterior queda restringida."),
    15: ([
        block("Registrar evaluación cognitiva", [PROF], [
            "Verificar instrumento y versión autorizados",
            "Identificar fecha y profesional evaluador",
            "Vincular los resultados al residente",
        ], [
            ("Impedir el uso de un instrumento no autorizado", "Si el instrumento o el punto de corte carece de autorización."),
        ]),
        block("Registrar valoración funcional", [PROF], [
            "Comprobar las competencias de la disciplina",
            "Registrar procedimiento, autor y fecha",
            "Documentar observaciones sobre autonomía",
        ]),
    ], "HU-19 + HU-20. La evaluación cognitiva conserva su instrumento y versión. La valoración funcional documenta el procedimiento y la disciplina correspondiente."),
    16: ([
        block("Consultar evolución cognitiva", [CLIN], [
            "Verificar que el residente esté autorizado",
            "Consultar evaluaciones cognitivas previas",
            "Consultar observaciones y valoraciones funcionales",
            "Mostrar los registros por fecha y fuente",
        ], [
            ("Comparar diferencias documentadas entre fechas", "Si se elige comparar registros metodológicamente compatibles."),
        ]),
    ], "HU-21. El historial presenta fuentes y fechas. La consulta respeta la compatibilidad de los instrumentos y los límites para inferir progresión."),
    17: ([
        block("Consultar y coordinar alertas institucionales", [ADM], [
            "Verificar alcance administrativo",
            "Consultar la alerta y su origen",
            "Registrar la coordinación permitida",
        ], [
            ("Cerrar alerta con permiso administrativo", "Si corresponde el cierre y Administración tiene el permiso."),
        ]),
        block("Consultar y atender alertas de Enfermería", [ENF], [
            "Verificar residente y jornada de su alcance",
            "Registrar reconocimiento o intervención",
            "Conservar responsable y cambios de estado",
        ], [
            ("Cerrar alerta con permiso de Enfermería", "Si corresponde el cierre y Enfermería tiene el permiso."),
        ]),
    ], "HU-22. Administración coordina. Enfermería interviene dentro de su jornada y asignación. El cierre depende del permiso vigente."),
    18: ([
        block("Consultar información del residente", [FAM], [
            "Identificar al residente vinculado",
            "Verificar el vínculo familiar",
            "Comprobar el permiso vigente",
            "Mostrar solo la información autorizada",
        ], [
            ("Denegar la consulta familiar", "Si el vínculo o el permiso se encuentra ausente o inactivo."),
        ]),
    ], "HU-23. El familiar solo consulta información expresamente habilitada. El acceso al expediente clínico permanece reservado."),
    19: ([
        block("Consultar conocimiento experto", [SUPER], [
            "Consultar conceptos y relaciones versionados",
            "Consultar reglas y fuentes documentadas",
            "Revisar el estado de aprobación",
        ]),
        block("Ejecutar un escenario de prueba", [TEC], [
            "Verificar que el entorno de pruebas esté controlado",
            "Comprobar que los datos sean sintéticos",
            "Conservar evidencias y trazas técnicas",
        ], [
            ("Impedir una prueba fuera de las condiciones admitidas", "Si el entorno no es controlado o los datos no son sintéticos."),
        ]),
        block("Revisar evidencias y trazas del motor", [TEC], [
            "Consultar los resultados del escenario ejecutado",
        ]),
    ], "HU-24. El superadministrador consulta el conocimiento. El responsable técnico ejecuta y revisa pruebas con datos sintéticos en un entorno controlado."),
    20: ([
        block("Consultar resultados expertos", [MED, ENF], [
            "Verificar permiso y residente autorizado",
            "Consultar los resultados técnicos disponibles",
            "Revisar las evidencias relacionadas",
            "Consultar las reglas que sustentan el resultado",
        ], [
            ("Consultar el historial del resultado", "Si existe historial disponible y se solicita revisarlo."),
            ("Denegar la consulta de resultados", "Si el permiso o el alcance sobre el residente no están autorizados."),
        ]),
    ], "HU-25. La consulta se limita al alcance profesional. Los resultados son técnicos y apoyan la revisión profesional."),
}


GROUPING = {
    3: "HU-03 organiza la planificación de personal y HU-04 concreta esa planificación en jornadas y asignaciones.",
    4: "HU-05 documenta la decisión de preadmisión y HU-06 confirma el ingreso cuando se cumplen las condiciones.",
    5: "HU-07 mantiene el expediente administrativo y HU-08 documenta el alojamiento del mismo residente.",
    7: "HU-10 delimita la jornada y los residentes asignados y HU-15 registra observaciones dentro de ese alcance.",
    15: "HU-19 conserva evaluaciones cognitivas y HU-20 añade valoraciones funcionales con sus propias reglas.",
}
