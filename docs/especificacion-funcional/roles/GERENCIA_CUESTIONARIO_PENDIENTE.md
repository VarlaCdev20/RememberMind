---
title: "Gerencia: cuestionario consolidado y respuestas adoptadas"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_DECISIONS_AND_FUNCTIONAL_BASELINE_REVIEW
runtime_verified: false
---

# Gerencia: cuestionario y decisiones adoptadas

## Uso y autoridad

La propietaria solicitó recibir las preguntas pendientes juntas y detalladas y respondió todas el 25/10/2026. Se conservan las opciones presentadas como historia; **solo las elecciones de la matriz final están adoptadas**, no las recomendaciones ni las alternativas descartadas. Las respuestas se concilian con el [plan de vistas](GERENCIA_PLAN_VISTAS.md) y el [baseline de roles](../../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). CURRENT describe esta constancia de decisiones; no certifica su implementación.

Las preguntas 15–18, 23 y 26 incluyen decisiones de acceso, responsabilidad o comunicación que deben quedar expresas antes de implementar la opción correspondiente. Elegir funcionalidad no aprueba cambios concretos del esquema congelado. Las preferencias visuales restantes no cambian permisos, hechos clínicos o relaciones de datos.

## Base ya acordada

- Inicio equilibrado de personal, ocupación y alertas, con acceso directo a cada módulo.
- Gerencia crea y mantiene cuentas de Administración, seis profesiones y Familiar; excluye Gerencia/Superadministración. No se aprobaron cambio de roles, edición del catálogo de permisos o eliminación.
- Personal y Familiares/contactos tienen apartados propios.
- Alta laboral con ficha y cuenta juntas, cuenta activa, cuatro pasos y guardado final atómico.
- Mínimo documental inmediato ratificado; otros requeridos admitidos con siete días calendario desde el alta y una sola prórroga de siete días adicionales al vencimiento original.
- Trabajador y Gerencia cargan; Gerencia revisa. Se conserva autoría de carga y revisión por separado.
- Avisos internos al trabajador y Gerencia dos días antes y al vencer, con pendientes. Sin suspensión automática por vencer.

Fuentes detalladas: [Personal](../vistas/gerencia/02-PERSONAL.md) y [Alta de trabajador](../vistas/gerencia/03-ALTA-TRABAJADOR.md). La especificación está en desarrollo; no representa implementación o pruebas funcionales completadas.

## Preguntas presentadas y opciones históricas

### 15. Primer acceso del trabajador

- **A:** contraseña temporal y cambio obligatorio en el primer ingreso; entrega mediante el mecanismo seguro que se implemente.
- **B:** enlace personal de un solo uso para definir su propia contraseña; Gerencia no la conoce. Recomendación.

En B el enlace debe expirar, poder revocarse/reemitirse y no funcionar otra vez después de usarse. La cuenta activa no permite ingresar sin establecer credenciales válidas. El código exige actualmente contraseña temporal; no se presenta B ni el cambio obligatorio de A como implementados.

### 16. Recuperación de acceso cuando el trabajador no puede entrar

- **A:** recuperación por la propia persona; las incidencias que no pueda resolver se escalan a Superadministración.
- **B:** además, Gerencia puede iniciar/reemitir un enlace de recuperación para las cuentas de su alcance, sin ver ni elegir la contraseña.

B amplía expresamente la operación gerencial; no viene incluida automáticamente en «editar datos». En ambos casos se requiere verificación de identidad y no se admiten cambios de rol o evasión de una suspensión.

### 17. Vínculo de un familiar con un residente

- **A:** Gerencia crea la cuenta usando un contacto y vínculo previamente registrados y autorizados por el proceso competente; si falta el vínculo, deriva su registro a ese proceso.
- **B:** Gerencia también registra y corrige el vínculo familiar dentro de su flujo, con residente, parentesco y trazabilidad.

B amplía responsabilidades; cualquiera de las opciones conserva el proceso institucional y sus restricciones. El vínculo no publica historia clínica ni concede automáticamente autorizaciones de información/salida. No se cambia la cardinalidad ni se elimina historia para corregir un parentesco.

### 18. Estado inicial de la cuenta Familiar

- **A:** activa al completar sus datos y contar con un vínculo autorizado; ve únicamente lo publicado y permitido para su residente vinculado.
- **B:** se crea suspendida y Gerencia debe activarla manualmente tras comprobar sus datos y vínculo.

