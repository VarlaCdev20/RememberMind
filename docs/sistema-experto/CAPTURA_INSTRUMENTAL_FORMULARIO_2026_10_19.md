# Captura instrumental conectada — 19/10/2026

Estado: incremento implementado; verificación automatizada registrada al final. No modifica esquema, catálogos, permisos ni datos de residentes existentes. No declara completada la interpretación clínica de los cinco dominios.

## Problema corregido

Los dos formularios de evaluación anteriores guardaban un total y una clasificación manuales, sin respuestas por pregunta. No proporcionaban las mediciones originales que necesita el extractor instrumental. Además, duplicaban la escritura y podían completar una hora ausente.

Ahora ambas entradas usan `CapturaInstrumentalModal` y la operación transaccional existente `AplicarInstrumento`. Se eliminaron `RegistrarEvaluacionGeriatrica` y `RegistrarEvaluacionGeriatricaArea`, sin conservar rutas de escritura alternativas.

## Recorrido disponible

1. En resultados del residente, seleccionar **Registrar mediciones**. También se mantiene la entrada de evaluaciones por residente y la de Psicología.
2. El backend comprueba la cuenta, los permisos de creación y de catálogo, el profesional propio activo y su contexto clínico. El alcance del residente conserva las comprobaciones institucionales existentes.
3. Elegir el instrumento realmente aplicado y su versión registrada. Se consultan sus preguntas y opciones activas. Un catálogo sin preguntas no permite guardar una aplicación vacía.
4. Registrar fecha y hora originales, respuestas y observaciones. Los tipos disponibles son número, texto, opción única y lógico. Las preguntas sin medición permanecen sin respuesta; cero y «No» se conservan.
5. Revisar y confirmar. Cambiar cualquier respuesta, incluso reemplazando todo el conjunto, invalida la confirmación. El residente fijo, las preguntas, las huellas y la identidad de la solicitud están protegidos del cliente.
6. Guardar aplicación y respuestas en una transacción. Se revalida el catálogo dentro de ella. Un fallo revierte el conjunto y conserva los valores del formulario; el mensaje no expone SQL.
7. Consultar el historial actualizado. Una captura sin interpretación aparece como **Sin clasificación**. La captura pasa a las fuentes del perfil; solo un vínculo explícito con un método compatible permite calcular una medida.

Las fechas históricas son admisibles cuando corresponden al registro original. La comprobación del profesional y de su contexto actual acredita quién realiza el registro ahora; no demuestra por sí sola quién aplicó una prueba histórica. El formulario no permite atribuir la aplicación a otro profesional mediante un código enviado por el cliente.

## Protecciones y límites

- PK interna reservada para la captura confirmada: el reenvío no crea una segunda aplicación bajo la misma solicitud. No se pretende detectar como duplicadas dos evaluaciones diferentes con respuestas iguales.
- La huella del catálogo incluye instrumento, identidad y definición de preguntas, opciones, orden, estados y puntuaciones. Es un control de concurrencia del formulario; no es conservación histórica de una evaluación experta.
- El total solo se calcula cuando existen todas las respuestas y sus puntuaciones conforme al contrato de `AplicarInstrumento`. El formulario no inventa puntuaciones de respuestas numéricas ni una clasificación clínica.
- Las consultas del historial revalidan cuenta, permiso, personal activo y alcance del residente. El panel de Psicología revalida además su rol y el permiso de instrumentos. Superadministración conserva consulta; no recibe creación por este incremento.
- Se preserva la excepción local/testing de escritura de Superadministración previamente existente, condicionada a su configuración; no se amplía. Los negativos ordinarios la desactivan expresamente.
- El historial sin residente muestra selección y no consulta aplicaciones globales. Las listas con datos se paginan; un evento de guardado actualiza el historial.
- Se reutiliza el modal canónico con labels, errores asociados, estados de carga, confirmación y protección de cambios sin guardar. La verificación visual en navegador sigue pendiente.

