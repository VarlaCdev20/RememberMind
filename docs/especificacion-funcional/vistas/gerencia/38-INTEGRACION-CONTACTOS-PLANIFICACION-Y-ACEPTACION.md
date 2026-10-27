---
title: "Gerencia: integración de contactos, planificación y aceptación"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: SCOPED_IMPLEMENTATION_SQL_AND_BROWSER_EVIDENCE
runtime_verified: true
---

# Integración posterior de Gerencia — 26/10/2026

**Instalación posterior — 27/10/2026:** el [informe40](40-INSTALACION-AUTORIZADA-Y-PRORROGA-EXPEDIENTE.md) acredita autorización, instalación local de35/37 y conciliación P19. Los estados pendientes que siguen conservan este corte histórico.

**Continuación posterior:** el [informe39](39-ACEPTACION-REGRESIONES-Y-CASOS.md) corrige mora retrospectiva, reconocimiento de fotografías y sustitución/privacidad de archivos; añade pruebas de concurrencia, rollback, edición obsoleta y teclado. El listado de pendientes y los números siguientes conservan este corte38; no se interpretan como ausencia de las correcciones posteriores.

Este corte continúa el [informe 36](36-RENOVACION-GERENCIA-IMPLEMENTACION-Y-PRUEBAS.md). Implementación y prueba desechable no equivalen a instalación institucional ni aceptación de los 212 escenarios. El árbol local conserva cambios simultáneos ajenos; no se ejecutan commit, push, cambio de rama ni despliegue.

## 1. Decisiones y estado de instalación

| Decisión/fuente | Aplicación | Estado |
|---|---|---|
| Respuesta expresa: enlace seguro al correo para que Familiar elija contraseña | Broker de identidad existente, token hasheado de un uso; Gerencia no ve ni elige contraseña | Implementado y probado; transporte de prueba sin correo externo |
| Respuesta expresa DA01: bloquear desactivar área con asignaciones o planes activos | Revalidación transaccional y coordinación con escritores relacionados | Implementado y probado; sin cierre clínico implícito |
| 17B: gestión gerencial de contactos/vínculos | Cuatro permisos existentes, sin permisos globales de usuarios o escritura clínica | Aplicados al rol GERENTE en la base configurada; caché invalidada y auditoría técnica registrada |
| [Diseño 35](35-PROPUESTA-ESTRUCTURAL-PERSONAL.md): ocho columnas y cuatro tablas | Documentación laboral, plazos, entregas y movimientos | Diseño aprobado para implementación inicial desechable; instalación institucional pendiente de decisión específica |
| [Diseño 37](37-PROPUESTA-HISTORIA-VINCULOS-ASIGNACIONES.md): dos tablas | Versiones nominales de vínculos y eventos de asignación | Propuesto, prototipado y probado; aprobación e instalación institucional pendientes |

Inventario institucional leído: **116 físicas = 100 de negocio + 16 técnicas**, base `remembermind_dev`, PostgreSQL local. Diseño35 instalado daría104 de negocio; ambos diseños darían106, bajo el máximo110. No se añadieron tablas en esa base en este corte. Se preparó respaldo privado y se verificó su catálogo; no se afirma un ensayo de restauración íntegra por esa comprobación. El respaldo contiene datos institucionales y no se publica ni se incluye en el repositorio.

Permisos instalados: `contactos.ver`, `contactos.gestionar`, `residentes_contactos.ver`, `residentes_contactos.gestionar`. Se reutilizan los catálogos existentes. Ninguno permite cambiar autorizaciones informativas/salida por editar parentesco. Evidencia técnica: `output/auditoria-integral-20261025/gerencia-permisos-contactos-instalados.json` y `gerencia-respaldo-previo.json`.

## 2. Familiares y contactos: proceso y límites

Entrada existente **Personal → Familiares y contactos**, ruta `admin.familia-social.red-apoyo`. Dos presentaciones de una identidad: Por residente y Por persona. Búsqueda, paginación, formularios separados y errores junto a los campos; no hay PK visibles como contenido. Tablas anchas conservan desplazamiento local y no desbordan la página.

