# Contrato de visualización y datos

## Forma del payload

Documentar campos realmente necesarios: identificador no sensible de serie, etiqueta, unidad, fecha/hora serializada con zona conocida, valor numérico finito, origen histórico/preview y estado autorizado opcional. DTO puede ser array u objeto plano; no requiere instalar una librería ni una nueva clase si el proyecto ya lo expresa.

No exponer modelos Eloquent, relaciones completas ni datos sensibles innecesarios. Decimal persistido puede llegar como string: normalizarlo explícitamente en backend y validar valor completo; no aceptar basura parcial como «120abc». Ausencia y cero tienen significados distintos. Un dato incompleto produce gap/estado explicativo; no una medición inventada.

JSON puede transportar ausencia como null si el contrato lo define; nunca imprimir ese literal en labels/tooltips. NaN/Infinity no son números JSON válidos. Objetos se acceden por campo permitido, nunca mediante stringify implícito.

## Casos de cardinalidad

| Datos válidos | Representación |
|---|---|
| 0 históricos, sin preview | EmptyState con causa y acción autorizada |
| 1 histórico, sin preview | Último valor, fecha y unidad; no afirmar tendencia |
| 0 históricos + preview | Medición actual aislada, Sin guardar; no inventar histórico |
| 1 histórico + preview | Dos puntos distinguibles; histórico ● y preview ◉, con leyenda/texto |
| 2 o más históricos | Tendencia temporal sin inferir diagnóstico; preview adicional diferenciado |

Los marcadores son descripción de diseño, no obligación de usar emojis como iconos del producto. Mantener timestamp real y serie ordenada. No sobrescribir punto histórico con preview de igual fecha ni interpolar una observación faltante.

## Mediciones y objetivos

Presión arterial: dos series etiquetadas, sistólica y diastólica, con unidad. Validar pareja según contrato real. SpO₂ y otras mediciones: bandas solo con información estructurada aprobada y vigencia/contexto. Objetivos individuales vienen de la extensión V2.2 cuando el flujo los autoriza. No deducir límites de indicaciones en texto ni añadir una constante visual médica.

## Presentación y ciclo de vida

Grid discreto, ejes legibles, leyenda cercana, tooltip accesible por teclado/touch o valores alternativos, marcador actual y referencia con origen. Ajustar ticks/altura/leyenda en móvil sin esconder datos. Loading es diferente de empty; error muestra recuperación.

Reutilizar window.RMCharts y la clave de instancia cuando el contrato local lo permita. Revisar morph de Livewire, actualización repetida, contenedor oculto→visible, cambio de tema, resize y reduced motion. No dejar canvas sin tamaño ni instancias huérfanas.

Probar sintéticamente 0, 1, 2+ datos, strings decimales válidos/incorrectos, faltantes, valores no finitos, fecha inválida y modelo/objeto inesperado. Las pruebas deben comprobar resultado observable, no imitar el código.
