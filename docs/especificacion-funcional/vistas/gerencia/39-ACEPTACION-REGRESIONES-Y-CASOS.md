---
title: "Gerencia: aceptación adicional, regresiones y catálogo de casos"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: SCOPED_REGRESSION_IMPLEMENTATION_AND_ACCEPTANCE_INVENTORY
runtime_verified: true
---

# Continuación de la aceptación de Gerencia — 26/10/2026

Este informe continúa el [corte38](38-INTEGRACION-CONTACTOS-PLANIFICACION-Y-ACEPTACION.md). Corrige defectos reproducidos durante la conciliación de casos; no sustituye la norma funcional, no instala estructura y no declara los212 escenarios aceptados. Se mantienen100 tablas de negocio en la base institucional observada;35/37 siguen fuera de sus migraciones instaladas. Sin commit, push, cambio de rama o despliegue.

## 1. Inventario reproducible de los212 casos

El [script de inventario](../../../../scripts/auditar-aceptacion-gerencia.ps1) localiza la primera definición normativa de cada caso, conserva archivo/línea y genera el [CSV de212 casos únicos](../../../../output/auditoria-integral-20261025/gerencia-casos-aceptacion.csv). Referencias repetidas en secciones posteriores no se cuentan como nuevos casos. Para ocho regresiones específicas, comprueba clase/método y ausencia de fallo/error/omisión en el JUnit indicado; si falta esa evidencia, falla el script en lugar de afirmar verificación. No arranca Laravel ni consulta BDD. Su validación comprueba el inventario y la evidencia nombrada, **no ejecuta los212 casos de aceptación**.

| Contrato | Casos |
|---|---|
| [06: Personal/documentación](06-ACEPTACION-PERSONAL-DOCUMENTOS.md) | P01–P36:36 |
| [08: Solicitud por rol](08-SOLICITUD-DOCUMENTAL-POR-ROL.md) | S01–S08:8 |
| [11: Familiares/contactos](11-ACEPTACION-FAMILIARES-CONTACTOS.md) | FA01–FA24:24 |
| [13: Áreas/equipo](13-ACEPTACION-AREAS-EQUIPO.md) | AE01–AE20:20 |
| [16: Planificación/cobertura](16-ACEPTACION-PLANIFICACION-COBERTURA.md) | PC01–PC28:28 |
| [20: Residentes/ocupación](20-ACEPTACION-RESIDENTES-OCUPACION.md) | RO01–RO24:24 |
| [23: Alertas](23-ACEPTACION-ALERTAS-GERENCIA.md) | AG01–AG24:24 |
| [27: Reportes](27-ACEPTACION-REPORTES-GERENCIA.md) | RG-T01–RG-T24:24 |
| [31: Galería](31-ACEPTACION-GALERIA-GERENCIA.md) | GL-T01–GL-T24:24 |

El CSV distingue las clases base localizadas de una prueba específica del caso. Su estado conservador `ACEPTACION_COMPLETA_NO_DECLARADA` evita convertir una suite satisfactoria en212 aceptaciones por coincidencia de cantidad. Los cierres parciales concretos tienen las evidencias siguientes; no se calcula un porcentaje de avance desde archivos o métodos.

## 2. Defectos corregidos y trazabilidad

