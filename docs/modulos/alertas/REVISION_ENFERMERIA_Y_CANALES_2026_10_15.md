# Alertas de Enfermería: revisión funcional y canales

Fecha: 2026-10-15. Implementación técnica y propuesta institucional separadas.
La propietaria indicó que el destinatario depende del tipo de notificación. Esto
autoriza preparar la matriz, no enviar datos ni aprobar nuevas reglas clínicas.

## Resultado implementado

- Prioridad antes de paginar o limitar: activas primero, críticas, altas, medias
  y bajas; antigüedad como desempate. CRITICO/CRITICA comparten representación.
- Bloque superior persistente para críticas/altas del ámbito autorizado, aun
  cuando el filtro de la lista muestra cerradas. Tarjetas por caso con motivo,
  residente, ubicación vigente, responsable, fecha completa y acciones legibles.
- Resumen con filtros mediante botones; gráficas secundarias desplegables.
  Se retiraron minigráficas con cifras derivadas artificialmente del contador.
- Campana funcional en la barra de Enfermería. Primera novedad desde una lista
  vacía, novedades agrupadas y cola visible; pasar de próxima a vencida genera
  un nuevo aviso para esa dosis. La carga inicial muestra pendientes sin una
  ráfaga de popups. El navegador conserva la deduplicación solo en esa sesión.
- Recibir, revisar u ocultar el aviso no reconoce/cierra la alerta ni registra
  administración. Atención y cierre exigen texto real y pasan por el escritor
  común con autorización, transacción, estado anterior real y evento.
- Cuenta inactiva/familiares excluidos; consultas según permiso y ámbito.
  El enlace de profesionales abre la pantalla, no el endpoint JSON.
- Una administración anulada no satisface una dosis ni genera una alerta de
  omisión desde el detector. RECHAZADA conserva su resultado en la agenda.
- Horas calculadas por el servidor, con fecha y zona institucional. Se conservan
  horarios de la orden y la anticipación configurada existente; no se prescribe,
  cambia la orden ni inventan ventanas terapéuticas.

No hubo cambios de tablas, columnas, catálogos, permisos ni umbrales clínicos.

## Qué debe distinguir el proceso

| Concepto | Evidencia | Lo que no demuestra |
|---|---|---|
| Registro clínico confirmado | Registro del módulo de origen | Que alguien haya visto el aviso |
| Alerta | Condición, prioridad y estado persistidos | Diagnóstico universal ni prescripción |
| Notificación | Aviso de una novedad o pendiente | Atención, respuesta clínica o dosis administrada |
| Intervención | Texto, autor, fecha y evento guardados | Resolución automática de la condición |
| Cierre | Resultado documentado y transición autorizada | Borrado del historial |

La UI representa la prioridad recibida del dominio. Un horario vencido es un
pendiente que requiere comprobar la orden y el registro; por sí solo no acredita
emergencia ni autoriza repetir una dosis. La campana lo etiqueta PROXIMA/VENCIDA,
sin convertirlo en una nueva gravedad clínica.

## Matriz propuesta de destinatarios (pendiente de aprobación)

No modifica competencias ni concede acceso. Una persona solo recibe información
de residentes para los que su función, permiso y relación vigentes lo autorizan.

