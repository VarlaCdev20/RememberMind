# Fase 4: fuentes nominales y sucesión del conocimiento

Fecha: 19/10/2026. Base: `f9537e60`. Alcance: corrección del puente autorizado y del contrato heredado; no activación de reglas sobre residentes ni cierre institucional de toda la fase 4.

## Contrato vigente y fuentes

La propietaria confirmó que las 286 aplicaciones por componente son **observaciones clínicas**. Su [conciliación de fase 2](DICTAMEN_CIERRE_FASE_2_2026_10_19.md) está cerrada. La [red de cinco dominios y 19 componentes](FASE_4_RED_Y_REGLAS_2026_10_19.md), los [contratos del conector](FASE_4_FUENTES_Y_METRICAS_60_MAS_2026_10_19.md) y el [gobierno aprobado](PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md) gobiernan este incremento. La investigación bibliográfica previa no se convierte en autorización para interpretar una observación como medición instrumental.

| Fuente | Autoridad y aplicación |
| --- | --- |
| Aclaración directa de la propietaria y acta de fase 2 | Observaciones reales por componente; no identificar retrospectivamente una prueba estandarizada |
| CSV privados retenidos de la importación | Cotejo agregado de forma: 286 parejas opción/texto; sus valores son exactamente iguales, sin opciones ausentes o cruzadas. No se publican contenidos ni identidades |
| Baseline y decisiones aprobadas | Se conservan 106 tablas de negocio, sus columnas y relaciones; cero DDL, catálogos o permisos nuevos |
| Código y pruebas anteriores | Evidencia técnica: el puente rechazaba estas parejas y las sucesoras heredaban claves de mapeo de su antecesora |
| Paquetes G09 de laboratorio | Fixtures para probar la infraestructura; no constituyen una versión institucional activada |

El conflicto de representación se resuelve manteniendo ambas columnas originales: una FK y su literal concordante representan la misma respuesta nominal. No se borra una columna para cumplir el adaptador ni se transforma una etiqueta o puntaje en el valor fuente.

## 1. Corrección de la entrada nominal

`ConstructorMemoriaInstrumental` admite la pareja solamente cuando el catálogo declara `OBSERVACIONAL`, la pregunta es `OPCION` / `OPCION_UNICA` y el vínculo del servidor selecciona `valor_texto`.

- Comprueba identidad y versión del instrumento, pregunta, respuesta, opción y aplicación; coteja las PK dentro del motor aunque el adaptador indique `coherente=true`.
- Exige igualdad estricta entre el texto aportado y `opcion_actual.valor`. No aplica trim, cambio de mayúsculas, similitud, interpretación del nombre o puntuación.
- Una opción presente no completa un texto NULL. Este queda `NO_DISPONIBLE`; un literal sin mapeo exacto queda `VALOR_NO_RECONOCIDO`.
- Una referencia cruzada, texto discordante, opción ausente en una respuesta nominal existente o número/lógico simultáneo impide utilizar ese registro según los controles existentes.
- Los catálogos inactivos para nuevas aplicaciones conservan su historia. Esto no rehabilita una aplicación ANULADA.
- Conserva el corte, la edad **en la aplicación ≥60**, procedencia, autor original, agrupación por aplicación y permisos ya existentes.
- Memoria mantiene su admisibilidad específica: estas observaciones no acreditan `VAR-MEM-INST-01`. No producen puntuaciones normativas, A/P ni DCL.

La explicación añade, solo para las respuestas nominales recién admitidas, `naturaleza_fuente=OBSERVACION_CLINICA` y la opción original proyectada por columnas. Los vínculos se identifican como `OBSERVACION_NOMINAL_ORIGINAL`. La traza mantiene pregunta, literal, autor, fecha, mapeo, variable, condición, regla y ruta del componente al dominio.

## 2. Corrección de vínculos entre sucesoras

El gestor regenera las PK de mapeos cuando crea una revisión. El servicio `ContratosConocimiento` heredaba las claves originales sin traducirlas, de modo que la consulta de una sucesora fallaba con «Referencia ausente del paquete».

Ahora recorre el linaje desde el contrato configurado y traduce en **cada paso** sus vínculos por la identidad compuesta:

`código semántico de la variable + código de fuente + tipo de extracción + campo directo + selector`