| Caso | Antes | Implementación y prueba |
|---|---|---|
| P30 | Una entrega puntual observada después podía anunciar plazo vencido, atribuyendo demora retrospectiva | `ConsultarDocumentacionPersonal::situacion` consulta la entrega original conservada, sin alterar límite o prórroga. `GerenciaSolicitudesDocumentalesTest::test_entrega_en_plazo_observada_despues_no_genera_mora_retroactiva_y_conserva_aviso` reproduce y verifica el caso; primera entrega realmente tardía se prueba por separado |
| P31 | Alta guardaba FOTO_DOC, pero ficha buscaba solo FOTO | `DocumentacionUsuarioService::obtenerChecklistUsuario` proyecta ambos como un requisito de fotografía, sin renombrar catálogo, PK, archivo o historia. `GerenciaPersonalRenovacionTest` comprueba alta real→reconocimiento; `GerenciaConciliacionDocumentalTest` cubre soporte anterior y sucesión |
| P31: conflicto | Con varios archivos se podía escoger por fecha de validación o código | Se conservan todos los candidatos vigentes y se muestra duplicidad. No se acredita validación/completitud a partir de esa elección inexistente. Ficha, resumen y exportaciones muestran advertencia; no falla `documento=NULL` |
| P31: sustitución | El botón podía enviar FOTO aunque el original fuese FOTO_DOC, rompiendo su cadena | Botones conservan tipo original; captura reconoce la clave existente sin crear catálogo. Sucesor mantiene tipo y FK al anterior correcto. Varios vigentes del mismo tipo se rechazan antes de almacenar un nuevo archivo |
| P31: procedencia | Un documento mixto podía participar en el requisito o convertirse en antecesor laboral | Lector y escritor exigen `cod_residente`, `cod_contacto` y `cod_preadmision` NULL. Regresión con mixto primero y laboral después comprueba que el mixto queda intacto |
| P12/P13/P14/P29, alcance parcial | Descarga antigua buscaba siempre disco público; revisión/carga genérica podían eludir alcance laboral | Descarga de ficha delega en la ruta con Policy y resolución de disco. Actor/permiso/alcance de carga se revalidan dentro de la transacción; revisión laboral aplica Policy aunque exista grant técnico genérico. Se conserva almacenamiento privado y, con capacidad35, cargador/hora/emisión nominales |
| P28, alcance parcial | Faltaban negativos propios del flujo documental | Archivo falso con MIME comprobado desde bytes y tamaño superior a5MB se rechazan sin filas ni archivos huérfanos. No se usan pruebas de Galería como sustituto de este flujo |
| AE07 | No había prueba de fallo de auditoría al crear área | `GerenciaAreasEquipoTest::test_fallo_de_auditoria_revierte_area_y_reintento_no_duplica`: rollback, reintento correcto, duplicado normalizado rechazado, sin auditoría falsa |
| AE08, alcance parcial | No se demostraba revocación después de abrir equipo | `test_revocar_lectura_de_equipo_abierto_no_mantiene_datos_residuales`: retira datos de equipo sin borrar asignaciones |
| PC12, alcance parcial | Las cuatro esperas por cambio de estado no eran dos guardados del mismo intervalo | Quinto dataset `horario` de `GerenciaDocumentacionConcurrenciaPostgresTest`: dos procesos reales, un confirmado y otro rechazado; una jornada, asignación y evento |
| PC13 | Faltaba fallo tras guardar la primera ocurrencia | `GerenciaPlanificacionSeguridadTest::test_fallo_en_segunda_ocurrencia_revierte_primera_jornada_evento_y_auditoria`: rollback completo y reintento posterior correcto |
| PC17 | Protección de horas históricas existía sin regresión específica | `test_turno_referenciado_conserva_horas_historicas_pero_permite_corregir_nombre`: niega cambio de horas, admite corrección de nombre y conserva la jornada cerrada |
| FA23/FA24, alcance parcial | Errores de contactos se acumulaban en listas sin asociación al campo | Formulario reutiliza `x-ui.field`, error con ID, `aria-invalid` y `aria-describedby`; se elimina duplicación del resumen. Navegador prueba Tab/Shift+Tab, Escape y retorno de foco en cinco tamaños |
| GL-T16 | Dos formularios abiertos sobre el mismo original podían sobrescribirse pese al lock SQL | Editor envía su `original` ya protegido por Livewire. Action compara metadatos después del lock y revalida autorización. Regresión con reloj congelado demuestra rechazo del segundo, entradas conservadas y ausencia de auditoría falsa; no depende de precisión de segundos ni añade columnas |

### Separación de datos y fechas

La proyección documental no modifica los originales. Una fecha de validación no se usa como fecha de carga; si esta no consta, exportación indica «No registrada». Los botones utilizan PK canónica interna; el contenido visible conserva nombres. Los archivos anteriores no se mueven indiscriminadamente de disco. Toda lectura de la nueva ruta vuelve a autorizar y valida su ubicación física.

El aviso «Corrección pendiente; la entrega original se recibió en plazo» no valida el archivo ni crea plazo adicional. Las entregas tardías siguen distinguiéndose. Un documento anulado no se utiliza como soporte de puntualidad.

