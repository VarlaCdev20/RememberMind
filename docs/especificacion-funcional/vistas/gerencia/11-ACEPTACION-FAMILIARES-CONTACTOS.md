---
title: "Gerencia: aceptación de Familiares y contactos"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: ACCEPTANCE_SPECIFICATION_WITH_STATIC_TEST_LOCALIZATION
runtime_verified: false
---

# Aceptación de Familiares y contactos

## 1. Alcance y fuentes de pruebas

Contrato: [21 campos FC y 12 acciones FV](09-FAMILIARES-Y-CONTACTOS.md), [formularios/privacidad](10-FORMULARIOS-FAMILIARES-PRIVACIDAD.md). Todos los casos FA01–FA24 son **ESPECIFICADOS, NO EJECUTADOS EN ESTA TAREA**. Pruebas sintéticas, nunca escritura en base institucional.

Pruebas localizadas, no reejecutadas:

- **FT1:** [Fase3SeguridadNucleoTest](../../../../tests/Feature/Fase3SeguridadNucleoTest.php): identidad permitida de vinculado, residente ajeno, contacto inactivo, listas globales y fuentes no publicadas.
- **FT2:** [UsuariosResidentesV2Test](../../../../tests/Feature/UsuariosResidentesV2Test.php): selección de residentes existentes y rechazo de identidad incompleta; no prueba total de Gerencia.
- **FT3:** [UsuariosDocumentoIdentidadTest](../../../../tests/Feature/UsuariosDocumentoIdentidadTest.php): correspondencia de identidad y límites; fixtures por contrastar.
- **FT4:** [UsuariosRoutesPermissionsTest](../../../../tests/Feature/UsuariosRoutesPermissionsTest.php) y [RolesVistasPermisosMatrixTest](../../../../tests/Feature/RolesVistasPermisosMatrixTest.php): entradas/permisos existentes; no acreditan nuevo scope de Gerencia automáticamente.
- **FT5:** [PasswordResetTest](../../../../tests/Feature/PasswordResetTest.php): identidad existente; no equivale a recuperación gerencial completa.

## 2. Matriz requisito → caso → evidencia futura

| Caso | Campo/acción | Escenario y resultado requerido | Prueba base / trabajo pendiente |
|---|---|---|---|
| FA01 | FC01–FC10, FV04 | Registrar contacto válido guarda identidad sin crear personal, cuenta o residente implícitos | FT2/FT3, ampliar |
| FA02 | FC01–FC03 | Identidad incompleta se rechaza sin dividir nombre ni generar apellidos ficticios | FT2, ampliar nuevo formulario |
| FA03 | FC04/FC07, FV02 | Búsqueda de coincidencias no fusiona automáticamente personas ni expone datos ajenos | FT3 como base; crear consulta/scope |
| FA04 | FV01 | Cambiar Por residente/Por persona conserva una identidad y los vínculos, con retorno/filtros pertinentes | Crear integración de presentaciones |
| FA05 | FC11/FC12, FV05 | Vincular persona existente a residente autorizado conserva sus otras relaciones | Crear caso Social/Identidad |
| FA06 | FC11 | No crear residente por vincular; ID ajeno, inválido o estado no admitido por el proceso se rechazan | FT2, ampliar estados/alcance reales |
| FA07 | FC13 | Parentesco de 40 caracteres admite lo permitido; valor superior se rechaza antes de SQL | Crear regresión Request/Action/Livewire |
| FA08 | FC14–FC17 | Responsable, emergencia, información y salida se conservan separados; ningún default habilita información | Crear regresión de todas las entradas |
| FA09 | FC16/FC17 | Usuario que puede editar parentesco no concede autorizaciones sensibles sin proceso/permiso/soporte válido | FT1/FT4 como base; ampliar mutaciones |
| FA10 | FV06 | Cambiar identidad compartida conserva vínculos/parentescos y no modifica correo de acceso silenciosamente | Crear caso multirresidente |
| FA11 | FV07 | Corrección de vínculo preserva residente/contacto originales, consentimientos y antecedentes sin reatribuir pasado | Crear comparación histórica y FK |
| FA12 | FV05 | Dos altas concurrentes del mismo par activo no duplican vínculo; reintento no cambia otra relación | Crear PostgreSQL desechable, no solo evento de modelo |
| FA13 | FC14/FC15 | Designar responsable/emergencia no toca registros de otro residente ni impone reglas nuevas de unicidad | Crear manipulación de ID/alcance |
| FA14 | FC20/FC21, FV09 | Cuenta Familiar nueva con datos/vínculo autorizados queda ACTIVO y vinculada al contacto existente, sin personal | FT2/FT4; ampliar alta transaccional |
| FA15 | FV09 | Cuenta sin vínculo autorizado no se informa lista para consultar; cuenta o contacto preexistentes no se duplican | Crear escenarios de coincidencia/insuficiencia |
| FA16 | FC20, FV09 | Cuenta ajena, rol excluido o cuenta multirrol manipulada no se vincula por ID para eludir permisos | FT4; crear negativos gerenciales |
| FA17 | FV09, atomicidad | Fallo parcial revierte solo efectos nuevos de cuenta/contacto/vínculo, preserva contactos y relaciones previas | Crear inyección de fallo y comparación SQL |
| FA18 | FV10/FV11 | Activar/suspender/recuperar cuenta respeta target, estado y alcance; recuperación no reactiva ni cambia roles | FT4/FT5; ampliar Gerencia |
| FA19 | Policy de lectura | Familiar activo con vínculo autorizado recibe solo contenido permitido; no clínica/PDF/doc ajenos | FT1; repetir entradas reales |
| FA20 | FC09/FC18/FC16 | Contacto o vínculo inactivo, permiso de información revocado o cuenta suspendida deniegan lectura; otros vínculos se evalúan solos | FT1; ampliar revocación y multirresidente |
| FA21 | FV08/historia | Vigencia y correcciones no borran historia; fechas/autores no conservados se muestran ausentes, no inferidos del ID | Crear prueba de historia/capacidad insuficiente |
| FA22 | Cualquier mutación | Cuenta inactiva, permiso revocado, modo Ver como rol y petición Livewire/HTTP manual no producen efecto | FT4; ampliar cada entrada |
| FA23 | Formularios/estados | Vacío, coincidencias, conflicto, fallo, cancelación, cambios sin guardar y éxito real se distinguen | Crear frontend/integración |
| FA24 | Responsive/seguridad visual | Ambos modos, teclado, foco, modales, labels, errores y reflow en cinco tamaños; sin códigos visibles o datos sensibles ocultos solo con CSS | QA visual y frontend pendientes |

