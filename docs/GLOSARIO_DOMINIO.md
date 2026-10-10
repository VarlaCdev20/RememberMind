---
title: "Glosario de dominio de RememberMind"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: true
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

# Glosario de dominio

Definiciones operativas del proyecto; no diccionario médico ni aprobación de reglas clínicas. Consultar [AGENTS](../AGENTS.md), [baseline](base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y [diccionario](base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md). Los valores de estados pendientes no se resuelven desde este glosario.

## POSTULANTE

**Definición:** Persona todavía en proceso de preadmisión, también adulto mayor en preadmisión.

**Entidad relacionada:** preadmisiones; no implica tabla postulantes.

**Uso correcto:** Solicitar revisión antes de admisión formal.

**No confundir con:** residente.

**Ejemplo:** Postulante con solicitud PENDIENTE.

**Fuente:** AGENTS.md §V2 terminology; baseline §6.

## RESIDENTE

**Definición:** Persona formalmente admitida; entidad central operativa.

**Entidad relacionada:** residentes / cod_residente.

**Uso correcto:** Identificar registro clínico por cod_residente.

**No confundir con:** postulante, usuario o contacto.

**Ejemplo:** Residente creado en formalización con cama.

**Fuente:** AGENTS.md §Mandatory institutional flow; baseline §6.

## USUARIO

**Definición:** Cuenta de acceso autenticada por correo; no identidad profesional/familiar completa.

**Entidad relacionada:** usuarios / cod_usuario.

**Uso correcto:** Verificar cuenta ACTIVO y permisos.

**No confundir con:** personal o contacto.

**Ejemplo:** Usuario vinculado a personal autorizado.

**Fuente:** AGENTS.md §Authorization; diccionario usuarios.

## PERSONAL

**Definición:** Trabajador/profesional, opcionalmente vinculado a una cuenta.

**Entidad relacionada:** personal / cod_personal.

**Uso correcto:** Atribuir autoría clínica al profesional real.

**No confundir con:** usuario o contacto.

**Ejemplo:** Enfermero activo asociado al usuario autenticado.

**Fuente:** AGENTS.md §V2 terminology; decisión autoría V2.1.

## CONTACTO

**Definición:** Persona familiar, responsable o relacionada con el residente.

**Entidad relacionada:** contactos; residentes_contactos.

**Uso correcto:** Consultar vínculo y autorización informativa.

**No confundir con:** usuario o residente.

**Ejemplo:** Contacto A vinculado al residente B.

**Fuente:** AGENTS.md §V2 terminology; diccionario contactos.

## PREADMISIÓN

**Definición:** Solicitud y revisión anteriores a admisión formal.

**Entidad relacionada:** preadmisiones.

**Uso correcto:** Aprobar/rechazar sin crear residente.

**No confundir con:** admisión formal.

**Ejemplo:** Aprobación pendiente de formalización.

**Fuente:** AGENTS.md §Mandatory institutional flow; baseline §6.

## ADMISIÓN

**Definición:** Operación formal que crea residente y relaciones institucionales requeridas, con cama.

**Entidad relacionada:** admisiones; FormalizarAdmision.

**Uso correcto:** Formalizar atómicamente desde preadmisión aprobada.

**No confundir con:** mera aprobación.

**Ejemplo:** Formalización con cama disponible.

**Fuente:** baseline §6; Action FormalizarAdmision.

## OCUPACIÓN

**Definición:** Vínculo de uso de una cama por un residente durante un intervalo/estado aprobado.

**Entidad relacionada:** ocupaciones_cama.

**Uso correcto:** Comprobar una ocupación activa por cama y residente.

**No confundir con:** cama libre mostrada en UI.

**Ejemplo:** Cama no disponible mientras tenga ocupación activa.

**Fuente:** baseline §13; diccionario ocupaciones.

## CAMA

**Definición:** Recurso de alojamiento relacionado con habitación.

**Entidad relacionada:** camas.

**Uso correcto:** Elegir disponibilidad con garantías backend.

**No confundir con:** ocupación o residente.

**Ejemplo:** Cama existente validada al formalizar.

**Fuente:** diccionario camas; baseline §13.

## JORNADA

**Definición:** Contexto laboral/operativo según el modelo y vigencia definidos.

**Entidad relacionada:** jornadas.

**Uso correcto:** Usar contexto vigente cuando la operación lo exige.

**No confundir con:** turno maestro o autoría profesional.

**Ejemplo:** Registro durante jornada permitida.

**Fuente:** baseline jornada/contexto; diccionario jornadas.

## ASIGNACIÓN

**Definición:** Relación aprobada de personal/residente/contexto operativo; distinguir su tipo real.

**Entidad relacionada:** asignaciones_personal y asignaciones_residente_jornada.

**Uso correcto:** Determinar cobertura/alcance desde relación vigente.

**No confundir con:** cuenta de acceso o competencia por sí sola.

**Ejemplo:** Responsable de área mostrado desde asignaciones.

**Fuente:** diccionario asignaciones; ContextoLaboralService.

## ATENCIÓN

**Definición:** Encuentro/acto clínico contextual del residente según contrato de disciplina.

**Entidad relacionada:** atenciones.

**Uso correcto:** Relacionar registro con atención del mismo residente.

**No confundir con:** nota aislada o turno.

**Ejemplo:** Nota asociada a atención del residente A.

**Fuente:** diccionario atenciones; app/AGENTS.

## DIAGNÓSTICO

**Definición:** Registro de diagnóstico en el contrato clínico del residente, por competencia autorizada.

**Entidad relacionada:** diagnosticos.

**Uso correcto:** Registrar/consultar diagnóstico en su flujo aprobado.

**No confundir con:** riesgo experto o control cognitivo.

**Ejemplo:** Diagnóstico vinculado al residente y profesional permitido.

**Fuente:** diccionario diagnósticos; app/AGENTS.

## INDICACIÓN

**Definición:** Instrucción clínica registrada por el profesional competente según modelo vigente.

**Entidad relacionada:** indicaciones_clinicas.

**Uso correcto:** Conservar procedencia/contexto; no extraer umbrales automáticos de texto.

**No confundir con:** prescripción estructurada o objetivo individual.

**Ejemplo:** Indicación textual que no reemplaza objetivo versionado.

**Fuente:** diccionario indicaciones; decisión objetivos V2.2.

## PRESCRIPCIÓN

**Definición:** Orden de medicación del profesional autorizado; distinta del hecho de administrar.

**Entidad relacionada:** prescripciones.

**Uso correcto:** Mantener residente, vigencia y competencia prescriptora.

**No confundir con:** medicamento o administración.

**Ejemplo:** Médico prescribe; Enfermería administra.

**Fuente:** baseline §14; AGENTS §Authorization.

## HORARIO DE PRESCRIPCIÓN

**Definición:** Programación temporal ligada a una prescripción según contrato.

**Entidad relacionada:** horarios_prescripcion.

**Uso correcto:** Validar pertenencia a la prescripción administrada.

**No confundir con:** administración ya realizada.

**Ejemplo:** Horario de prescripción A no se usa para B.

**Fuente:** baseline §14; diccionario horarios.

## ADMINISTRACIÓN DE MEDICACIÓN

**Definición:** Registro de lo ocurrido respecto de una prescripción/horario cuando aplica.

**Entidad relacionada:** administraciones_medicacion.

**Uso correcto:** Distinguir resultado administrada/omitida de estado observado.

**No confundir con:** orden médica.

**Ejemplo:** Omisión queda documentada sin cambiar prescripción.

**Fuente:** baseline §14; RegistrarAdministracionMedicacionService.

## PLAN DE CUIDADO

**Definición:** Plan del residente que organiza intervenciones según contrato vigente.

**Entidad relacionada:** planes_cuidado.

**Uso correcto:** Relacionar intervención/ejecución con plan correspondiente.

**No confundir con:** ejecución o nueva entidad tarea.

**Ejemplo:** Plan vigente contiene intervenciones.

**Fuente:** baseline cuidados; diccionario planes.

## INTERVENCIÓN

**Definición:** Componente del plan que define cuidado/actuación previsto según modelo aprobado.

**Entidad relacionada:** intervenciones_cuidado.

**Uso correcto:** Distinguir qué se debe hacer del hecho realizado.

**No confundir con:** programación o ejecución.

**Ejemplo:** Intervención se programa antes de ejecutar.

**Fuente:** baseline cuidados; diccionario intervenciones.

## PROGRAMACIÓN

**Definición:** Previsión temporal de una intervención/cuidado.

**Entidad relacionada:** programaciones_cuidado.

**Uso correcto:** Consultar cuándo corresponde sin afirmar realización.

**No confundir con:** ejecución.

**Ejemplo:** Cuidado programado todavía pendiente.

**Fuente:** baseline cuidados; diccionario programaciones.

## EJECUCIÓN

**Definición:** Registro de lo ocurrido al realizar/documentar un cuidado conforme contrato.

**Entidad relacionada:** ejecuciones_cuidado.

**Uso correcto:** Preservar resultado/autor/residente y omisión cuando existe.

**No confundir con:** programación.

**Ejemplo:** Programación no constituye ejecución.

**Fuente:** baseline cuidados; diccionario ejecuciones.

## INCIDENTE

**Definición:** Hecho de cuidado/residencia registrado en el dominio y flujo aprobados.

**Entidad relacionada:** incidentes.

**Uso correcto:** Documentar hecho y continuidad sin inventar gravedad.

**No confundir con:** alerta automática o diagnóstico.

**Ejemplo:** Incidente registrado se consulta en pase según alcance.

**Fuente:** diccionario incidentes; IncidentesEnfermeriaService.

## ALERTA

**Definición:** Situación gestionable generada por condición y flujo autorizados.

**Entidad relacionada:** alertas.

**Uso correcto:** Gestionar transición y responsable competente.

**No confundir con:** notificación o audit log.

**Ejemplo:** Alerta abierta conserva eventos.

**Fuente:** baseline alertas; AlertaController.

## EVENTO DE ALERTA

**Definición:** Registro de un hecho/cambio dentro del ciclo de vida de una alerta.

**Entidad relacionada:** eventos_alerta.

**Uso correcto:** Preservar trayectoria aun cuando cambie estado actual.

**No confundir con:** estado único sobrescribible.

**Ejemplo:** Cierre no elimina eventos anteriores.

**Fuente:** baseline alertas; diccionario eventos.

## INSTRUMENTO

**Definición:** Definición versionada de aplicación con preguntas/opciones según modelo.

**Entidad relacionada:** instrumentos / preguntas_instrumento / opciones_pregunta.

**Uso correcto:** Confirmar versión, método y derechos antes de contenido real.

**No confundir con:** motor experto o aplicación individual.

**Ejemplo:** Instrumento sintético en prueba técnica.

**Fuente:** diccionario instrumentos; tests/AGENTS.

## APLICACIÓN DE INSTRUMENTO

**Definición:** Aplicación concreta al residente con contexto/respuestas coherentes.

**Entidad relacionada:** aplicaciones_instrumento / respuestas_instrumento.

**Uso correcto:** Validar pregunta del instrumento y opción de pregunta.

**No confundir con:** definición de instrumento.

**Ejemplo:** Respuesta ajena al instrumento se rechaza.

**Fuente:** diccionario aplicaciones/respuestas; InstrumentoController.

## VALORACIÓN

**Definición:** Proceso/registro profesional específico, no sinónimo de todo expediente.

**Entidad relacionada:** valoraciones_psicologicas, valoraciones_nutricionales, valoraciones_funcionales o valoraciones_enfermeria_preadmision según proceso.

**Uso correcto:** Identificar disciplina, contexto y grano concreto.

**No confundir con:** diagnóstico automático o score universal.

**Ejemplo:** Valoración de preadmisión de Enfermería normalizada.

**Fuente:** diccionario valoraciones; decisión V2.1.

## SEGUIMIENTO

**Definición:** Continuidad del hecho/intervención en consultas y registros aprobados.

**Entidad relacionada:** registros longitudinales, planes/alertas/pase según caso.

**Uso correcto:** Indicar origen, próximo uso y profesional permitido.

**No confundir con:** una tabla universal seguimiento.

**Ejemplo:** Pendiente llega al próximo punto operativo autorizado.

**Fuente:** baseline longitudinalidad; PaseTurnoService.

## DOCUMENTO ADMINISTRATIVO

**Definición:** Documento del proceso institucional, con categoría y acceso del contrato.

**Entidad relacionada:** documentos y entidades pertinentes.

**Uso correcto:** Distinguir contenido administrativo de clínico para autorización.

**No confundir con:** expediente clínico compartible por defecto.

**Ejemplo:** Documento de admisión con acceso autorizado.

**Fuente:** diccionario documentos; DocumentacionResidenteService.

## DOCUMENTO CLÍNICO

**Definición:** Archivo/documento de contexto clínico y procedencia aprobada.

**Entidad relacionada:** documentos_clinicos.

**Uso correcto:** Storage privado, metadata/hash y descarga reautorizada.

**No confundir con:** log técnico o archivo público.

**Ejemplo:** Informe accesible solo al actor competente autorizado.

**Fuente:** diccionario documentos clínicos; app/AGENTS.

## Distinciones transversales

- Autor clínico: personal responsable del hecho. Actor técnico: usuario que opera cuando el modelo los distingue.
- Medicamento → qué existe; prescripción → orden; horario → cuándo corresponde; administración → hecho ocurrido.
- Plan → objetivo/contexto; intervención → qué hacer; programación → cuándo; ejecución → qué ocurrió.
- Alerta y evento son dominio; notificación distribuye; Activitylog audita técnicamente. Ninguno sustituye al otro.
- Significado/severidad proceden del contrato/backend aprobado; color o ausencia de alertas no acreditan estabilidad.
- Las referencias a instrumentos no autorizan copiar contenido protegido o inventar puntajes.