| Tipo de aviso | Destinatario principal propuesto | Derivación propuesta | Contenido externo mínimo |
|---|---|---|---|
| Medicación programada/próxima | Enfermero asignado a ese residente y jornada | Suplente autorizado ante ausencia, según protocolo aprobado | Pendiente de medicación, hora institucional, enlace autenticado |
| Horario vencido sin registro válido | Enfermero asignado | Coordinación de Enfermería si no hay cobertura; criterio/plazo por aprobar | Pendiente de comprobar, hora programada, enlace |
| Omisión o rechazo documentado | Enfermero responsable; profesional médico cuando el protocolo lo requiera | Coordinación para asegurar continuidad | Solicitud de revisión del registro, hora, enlace |
| Alerta clínica crítica ya clasificada | Enfermero responsable y profesional médico responsable/de guardia definido institucionalmente | Cadena urgente institucional aprobada | Alerta prioritaria, hora del evento, enlace; sin datos clínicos en WhatsApp |
| Alerta alta / deterioro comunicado | Enfermero responsable | Profesional responsable según origen y protocolo | Revisión prioritaria, hora, enlace |
| Caída, lesión u otro incidente | Enfermero responsable | Médico según necesidad establecida; coordinación para gestión del incidente | Incidente pendiente de revisión, hora, enlace |
| Seguimiento cognitivo, nutrición o bienestar | Profesional competente y enfermero si tiene una tarea asignada | Equipo interdisciplinario autorizado según plan | Pendiente del plan, fecha/hora, enlace |
| Cuidado pendiente / pase de turno | Enfermero asignado, siguiente responsable al relevar | Coordinación si no existe responsable | Pendiente asistencial, hora, enlace |
| Falta de cobertura/asignación | Coordinación de Enfermería / gestión de jornadas | Responsable institucional autorizado | Problema de cobertura, jornada, enlace sin datos clínicos |
| Comunicación familiar | Contacto vinculado y autorizado, solo tras decisión profesional/institucional | Responsable de comunicación definido por la institución | Información aprobada para esa persona; nunca copia de alertas internas |

Gerencia/Administración pueden requerir avisos operativos de cobertura; esto no
las convierte en prescriptores ni receptores automáticos de detalles clínicos.
No se proponen grupos masivos, todos los teléfonos del personal ni familiares
como destino automático de alertas clínicas.

Para aprobar la matriz faltan: cargos concretos de guardia/suplencia, qué eventos
exigen comunicación médica, plazo y repetición por categoría, relevo, y quién
autoriza comunicar a familiares. Una urgencia requiere el protocolo presencial
o telefónico institucional; WhatsApp sería complementario.

## Horario exacto y entrega: límites actuales

La fecha/hora programada se muestra completa, con zona. Panel: revisión cada
30 segundos; campana: cada 12 segundos mientras la pantalla está activa.
Suspensión del navegador, desconexión, sesión vencida o pantalla cerrada impiden
garantizar entrega en el segundo programado. No existe aún una cola de envíos
clínicos por servidor ni constancia persistida de entrega/lectura externa.

La agenda actual consulta el día en curso. Al cambiar de fecha, las dosis sin
registro del día anterior dejan de aparecer en ese recordatorio. **Pendiente
institucional prioritario:** definir continuidad entre jornadas y cuánto tiempo
debe seguir visible una dosis, sin generar retrospectivamente órdenes o dosis.
Este cambio no introduce una ventana retrospectiva por inferencia.

## WhatsApp: diseño preparado, integración no activada

Propuesta: canal oficial WhatsApp Business con plantillas aprobadas, números
individuales autorizados y contenido mínimo. Ejemplo ficticio:

> RememberMind: tienes un pendiente de medicación programado para 15/10/2026
> 10:00, hora de Bolivia. Revisa la orden y el registro dentro del sistema.

El mensaje no incluiría nombre, documento, diagnóstico, imagen ni dosis. El
enlace debe requerir autenticación y autorización vigentes; no debe contener
credenciales, datos clínicos o acceso público. No se ha creado un enlace externo
funcional ni se ha enviado este ejemplo.

Antes de activar: aprobar categorías/destinatarios/plantillas; verificar números,
consentimiento u otra base aplicable y preferencias del receptor; evaluar
privacidad y proveedor/costos; configurar credenciales fuera del código.
La política oficial exige plantillas aprobadas para conversaciones iniciadas
fuera de la ventana de atención de 24 horas y respeto de las preferencias del
destinatario. Su política sobre datos de salud debe revisarse para el uso y la
jurisdicción concretos; esta revisión no certifica cumplimiento legal boliviano.

