# Contratos de prueba por riesgo

Consultar tests/AGENTS y el contrato concreto antes de seleccionar casos. Tabla de riesgos, no certificado de que estas pruebas existan o hayan pasado.

| Riesgo | Antes / Acción / Después esperado | Efectos prohibidos |
|---|---|---|
| Aprobación preadmisión | Pendiente → decisión válida → aprobada/rechazada según contrato | Crear residente/ocupación durante mera aprobación |
| Admisión formal | Aprobada + cama libre + requisitos vigentes → formalizar → agregado completo | Crear sin aprobación o con cama ocupada; dos ocupaciones activas |
| Atomicidad | Snapshot → fallo controlado tras primera escritura → rollback | Residente/admisión/ocupación/evento/audit de éxito parciales |
| Carrera por cama | Dos transacciones independientes compiten de forma sincronizada → resultado conforme contrato | Ambas ocupaciones activas; deadlock/exception capturado como éxito |
| Cuenta/permiso | Actor inactivo/sin permiso → entrada real → denegación | Cambios de dominio, alertas, audit de éxito, fuga de información |
| Competencia/autoría | Actor sin competencia o input de otro personal → mutación → denegación | Registro atribuido a tercero; fallback a primer profesional |
| Familia/IDOR | Vínculo activo autorizado o inexistente → URL/ID directo y contenido solicitado | Expediente/export/archivo no publicado; residente ajeno |
| Medicación | Prescripción de A, horario de B o administración con residente distinto → rechazo | Cambiar orden, administrar inconsistente; borrar omisión |
| Historia | Registro original → nuevo hecho/corrección aprobada → trayectoria reconstructible | Sobreescritura para ocultar original o borrado ordinario |
| Instrumentos | Aplicación y pregunta/opción sintéticas ajenas → rechazo | Puntuación/resultado incoherente; uso de reactivos protegidos |
| Estudios/consentimiento | Componente del tipo/contacto del residente distintos → rechazo | Resultado/firma cruzados; duplicado fuera de contrato |
| Plan/ejecución | Programación vigente → ejecución/omisión válida → persistencia coherente | Dato de otro plan/residente; programado marcado realizado |
| Preview/alerta | Entrada preview → evaluar → sin efectos; luego confirmación → efectos exigidos | Alerta/evento desde preview; confirmación sin evento exigido |
| Ciclo alerta | Estado permitido → transición por competente → estado + evento históricos | Transición prohibida, pérdida de eventos, notificación como prueba de intervención |
| Continuidad | Hecho/omisión en turno A → consulta posterior por B autorizado | Desaparición del pendiente; acceso del receptor no autorizado |
| Reporte | Filtros/fecha/actor → consulta/export → conjunto autorizado reproducible | Mutación de fuente, overfetch familiar, orden/paginación inconsistentes |

## Matriz del encargo

Registrar por caso: requisito y fuente, categoría(s), setup sintético, acción real, antes/después, efectos permitidos/prohibidos, motor y evidencia. Usar UNIT para lógica aislable; FEATURE para HTTP/Livewire real; INTEGRATION para colaboradores/DB; AUTHORIZATION, WORKFLOW, REGRESSION para propiedades pertinentes.

Snapshots/assertions proporcionales: conteo/identidad/campos de entidades afectadas y eventos; no snapshots masivos del expediente. Un HTTP 403 no acredita efectos cero. Para lectura, verificar también contenido ausente.

Fakes de mail/notificación/storage/jobs cuando son periféricos; no mockear Action/Policy/DB central para afirmar integración. FreezeTime/travel; no sleep para arreglar carreras. Fallo controlado con seam existente o transacción/test específico que ejecute operación real; no nueva interfaz sin necesidad.

## Entornos y evidencia

SQLite :memory: acredita casos rápidos que se ejecutaron. PostgreSQL acredita constraints, queries y bloqueos específicos solo si los tests se ejecutaron allí. Configuración de CI no acredita su resultado.

Carreras: DB desechable PostgreSQL, conexiones/procesos independientes, barrera/sincronización controlada, resultado y postcondiciones de ambos; secuencia de requests no es concurrencia. Confirmar entorno antes de cualquier reset; nunca contra DB desconocida.

Comandos habituales, seleccionar por alcance:
- `php artisan test --filter=<Caso>`
- `php artisan test <ruta>`
- `composer test`
- `npm run build` solo cuando aplica frontend; no prueba UX.

No incluir passwords/DSN/secretos en reporte de entorno. Informar ejecutado/no ejecutado, motivo y comando pendiente. No llamar clínicamente validado a un motor probado con fixtures sintéticas.