## 3. Archivos y responsabilidades

- `app/Modulos/Identidad/Aplicacion/Acciones/DocumentacionUsuarioService.php`: proyección, captura, sucesión y revisión laboral.
- `app/Modulos/Identidad/Aplicacion/Consultas/ConsultarDocumentacionPersonal.php`: aviso derivado de entrega y plazo conservados.
- `app/Modulos/Identidad/Presentacion/Livewire/Paginas/UsuarioFichaPanel.php`, sus vistas y `usuarios-panel.blade.php`: tipo original, duplicidad y descarga autorizada.
- `resources/views/components/ui/document-requirement-warning.blade.php` y tres plantillas `resources/views/pdf/exports/usuarios/`: misma advertencia y fechas reales, incluida exportación usada por correo.
- `app/Modulos/Social/Presentacion/Vistas/livewire/residentes/red-apoyo-panel.blade.php`: etiquetas, ayuda y errores asociados, sin cambiar permisos o SQL.
- `app/Modulos/Institucion/Aplicacion/Acciones/GestionarFotografiasInstitucionales.php` y `GaleriaInstitucionalPanel.php`: control de edición obsoleta mediante original existente.
- Tests de Conciliación, Solicitudes, Áreas, Planificación, concurrencia documental y Galería; extensión del navegador gerencial con opción de accesibilidad.
- `scripts/probar-sqlite.ps1`: almacenamiento temporal único por ejecución, manteniendo SQLite `:memory:` y excluyendo PostgreSQL. Impide interferencia entre `Storage::fake` de procesos simultáneos; limpieza limitada a su raíz comprobada.

Los tests utilizan datos sintéticos. No se modificaron migraciones institucionales, catálogos, destinatarios reales ni remitente protegido. El mapa de compatibilidad de fotografía es de aplicación/consulta sobre dos claves existentes; no equivale a una equivalencia clínica ni altera tipos originales.

## 4. Verificaciones y evidencia

Resultados de cortes distintos se solapan y **no se suman como pruebas únicas**. El grupo ampliado corresponde a su ejecución; los cambios posteriores se complementan con los grupos finales específicos.

| Verificación | Resultado observado | Evidencia local |
|---|---|---|
| Grupo ampliado relacionado |309 pruebas /2041 aserciones, PASS | `gerencia-continuacion-aceptacion.xml/.txt`; incluye Alertas/Historial y Mapa que no estaban en218; anterior al último ajuste nominal y a edición obsoleta de Galería |
| Grupo aislado de regresiones |76 pruebas /442 aserciones, PASS | `gerencia-verificacion-aislada.xml/.txt`; lectura/escritura/seguridad documental, Planificación, Áreas y consumidores de correo |
| Procedencia final SQLite |31 pruebas /200 aserciones, PASS | `gerencia-procedencia-documental-final.xml/.txt`; captura nominal con35 y los últimos casos de contexto |
| Integración PostgreSQL y carreras |42 pruebas /285 aserciones, PASS | `gerencia-adicional-postgres-final.xml/.txt`; incluye quinto dataset horario y carrera de duplicidad de Galería; corte complementado por procedencia final |
| Procedencia final PostgreSQL |23 pruebas /171 aserciones, PASS | `gerencia-procedencia-postgres-final.xml/.txt`; mismos contratos documentales con los últimos cambios |
| Navegador de accesibilidad |Un recorrido real PASS; cinco tamaños del modal,12 controles por tamaño sin nombre ausente y foco confinado | `gerencia-accesibilidad-e2e.txt` y `gerencia-renovada/aceptacion-accesibilidad/teclado-y-formularios.json`; repetición final registrada por separado |
| Última repetición de documentos/Galería, SQLite |42 pruebas /311 aserciones, PASS | `gerencia-cierre-vacios-sqlite.xml/.txt`; incluye dos editores con reloj congelado, NULL/vacío y conservación de texto0 |
| Última repetición de Galería, PostgreSQL |19 pruebas /140 aserciones, PASS | `gerencia-cierre-vacios-postgres.xml/.txt`; misma protección de edición obsoleta y ausencia de conflicto falso |
| Navegador final |Un recorrido integrado PASS, sin errores JS/HTTP | `gerencia-continuacion-e2e.txt`; teclado/errores/operaciones reales,53,6s |
| Compilación y whitespace |Build PASS; permanece aviso de bundle mayor de500kB. `git diff --check` exit0 | `gerencia-continuacion-build.txt`, `gerencia-continuacion-diff-check.txt` |
| Enlaces de la continuación |7 documentos /124 enlaces, cero destinos faltantes | `gerencia-continuacion-enlaces.json` |
| Inventario de aceptación |212 códigos únicos en nueve contratos | `gerencia-casos-aceptacion.csv`; validación documental, sin afirmación automática de aceptación |