## Trazabilidad

| Necesidad | Componente | Prueba principal |
| --- | --- | --- |
| Preparación autorizada de contexto y catálogo | `Valoraciones/Aplicacion/Consultas/PrepararCapturaInstrumental` | `CapturaInstrumentalFormularioTest` |
| Preguntas originales y detección de catálogo modificado | `CatalogoPreguntasInstrumento`, `AplicarInstrumento` | `test_revalida_catalogo_original_antes_y_dentro_del_guardado` |
| Dos entradas con una sola captura | `CapturaInstrumentalModal`, sus dos wrappers | `test_ambas_entradas_registran_respuestas_con_null_distinto_de_cero_y_autoria_propia` |
| Confirmación ligada a la entrada exacta | `CapturaInstrumentalModal` | `test_cambiar_array_completo_de_respuestas_invalida_confirmacion` |
| Error sin cabecera parcial ni éxito falso | `AplicarInstrumento`, modal | `test_fallo_al_escribir_respuestas_revierte_la_cabecera_y_muestra_error_seguro` |
| Continuidad resultados → captura → historial | `ResultadosExpertoResidente`, `SaludEvaluacionesGeriatricasPanel` | pruebas de apertura, actualización y ausencia de residente |
| Revocación efectiva después de abrir | ambos paneles y preparación de captura | pruebas de cuenta, permisos, personal y contexto revocados |

## Verificación

Pruebas solo con registros sintéticos y bases desechables. Comandos del corte:

```powershell
php vendor/phpunit/phpunit/phpunit --filter 'CapturaInstrumentalFormularioTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest|NormalizacionBateriaBolivia2015Test|NormalizacionInstrumentalInvestigadaTest|PerfilInstrumentalInstitucionalTest|FrontendRestauradoV2Test|ApplicationBoundaryTest|PresentationBoundaryTest|FrontendArchitectureTest|VisibilidadNavegacionTest' --log-junit storage/app/private/gobierno-reglas-20261018/captura-formulario-sqlite.xml
pwsh -NoProfile -File scripts/probar-postgres.ps1 -Filtro 'CapturaInstrumentalFormularioTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest' -Informe storage/app/private/gobierno-reglas-20261018/captura-formulario-postgres.xml -Log storage/app/private/gobierno-reglas-20261018/captura-formulario-postgres.log
npm run build
```

Resultados del corte: **SQLite 127 pruebas / 2849 aserciones PASS; PostgreSQL 45 / 497 PASS; build PASS**. PostgreSQL utilizó un clúster temporal independiente y lo cerró al terminar. No se suman corridas solapadas. Revisión independiente de seguridad: sin hallazgos P1/P2 tras corregir invalidación de confirmación y revocación del profesional. Pint PASS sobre los once PHP propios seleccionados. No se ejecutó una carrera concurrente específica de este formulario ni se acredita validez clínica mediante PHPUnit.

## Lo que sigue abierto

- Identificar las aplicaciones originales y sus vínculos exactos con edición, preguntas y método; no se detecta el instrumento por parecido del nombre.
- Incorporar mediciones realmente documentadas y las condiciones de aplicación; la investigación bibliográfica no crea mediciones de residentes.
- Completar los once componentes sin normalizador compatible, la integración clínica y los soportes originales de C01–C05.
- Resolver la conservación institucional integral de `PERFIL-INSTRUMENTAL-1`. Véase [análisis de conservación](CONSERVACION_INSTRUMENTAL_ANALISIS_2026_10_19.md); la RFC del laboratorio no resuelve ese contrato.
- Comprobar responsive, teclado y estados visuales en navegador. El intento de este corte fue rechazado con `Local browser use is disabled in Work settings`; no se sustituyó por una afirmación de QA visual.

No hay firmas precargadas, resultados esperados insertados, nuevos valores clínicos ni alteración de historias anteriores. Las revisiones profesionales separadas siguen siendo posteriores al resultado íntegro conservado, según el contrato vigente.
