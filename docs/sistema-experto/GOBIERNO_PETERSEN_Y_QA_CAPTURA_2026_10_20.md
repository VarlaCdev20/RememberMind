# Gobierno Petersen y verificación de captura original

20/10/2026. Continuación autorizada por la propietaria después de habilitar la
lectura IADL. Implementa el gobierno técnico del contrato E–H ya adoptado y
verifica su captura; no adopta nuevos criterios clínicos ni amplía la BDD.

## Contrato y fuentes

Prevalecen el [contrato E–H adoptado](FASE_5_CAPTURA_PETERSEN_Y_REPRODUCCION_2026_10_19.md),
la [decisión de lectura IADL](PROPUESTA_LECTURA_IADL_PETERSEN_2026_10_19.md),
el [baseline estructural vigente](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md)
y el [gobierno de reglas aprobado](PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md).
La frase del plan anterior que mantenía pendiente el permiso IADL queda
sustituida por su instalación documentada y el commit `b120605a`.

Se reutilizan versiones, nodos, variables, dominios, valores, condiciones,
consecuencias y revisión de reglas. No hay migraciones, permisos nuevos,
JSON/EAV, nuevas relaciones ni datos clínicos añadidos a la instalación local.
El inventario aprobado permanece en 75 operativas + 33 expertas = 108.

## Gobierno conectado al cálculo

- `PaquetePetersenInstitucional` define la familia `ORION-PETERSEN-1`, separada
  de las 17 reglas cognitivas A–D. Conserva exactamente PET-01…PET-04.
- Cada regla exige ocho antecedentes: C01…C05 acreditados, cinco dominios
  interpretables, estado de Memoria y uno o varios dominios alterados. Esos
  estados se derivan después del control de originales y soportes; no se
  introduce el patrón esperado como dato del residente.
- `RegistrarConocimientoPetersen` registra el paquete de forma atómica e
  idempotente con cuenta autorizada y Activitylog. Las cuatro revisiones quedan
  pendientes, sin firmas ni aprobaciones profesionales precargadas.
- El gestor existente permite probar condiciones, revisar, crear sucesora y
  archivar. La definición clínica queda cerrada también ante llamadas directas:
  no se puede quitar C03, sustituir una consecuencia o publicar otro criterio
  mediante el formulario genérico.
- La consulta institucional exige una versión SQL vigente e inequívoca. La
  ausencia de gobierno, el archivo o el rechazo producen abstención explícita;
  no se recurre silenciosamente a un clasificador fijo. El ensayo puro utiliza
  las mismas conjunciones declarativas.
- El cálculo incluye versión, reglas y condiciones del gobierno consultado.
  Esa traza sigue siendo una consulta actual, con `persistido=false` y
  `conservacion_completa=false`.
- La vigencia de ambas familias se selecciona en el intervalo
  `[fecha_hora_vigencia, fecha_hora_retiro)`. Crear una sucesora no elimina la
  selección de su antecesora para un corte anterior al retiro. Esto no equivale
  a retener íntegramente todas las fuentes y estados observados de fase 6.

Entrada: **Conocimiento y reglas → Registrar patrones de Petersen adoptados**.
El registro exige la sesión y competencias existentes de Medicina/Psicología;
Superadministración mantiene lectura técnica. No se registra el paquete con
una cuenta elegida arbitrariamente por un script.

Tras superar las verificaciones se pulsó ese botón en la instalación local,
desde su sesión activa de Psicología. La interfaz confirmó los cuatro patrones
y seleccionó `ORION-PETERSEN-1 · ACTIVO`; `ORION-COGNITIVO-1` siguió disponible
por separado. Las revisiones permanecen pendientes. No se registraron
valoraciones originales ni evaluaciones de residentes como parte de esta
instalación. La evidencia visual del registro muestra solo el encabezado,
confirmación y selector, sin información clínica.

## Seguridad y captura

El selector, la adquisición y la vinculación IADL comprueban también
`consultarFuncionalidad` sobre el residente, además del permiso de la fuente.
Un rol médico con personal de profesión incompatible no recibe IADL a través
de Petersen. El reintento autorizado tampoco elude la Policy actual.

El historial identifica sucesoras y ofrece corrección solo sobre una cabeza
vigente; una carrera devuelve un mensaje recuperable. Se puede consultar la
sucesora y regresar al informe del residente. Las confirmaciones de guardado y
descarte reutilizan el modal del Design System con foco, teclado y Escape.