Los logs iniciales fallidos se preservan: P30 se reprodujo antes de corregir; el fake de MIME se sustituyó por un UploadedFile que examina contenido; dos procesos SQLite compartían almacenamiento simulado y lo limpiaban mutuamente. La repetición aislada solucionó esa interferencia sin relajar ninguna validación. PostgreSQL usa su clúster y almacenamiento temporales propios. La primera comparación de identidad de foto comparaba instancias Carbon por identidad de objeto; la regresión final compara los valores SQL originales, manteniendo la igualdad estricta del contenido conservado. La revisión independiente identificó un falso conflicto NULL/cadena vacía: se normaliza únicamente la comparación de descripción/alt, sin escribir al abrir ni convertir el texto0 en ausencia; los42/19 casos finales prueban esa corrección.

Comandos de repetición:

```powershell
./scripts/auditar-aceptacion-gerencia.ps1
./scripts/probar-sqlite.ps1 -Filtro 'GerenciaConciliacionDocumentalTest|GerenciaSolicitudesDocumentalesTest|ContratoDocumentacionUsuarioTest|GerenciaAreasEquipoTest|GerenciaPlanificacionSeguridadTest|SeguimientoCorreoDocumentalTest|HistorialCorreoDocumentalTest' -Informe output/auditoria-integral-20261025/gerencia-verificacion-aislada.xml -Log output/auditoria-integral-20261025/gerencia-verificacion-aislada.txt
./scripts/probar-sqlite.ps1 -Filtro 'GaleriaInstitucionalTest|GerenciaConciliacionDocumentalTest|GerenciaSolicitudesDocumentalesTest' -Informe output/auditoria-integral-20261025/gerencia-conflictos-guardado.xml -Log output/auditoria-integral-20261025/gerencia-conflictos-guardado.txt
./scripts/probar-sqlite.ps1 -Filtro 'GaleriaInstitucionalTest|GerenciaConciliacionDocumentalTest|GerenciaSolicitudesDocumentalesTest' -Informe output/auditoria-integral-20261025/gerencia-cierre-vacios-sqlite.xml -Log output/auditoria-integral-20261025/gerencia-cierre-vacios-sqlite.txt
./scripts/probar-postgres.ps1 -Filtro 'GerenciaDocumentacionConcurrenciaPostgresTest|GerenciaPlanificacionSeguridadTest|GerenciaSolicitudesDocumentalesTest|GerenciaConciliacionDocumentalTest|GerenciaAreasEquipoTest|GaleriaInstitucionalConcurrenciaPostgresTest' -Informe output/auditoria-integral-20261025/gerencia-adicional-postgres-final.xml -Log output/auditoria-integral-20261025/gerencia-adicional-postgres-final.txt
./scripts/probar-postgres.ps1 -Filtro 'GerenciaConciliacionDocumentalTest|GerenciaSolicitudesDocumentalesTest' -Informe output/auditoria-integral-20261025/gerencia-procedencia-postgres-final.xml -Log output/auditoria-integral-20261025/gerencia-procedencia-postgres-final.txt
./scripts/probar-postgres.ps1 -Filtro 'GaleriaInstitucionalTest' -Informe output/auditoria-integral-20261025/gerencia-cierre-vacios-postgres.xml -Log output/auditoria-integral-20261025/gerencia-cierre-vacios-postgres.txt
$env:RM_GERENCIA_QA_AMPLIACION='1'
$env:RM_GERENCIA_QA_HISTORIA='1'
$env:RM_GERENCIA_QA_RAPIDO='1'
$env:RM_GERENCIA_QA_ACCESIBILIDAD='1'
node --test tests/Frontend/gerencia-renovacion-e2e.test.js
# Retirar estas cuatro variables del proceso al terminar.
npm run build
git diff --check
```