1. Registrar contacto conserva nombres/apellidos originales por separado y medios de contacto. No crea residente, personal, usuario ni autorizaciones.
2. Vincular selecciona residente ADMITIDO y contacto ACTIVO existentes. Responsable y emergencia son independientes; información y salida empiezan sin acreditación. La misma pareja activa no se duplica bajo dos solicitudes concurrentes.
3. Corregir parentesco/funciones conserva extremos residente/contacto y autorizaciones. La historia nominal exige diseño37; sin él se rechaza antes de escribir, con explicación visible. Vigencia sigue la misma protección.
4. Crear acceso Familiar exige contacto y vínculo informativo autorizados existentes, correo de acceso único y rol exclusivo FAMILIAR; no fabrica Personal. No es el correo administrativo de contacto.
5. La cuenta queda guardada transaccionalmente; el broker envía el enlace después. Un fallo de transporte posterior no se presenta como rollback de SQL: se informa cuenta creada/envío pendiente y se permite reemitir. Aceptación por transporte no acredita entrega o lectura.
6. Suspender, activar, recuperar o editar correo de acceso no cambia otros vínculos, rol ni correo de contacto. Cambiar correo invalida tokens anteriores. Recuperar no reactiva una cuenta suspendida.

Las entradas HTTP anteriores convergen en la misma Action; se eliminaron las divisiones de nombre, apellidos de relleno, información concedida por defecto y edición cruzada por identificador. Las altas genéricas de usuarios rechazan Familiar y remiten al circuito de contacto autorizado.

### Seguridad

Cuenta activa, permiso explícito, alcance y Policy de residente se revalidan en backend. Sin `contactos.ver`, el modo Por persona se deniega y la lectura queda limitada a vínculos del residente autorizado. La relación de acceso se carga solo para quienes pueden gestionarla. Se bloquean cuenta ajena, multirrol y Familiar manipulando la llamada. Vista previa no escribe. Los formularios confirman mutaciones, impiden doble envío y permiten Escape con confirmación antes de descartar.

## 3. Planificación, área e historia

- Gerencia consulta una sola planificación real: vista semanal inicial, mes/día y filtro de Enfermería. La planilla secundaria ya no calcula una cobertura ficticia ni ejecuta el generador alternativo para Gerencia.
- Cada alta registra ocurrencias explícitas de los días seleccionados durante la semana desde la fecha inicial; no promete recurrencia indefinida. Nuevas observaciones son texto original, sin escribir agrupaciones JSON. El lector legacy solo conserva los grupos anteriores existentes.
- Turno con jornadas referenciadas no admite modificar sus horas: se informa crear otra definición para el nuevo horario. No se reescriben intervalos históricos.
- Finalizar/corregir exige historia nominal37. Se conservan ocurrencias previas al corte, observaciones originales y cancelaciones previas; solo se modifican ACTIVO/ACTIVA y se rechaza un origen ya inactivo/anulado.
- Se bloquean cruces nocturnos reales, incluida la guardia del día anterior que permanece activa al editar. Fin contiguo no es cruce.
- Área, cuenta, trabajador, turno y jornada se revalidan bajo bloqueo. Jornada cerrada no admite asignaciones. La búsqueda por fecha funciona tanto con DATE de PostgreSQL como con la representación del cast en SQLite; no crea otra jornada para eludir el cierre.
- DA01 bloquea desactivar con asignaciones/planes activos. Guardar un plan activo revalida el área bajo el mismo bloqueo. La desactivación no traslada personal ni cierra planes por efecto lateral.
- Ocupación verifica Policy por residente histórico. Muestra traslado/motivo solo cuando el evento conservado lo acredita; si no existe, «Origen no documentado». No interpreta todas las filas históricas como admisiones.

El diseño37 conserva dos granos diferentes, sin JSON/EAV, con FK compuestas que impiden encadenar historias de otro propietario, trigger INSERT que exige el anterior inmediato y triggers SQL que rechazan UPDATE/DELETE. Baseline desconocida de un vínculo mantiene autor/fecha del acto NULL; fecha técnica de conservación no se presenta como fecha original. Reversión poblada se rechaza. Los dibujos y diccionario exactos están en la propuesta37, fuera de las migraciones automáticas. La propuesta instalable, inventarios y SHA256 de ambos DDL están en `output/auditoria-integral-20261025/gerencia-propuesta-instalacion.json`; el manifiesto no constituye aprobación.

## 4. Trazabilidad de implementación

Rutas relativas al repositorio:

| Requisito | Archivos responsables | Pruebas |
|---|---|---|
| Contacto, vínculo, alcance y defaults | `app/Modulos/Social/Aplicacion/Acciones/GestionarRedApoyo.php`; `Presentacion/Livewire/Paginas/RedApoyoPanel.php`; `Presentacion/Vistas/livewire/residentes/red-apoyo-panel.blade.php` | `GerenciaContactosTest` |
| Historia de vínculo | `app/Modulos/Social/Aplicacion/Acciones/ConservarVersionVinculo.php`; DDL37 | `GerenciaHistoriaNominalTest`, negativos de capacidad en `GerenciaContactosTest` |
| Acceso Familiar y broker | `app/Modulos/Identidad/Aplicacion/Acciones/GestionarAccesoFamiliar.php` | `GerenciaAccesoFamiliarTest`: un uso, rollback SQL, correo separado, suspensión y multirrol |
| Entradas antiguas convergentes | `GestionarVinculoFamiliarHttp.php`, `VincularContactoResidenteHttp.php`, `StoreFamiliarAdultoRequest.php`, `AdultoMayorFamiliarController.php`, modal residente; `GestionarUsuariosHttp.php`/`GestionarUsuariosPanel.php` | Regresiones Social/Identidad incluidas en el grupo ampliado |
| DA01 y escritores | `GestionarAreasInstitucionales.php`, `GestionarPlanesCuidado.php`, `GestionarInstitucionHttp.php` | `GerenciaAreasEquipoTest`, concurrencia real de Planificación |
| Jornadas, cruces, ocurrencias y eventos | `GestionarHorariosPersonal.php`, `GestionarPlazasEnfermeria.php`, `ConservarEventoAsignacion.php`, `PersonalInstitucionalHorarios.php` y vista compartida | `GerenciaPlanificacionSeguridadTest`, `PersonalInstitucionalHorariosTest`, `GerenciaHistoriaNominalTest` |
| Calendario/filtro y horas históricas | `TurnosAsignacionesPanel.php`, `GestionarTurnosInstitucionales.php` y vista gerencial | Pruebas de Planificación y recorrido navegador |
| Origen de ocupación autorizado | `MapaHabitacionesService.php` | `MapaHabitacionesTest` |
| Permisos y navegación | `RolesAndPermissionsSeeder.php`, sidebar institucional existente | Matriz de permisos y tests de contactos; evidencia de instalación de cuatro grants |
| Feedback móvil completo | `resources/frontend/styles/design-system/patterns/management-workspace.css` | Navegador real de cierre y build |

Los archivos se localizan por nombre dentro del propietario indicado; no se crea otro módulo ni un segundo almacén de auditoría. El inventario completo del árbol está en los snapshots de Git de este corte, incluyendo cambios ajenos que no se atribuyen a esta entrega.

## 5. Pruebas ejecutadas

Datos sintéticos, SQLite en memoria/archivo temporal y PostgreSQL desechable con runner propio loopback. No se reinició ni sembró la base institucional.