Diseño técnico posterior: ejecución en servidor, identidad estable del evento y
destinatario, revalidación de permiso/asignación antes del envío, reintentos
acotados e idempotencia, webhooks autenticados y trazabilidad mínima. Entregado
o leído nunca equivale a intervención clínica. Cualquier persistencia nueva
requiere revisar el esquema congelado y aprobación explícita si lo extiende.
No se instaló proveedor, contrató servicio ni creó tabla de notificaciones.

## Hallazgos que permanecen aislados

1. Continuidad nocturna descrita arriba; necesita decisión institucional.
2. Las preventivas pueden recrearse tras cerrar una condición que persiste.
   Debe acordarse identidad del episodio, reevaluación y cierre por origen antes
   de aumentar avisos. No se ha habilitado repetición/escalamiento automático.
3. Contrato completo de estados pendiente DEC-OPEN-002 del módulo. La lectura
   incluye estados pendientes existentes sin imponer transiciones nuevas.
4. Entrada HTTP de creación permite referencia lógica modulo/cod_registro sin
   comprobar coherencia con el residente. Hallazgo previo de integridad fuera
   del escritor de atención corregido; requiere reparación y pruebas específicas.
5. No se certificó el motor de detección completo, todas sus reglas ni todos los
   orígenes. No se ha validado clínicamente el módulo mediante pruebas técnicas.

## Evidencia y fuentes

- Contrato: [módulo Alertas](README.md), AGENTS aplicables, baseline y arquitectura
  institucional. El contrato histórico contiene rutas anteriores a la arquitectura
  híbrida; implementación actual bajo `app/Modulos/Alertas`.
- Pruebas: `AlertasFlujoTest`, `PanelAlertaPreparadoTest`, `AgendaMedicacionTest`,
  `Fase3AlertasTest`, `FrontendArchitectureTest`; cobertura de prioridad antes de
  paginar, primera novedad, transición a vencida, anulaciones, eventos y límites
  de cuenta/familia. SQLite en memoria; no prueba de concurrencia PostgreSQL.
- Revisión visual inicial independiente: detectó contraste insuficiente de las
  franjas críticas; se corrigió. Revisión final del implementador: 20 escenarios
  de tarjetas/campana/cola y 60 de exports Blade del panel/detalle/atención, sin
  errores JS ni desbordamientos y con áreas táctiles de 44 px. Dimensiones
  1440, 1280, 1024, 768 y 390, temas claro/oscuro, en ambos builds. Alpine real;
  transporte Livewire sustituido únicamente en fixtures visuales. Persistencia
  y autorización se prueban con Livewire/PHP, no con esos fixtures. La cola fue
  probada recibiendo un aviso medio antes de uno crítico: el crítico pasa primero.
- Ejecución final: 62 pruebas, 797 aserciones; nueva comprobación de deduplicación
  y texto contextual: 23 pruebas, 109 aserciones. Build Vite correcto en la rama
  y en el checkout que sirve localhost. `git diff --check` correcto.
- Revisión independiente de seguridad: PASS estático acotado a atención/cierre
  compartidos y campana. No certifica la entrada HTTP ni todo el módulo. No se
  ejecutaron pruebas nuevas de concurrencia PostgreSQL ni entrega de WhatsApp.
- [OMS: Medication Without Harm](https://www.who.int/initiatives/medication-without-harm):
  referencia para considerar sistemas, factores humanos y transiciones de cuidados.
- [NICE SC1, recomendaciones](https://www.nice.org.uk/guidance/sc1/chapter/Recommendations)
  y [registro de medicamentos](https://www.nice.org.uk/guidance/sc1/ifp/chapter/Keeping-records):
  referencia para documentar administración/rechazo y compartir información
  pertinente durante cambios de atención. No se adoptaron nuevos plazos locales.
- [OMS: ICOPE, segunda edición](https://www.who.int/publications/i/item/9789240103726):
  referencia para atención centrada en la persona y continuidad del plan.
- [WhatsApp Business Messaging Policy](https://whatsappbusiness.com/policy/):
  condiciones del canal; verificar de nuevo antes de contratar y activar.

Las fuentes externas orientan el análisis; no aprueban protocolos de RememberMind.