La revisión independiente del ajuste documental encontró cruces de contexto y sustitución entre tipos; se corrigieron y una lectura final acotada confirmó las protecciones. Las comprobaciones de runtime corresponden a los registros indicados, no al dictamen estático del revisor.

## 5. Evidencia visual y límites de accesibilidad

- [Error de contacto asociado al campo,390px](../../../../output/auditoria-integral-20261025/gerencia-renovada/aceptacion-accesibilidad/contacto-error-asociado-390.png).
- [Teclado/controles por tamaño](../../../../output/auditoria-integral-20261025/gerencia-renovada/aceptacion-accesibilidad/teclado-y-formularios.json) y [diagnóstico JS/HTTP](../../../../output/auditoria-integral-20261025/gerencia-renovada/aceptacion-accesibilidad/diagnostico.json).

El recorrido confirma nombre/descripcion del diálogo,12 controles con nombre, Tab y Shift+Tab confinados, cierre Escape con confirmación, retorno al botón de origen, reflow y mensaje de error asociado en1440/1280/1024/768/390. No acredita todos los formularios, lector de pantalla real, contraste de cada estado, ni certificación WCAG completa. La matriz visual de58 combinaciones del informe38 conserva su alcance anterior.

## 6. Pendientes materiales, sin falsa culminación

**Sucesión de P03/P17 — 27/10/2026:** cerrados en el alcance del [informe41](41-ALTAS-Y-SUSTITUCIONES-CONCURRENTES.md), con carreras PostgreSQL, formularios obsoletos y archivos conservados. No se presentan como pendientes actuales las referencias históricas que siguen.

**Sucesión — 27/10/2026:** los pendientes1/2 siguientes quedaron resueltos en el alcance del [informe40](40-INSTALACION-AUTORIZADA-Y-PRORROGA-EXPEDIENTE.md): aprobación específica, instalación de35/37 y concesión por expediente comprobada. Se preserva el listado del corte anterior; los demás casos no se declaran aceptados por esta instalación.

1. Instalación específica35/37: sigue pendiente, sin convertir `continúa` en aprobación estructural. Antes de instalar hay que cerrar también las inconsistencias materiales identificadas en la aceptación del diseño.
2. P19: la regla funcional exige una única prórroga por alta, mientras la representación/aplicación revisada conserva la prórroga por destinatario. No se declara equivalencia ni unicidad global probada por la carrera de una sola fila; requiere conciliación antes de instalar.
3. P03/P17: faltan carreras específicas de alta de trabajador y sustitución documental; las carreras de solicitudes/horarios/Galería no las sustituyen.
4. PC14/PC15: aceptación específica de avisos de cambios de planificación y sus reintentos. Un aviso derivado o evento técnico no acredita comunicación entregada/leída.
5. RG-T13/T16/T17: igualdad exhaustiva PDF/CSV, PDF multipágina y caracteres/fórmulas. Las cabeceras de descarga y un PDF válido no prueban todos esos requisitos.
6. RO18 y otros escenarios: ampliar casos de origen histórico por acto, alcance del residente histórico y límite visible del historial; ejecución de Mapa no demuestra por sí sola esas variantes nuevas.
7. Accesibilidad y resto de212: completar la conciliación por cada condición y superficie. No convertir los309 tests del grupo seleccionado en309 requisitos de Gerencia aceptados.

Dictamen de este hito: defectos concretos corregidos y regresiones verificadas en el alcance descrito; **aceptación e instalación integral de Gerencia no declaradas**. Pruebas administrativas no son validación clínica y no modifican el motor experto.

Git: rama `feature/apoyo-decision-informe-neurocognitivo`, HEAD `6d7b8c6d`, cambios locales simultáneos conservados. `gerencia-continuacion-estado.txt` y `gerencia-continuacion-diff-stat.txt` registran el árbol, incluyendo trabajo ajeno que no se atribuye a esta entrega. No se ejecutaron staging, commit, push, cambio de rama ni despliegue.