| Verificación | Evidencia y alcance |
|---|---|
| Backend ampliado definitivo | `gerencia-aceptacion-cierre.xml/.txt`: **218 pruebas /1401 aserciones, PASS**; repetición completa posterior a corregir las expectativas/fixtures, sin casos omitidos |
| Backend específico | `gerencia-cierre-especifico-final.xml/.txt`: **45 pruebas / 253 aserciones, PASS**; corte anterior al último negativo de cancelación, cubierto de nuevo por el grupo ampliado |
| PostgreSQL inicial integrado | `gerencia-cierre-postgres.xml/.txt`: **29 pruebas / 148 aserciones, PASS**; no se suma como casos únicos al grupo posterior |
| Bloqueos reales de Planificación | `gerencia-concurrencia-planificacion-final.xml/.txt`: **4 pruebas / 28 aserciones, PASS**; se observa espera Lock antes de cambiar el estado y liberar la transacción |
| Navegador completo | `gerencia-cierre-e2e.txt`, matriz: **58 combinaciones** (11 vistas × 5 anchos + 3 oscuras), sin desbordamiento de página; diagnóstico JS/HTTP vacío y sin errores de servidor |
| Historia/procedencia final, SQLite | `gerencia-historia-detalle-final.xml/.txt`: **10 pruebas /81 aserciones, PASS**; incluye los nuevos negativos SQL y la ficha gerencial con contactos autorizados |
| Historia/procedencia final, PostgreSQL | `gerencia-historia-sql-postgres.xml/.txt`: **10 pruebas /61 aserciones, PASS**; incluye los nuevos triggers de secuencia y recuperación real de autor/fecha |
| Contactos/áreas/planificación integrados, PostgreSQL | `gerencia-aceptacion-postgres-final.xml/.txt`: **41 pruebas /222 aserciones, PASS**; incluye cuatro carreras documentales/vínculo y cuatro esperas de Planificación; corte complementado por el grupo de historia final |
| Regresiones de lectura y colección protegida | `gerencia-regresiones-final.xml/.txt`: **6 pruebas /89 aserciones, PASS**; se actualiza la expectativa obsoleta de permisos y se carga la colección desde SQL, sin desbloquearla |
| Limpieza acotada de escritura legacy | `gerencia-horarios-limpieza-final.xml/.txt`: **3 pruebas /16 aserciones, PASS**; eliminado el serializador privado sin consumidores que escribía metadatos JSON en observaciones; lector anterior conservado |
| Navegador posterior al feedback | `gerencia-cierre-e2e-final.txt`: **1 recorrido integrado PASS**, con cuenta/carga documental y confirmación completa en móvil; no sustituye matriz de58 |
| Build final | `gerencia-cierre-build-final.txt`: **PASS**,116 módulos; permanece aviso de bundle mayor de500kB |
| Documentación y whitespace | `gerencia-cierre-enlaces.json`: **9 documentos /163 enlaces, cero faltantes**; `gerencia-cierre-diff-check.txt`: **exit0** |

Los fallos iniciales no se ocultaron: se corrigieron la búsqueda de jornada en SQLite y la lectura de la instantánea estadística de PostgreSQL dentro de la transacción del test. Esta última corrección permite observar el bloqueo del worker nuevo; no elimina ni simula la comprobación del lock. La revisión independiente encontró la cancelación sobrescrita y los saltos de secuencia; se corrigieron en implementación y se añadieron regresiones. Un primer grupo ampliado terminó con214 casos satisfactorios y2 fallos de expectativas/fixtures antiguas: la prueba de ficha aún esperaba403 para Contactos y la de presentación inyectaba la colección bloqueada. Se corrigieron los tests al contrato aprobado, manteniendo negativos de revocación y la protección de Livewire; el grupo de6 posterior pasó. Los dos casos nuevos de historia requirieron crear explícitamente Personal en su fixture antes de usarlo; el runtime no crea fichas como fallback. **La repetición definitiva pasó los218 casos/1401 aserciones**, incluidos esos dos nuevos casos; el log inicial no representa el resultado final.

La revisión independiente de seguridad de contactos y las revisiones de Planificación/BDD son acotadas. El gate BDD detectó dos P2 (secuencia SQL y procedencia inventada); una nueva lectura independiente confirmó ambas correcciones, sin afirmar ejecución propia de suites ni instalación institucional.

Comandos finales y nombres de evidencia:

```powershell
php artisan test --filter='Gerencia|ReportesGerencia|ContratoPersonalInstitucionalTest|ContratoDocumentacionUsuarioTest|ContratoSeguridadCuentaTest|PersonalInstitucionalHorariosTest|PersonalInstitucionalResponsiveTest|AreasInstitucionalesV2Test|GaleriaInstitucionalTest|AlertasExperienciaVisualTest|RolesVistasPermisosMatrixTest|Fase3SeguridadNucleoTest|UsuariosResidentesV2Test|UsuariosDocumentoIdentidadTest|UsuariosRoutesPermissionsTest|AuthenticationTest|PasswordInicialPersonalTest|UpdatePasswordTest|PasswordResetTest|RecuperacionContrasenaFlujoTest' --exclude-group=postgresql --log-junit=output/auditoria-integral-20261025/gerencia-aceptacion-cierre.xml
./scripts/probar-postgres.ps1 -Filtro 'GerenciaContactosTest|GerenciaAccesoFamiliarTest|GerenciaHistoriaNominalTest|GerenciaDocumentacionConcurrenciaPostgresTest|GerenciaConcurrenciaPlanificacionPostgresTest|GerenciaPlanificacionSeguridadTest|GerenciaAreasEquipoTest' -Informe output/auditoria-integral-20261025/gerencia-aceptacion-postgres-final.xml -Log output/auditoria-integral-20261025/gerencia-aceptacion-postgres-final.txt
./scripts/probar-postgres.ps1 -Filtro 'GerenciaHistoriaNominalTest|PersonalInstitucionalHorariosTest' -Informe output/auditoria-integral-20261025/gerencia-historia-sql-postgres.xml -Log output/auditoria-integral-20261025/gerencia-historia-sql-postgres.txt
$env:RM_GERENCIA_QA_AMPLIACION='1'
$env:RM_GERENCIA_QA_HISTORIA='1'
node --test tests/Frontend/gerencia-renovacion-e2e.test.js
# RM_GERENCIA_QA_RAPIDO=1 repite operaciones después del ajuste de feedback sin sustituir la matriz completa.
npm run build
git diff --check
```