La decisión previa de cuenta activa se refería al alta laboral; no se extiende a Familiar por inferencia. Ninguna opción permite consultar residentes ajenos o toda su historia clínica.

### 19. Organización de Familiares y contactos

- **A:** listado principal por persona, con los residentes vinculados dentro de su ficha. Evita presentar una misma persona como varias identidades.
- **B:** listado agrupado por residente, mostrando sus contactos; cada contacto abre su ficha única compartida.

Es una preferencia de navegación sobre las mismas entidades y vínculos, no una duplicación de contactos ni un cambio de relaciones.

### 20. Navegación de áreas y personal por área

- **A:** una entrada «Áreas y equipo», con detalle del área y su personal en la misma pantalla. Recomendación para reducir duplicidad.
- **B:** dos entradas, «Áreas» y «Personal por área», que reutilizan el mismo módulo y abren enfoques/pestañas distintos.

Actualmente dos entradas apuntan a la misma ruta. Elegir B no autoriza mantener dos pantallas duplicadas ni otra fuente de pertenencia institucional.

### 21. Vista inicial de planificación maestra

- **A:** calendario semanal para revisar días, horarios y cruces; conmutador a mes.
- **B:** calendario mensual para organizar el período; conmutador a semana.

Ambas conservan detalle accesible en tabla y validación de solapamientos, incluidos turnos que cruzan medianoche. Gerencia planifica; Administración mantiene la operación diaria conforme al baseline. No se eligen nuevos límites de guardia ni reglas laborales por esta pregunta.

### 22. Consulta de cobertura

- **A:** tabla por área y turno, con personal asignado y filtros de fecha; detalle de origen disponible. Recomendación para lectura precisa.
- **B:** calendario visual por área, con tabla alternativa de los mismos datos.

Solo se muestra cobertura sustentada por asignaciones y jornadas existentes. No se presenta asignación como asistencia confirmada ni se inventan metas para declarar déficit de personal.

### 23. Avisos de cambios de planificación

- **A:** notificar dentro del sistema a Administración y al personal afectado cuando Gerencia confirme cambios de planificación.
- **B:** notificar solo a Administración; la comunicación al personal se realiza por el circuito operativo fuera de este aviso.

Solo cambios confirmados y destinatarios afectados; no avisos por vista previa ni acceso a registros ajenos. A no autoriza correo o mensajería externa. Ninguna opción cambia quién realiza las asignaciones operativas.

### 24. Vista inicial de ocupación

- **A:** mapa de habitaciones/camas con disponibilidad y leyenda; tabla alternativa y acceso al detalle autorizado.
- **B:** tabla por habitación con camas disponibles/ocupadas y filtros; mapa como alternativa.

Lectura gerencial: las operaciones de admisión, traslado o alojamiento conservan su proceso y autorización. Los estados provienen del dominio; no se equiparan todas las camas sin ocupante con camas disponibles.

### 25. Organización de alertas de Gerencia

- **A:** bandeja única con filtros de estado, tipo y fecha, mostrando solo contenido autorizado.
- **B:** secciones visibles por estado, por ejemplo abiertas y cerradas, con búsqueda y filtros equivalentes.

Se reutiliza el ciclo real de alertas, sin inventar severidad, otro dashboard ni permiso para cerrar clínicamente una alerta. Un estado que no exista en el dominio no se crea por elegir una etiqueta visual.

### 26. Salida de reportes gerenciales

- **A:** consulta dentro del sistema, sin ampliar permisos de exportación.
- **B:** además, PDF de indicadores gerenciales agregados, con período y fecha de generación.
- **C:** además del PDF, CSV de los mismos indicadores agregados para análisis.

B/C requieren autorización explícita para exportar y auditoría de la operación; no amplían la consulta a fuentes no permitidas. No incluyen historias clínicas, archivos laborales, documentos de identidad o listados nominales masivos por implicación. Sin envío automático a terceros.

### 27. Ubicación de la galería institucional

- **A:** dentro de un apartado «Institución», como entrada secundaria.
- **B:** entrada directa «Galería» en el menú de Gerencia, como ahora.

No cambia los permisos ya establecidos, el ámbito de fotografías institucionales o la privacidad de imágenes de personas. No es una galería clínica.

