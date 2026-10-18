# Informe neurocognitivo de O.R.I.O.N.

Implementación del documento solicitado por la propietaria el 17/10/2026. Sustituye la jerarquía visual de Fase 4; mantiene el aislamiento de simulaciones y las restricciones de inferencia clínica. No modifica el esquema, los datos locales, los instrumentos ni las reglas del motor.

## Fuentes y versiones

Se consultaron la arquitectura híbrida, los contratos de lectura y explicación, el baseline y las decisiones aprobadas de IADL/conservación/revisión profesional. El Documento Maestro y los contratos externos se inspeccionaron como fuentes del proyecto, sin convertir las propuestas en aprobación clínica:

| Fuente | Evidencia de revisión |
|---|---|
| [Documento Maestro](https://docs.google.com/document/d/1d68S-EZTyYVKsYm-cOtaktYSNUBfDAi59FHexkEd88I/edit) | Exportación HTTP 200. SHA-256 del texto UTF-8: `EF9F3A9852EAD41519EB5A32E1D7F92A91C280F863340AC0DEFEDA4898D0BCFF` |
| [DCL-04](https://docs.google.com/document/d/1wZqJRreRVbQld2WakWssWhehq8ZZshgEQg7RYHg78Pk/edit) | `EXPL-DCL-20261017-v0.1`, propuesta no activa; texto SHA-256 `5A0BD370E160690116DB419DB5A52E7FD5B483AC457321324F86CAC022294D0C` |
| [DCL-04A](https://docs.google.com/spreadsheets/d/1VLh9uKkxYRQlP5DlOGG4Nx12-qc9oXVjuCTtmxgjaQY/edit) | Hojas Pantalla clínica, Mensajes, Ejemplos ficticios y Trazabilidad; plantillas E01–E10 no activas. La presentación experimental respeta ausencia, contradicción, falta de interpretabilidad, episodios y requisitos no acreditados. |
| [DCL-03](https://docs.google.com/document/d/108-74YgGmw3JmA3Lba_bmwMcDeR39oVKIkamtyqJLgw/edit) | `DCL-PET-20261017-v0.1`, investigación sin uso clínico; SHA-256 `BCDCDD38F68A99D1B5296DC860B690980B7C053EF161DF63615B7C031E39A7D7` |
| [PR #15](https://github.com/VarlaCdev20/RememberMind/pull/15) | Referencia de investigación. Checkout local `pr15-ci-utf8/RememberMind`, HEAD `8085ec0e`; guía DCL y constructor de informes experimentales. No se presupone que la rama esté fusionada. |
| [PR #16](https://github.com/VarlaCdev20/RememberMind/pull/16) | Checkout `pr16-ci-iadl/RememberMind`, HEAD `195a39cd`; contrato de lectura IADL, analizador descriptivo, permiso específico y diccionario. Solo Model/tabla/adaptador aprobados estaban incorporados en el árbol de trabajo. |

El acceso de red dejó de estar disponible durante las revisiones delegadas. Las comprobaciones restantes usaron esos checkouts locales y contratos conservados. No se afirma aprobación clínica ni estado remoto actual de fusión de los PR.

## UX FLOW y reutilización

Objetivo: comprender qué resultado puede sostenerse, por qué y qué falta antes de confirmarlo. Usuarios: Medicina, Psicología y Enfermería dentro de sus permisos y alcance actuales. Entrada: la misma ficha del residente y sus rutas de resultados. Salida: lectura de la evaluación exacta y revisión profesional separada cuando está habilitada; volver a la ficha conserva el recorrido.

Orden: contexto del residente → conclusión principal → cinco funciones → fundamento y fuentes → autonomía cotidiana → faltantes → significado → puntos a revisar → historial → confirmación profesional. No hay dashboard, puntuación global, radar ni semáforo de enfermedad.

| Información | Fuente | Presentación |
|---|---|---|
| Conclusión | Resultado autorizado y trazable del lector existente | Primer bloque destacado, con alcance explícito |
| Cinco funciones | Perfil de la evaluación histórica | Frases naturales; ausencia nunca equivale a normalidad |
| Fundamentos | Soportes conservados, admisibilidad y relaciones del mismo episodio | Razones breves; fuentes en «Ver en qué se basa» |
| Observaciones | Controles cognitivos ordinarios autorizados | Fechas y responsable; no se promueven a evidencia objetiva |
| Autonomía | Fuente IADL específica y permiso del contrato PR #16 | Actividad, oportunidad, ayuda, causa declarada y nivel previo; sin acreditación automática |
| Revisión | Dictámenes conservados por disciplina | Psicología y Medicina separadas; autoría derivada del backend |
| Identificadores/reglas/versiones | Traza conservada | Exclusivamente en auditoría técnica restringida |

Se reutilizan Blade, Livewire, layouts, iconos, cards, drawers y tokens del Design System existente. Una columna en móvil; dos funciones por fila desde 768 px y tres desde 1280 px. Identidad y texto largo pueden envolver; controles de al menos 44 px, foco visible y detalles operables con teclado. No se redefine la paleta.

Skills aplicadas: selector UI, flujo, redacción, Design System, responsive/accesibilidad e ingeniería del sistema experto. No se requiere una nueva dirección artística ni imagen; la captura anterior sirve de contexto, y el documento actual solicita reorganizar la vista. No se crean formularios clínicos nuevos ni animaciones; se conserva el formulario de revisión y su protección de captura pendiente.

## Implementación e integración

- `PresentarInformeCognitivo` conserva el contrato validado por área y agrega `narrativa`.
- `ExplicarResultadoNeurocognitivo` genera las frases operativas mediante plantillas; no interpreta puntajes, ejecuta reglas ni llama a IA generativa.
- `PresentarPatronExperimental` recibe el resultado tipado del clasificador existente. Conserva siempre la advertencia y `publicable_clinicamente=false`. Está conectado al lector explícito de instantáneas; no se mezcla con la lectura operativa. La contradicción utiliza el motivo original del resultado, incluida la funcional; la evidencia no interpretable muestra fuente, fecha y razón legible, conservando códigos en auditoría.
- El mismo parcial `informe-narrativo.blade.php` se comprueba con el caso sintético completo y las abstenciones. No hay ruta nueva que active Petersen; las pruebas renderizan ese parcial con resultados reales del clasificador y objetos ficticios del Domain.
- `ConsultarAutonomiaDescriptiva` consulta solo la fuente instalada y autorizada. Respeta fechas, residente, rectificaciones y lotes excluidos; conserva su naturaleza descriptiva. La ayuda institucional no se interpreta como incapacidad. Las discrepancias simultáneas de oportunidad, desempeño, ayuda, causa o nivel previo conservan cada descripción y responsable, sin elegir una por su PK.
- El lector reutiliza las agrupaciones de origen/episodio para no repetir evidencias, sin cambiar sus comprobaciones de independencia.
- `consultarTrazabilidad` exige además la autorización técnica ya existente: cuenta activa, Superadministración, `auditoria.ver`, sin preview y acceso al residente. Se reautoriza durante cada interacción; ocultar el botón no es el único control.

Rutas conservadas:

- Enfermería: `/admin/enfermeria/pacientes/{residente}/resultados-experto`.
- Medicina: `/admin/medico/residente/{residente}/evaluacion-cognitiva/resultados-experto`.
- Psicología: `/admin/psicologia/residente/{residente}/resultados-experto`.

La clasificación no se recalcula al consultar ni al cambiar de área o de evaluación. Los cambios de área tampoco cambian la conclusión global. No se modifica el formulario de firma, su idempotencia ni la conservación append-only.

La instrucción posterior de la propietaria elimina el selector de vista previa: la ruta habitual consulta el resultado guardado disponible. Los parámetros antiguos `?simulacion=0` y `?simulacion=1` ya no controlan ni ocultan resultados. Una evaluación operativa conserva prioridad; cuando no existe, se puede consultar la instantánea del lote local autorizado, con su procedencia visible. La selección de una evaluación utiliza su ID exacto. Se mantienen entorno/capacidad local, cohorte ficticia, integridad y Policy; no se mezclan soportes ni se admiten firmas clínicas sobre SIM. La auditoría conserva su autorización independiente.

El error de acceso a offsets nulos en la cabecera se corrige trasladando el resumen de revisión a `PresentarInformeCognitivo::resumirRevision`. La ausencia de dictamen y las disciplinas incompletas se representan explícitamente sin fabricar confirmaciones. Blade no indexa las dos disciplinas para formar ese resumen. Se eliminó «Salir de la vista previa» y se muestran los cinco requisitos conservados con estado, fundamento descriptivo, fechas de soporte y fecha de corte.

Una evaluación histórica sin IADL conservada no se completa con las observaciones funcionales actuales; esa ausencia se indica expresamente. Un perfil experimental con datos suficientes y requisitos conocidos que no permiten un patrón se diferencia de un perfil incompleto, incluso en el significado y los puntos de revisión.

## Alcance disponible y límites

La pantalla muestra las evaluaciones disponibles bajo su contrato y procedencia. Actualmente `lectura_clinica` sigue vacío: no se genera un patrón operativo cambiando la presentación. La consulta del lote local muestra resultados realmente guardados de casos simulados; no los convierte en evaluaciones clínicas reales. Un caso sin evaluación de ninguna fuente autorizada muestra una conclusión pendiente y los faltantes concretos.

El caso hipotético completo presenta dificultades de memoria y atención, autonomía relativamente conservada según revisión ficticia, patrón amnésico multidominio candidato y ausencia de faltantes, con advertencia inequívoca de simulación. No son datos predeterminados de un residente.

El permiso `controles_funcionales_iadl.ver` del contrato PR #16 no se instala ni concede automáticamente. Si no existe o no está autorizado, se muestra la indisponibilidad sin consultar sus filas. No se sustituye por el permiso de valoraciones funcionales básicas. Incorporar los permisos y captura del PR completo sigue fuera de esta entrega.

Siguen pendientes G08/G12/G13 y la aprobación de los soportes clínicos operativos descritos en la matriz de interpretación. La firma final de un caso no sustituye esa aprobación.

## Verificación y entrega

Archivos de esta entrega (rutas relativas al proyecto):

| Grupo | Archivos |
|---|---|
| Consultas | `app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarAutonomiaDescriptiva.php` (nuevo), `LecturaResultadosExperto.php`, `LecturaFuenteEvidencia.php` en la misma carpeta |
| Presentación determinista | `app/Modulos/ApoyoDecision/Aplicacion/DTOs/ExplicarResultadoNeurocognitivo.php` y `PresentarPatronExperimental.php` (nuevos), `PresentarInformeCognitivo.php` |
| Integración y seguridad | `app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php`, `Presentacion/Policies/EvaluacionExpertaPolicy.php`, `Infraestructura/Simulacion/LectorSimulacionLocal.php` dentro del mismo módulo |
| Blade | `app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto-residente.blade.php`; parciales `informe-narrativo.blade.php` (nuevo), `evidencias.blade.php`, `historial.blade.php`, `revision-profesional.blade.php` en `resultados-experto/` |
| Estilos | `resources/frontend/styles/design-system/patterns/expert-results.css` |
| Pruebas | `tests/Feature/InformeNeurocognitivoTest.php`, `tests/Unit/PresentacionNeurocognitivaTest.php` (nuevos), `tests/Feature/InformeCognitivoTest.php`, `tests/Feature/SimulacionLocalExpertoTest.php`; ampliación opcional de razones en `tests/Unit/SistemaExperto/Domain/Entities/EscenarioEntidades.php`, sin cambiar sus valores predeterminados |
| Documentación | Este informe, `docs/sistema-experto/README.md` y `CONTRATO_INFORME_COGNITIVO.md` |

Las pruebas cubren resultado completo, memoria desconocida, autonomía pendiente, contradicción, diferencial no resuelto, ausencia de registros, evidencia no interpretable/excluida, permisos, episodios repetidos, IADL institucional, rectificación y revocación de auditoría. Las pruebas de presentación no cambian los resultados del motor ni escriben diagnósticos o alertas.

Evidencia de verificación, conservada en `%TEMP%`:

| Comprobación | Resultado | Evidencia |
|---|---|---|
| Regresión SQLite anterior a las últimas adiciones concurrentes | 117 pruebas, 661 aserciones, sin errores ni fallos | `orion-rediseno-entrega.sqlite.xml` y `.log` |
| Cierre de narrativa y sus casos Feature, SQLite | 31 pruebas, 221 aserciones, sin errores ni fallos | `orion-explicacion-cierre.sqlite.xml` y `.log` |
| Mismo cierre, PostgreSQL 18 desechable | 31 pruebas, 221 aserciones, sin errores ni fallos | `orion-explicacion-cierre.pg.xml` y `.log` |
| Integración concurrente, SQLite | 36 pruebas, 185 aserciones; 35 aprobadas y una expectativa de acceso desactualizada | `orion-integracion-concurrente.sqlite.xml` y `.log` |
| Integración concurrente, PostgreSQL 18 desechable | 67 pruebas, 379 aserciones; 66 aprobadas y la misma expectativa desactualizada | `orion-integracion-concurrente.pg.xml` y `.log` |
| Reejecución de la expectativa corregida, SQLite y PostgreSQL | 1 prueba, 4 aserciones, aprobada en cada motor | `orion-cuenta-inactiva-cierre.sqlite.log`; `orion-cuenta-inactiva-cierre.pg.xml` y `.log` |
| Formato PHP y espacios del diff de esta entrega | Pint y `git diff --check` aprobados | Comprobación dirigida a los archivos enumerados, sin formateo global |
| Frontend | Build aprobado, Vite 3,77 s | `orion-rediseno-entrega.build.log` |
| Lectura local ordinaria y renderizado de Blade | Tres expedientes ficticios comprobados, cero escrituras SQL; sin evaluación publicable ni etiquetas SIM en la lectura normal | Script temporal `verificar-valoracion-operativa-local.php`; comprobación de Gloria, Mercedes y Elena |
| Revisión independiente del contrato experto | PASS técnico acotado; 11 pruebas unitarias y 87 aserciones aprobadas | Revisión de motivos de abstención, contradicción funcional y razones I/X; no acredita aprobación clínica |
| Revisión independiente de aislamiento y autorización | PASS estático, sin P1/P2 pendientes en el alcance revisado | Modo explícito, revocación, instantánea íntegra, firmas y auditoría |
| QA visual y funcional en navegador | **Pendiente** | Navegador deshabilitado en esta sesión |

La única expectativa fallida de las ejecuciones de integración pedía HTTP 403 al inactivar una cuenta. La Policy oculta el recurso con HTTP 404; la prueba concurrente se corrigió a `assertNotFound()` y se volvió a ejecutar correctamente en ambos motores. No se modifica la Policy para obtener un resultado verde. Los informes originales se conservan tal como se produjeron; no se presenta esa ejecución completa como aprobada ni se suman suites solapadas para anunciar una cifra total.

Una ejecución anterior de PostgreSQL coincidió con la incorporación concurrente de la propiedad `simulacion` y leyó el nuevo Blade con una clase antigua ya cargada. La integración de la tabla anterior se ejecutó después en procesos nuevos y no reprodujo ese error. Los clústeres PostgreSQL utilizados para pruebas eran temporales y fueron detenidos y retirados por el script de pruebas; no se ejecutaron migraciones ni escrituras contra la base de desarrollo durante este cierre.

La revisión visual de los cinco viewports requiere navegador; en esta sesión su uso está deshabilitado. Renderizar Blade y compilar CSS no prueba contraste, responsive real ni foco del navegador. Se entrega la implementación y la evidencia automatizada; la aceptación visual continúa pendiente.

### Corrección posterior: consulta directa del resultado guardado

La solicitud posterior elimina la vista previa y corrige el error de la URL de Mercedes con `?simulacion=0`. Las regresiones verifican ausencia/estructura parcial de revisiones, los parámetros antiguos, los cinco requisitos visibles, conservación de las cinco funciones, prioridad operativa, permisos, revocación y corrupción. No se modificaron contratos clínicos, permisos, esquema ni datos de residentes.

Comprobación local adicional: `orion-verificar-sin-preview-local.php` renderizó las vistas de `COD_RES_000030`, `COD_RES_000028` y `COD_RES_000010` con el parámetro antiguo en cero, dentro de una transacción PostgreSQL `READ ONLY` revertida al terminar. Los tres devolvieron resultado guardado, cinco funciones, cinco requisitos, estado «Resultado conservado», procedencia visible y ausencia del selector, sin error de lectura.

El build posterior se conserva en `%TEMP%/orion-resultado-sin-preview.build.log`: aprobado en 8,52 s. Pint y comprobación de espacios aprobados sobre los archivos de la corrección. La revisión independiente estática no encontró P1/P2 en acceso por ID, revocación, procedencia y revisión opcional. Las suites dirigidas de esta corrección se conservan en `%TEMP%/orion-resultado-sin-preview.sqlite.*` y `%TEMP%/orion-resultado-sin-preview.pg.*`.

Resultado final de la corrección: **102 pruebas y 645 aserciones aprobadas en SQLite y, por separado, 102 pruebas y 645 aserciones aprobadas en PostgreSQL 18 desechable**, sin errores ni fallos. Incluye `SimulacionLocalExpertoTest`, `InformeCognitivoTest`, `InformeNeurocognitivoTest`, `RevisionValoracionExpertaTest` y `PresentacionNeurocognitivaTest`. El clúster PostgreSQL temporal fue detenido y retirado por el script. La comprobación visual en navegador sigue pendiente.

Rama: `feature/apoyo-decision-informe-neurocognitivo`, creada desde el checkout existente, descendiente de `develop`, preservando el trabajo sin confirmar. No se fusiona ninguna rama ni se realiza commit/push. Se conservan los cambios concurrentes ajenos de Clínica. No se incorporan dependencias ni nomenclatura V1 en el código nuevo.