Retirar las variables de QA del proceso al terminar. Registros en [evidencia local](../../../../output/auditoria-integral-20261025). No sumar grupos solapados como pruebas únicas ni convertir pruebas técnicas en validación clínica.

## 6. Evidencia visual

Capturas de Laravel con datos sintéticos:

- [Contactos 1440](../../../../output/auditoria-integral-20261025/gerencia-renovada/contactos-1440.png) y [390](../../../../output/auditoria-integral-20261025/gerencia-renovada/contactos-390.png).
- [Cuenta Familiar creada](../../../../output/auditoria-integral-20261025/gerencia-renovada/depuracion-rapida/contactos-cuenta-390.png), repetición tras corregir el texto cortado.
- [Matriz completa](../../../../output/auditoria-integral-20261025/gerencia-renovada/matriz.json), [diagnóstico completo](../../../../output/auditoria-integral-20261025/gerencia-renovada/diagnostico.json) y [diagnóstico de repetición](../../../../output/auditoria-integral-20261025/gerencia-renovada/depuracion-rapida/diagnostico.json).

No es una certificación WCAG ni prueba exhaustiva de lector de pantalla/contraste de cada estado. Se verifica reflow en1440/1280/1024/768/390, controles reales y errores del navegador; los formularios reutilizan el componente modal oficial.

## 7. Qué sigue pendiente y quién lo decide

1. **Propietaria:** aprobación/instalación institucional específica de35 y37. Mientras tanto las operaciones que necesitan la historia37 se bloquean sin efectos; no se guardan en observación o auditoría para fingir equivalencia.
2. **Aceptación completa:** conciliar y ejecutar individualmente los212 escenarios; no se declara ese total aprobado por tener una suite con un número parecido de métodos. La evidencia de este corte acredita su alcance concreto.
3. **Archivos anteriores:** transición revisada de ficheros históricos y equivalencia FOTO/FOTO_DOC entre consumidores, sin publicar ni mover indiscriminadamente documentos sensibles.
4. **Fuera del alcance cambiado:** generador alternativo de Enfermería usado por otros actores, otras entradas generales de cuentas y deuda funcional de otros módulos. El calendario de Gerencia ya no depende del indicador estimado de aquel generador.
5. **Reglas clínicas y experto:** no se modifican por completar Gerencia. Pruebas administrativas y perfiles sintéticos no acreditan validación clínica.

El diseño y los tests no sustituyen las aprobaciones pendientes. No se declara Gerencia íntegramente instalado/aceptado mientras estos puntos materiales permanezcan.

## 8. Git y cierre verificable

Rama observada `feature/apoyo-decision-informe-neurocognitivo`; HEAD `6d7b8c6d`. `git status --short` y `git diff --stat` se conservan en `gerencia-cierre-git-status.txt`/`gerencia-cierre-git-diff-stat.txt`, dentro de la evidencia local. Incluyen cambios simultáneos ajenos que se preservaron y no se atribuyen en bloque a esta entrega. No se ejecutó `git add`, commit, push, cambio de rama ni despliegue. No se modificaron contraseñas institucionales, remitente protegido ni datos clínicos para completar esta tarea.

Dictamen: **implementación y verificación técnica acotadas satisfactorias; instalación estructural pendiente y aceptación exhaustiva no declarada**. Los218 tests son casos de software del grupo seleccionado, no los212 escenarios documentales ni una validación clínica.