## 3. Evidencia de ejecución requerida

Registrar comando, entorno desechable, número de pruebas/aserciones, resultados y limitaciones. Para FA12 y operaciones concurrentes usar PostgreSQL aislado; SQLite no acredita bloqueo por equivalencia. Las pruebas de historia comparan filas/FK/autoría, no solo un mensaje de éxito.

Las pruebas visuales cubren 1440, 1280, 1024, 768 y 390 px, recorrido de ambos modos, formularios y mensajes. Build solo cuando se implemente frontend; no hace falta para documentar los requisitos.

Comandos dirigidos candidatos para implementación posterior, **no ejecutados aquí**:

```bash
php artisan test --filter=Fase3SeguridadNucleoTest
php artisan test --filter=UsuariosResidentesV2Test
php artisan test --filter=UsuariosDocumentoIdentidadTest
php artisan test --filter=UsuariosRoutesPermissionsTest
```

Leer tests/AGENTS.md y comprobar configuración antes de ejecutarlos. Los escenarios FA nuevos requieren ampliar o crear pruebas de las entradas reales; los comandos históricos solos no completan esta matriz.

## 4. Cierre de especificación

La documentación queda trazada entre decisiones, campos, acciones, persistencia y casos. La aceptación funcional del software sigue pendiente; no se declaran 24 pruebas aprobadas por haber escrito esta tabla. Los hallazgos estáticos no sustituyen pruebas de explotación o funcionamiento en la base institucional.

## 5. Evidencia posterior de implementación — 26/10/2026

El [informe38](38-INTEGRACION-CONTACTOS-PLANIFICACION-Y-ACEPTACION.md) conserva el corte ejecutado. La tabla anterior describe el trabajo requerido al especificar; no debe interpretarse como ausencia del código posterior. Correspondencias concretas, sin declarar aceptación exhaustiva:

| Escenarios | Evidencia ejecutable posterior | Límite |
|---|---|---|
| FA01–FA02 | `GerenciaContactosTest::test_contacto_separado_no_crea_cuenta_personal_residente_o_autorizaciones` y captura desde Livewire | Rechazo de campos originales incompletos sin rellenos |
| FA03–FA04 | Directorio de dos presentaciones en `GerenciaContactosTest` y navegador real | No acredita fusión de identidades ni algoritmo de semejanza; no se implementó fusión |
| FA05–FA09 | Contactos: vínculo con autorizaciones falsas, extremos inmutables, longitud41 y residente no ADMITIDO rechazados | Autorizaciones sensibles siguen su proceso propio |
| FA10–FA11 | Corrección preserva identidad/autorizaciones; correo de acceso separado; versión nominal anterior/después | Correcciones requieren instalación37; no se reconstruye autoría antigua |
| FA12 | Cuarto caso `vinculo` de `GerenciaDocumentacionConcurrenciaPostgresTest` | Dos procesos reales, un par activo y una versión; no se extrapola a todas las carreras |
| FA13 | Flags independientes en Action/Contactos; ID de vínculo debe pertenecer al residente y contacto | No se impone unicidad institucional nueva de responsables |
| FA14–FA18 | Seis casos de `GerenciaAccesoFamiliarTest`: alta autorizada, token un uso, rollback, multirrol, suspensión, correo y token anterior | Transporte de pruebas; no afirma entrega real al correo |
| FA19–FA20 | `Fase3SeguridadNucleoTest`, suite de roles/usuarios y negativos de alcance en Contactos | No es aceptación de todas las fuentes clínicas y descargas del repositorio |
| FA21 | `GerenciaHistoriaNominalTest`: NULL original, FK, secuencia SQL, inmutabilidad y reversión; capacidad ausente en Contactos | Diseño37 solo desechable, sin instalación institucional |
| FA22 | Actor inactivo/Familiar/revocación, scoped lectura sin permiso de contactos y cuenta ajena | Las entradas HTTP convergen en Action; la matriz completa por cada entrada no se declara ejecutada por analogía |
| FA23–FA24 | Navegador Laravel con modal/captura/cuenta y matriz de58 combinaciones de Gerencia | No certifica WCAG completa ni prueba exhaustiva de teclado/lector en cada estado |

Esta correspondencia acredita pruebas concretas y señala límites. No convierte la numeración FA en24 pruebas aprobadas ni la suma de suites en aceptación de los212 escenarios.