Todas las tablas de la búsqueda están limitadas a la versión correspondiente. La pregunta del vínculo debe coincidir con el selector original. La traducción exige un único destino; si falta o hay dos equivalentes, falla explícitamente. No elige el primero ni prefiere el activo frente a una identidad duplicada.

Solo cambia la clave del mapa del contrato en memoria. La identidad instrumental, pregunta y columna fuente se conservan; no se modifica la configuración del antecesor ni su contenido SQL. Las reglas archivadas dejan de emitir en nuevas ejecuciones y la instantánea previa sigue siendo reproducible.

## 3. Reproducción histórica y revisión independiente

El revisor detectó un P2 durante la implementación: una pregunta OPCION sin respuesta ya estaba admitida antes. Añadirle la nueva categoría nominal habría cambiado su salida histórica bajo `INSTRUMENTAL-DECLARATIVO-1`. Se corrigió condicionando la extensión a una respuesta presente, sin modificar el formato anterior de las ausencias.

`ReproduccionFuenteNominalTest` usa una fixture sintética determinista. Se ejecutó con los bytes anteriores reales de `ConstructorMemoriaInstrumental` y `TrazaFuentesInstrumentales` de `f9537e60`, en el candidato aislado y sin SQL. Su huella fue:

`d96661ed285c7b257fcc7a87df0768f8555be6e5e49d59e66d62794aa903d631`

El motor corregido reproduce exactamente esa huella. No se incorporan instantáneas clínicas como fixtures ni se modifica el contrato histórico para ocultar una incompatibilidad.

La revisión de solo lectura se separó de la implementación. El dictamen estático posterior cerró el P2 y no identificó P1/P2 pendientes en el delta. También recomendó el negativo SQL de destinos ambiguos, incorporado y verificado antes de la selección final. El revisor no ejecutó las pruebas ni consultó datos clínicos.

## 4. Matriz de aceptación del incremento

| Requisito | Prueba o evidencia |
| --- | --- |
| Respuesta nominal → hecho → variable → condición → regla → componente/dominio → explicación | Feature SQL con tres respuestas sintéticas, una conclusión candidata y un solo soporte efectivo |
| NULL / literal desconocido / discordancia / número simultáneo / opción ausente | Negativos de `ConocimientoCognitivoInstitucionalTest` |
| Opción o pregunta cruzada; no confiar en el booleano de coherencia | Exclusión del lector y verificación directa del motor con PK manipuladas |
| Catálogo retirado y OPCION_UNICA | Conservación del registro histórico sin reactivar catálogo |
| Separación respecto a la medición de Memoria y DCL | Evidencia NO_ADMISIBLE con motivo explícito y clasificación DCL NULL |
| Sucesión y archivo | Dos generaciones; nuevas PK de mapeo, contrato original intacto, retiro de la regla y replay del resultado anterior |
| Extracción modificada o destino ambiguo | Rechazo explícito, sin selección arbitraria |
| Compatibilidad con ausencia histórica | Huella obtenida del código anterior y reproducida por el nuevo |
| Autorización y cohortes | Selección relacionada de cuenta inactiva, familia, permisos de fuentes, residente cruzado, corte y edad histórica |

Las conclusiones positivas de estas pruebas usan conocimiento de ingeniería sintético. Demuestran el recorrido del conector, no que un literal de un residente cumpla una regla G09 institucional.

## 5. Integración, seguridad y límites

La consulta ya existente `ConsultarConocimientoCognitivo` usa este puente; `ConsultarPerfilInstrumental` reutiliza la misma captura autorizada. No se crea una segunda ruta de hechos enviada por el cliente, otra pantalla ni un extractor alternativo. Las Policies, cuenta activa, permisos de fuentes y competencia profesional permanecen vigentes.

La comprobación ampliada utiliza un candidato de `f9537e60` más cinco archivos PHP propios, sin `.env`, con dependencias locales físicas y almacenamiento separado por motor. Los cambios simultáneos de horarios/proveedor modular quedan fuera. No se ejecutan migraciones ni escrituras en la base operativa. La sucesión SQL de las pruebas ocurre exclusivamente en bases desechables.