La corrección visualiza una fecha válida para `datetime-local` y conserva en el
servidor los seis decimales originales cuando no se cambia su representación.
La anulación deriva siempre la fecha original. Corregirla expresamente sigue
exigiendo motivo; no se truncan microsegundos para satisfacer el navegador.

## Evidencia y límites

Las pruebas utilizan datos sintéticos y bases desechables. La QA HTTP utiliza
una copia exclusivamente de SQLite `:memory:` en un directorio temporal propio,
servidor en loopback y sesiones separadas; no utiliza `remembermind_dev`.

La captura se ejercitó en navegador: consulta y vinculación IADL, error de fecha
futura sin pérdida de valores, confirmación y guardado, rectificación,
anulación, conservación de la cadena, seguir editando y descarte. Se verificaron
foco atrapado y Escape, y los anchos CSS reales 1440, 1280, 1024, 768 y 390 en
claro/oscuro, sin desbordamiento horizontal ni literales inválidos visibles.
Las capturas y la matriz quedan fuera de Git en el directorio privado de QA.

La verificación del gobierno incluye registro, prueba completa y sin C03,
ocultación del resultado anterior al cambiar una entrada, rechazo y sucesora
con archivo sobre el entorno sintético. Los ocho selectores envían sus valores
juntos al probar; se eliminó la carrera de render observada con actualizaciones
individuales. Las pruebas automáticas cubren los cuatro
patrones desde originales, archivo, rechazo, sucesión, ambigüedad, precisión,
permisos y regresiones de las 17 reglas existentes.

| Verificación final | Resultado |
|---|---|
| SQLite relacionada: núcleo, captura, gobierno, A–D e informe | 172 pruebas / 1088 aserciones, PASS |
| PostgreSQL desechable: núcleo, captura, gobierno, A–D, permisos y concurrencia | 158 pruebas / 858 aserciones, PASS |
| Comprobación posterior de precisión, panel y frontera temporal, en cada motor | 4 pruebas / 34 aserciones, PASS |
| Regresión de instantánea instrumental y release recuperable | 11 pruebas / 179 aserciones, PASS |
| Independencia del motor y O.R.I.O.N. | Ambos ejecutables PASS |
| `npm run build` | PASS, 107 módulos |
| Sintaxis de los 14 PHP propios, Pint y `git diff --check` | PASS |
| Revisiones independientes de seguridad y contrato experto/BDD | PASS estáticos acotados, incluido el hallazgo temporal corregido |

Los subconjuntos se solapan y no se suman. El clúster desechable de cada prueba
PostgreSQL fue detenido y retirado por el script. El servidor temporal de QA
también quedó detenido. Se preservaron fuera del commit los cambios ajenos de
horarios, proveedor modular y su prueba, así como `work/`.

Comandos principales:

```powershell
php vendor/phpunit/phpunit/phpunit --filter 'PetersenInstitucionalTest|CapturaPetersenOriginalTest|GobiernoPetersenInstitucionalTest|GobiernoReglasExpertasTest|ConocimientoCognitivoInstitucionalTest|InformeNeurocognitivoTest'
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'GobiernoPetersenInstitucionalTest|CapturaPetersenOriginalTest|PetersenOriginalConcurrenciaPostgresTest|GobiernoReglasExpertasTest|GobiernoReglasConcurrenciaPostgresTest|PetersenInstitucionalTest|ConocimientoCognitivoInstitucionalTest|PermisosLecturaIadlPetersenTest|PermisosPetersenOriginalTest'
php motor-experto/tests/independencia.php
php motor-experto/tests/orion_independencia.php
npm run build
```

La selección final de cuatro pruebas usa el filtro
`test_rectificar_y_anular_desde_formulario_preserva_microsegundos|test_panel_registra_prueba_y_revisa_sin_confundir_entradas_con_datos_clinicos|test_corte_anterior_a_sucesora_conserva_la_version_de_cada_familia`.
No se ha ejecutado ni declarado aprobada la suite global completa. Los
dictámenes técnicos no acreditan desempeño clínico ni sustituyen la revisión
de Medicina/Psicología.

## Pendiente posterior

La [fase 6](CONSERVACION_INSTRUMENTAL_ANALISIS_2026_10_19.md) requiere cerrar el
contrato compuesto instrumental + Petersen, su igualdad exacta y el diccionario
relacional mínimo antes de solicitar una ampliación estructural concreta.
Siguen pendientes conservación íntegra, vínculo evaluación → release y las
revisiones profesionales sobre esa ejecución conservada. La QA de esta captura
no declara terminado todo el recorrido de resultados, revisión e historial de
las fases 7–8. Push e integración por PR requieren instrucción expresa.