### 28. Seguimiento pendiente en Inicio

- **A:** debajo de las tres tarjetas equilibradas, un bloque compacto «Pendientes de Gerencia» con documentación por revisar/vencer y otros pendientes sustentados por fuentes autorizadas; cada fila abre su módulo. Recomendación.
- **B:** Inicio conserva las tarjetas y accesos principales; los pendientes se consultan dentro de cada módulo y las notificaciones correspondientes.

No se añaden formularios de gestión al Inicio ni se mezclan avisos documentales con severidad clínica. No se muestran datos restringidos ni cifras calculadas como si fueran registros guardados.

### 29. Vista inicial de Residentes para Gerencia

- **A:** tabla como vista inicial, con alternativa de tarjetas.
- **B:** tarjetas como vista inicial, con alternativa de tabla.

Ambas muestran identidad legible y datos administrativos autorizados; sin códigos/ID visibles, alta directa o edición clínica. Mantener búsqueda, filtros, paginación y detalle del residente desde el módulo existente.

## Registro y próximos pasos

**Respuesta recibida el 25/10/2026:** `15A, 16B, 17B, 18A, 19B Y A, 20A, 21A, 22A, 23A, 24A Y B, 25A, 26B Y C, 27B, 28A, 29B`.

| Pregunta | Elección | Contrato adoptado |
|---|---|---|
| 15 | A | Contraseña temporal y cambio obligatorio al primer ingreso del trabajador |
| 16 | B | Gerencia puede iniciar/reemitir enlace de recuperación de cuentas de su alcance; nunca ver o elegir la contraseña recuperada |
| 17 | B | Gerencia registra y corrige vínculos familiares con residente/parentesco y trazabilidad, sin publicar clínica por implicación |
| 18 | A | Cuenta Familiar activa al completar datos y contar con vínculo autorizado; consulta solo contenido permitido |
| 19 | B y A | Dos presentaciones con selector: por residente y por contacto, sobre identidades/vínculos únicos |
| 20 | A | Entrada única «Áreas y equipo» |
| 21 | A | Planificación semanal inicial, con alternativa mensual |
| 22 | A | Cobertura en tabla por área/turno y filtros de fecha |
| 23 | A | Avisos internos de cambios confirmados de planificación para Administración y personal afectado |
| 24 | A y B | Ocupación con mapa y tabla intercambiables sobre la misma fuente |
| 25 | A | Bandeja única de alertas con filtros de estado/tipo/fecha |
| 26 | B y C | Consulta y exportación PDF/CSV de los mismos indicadores gerenciales agregados, con autorización y auditoría |
| 27 | B | Entrada directa «Galería» en el menú |
| 28 | A | Bloque compacto «Pendientes de Gerencia» debajo de las tres tarjetas equilibradas |
| 29 | B | Residentes en tarjetas inicialmente, con alternativa de tabla |

**Estado de 15–29: ADOPTADAS FUNCIONALMENTE; implementación y pruebas pendientes.** La respuesta doble 19/24 se resuelve como selector entre vistas, no pantallas o registros duplicados; no se ha elegido una prioridad inicial entre esas dos presentaciones. En 26, C incluye los formatos de B: se habilitarán PDF y CSV del mismo conjunto agregado, no dos reportes independientes con fuentes divergentes.

La elección 15A gobierna primer acceso; 16B gobierna recuperación posterior. No se sustituye la contraseña temporal inicial por la alternativa 15B. Reemitir recuperación no cambia roles, no activa una cuenta suspendida y no elimina el cambio inicial obligatorio sin el contrato de credenciales correspondiente.

Siguiente trabajo documental: detallar por vista campos, botones, estados, permisos, fuentes y aceptación; contrastar persistencia y consumidores. Las decisiones técnicas rutinarias, accesibilidad, validación backend y corrección de defectos se resuelven en desarrollo con evidencia, sin convertir cada botón en otra aprobación. Las cuestiones nuevas de esquema, responsabilidades o autorización que el contraste revele se aislarán y explicarán concretamente. No se ejecutaron migraciones, permisos, comunicaciones, altas o cambios de aplicación para registrar estas elecciones.

No se reabren las decisiones 1–14. Esta lista cubre las decisiones funcionales y preferencias principales aún pendientes de Gerencia; no certifica que todas las brechas de implementación estén localizadas.
