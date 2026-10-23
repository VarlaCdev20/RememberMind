# Punto 1 — Evidencia cognitiva original

**Estado al 21/10/2026: captura técnica disponible; adquisición clínica original pendiente.** Se mantienen 104 tablas de negocio. No se añadieron catálogos, permisos, mediciones ni firmas de residentes.

## Recorrido implementado

En los resultados del residente, «Registrar mediciones» abre la captura compartida de Valoraciones. Psicología puede elegir un instrumento activo y su versión, fecha/hora clínica, preguntas tipadas y respuestas originales. La aplicación propia toma el profesional de la sesión. La transcripción de un original cotejado distingue aplicador y digitador, documento privado, localizador y rectificación. La escritura es transaccional; el catálogo se revalida antes de guardar. Cada respuesta conserva `cod_aplicacion → cod_pregunta → valor`; una pregunta sin respuesta queda ausente, mientras `0` y `No` son explícitos.

El formulario ahora expone el código de tarea, dominio y tipo de respuesta. Para un perfil configurado con PK, código, versión, identidad y selectores exactos, presenta método, componentes y tareas requeridas. Si falta un selector, el instrumento tiene otra edición o el perfil es ambiguo, muestra que no está listo para interpretación. Un instrumento `OBSERVACIONAL` se identifica como observación; las 286 aplicaciones observacionales previas no se convierten en pruebas. La lectura experta solo extrae un perfil instrumental cuando existe el vínculo explícito y evidencia admisible; la ausencia de una entrada produce abstención en el extractor.

## Ficha que debe completar Psicología en una aplicación real

| Dato | Registro actual | Condición para usarlo como medida objetiva |
| --- | --- | --- |
| Residente, aplicador, fecha y hora | `aplicaciones_instrumento`; aplicador de sesión o del original cotejado | Identidad y alcance autorizados; fecha real |
| Instrumento, edición/forma/idioma | `instrumentos` y perfil de captura configurado por identidad exacta | Cotejar con protocolo administrado; no deducir del nombre |
| Tarea, aciertos, tiempo u otra medida y unidad | `preguntas_instrumento` con tipo/etiqueta/unidad de protocolo; `respuestas_instrumento.valor_numero`, `valor_logico` o texto | Cada selector apunta a una pregunta única y compatible; no convertir una nota en puntuación |
| Escolaridad y condiciones de aplicación | Preguntas tipadas del protocolo cuando el catálogo real las incluya | Registrar años completos, idioma, ayudas, limitaciones sensoriales/motoras y desviaciones requeridas por el método; desconocido permanece ausente |
| Procedencia documental histórica | `documentos_clinicos` privado + `cod_documento_clinico` y `localizador_fuente` | Original vigente, mismo residente, hash y autor cotejados |
| Método y referencia para mayores de 60 | `RegistroMetodosInstrumentales` y diccionario de 23 métodos | Edad y escolaridad dentro de la población de referencia, identidad exacta y condiciones de aplicabilidad; una referencia bibliográfica no acredita que se administró esa prueba |

La ficha usa las tablas actuales. Antes de activar un catálogo cognitivo real hay que decidir **qué protocolo, edición, idioma y tareas se aplicarán** y documentar derechos de uso. El catálogo actual de la instalación contiene aplicaciones observacionales/funcionales, no protocolos cognitivos estandarizados con aciertos o tiempos originales. `config/sistema_experto.php` mantiene vacíos `perfiles_instrumentales` y `perfiles_interpretativos`; el formulario no puede crear esas decisiones mediante una etiqueta o un valor inferido. Incorporar un catálogo instrumental nuevo es una decisión institucional que exige aprobación conforme a `database/AGENTS.md`; ningún cambio de BDD se aplicó en este punto.

## Decisión de uso de MoCA

La [FAQ oficial de MoCA](https://mocacognition.com/faq) indica que interpretar tareas, subpuntuaciones o dominios exige su formación y certificación oficiales; para uso solo del total la FAQ recoge una excepción desde diciembre de 2025. La [página oficial de permisos](https://mocacognition.com/permission) conserva textos más restrictivos sobre certificación y prohíbe desarrollar material electrónico relacionado sin autorización. Esa discrepancia se debe resolver con MoCA antes de codificar reactivos, puntuación por tareas o uso integrado en RememberMind. Ningún reactivo de MoCA se incorporó aquí.

## Condición de cierre clínico del punto

Por cada método que se quiera activar, Psicología debe aportar una aplicación realmente realizada y verificable: protocolo y edición/forma/idioma exactos, profesional, fecha/hora, respuestas originales con unidades, escolaridad y condiciones exigidas por la referencia. La institución debe adoptar el catálogo y el vínculo de preguntas/entradas después de comprobar derechos y pertinencia poblacional. Entonces se prueba con un original cotejado el recorrido `aplicación → respuesta → selector → variable → condición → regla → consecuencia`, además de ausencia, contradicción y fuera de rango. Hasta entonces el punto no está cerrado clínicamente y O.R.I.O.N. debe abstenerse donde falte evidencia.

## Verificación de este incremento

- `php artisan test --filter='CapturaInstrumentalFormularioTest' --compact`: 33 pruebas, 225 aserciones, PASS.
- `php artisan test --filter='CapturaInstrumentalInstitucionalTest' --compact`: 49 pruebas, 891 aserciones, PASS; incluye ausencia, cero, vínculo exacto, procedencia, permisos y rectificación.
- `vendor/bin/pint --test` de los PHP modificados: PASS.
- `npm run build`: PASS.
- Verificación visual real: pendiente. El navegador local respondió `Local browser use is disabled in Work settings`; las pruebas Livewire y el build no sustituyen esa comprobación.