Build no aplica: backend y documentación, sin cambios de interfaz o assets. No se incorpora legacy, dependencia, permiso, firma clínica ni un depósito JSON de historia. El DTO continúa en memoria y `persistido=false`; la conservación relacional íntegra pertenece a fase 6.

## 6. Verificación final

| Verificación | Resultado |
| --- | --- |
| SQLite `:memory:`, selección relacionada | **178 pruebas / 5902 aserciones**, cero fallos, errores u omisiones; 3 min 31 s |
| PostgreSQL desechable, misma selección más carreras de gobierno | **183 pruebas / 5961 aserciones**, cero fallos, errores u omisiones; 5 min 04 s. Incluye cinco carreras con procesos independientes; parada del servidor confirmada |
| Independencia del motor y O.R.I.O.N. | Ambos ejecutables PASS, sin Laravel ni SQL |
| Pint de los cinco PHP propios | PASS |
| Correspondencia candidato / workspace | SHA-256 idéntico de los cinco PHP; reflexión del autoloader resuelve el archivo del candidato |
| Documentación | 69 enlaces locales comprobados en los cuatro documentos del incremento; diff sin errores de espacios |
| Gate independiente final | PASS técnico estático del delta final, sin P1/P2 pendientes identificados; no validación clínica |

Los XML y logs se conservan en `storage/app/private/fase4-nominal-final-{sqlite,postgres}.{xml,log}`; las dos regresiones iniciales fallidas están en `fase4-nominal-red.xml` y `fase4-herencia-red.xml`. El manifiesto de integridad del candidato es `fase4-nominal-candidato-verificado.json`. Son evidencia técnica local privada, excluida de Git. No se ejecutó la suite global ni QA visual, y no se presentan como validez diagnóstica.

Comandos reproducibles sobre el candidato sin `.env`, con `LARAVEL_STORAGE_PATH` distinto por motor:

```powershell
$filtro = 'ConocimientoCognitivoInstitucionalTest|ReproduccionFuenteNominalTest|AdmisibilidadObservacionalTest|PerfilCognitivoDeclarativoTest|IntegracionRedCognitivaTest|GobiernoReglasExpertasTest|SucesionObservacionalTest|CapturaInstrumentalCanonicaTest|AplicabilidadGeriatricaTest|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|ApplicationBoundaryTest|FolderOrganizationTest|MapeadorReglasTest'
php vendor/phpunit/phpunit/phpunit --filter $filtro --log-junit storage/app/private/fase4-nominal-final-sqlite.xml
./scripts/probar-postgres.ps1 -Filtro ($filtro + '|GobiernoReglasConcurrenciaPostgresTest') -Informe storage/app/private/fase4-nominal-final-postgres.xml -Log storage/app/private/fase4-nominal-final-postgres.log
php motor-experto/tests/independencia.php
php motor-experto/tests/orion_independencia.php
```

## 7. Checklist y siguiente dependencia

- [x] Reproducir el rechazo nominal original con una regresión SQL antes de corregirlo.
- [x] Corregir el puente con identidad, igualdad exacta y trazabilidad.
- [x] Reproducir el fallo de herencia antes de corregirlo.
- [x] Traducir vínculos entre revisiones y verificar dos generaciones, archivo y rechazo de identidad alterada/ambigua.
- [x] Corregir el P2 de replay señalado por revisión independiente y comparar la huella anterior real.
- [x] Registrar los resultados de la selección final SQLite/PostgreSQL y su evidencia.
- [x] Implementar la versión institucional con predicados, mapeos literales y consecuencias explícitos de Memoria/G09, mediante el [contrato A–D adoptado e integrado posteriormente](FASE_4_CONTRATO_AD_INTEGRADO_2026_10_19.md); no se copian las fixtures como reglas clínicas.
- [ ] Conservar relacionalmente toda la evaluación y vincular el release en fase 6.

En el corte de este incremento, la configuración operativa conservaba `version_cognitiva=null`, contratos vacíos y lectura clínica vacía; no se declaraba toda la fase 4 cerrada. El [acta posterior del contrato A–D](FASE_4_CONTRATO_AD_INTEGRADO_2026_10_19.md) incorpora la familia institucional y su selección por linaje cuando no hay versión configurada, y explicita los pendientes de instalación, perfiles y fases posteriores.
