---
title: "Gerencia: alta de trabajador en cuatro pasos"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-25
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_DECISION_AND_STATIC_FORM_REVIEW
runtime_verified: false
---

# Alta de trabajador

Complementos: [matriz campo/acción/permiso/persistencia](04-FICHA-Y-OPERACIONES-PERSONAL.md), [contrato de documentos](05-DOCUMENTACION-LABORAL.md) y [aceptación verificable](06-ACEPTACION-PERSONAL-DOCUMENTOS.md). Las [respuestas R01–R03](07-DECISIONES-RESIDUALES-PERSONAL.md#respuestas-adoptadas-el-25102026) están adoptadas; la [petición por rol](08-SOLICITUD-DOCUMENTAL-POR-ROL.md) no convierte todos los respaldos adicionales en obligatorios al alta.

## 1. Decisión y ubicación

**Respuesta expresa de la propietaria, 25/10/2026:** «A», correspondiente a «Cuatro pasos guiados: identidad y contacto → información laboral → cuenta → documentación. Permite avanzar y retroceder; guarda todo al finalizar».

Entrada prevista: **Personal → Registrar trabajador**, desde el listado existente `admin.personal-institucional`. Este documento especifica la interacción del formulario reutilizado, no crea otra ruta o módulo. [Alcance y decisiones de Personal](02-PERSONAL.md).

Gerencia registra Administración y los seis roles profesionales; excluye Gerencia, Superadministración y el alta de Familiar, que tiene su apartado propio. Las restricciones se comprobarán en backend, además de la interfaz.

## 2. Cuatro pasos

Los campos de esta tabla proceden de la validación observada. La distribución es propuesta técnica conforme a la composición aprobada; no aprueba nuevos campos, catálogos o reglas institucionales.

| Paso | Contenido | Validación y precauciones |
|---|---|---|
| 1. Identidad y contacto | Nombres, apellidos, número y expedición de documento, nacimiento, género, teléfono y dirección | Comprobar formatos y coincidencias según contrato aplicable; no convertir nombre coincidente en vínculo automático. El apellido materno es nullable en la Action actual |
| 2. Información laboral | Función/rol institucional, matrícula cuando corresponda, fecha de ingreso, observaciones y situación laboral | Diferenciar profesión y acceso; no inventar especialidades, áreas o contratos desde un selector sin persistencia comprobada. Matrícula y fecha de ingreso son nullable en la Action actual; no prometer nuevas obligatoriedades |
| 3. Cuenta de acceso | Correo, resumen del rol de acceso derivado de la selección autorizada y mecanismo de credenciales existente | Correo único y rol permitido; una sola elección coherente entre pasos 2 y 3. Mostrar que la nueva cuenta quedará activa tras el alta correcta, según sección 8. Revalidar todos los datos al guardar. No guardar contraseñas en texto plano, logs o documentación |
| 4. Documentación y comprobación final | Archivos por función, pendientes, fecha límite y resumen de los tres pasos anteriores | Exigir el mínimo inmediato de la sección 7 y distinguir archivo recibido de revisado. Aplicar siete días y única prórroga de siete solo a documentos requeridos que admitan plazo; no simular expediente completo |

No ofrecer propiedades rechazadas por la Action actual como teléfono alternativo, institución de formación, años de experiencia, subtipo de Enfermería, nivel de responsabilidad o forzar cambio de contraseña como si ya se guardaran. Su incorporación requeriría un alcance separado y, cuando corresponda, decisión estructural.

## 3. Botones y comportamiento

| Control | Ubicación y efecto previsto |
|---|---|
| Continuar | Pasos 1–3; validar los campos del paso, conservar lo introducido y avanzar sin crear ficha ni cuenta definitivas |
| Atrás | Pasos 2–4; conservar entradas y archivos temporales válidos sin duplicar cargas |
| Volver a un paso anterior | Indicador de pasos visitados; no saltar validaciones pendientes para finalizar |
| Seleccionar archivo | Paso 4; indicar nombre, tipo, validación y errores de la carga temporal; todavía no significa entrega definitiva o aprobación |
| Quitar archivo seleccionado | Paso 4; retirar solo la selección temporal de esta alta, no borrar documentos históricos |
| Registrar trabajador | Solo en el paso final; validar el conjunto y crear ficha, cuenta y vínculos de forma atómica. Bloquear doble envío mientras se procesa |
| Cancelar | Disponible durante el recorrido; advertir si hay cambios sin guardar y regresar al listado sin crear una alta parcial |

Un error final dirige al primer paso con errores y conserva la información recuperable. Un fallo de carga no se anuncia como documento recibido. El mensaje de éxito solo aparece tras confirmación real de la operación; informar separadamente cualquier fallo de comunicación posterior sin fingir que la cuenta no existe ni invitar a duplicar el alta.

No se adopta guardado de borradores definitivos entre pasos ni almacenamiento de contraseñas en el navegador. Las cargas temporales usan el mecanismo existente y su limpieza; su disponibilidad no equivale a alta completada.

## 4. Presentación y accesibilidad previstas

Título «Registrar trabajador», indicador «Paso n de 4» y nombre del paso. Emplear componentes y tokens del Design System, botones con foco visible, labels vinculados y errores junto a sus campos. Al cambiar de paso, dirigir el foco al encabezado o al primer error y anunciar el progreso de forma accesible. Mantener el resumen de pendientes visible en el paso final.

En pantalla estrecha, indicador compacto y campos en una columna. Si se conserva el contenedor modal actual, comprobar Escape, foco atrapado, retorno al botón de alta y confirmación ante cambios pendientes. El formato de cuatro pasos no autoriza declarar QA responsive o visual sin ejecutarla.

## 5. Código observado y diferencias

- [Componente existente](../../../../app/Modulos/Identidad/Presentacion/Livewire/Funcionalidades/PersonalInstitucionalForm.php): declara `totalPasos = 7`. La composición de cuatro pasos todavía no está implementada.
- [Blade existente](../../../../app/Modulos/Identidad/Presentacion/Vistas/livewire/identidad/personal-institucional-form.blade.php): reutilizar y contrastar consumidores al reorganizar; no crear una segunda alta paralela.
- [Action de guardado](../../../../app/Modulos/Identidad/Aplicacion/Acciones/GuardarPersonalInstitucional.php): contiene transacción y validación de alta conjunta, pero los permisos y límites de cuentas deben conciliarse con las nuevas decisiones. Incluye un mensaje de regularización en 48 horas y calcula una fecha con `addHours(48)` para la comunicación; estos valores no representan el nuevo plazo aprobado de siete días.
- [Matriz documental observada](../../../../app/Modulos/Identidad/Dominio/Reglas/DocumentosPersonalInstitucional.php): exige inmediatamente cédula, fotografía y CV; añade título para las seis profesiones y matrícula inmediata para Medicina. La propietaria ratificó ese mínimo en la sección 7. Otros detalles del catálogo observado no quedan aprobados automáticamente por esa ratificación; conservar la distinción entre entrega y competencia clínica acreditada.

## 6. Aceptación a verificar al implementar

1. Recorrer los cuatro pasos, retroceder y corregir sin perder datos válidos.
2. Validaciones por paso y conjunto; ninguna ficha/cuenta definitiva creada al pulsar Continuar.
3. Alta conjunta correcta, rollback ante fallo parcial y prevención de duplicados al reintentar.
4. Denegar rol destino manipulado, cuenta inactiva, permiso revocado y operación fuera del alcance.
5. Carga temporal válida/inválida, autoría real y pendientes conforme al mínimo ratificado: impedir guardar si falta un archivo inmediato y permitir otros requeridos con plazo; no generar vencimientos por opcionales ausentes.
6. Fechas y avisos de siete días, prórroga única y ausencia de mensajes residuales de 48 horas.
7. Cancelación, cambios pendientes, doble envío, fallo de carga y confirmación de éxito comprobables.
8. Teclado, foco, labels, responsive en los cinco tamaños institucionales y build de frontend.
9. Alta correcta crea la cuenta ACTIVA con el rol permitido, sin marcar archivos como validados ni conceder otras competencias. Fallo de alta no deja una cuenta activa parcial. Alta nueva no reactiva una cuenta preexistente.

**Estado:** decisión de composición adoptada y especificación detallada. Implementación, pruebas de aplicación y revisión visual pendientes. Solo se verificaron coherencia documental y enlaces; no se modificó la aplicación o la BDD.

## 7. Mínimo documental inmediato ratificado

**Respuesta expresa de la propietaria, 25/10/2026:** «A», correspondiente a «mantener esos archivos obligatorios al alta; los demás pueden completarse en siete días».

| Grupo de trabajadores | Documentos que se adjuntan para finalizar el alta |
|---|---|
| Administración y las seis profesiones | Cédula de identidad, fotografía y hoja de vida/CV |
| Enfermería, Medicina, Psicología, Nutrición, Fisioterapia y Pedagogía | Los anteriores y título o certificado de formación del catálogo vigente |
| Medicina | Los anteriores y matrícula profesional |

Los demás documentos requeridos que admitan plazo pueden quedar pendientes con fecha límite y seguimiento ya acordados. Los opcionales conservan su carácter opcional: no generan deuda documental ni avisos por no entregarlos. Esta respuesta no convierte todos los archivos del catálogo en obligatorios ni establece nuevas exigencias legales.

Antes de finalizar, mostrar qué archivo inmediato falta y permitir incorporarlo sin perder los demás campos. El backend comprueba el mismo mínimo, incluidos cambios de rol que alteren los documentos exigibles. No crear ficha o cuenta incompletas como sustituto del rechazo de validación.

La obligatoriedad de adjuntar no implica aprobación automática del contenido. Los requisitos de ejercicio profesional y la revisión documental permanecen separados del alta y del acceso. El estado inicial de la cuenta quedó definido en la sección 8.

## 8. Estado inicial de acceso adoptado

**Respuesta expresa de la propietaria, 25/10/2026:** «A», correspondiente a «activa al finalizar el alta, con los permisos y controles correspondientes».

La nueva cuenta laboral queda en el estado existente **ACTIVO** al completarse correctamente el alta conjunta. No hay una activación manual adicional por Gerencia después del guardado correcto. El resumen final debe informar ese efecto antes de confirmar y el resultado de guardado debe distinguir cuenta creada, documentación recibida y documentos aún pendientes.

Esta decisión conserva el mínimo documental inmediato, rol destino permitido, integridad de la operación y permisos/Policy/contexto de cada acción posterior. No significa validación automática de archivos, aprobación profesional, inicio de sesión automático del nuevo trabajador ni reactivación de una cuenta preexistente. Un fallo de alta no puede dejar una cuenta activa huérfana.

El código actual ya asigna `ACTIVO` a la cuenta nueva en la Action, pero ese hecho no acredita la integración completa del nuevo formulario, plazos, avisos o permisos de Gerencia. No se modifica la aplicación ni se realiza un alta para documentar la decisión.

El mecanismo inicial se adoptó mediante 15A en la sección 9. La Action actual exige contraseña temporal y rechaza `forzar_cambio_password` con contenido; por ello no se afirma que ya exista cambio obligatorio en el primer ingreso. Debe integrarse y probarse antes de declarar completo el acceso inicial.

## 9. Credenciales iniciales y recuperación adoptadas

**Respuestas expresas del 25/10/2026: 15A y 16B.** El trabajador recibe una contraseña temporal y debe cambiarla en su primer ingreso. El cambio se completa antes del acceso ordinario a funciones de trabajo; no basta con mostrar un aviso que pueda omitirse. No se almacenan contraseñas legibles en historia, auditoría, logs o documentación. El mecanismo seguro de entrega se concretará al integrar las capacidades de identidad existentes, sin envíos reales durante la especificación.

Gerencia puede iniciar o reemitir un enlace de recuperación para las cuentas de Administración, seis profesiones y Familiar de su alcance, con verificación de identidad y auditoría. No conoce ni elige la nueva contraseña. Revalidar los roles y estado de la cuenta destino al ejecutar; Gerencia/Superadministración siguen excluidas aunque la cuenta tenga otro rol adicional. Una recuperación no cambia permisos, evita una suspensión ni equivale a activar la cuenta.

El primer acceso y la recuperación son operaciones distintas: no se adopta 15B como sustituto del alta con contraseña temporal. La reemisión debe invalidar el enlace anterior aplicable y evitar dobles envíos por reintentos; tokens con expiración y uso limitado conforme al mecanismo real. No crear un servicio paralelo de autenticación.

Pruebas requeridas: cambio obligatorio no eludible en HTTP/Livewire, credenciales incorrectas, fallo de entrega tratado por separado, recuperación ajena/manipulada, permiso revocado, roles destino excluidos, cuenta suspendida, enlace expirado/usado y reemisión. **Estado:** decisión adoptada; implementación y verificación pendientes. Cualquier necesidad estructural concreta sigue su aprobación propia.
