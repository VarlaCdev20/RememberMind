# Dictamen de la propuesta instrumental para O.R.I.O.N.

Fecha de revisión: 20/10/2026. Estado: **propuesta contrastada; no constituye adopción institucional ni acreditación de uso de instrumentos**.

## Decisión técnica

La propuesta recibida distingue correctamente observaciones clínicas, cribado y mediciones instrumentales. Puede orientar la captura y la adquisición futura, pero **MoCA y Mini-Cog no desbloquean automáticamente los 19 componentes ni las quince reglas G09**. Cada condición C1 debe vincular una medición original a una tarea y componente mediante un método interpretativo aplicable y versionado; C2 y C3 requieren atribución y contexto comprobables en la misma aplicación. Un puntaje global o una observación nominal no sustituyen esa cadena. La falta de un componente debe producir abstención localizada, conservando el resultado de los demás.

## Contraste con el sistema actual

| Elemento | Situación comprobada | Consecuencia |
| --- | --- | --- |
| Cinco criterios, 19 componentes, reglas Memoria/G09 y compuertas | Contratos y motor existentes; matriz en `FASE_2_MATRIZ_MAESTRA_INFERENCIA_2026_10_20.md` y A–D en `FASE_4_CONTRATO_AD_INTEGRADO_2026_10_19.md`. | Reutilizar conocimiento y ejecutor; no crear otro sistema. |
| Captura de aplicaciones y respuestas | Existe `CapturaInstrumentalModal` y consulta de capturas por residente. | Extender contratos y UX sobre estas rutas cuando el instrumento y el método estén definidos. |
| Observaciones por componente | Son observaciones clínicas, conforme aclaración de la propietaria del 19/10; no son pruebas estandarizadas. | Mostrar como observación y soporte contextual; no convertir a puntuación objetiva. |
| Perfiles activos | `config/sistema_experto.php` mantiene vacíos `perfiles_interpretativos` y `perfiles_instrumentales`. | No existe hoy un perfil instrumental activo que convierta nuevas aplicaciones MoCA/Mini-Cog en C1 por componente. |
| Historia cognitiva, IADL y revisión diferencial | Contratos A–D/E–H y captura Petersen ya existen; deben cotejarse sus campos/alcance antes de ampliar formularios. | No atribuir automáticamente pérdida funcional a una tarea asumida por la residencia; conservar causa, cambio previo e informante. |
| Conservación integral | Sigue pendiente y el inventario actual es de 104 tablas de negocio; la propietaria fijó un máximo de 100. | Ningún catálogo propuesto se traduce por sí solo en tabla o migración. Resolver reducción y replay exacto por separado. |

## Fuentes primarias y condiciones verificadas

- [MoCA, aplicación en papel](https://www.mocacognition.com/paper): la política actual permite usar el puntaje total para cribado/derivación sin certificación; **exige certificación para interpretar tareas, subpuntuaciones o dominios**. Este último es el uso que necesitaría un mapeo detallado de O.R.I.O.N. Confirmar en el portal oficial la forma e idioma disponibles al momento de aplicar.
- [MoCA, permisos](https://www.mocacognition.com/permission) y [términos de uso](https://mocacognition.com/terms-of-use): diferencian uso sanitario ordinario de incorporación en otro producto/servicio. Reproducir reactivos o desarrollar una versión electrónica propia requiere autorización específica. No incorporar contenido protegido a formularios, seeders ni pruebas mientras no conste el derecho aplicable.
- [Mini-Cog, desarrollo](https://mini-cog.com/instrument-development/): sus responsables indican evidencia insuficiente para recomendarlo específicamente como prueba de cribado de deterioro cognitivo leve. Puede ser una fuente breve complementaria, no fundamento único de Petersen ni de cinco dominios.
- [Mini-Cog, idiomas](https://mini-cog.com/mini-cog-in-other-languages/) y [condiciones de uso](https://mini-cog.com/contact/): hay formulario estandarizado en español; las condiciones distinguen atención clínica, actividad comercial e investigación. Determinar el encuadre institucional antes de reproducirlo o integrarlo electrónicamente.

La información oficial actual de MoCA sobre certificación para el puntaje total difiere de textos antiguos de su página de permisos. Para interpretación por tareas, ambas rutas conducen a la condición más exigente: certificación. La disponibilidad de herramientas digitales oficiales en español no autoriza crear una integración propia.

## Contrato de adquisición propuesto, sin activación clínica

1. Identificar el instrumento realmente aplicado, forma/edición, idioma, condiciones de uso, profesional y su capacitación para el nivel de interpretación solicitado.
2. Guardar fecha clínica, residente, fuente documental, respuesta y puntuación originales por tarea, incluyendo omisiones y motivos. No reconstruir mediciones desde observaciones anteriores.
3. Publicar una matriz *tarea oficial → variable semántica → componente O.R.I.O.N. → método/versión → condiciones y exclusiones*, con fuente y población de referencia mayores de 60 años, idioma y escolaridad pertinentes. No inferir que cubrir un dominio equivale a cubrir todos sus subcomponentes.
4. Probar cada mapeo con aplicaciones sintéticas completas, incompletas y contradictorias, manteniendo separadas las aplicaciones. Sólo luego habilitar el perfil correspondiente; las reglas sin soporte siguen no ejecutables para esa evidencia.
5. Documentar por separado cambio cognitivo, autonomía IADL, causas alternativas y revisión de Medicina/Psicología posterior al resultado. La asignación propuesta de formularios a profesiones requiere decisión institucional explícita antes de cambiar permisos o competencias.

## Información y decisiones aún necesarias

- Confirmación institucional del instrumento que se aplicará realmente, forma/edición/idioma y material oficial disponible. La propuesta de MoCA Full 8.1 y Mini-Cog todavía no acredita una aplicación a residente alguno.
- Derecho concreto para el uso previsto en RememberMind y, si se pretende interpretación de tareas MoCA, capacitación/certificación de quien la realice. No basta el permiso de aplicación en papel para replicar reactivos en software.
- Adopción de responsabilidades profesionales y de los formularios propuestos; los permisos actuales no se amplían por este documento.
- Mediciones originales futuras por tarea, condiciones e informantes. La investigación puede aportar métodos y límites, pero no generar respuestas, tiempos o autores de un paciente.
- Mapeos y perfiles interpretativos fundamentados para cada subcomponente que se quiera activar. Mini-Cog por sí solo no cubre la matriz de 19.

No se han creado catálogos, tablas, permisos, reglas clínicas, perfiles activos ni datos de residentes a partir de esta propuesta.
