# Decisiones pendientes de aprobación

El diseño propone una solución concreta. Estas decisiones validan semántica institucional, datos y operación; no autorizan implementar durante esta tarea. No se necesita elegir Vue/Inertia: **Blade + Livewire está decidido por el usuario**. PostgreSQL, monolito modular y ausencia de paquetes nuevos también son restricciones fijadas.

| ID | Decisión | Recomendación de diseño | Responsable que debe aprobar | Bloquea |
|---|---|---|---|---|
| D01 | ERD y alcance | Aprobar 83 tablas y 14 módulos de 03/05; claves bigint técnicas y código visible preservado, 15 contratos técnicos conservados | Responsable de producto + técnico | Nuevas migraciones/modelos |
| D02 | Unificación de personas | Usar enlaces explícitos/documento verificado; cuarentena de colisiones, nunca nombre solo; admitir contacto/profesional sin User | Responsable de datos/registro | Importación de identidad |
| D03 | Estados, estancias y ocupación histórica | Separar expediente/caso/estancia/condición; un caso→máximo una estancia; reingreso nuevo caso; resolver fechas/camas incompatibles sin inventar | Admisiones + coordinación asistencial | Importación y alta/traslado |
| D04 | Autoridad y acceso excepcional | Eliminar bypass clínico de superadmin; 16 plantillas de 07 combinables, scope contextual. No acceso de emergencia sin protocolo auditable acordado | Dirección + seguridad + responsable clínico | Policies y permisos definitivos |
| D05 | Profesión, especialidad y prescripción | Acreditación independiente de rol/cargo; definir quién puede prescribir, administrar, valorar, validar y delegar en la institución | Dirección clínica + RRHH | Firma clínica/medicación/VGI |
| D06 | Firma, corrección y conservación clínica | Hechos firmados append-only con supersedes; anulación con motivo, sin borrado. Acordar significado de firma electrónica interna y plazos de retención | Dirección clínica + responsable normativo | Registros clínicos y purgas |
| D07 | VGI y reglas clínicas | Validar versiones/formularios/puntos de corte de los 26 instrumentos + Barthel/ingreso; conservar legacy sin recalificar; revisar derechos de uso | Equipo geriátrico | Publicación de instrumentos |
| D08 | Pautas/dosis y correcciones | Separar prescripción/línea/dosis; conciliación de texto ambiguo, PRN y ventanas de administración; corrección nunca redosifica | Medicina + enfermería | Agenda y administración |
| D09 | Consentimiento y portal familiar | Vínculo vigente + consentimiento validado + campos expresamente compartibles; booleano legado no acredita firma | Dirección + trabajo social + privacidad | Portal/documentos familiares |
| D10 | Horarios y capacidad | Aprobar reglas de descanso/cobertura/rotación, plazas y reemplazos; capacidad autorizada de habitación distinta de camas habilitadas | RRHH + coordinación de turnos | Planilla y ocupación |
| D11 | Documentos y entregas | Disco privado/versiones/hash, plazos48h/vencimiento por tipo; definir destinatario permitido, validador, entrega y firma | Registro + privacidad | Migración archivos y correo |
| D12 | Funciones anunciadas sin backend | Completar visitas/ficha social/portales, fisioterapia/nutrición sobre módulos comunes. Retirar enlace estado-cuenta hasta aprobar alcance financiero independiente | Producto + dirección | Aceptación de navegación; facturación NO incluida por defecto |
| D13 | Sistema experto | Apoyo determinista explicable con versión/input congelados y revisión humana; no diagnóstico/prescripción autónoma; reglas concretas y validación antes de uso | Dirección clínica + producto | Motor experto operativo |
| D14 | Migración, retención y recuperación | Snapshot autorizado, ensayos aislados, cero cuarentena crítica al corte, ventana y rollback/roll-forward medidos; retención de procedencia cifrada | Operaciones + responsable de datos | Cualquier corte o retiro de tablas |
| D15 | Carga, soporte y aceptación | Fijar volumen representativo, tiempos máximos por flujo/exportación, responsables y evidencias de aceptación por los 14 módulos | Producto + operaciones | Publicación final |

## Supuestos explícitos hasta aprobación

Un centro inicial, con institutions para identidad institucional y zona horaria; no promesa de multitenencia aislada entre sedes. Cada persona tiene a lo sumo una cuenta activa estructural (U(person_id)); puede ser residente, contacto, profesional y voluntario sin duplicarse. Múltiples acreditaciones y vínculos laborales posibles. Los intervalos y actor son requeridos para operación nueva; en legado incompleto se marca incertidumbre sin habilitar automatismos.

No se han determinado plazos legales, habilitaciones profesionales ni instrumentos clínicos válidos por esta auditoría de código. La aprobación correspondiente define esos parámetros; no es consejo jurídico/médico ni dato obtenido de la BD. No se agregan tablas de inventario/farmacia/facturación sin una necesidad implementada o alcance aprobado.

## Decisiones técnicas ya resueltas en esta propuesta

Roles Spatie aditivos y Policies contextuales; no wildcard de superadmin clínico. Persona separada de cuenta. Historial por versiones/hechos, no Event Sourcing. Cama a través de asignación de estancia. JSONB limitado a formulario/regla/snapshot validado. Catálogos documentales y geríatricos distintos de organización. El sistema experto no reemplaza datos originales. Acciones y Queries solo donde tienen reglas/reutilización. La decisión pendiente es aceptación del diseño y reglas de producto, no una lista abierta de arquitecturas alternativas.

## Preguntas de datos para el ensayo (no requeridas para crear esta documentación)

Qué fuente resuelve un documento de identidad conflictivo; cómo cerrar ocupación sin fecha de fin; qué campos de bitácora médica son evidencia recuperable; cuántos binarios faltan; qué pautas no son parseables; qué consentimientos tienen respaldo; cuáles roles actuales están sobredimensionados. El ensayo debe producir lista de casos, resolución y responsable. Ninguna se resuelve copiando defaults de formularios o seeders de demostración.

