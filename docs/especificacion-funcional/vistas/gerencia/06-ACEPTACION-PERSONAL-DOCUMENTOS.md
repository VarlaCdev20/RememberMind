---
title: "Gerencia: matriz de aceptación de Personal y documentos"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: ACCEPTANCE_SPECIFICATION_WITH_STATIC_TEST_LOCALIZATION
runtime_verified: false
---

# Aceptación de Personal y documentación

## 1. Alcance y estados de evidencia

Contrato: [campos F01–F23 y acciones A01–A16](04-FICHA-Y-OPERACIONES-PERSONAL.md), [documentación laboral](05-DOCUMENTACION-LABORAL.md), [alta](03-ALTA-TRABAJADOR.md). Las decisiones [R01A con extensión, R02A y R03A](07-DECISIONES-RESIDUALES-PERSONAL.md#respuestas-adoptadas-el-25102026) se adoptaron el 25/10/2026. [S01–S08](08-SOLICITUD-DOCUMENTAL-POR-ROL.md#6-aceptación-adicional-no-ejecutada) extienden los 36 escenarios de esta matriz; no se presentan como pruebas ejecutadas.

Todas las filas siguientes son **ACEPTACIÓN ESPECIFICADA, NO EJECUTADA EN ESTA TAREA**. Una prueba existente citada acredita localización, no cobertura completa ni resultado actual. Los casos nuevos usan identidades/archivos sintéticos. SQLite valida lógica portátil; PostgreSQL desechable debe comprobar bloqueos/carreras. No ejecutar suites o resets en la base institucional para revisar documentación.

Archivos localizados:

- **T1:** [ContratoPersonalInstitucionalTest.php](../../../../tests/Feature/ContratoPersonalInstitucionalTest.php): alta, permisos, edición, duplicados, correo y rollback.
- **T2:** [ContratoDocumentacionUsuarioTest.php](../../../../tests/Feature/ContratoDocumentacionUsuarioTest.php): validación, propietario cruzado, anulado, subida, sustitución y compensación.
- **T3:** [DocumentacionFichaTest.php](../../../../tests/Feature/DocumentacionFichaTest.php): render y controles de documentación; fixtures y expectativas por contrastar.
- **T4:** [PersonalInstitucionalHorariosTest.php](../../../../tests/Feature/PersonalInstitucionalHorariosTest.php) y [PersonalInstitucionalResponsiveTest.php](../../../../tests/Feature/PersonalInstitucionalResponsiveTest.php): pruebas relacionadas, no aceptación global del nuevo flujo.
- **T5:** [PasswordResetTest.php](../../../../tests/Feature/PasswordResetTest.php) y [UpdatePasswordTest.php](../../../../tests/Feature/UpdatePasswordTest.php): identidad existente; no prueba de 15A/16B gerenciales por sí sola.

## 2. Matriz requisito → operación → prueba → evidencia requerida

«Ampliar/crear» es trabajo posterior de pruebas; no se ha escrito código de aplicación/test para aparentar ejecución de este contrato.

| Caso | Campos/acciones y contrato | Escenario y resultado verificable | Prueba localizada / trabajo pendiente | Evidencia requerida al ejecutar |
|---|---|---|---|---|
| P01 | F01–F20, A01; alta conjunta | Datos válidos y mínimo inmediato crean una cuenta y un trabajador enlazados, cuenta ACTIVO y rol permitido | T1, ampliar al wizard/alcance nuevo | Filas SQL, FK, rol, estado y ausencia de duplicados |
| P02 | A01; mínimo inmediato | Omitir cada archivo inmediato del rol impide alta; diferible requerido permite alta con pendiente; opcional no genera deuda | T1 y catálogo por ampliar tras R01 | Errores por campo, cero alta parcial, checklist exacto |
| P03 | F04/F15, A01 | Documento o correo duplicado; reintento y doble clic no crean otra persona/cuenta | T1 + carreras PostgreSQL y revisión posterior al bloqueo; verificadas en informe41 | Conteos, UNIQUE, respuesta de conflicto sin secretos |
| P04 | A01; atomicidad | Fallo SQL/archivo/auditoría revierte SQL y compensa solo archivo nuevo; fallo de comunicación posterior se informa separado | T1, T2; ampliar nuevos pasos | SQL y almacenamiento anterior intactos, efecto de alta real |
| P05 | A02 | Target ajeno, cuenta excluida o permiso revocado no expone ficha/payload; retorno conserva filtros | Ampliar T1/T3 | HTTP/Livewire y ausencia de datos sensibles |
| P06 | F01–F15, A03 | Editar datos autorizados conserva contraseña, roles, vínculos y estado de cuenta | T1, ampliar campos | Comparación antes/después y actor auditado |
| P07 | F16, A03 | Manipular rol en alta o edición, incluso cuenta con rol excluido adicional, se deniega | T1; ampliar negativos de Gerencia | Sin grants nuevos ni cambios laterales |
| P08 | F18, A04 | Suspender cuenta autorizada requiere confirmación y backend; no modifica estado laboral ni historia | Crear caso del nuevo alcance | usuarios.estado y personal.estado diferenciados, auditoría |
| P09 | A05 | Activar cuenta admitida no fabrica profesión, documento validado o vínculo familiar; no cuenta excluida | Crear caso del nuevo alcance | Cuenta actualizada y otros dominios intactos |
| P10 | F17; 15A | Primer ingreso con contraseña temporal exige cambio real antes de funciones de trabajo; no omisible por URL/Livewire | T5 como base; crear integración | Sesión/entradas denegadas hasta cambio, hash y estado soportados |
| P11 | A06; 16B | Recuperación por Gerencia propia/excluida, revocación, token usado/expirado, reemisión y cuenta suspendida | T5; crear autorización gerencial | Enlace válido una vez, actor, sin conocer contraseña ni reactivar cuenta |
| P12 | A07 | Consulta/descarga por identificador de otro titular o sin permiso se deniega; no ruta privada pública | T2/T3, ampliar HTTP/Livewire | Estado de respuesta y payload sin archivo ajeno |
| P13 | A08 | Trabajador carga archivo propio; mismo request con otro titular o revisión propia se deniega | T2; crear ownership nuevo | Titular, cargador y revisión separados |
| P14 | A08 | Gerencia carga documento recibido para target permitido; no se atribuye carga al trabajador ni valida automáticamente | T2, ampliar | Registro CARGADO y autor/hora reales conservados |
| P15 | A09 | Gerencia valida archivo vigente con permiso; inactivo/revocado o antecesor reemplazado no valida | T2, ampliar scope | Revisor y fecha correctos, historial y auditoría |
| P16 | A10 | Observación requiere motivo concreto; se muestra para corrección sin inventar estado RECHAZADO | T2, ampliar | OBSERVADO, motivo y revisor; ausencia de pérdida de historia |
| P17 | A11 | Corrección crea sucesor del mismo titular/tipo, conserva archivo anterior y evita doble vigente concurrente | T2 + carreras PostgreSQL y dos formularios Livewire; verificadas en informe41 | Cadena FK, archivos, unicidad lógica y comparación histórica |
| P18 | F21 | Alta con fecha laboral anterior, zona local/UTC, día límite y fines de semana conserva plazo de siete días desde alta real | Crear tras soporte de alta/plazo | Fechas retenidas, borde local y no deriva desde now() al consultar |
| P19 | F22, A12 | Primera prórroga +7 al límite original; no otra por archivo o por volver a abrir la ficha | Crear tras soporte relacional | Alta única, fechas, actor, motivo; total catorce días |
| P20 | A12 | Dos solicitudes concurrentes no conceden dos prórrogas; fallo parcial no consume una inexistente | Crear PostgreSQL desechable | Transacciones/bloqueos reales, resultado único y reintento |
| P21 | F23, A13 | Abrir planificación no concede escritura de horarios; asignación nocturna/varias áreas no se resume como presencia confirmada | T4 y auditoría H02/H03; ampliar | Permisos por acción, fuentes/fechas y ausencia de área arbitraria |
| P22 | A14 | Historial distingue altas, cargas, revisiones y sustituciones; no rellena fechas desconocidas | T1/T2, ampliar | Identidades/fechas verificables y ausencia explícita |
| P23 | A15 | R02A: Gerencia anula solo documento laboral gestionable, con motivo/autor/fecha/historia; rechazar clínica o target excluido | T2 como base; ampliar alcance aprobado | Estado, actor, contenido histórico conservado |
| P24 | F13, A16 | R03A: cambio ACTIVO/INACTIVO con motivo/fecha, cuenta separada y Administración revisa asignaciones; no borrado de relaciones/pasado | Crear integración del alcance aprobado | Estados diferenciados, continuidad y efectos de dominio sin toggle erróneo |
| P25 | Avisos; decisión 11 | Dos días antes, solo requeridos sin entregar y destinatarios autorizados; reintentos/render no duplican aviso | Crear tras soporte de seguimiento | Destinatarios, clave/fecha límite, cantidad de avisos |
| P26 | Avisos/vencimiento | Superar límite original/prorrogado avisa; no suspensión automática; no sigue usando límite anterior como actual | Crear tras soporte de seguimiento | Notificación interna y estado de cuenta sin cambio lateral |
| P27 | Checklist | Sin permiso, error, cero requisitos y documento entregado pendiente de revisión no producen «completo» o mora incorrectos | T3; ampliar | Mensajes distintos, denominador requerido real |
| P28 | F20 | Extensión falsa, formato/tamaño incompatible y fallo de carga se rechazan con el contrato técnico único elegido | T1/T2; unificar límites al implementar | Error por archivo, sin fila/archivo definitivo huérfano |
| P29 | Fechas documentales | Emisión, caducidad, carga, validación, alta y límite se conservan/representan como hechos distintos | Crear tras resolver representación | Comparación nominal, NULL y zonas/precisión sin equivalencias falsas |
| P30 | A10/A11 | Documento entregado en plazo y observado tarde conserva entrega e historia; no mora retrospectiva por demora del revisor ni prórroga automática nueva | Crear; límite actual sigue aplicando | Entrega original y decisión posterior visibles, corrección contextual |
| P31 | Catálogos | Fotografía subida en alta se reconoce correctamente en ficha; no doble requisito por FOTO/FOTO_DOC ni mapeo destructivo de historia | Crear con decisión de mapeo autorizada si altera catálogo | Mismo documento reconocido y cadenas/consumidores intactos |
| P32 | G01–G03 | Datos históricos sin fecha/autor nominal no se reconstruyen del nombre o ID; no usar fecha_ingreso o fecha_validacion como sustitutos | Crear prueba de insuficiencia | Ausencia explícita y evaluación de capacidad estructural |
| P33 | Campos no soportados | Propiedad declarada sin persistencia no se ofrece como guardada; no asignar por mass assignment datos extra | T1; ampliar negativos | Rechazo claro o campo no ofrecido, SQL sin falso éxito |
| P34 | A01–A16; accesibilidad | Teclado, foco, labels, confirmaciones, modales, archivos y contraste; texto humano sin códigos visibles | T4 y casos nuevos de interfaz | Capturas/recorrido real y árbol accesible, no solo búsqueda de CSS |
| P35 | Listado/ficha | Vacío, sin coincidencias, carga, denegación, conflicto, error y éxito reales; no doble envío ni pérdida de contexto | T3/T4, ampliar | Navegación/render/acciones y efectos observados |
| P36 | Integración | Cuatro pasos conectados, build, rutas/policies y layout en 1440/1280/1024/768/390 | Suites pertinentes al implementar | Comandos y resultados de la ejecución, evidencia visual separada |

## 3. Ejecución posterior

Antes de ejecutar, leer `tests/AGENTS.md` y comprobar configuración desechable. Comandos dirigidos candidatos, no ejecutados aquí:

```bash
php artisan test --filter=ContratoPersonalInstitucionalTest
php artisan test --filter=ContratoDocumentacionUsuarioTest
php artisan test --filter=DocumentacionFichaTest
php artisan test --filter=PersonalInstitucional
```

Los escenarios nuevos requieren pruebas reales adicionales; no se obtiene su PASS por ejecutar solo las suites históricas. Build y pruebas frontend se seleccionarán según los archivos modificados. PostgreSQL desechable prueba concurrencia; SQLite no la acredita por equivalencia.

## 4. Cierre proporcional

La documentación pasa cuando cada fila tiene requisito, operación, salida y evidencia requerida; los archivos enlazados existen y las decisiones pendientes están identificadas. El software solo pasa tras implementar los casos aplicables y registrar resultados. No se marcan P01–P36 como ejecutados por esta entrega documental.
