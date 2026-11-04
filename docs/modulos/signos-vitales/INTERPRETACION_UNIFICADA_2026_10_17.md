# Interpretación compartida de signos vitales — 17/10/2026

## Contrato y fuentes

La solicitud de la propietaria es eliminar interpretaciones contradictorias entre frontend y backend. Se mantienen los umbrales y competencias existentes; no se aprueban reglas médicas nuevas.

- Estructura: [baseline congelado](../../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y [extensión V2.2 aprobada](../../base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md). No hay cambios de esquema.
- Comportamiento: [contrato del módulo](README.md). Prioridad: crítico general, objetivo médico válido en la fecha de la medición, referencia general aprobada.
- Implementación canónica: `EvaluadorSignosVitales`, sus reglas y `config/signos_vitales.php`. Esta tarea no altera sus umbrales ni la creación de alertas al confirmar.
- Presentación: [contrato visual](../../frontend/CONTRATO_VISUAL_UX_UI.md). La vista expresa el resultado del servidor, sin interpretar cifras ni texto libre.

## Contradicciones corregidas

Había límites alternativos en Blade, JavaScript, el resumen longitudinal y `ClasificacionSignosPanel`. También existían rangos editables en localStorage, una banda de PA que mezclaba sistólica y diastólica, valores ficticios por defecto y una lectura anterior duplicada para dibujar líneas.

`InterpretacionSignosVitales` ahora prepara lecturas planas desde los resultados del evaluador: estado, explicación, severidad, fuente y referencia. Ficha, tarjetas, historial, tooltip, resúmenes, distribución de PA, avisos del formulario y lista médica consumen estas lecturas o el mismo evaluador. Se elimina la clase de clasificación alternativa.

- Sin medición: **Sin datos**. Medición sin regla aplicable: **Sin interpretación automática**. Un resumen con parámetros sin clasificar identifica su interpretación parcial.
- SpO₂ no recibe un umbral universal automático. Glucemia alta aislada conserva la revisión sugerida del motor.
- Los críticos generales prevalecen sobre objetivos individuales y otras mediciones normales.
- La interpretación histórica utiliza `fecha_hora` y todas las versiones temporales de objetivos, incluidas las reemplazadas. No se utiliza la relación filtrada a objetivos actualmente vigentes para evaluar tomas antiguas.
- Consultar no crea alertas, intervenciones ni registros. No se reescribe historia clínica. La clasificación general se calcula con las reglas configuradas actualmente; no se afirma disponer de una versión histórica persistida de esas reglas.
- La precarga de objetivos necesita una consulta para una colección de controles. No hay consultas de basal por cada fila.
- Los anulados permanecen identificados en el historial y se excluyen de las series vigentes. El período usa la fecha y hora reales y no recupera controles externos para rellenar una gráfica.
- Una sola toma es un punto. Las minigráficas permiten ampliar la escala para no recortar mediciones extremas.
- Referencias y objetivos sustituye la edición local de rangos por una consulta de solo lectura.

## Integración y seguridad

Se conserva la autorización de acceso y del servicio de registro. El modal compartido vuelve a mantener el código del residente tras abrirse; el código queda protegido con `Locked`. Se elimina su consulta a una columna de peso inexistente en signos vitales.

Pruebas verifican preview sin persistencia, registro por Enfermería con autoría propia y denegación de escritura médica conforme al servicio actual. Esta tarea no amplía competencias. La existencia previa de entradas médicas al formulario no constituye autorización para cambiar ese servicio.

No se modifica la BDD, permisos, persistencia de alertas ni la autoría de registros existentes. Los DTO enviados al navegador no serializan el modelo clínico completo.

## Evidencia

- Antes: pruebas reprodujeron diferencias en clasificación, aplicación del objetivo actual a una toma anterior y etiqueta de hiperglucemia ajena a la regla vigente.
- Suite relacionada: **75 pruebas PHP, 704 aserciones**, SQLite `:memory:`. Incluye reglas, objetivos versionados, ficha, panel, captura, autoría y arquitectura frontend.
- Tras endurecer el manejo de fecha inválida: **9 pruebas, 41 aserciones** adicionales de verificación focalizada, todas correctas.
- Frontend: **38 pruebas JavaScript**, correctas. Incluyen inmunidad a localStorage, tooltip del servidor y ausencia de puntos fabricados.
- Copia operativa: **14 pruebas PHP, 60 aserciones**, correctas antes del endurecimiento final de fecha; misma corrección aplicada en ambas copias.
- Build: correcto en `UNIFICACION` y en la copia operativa, respetando sus otros cambios.
- QA de la ficha: **30 casos por copia** con datos sintéticos: vacío, crítico y objetivo individual; claro/oscuro; 1440, 1280, 1024, 768 y 390 px. Gráficas, datos visibles, apertura/cierre, Escape, foco y consulta sin campos editables. Sin errores JavaScript ni desbordamiento horizontal en la matriz revisada.
- Evidencia privada sintética: `C:/Users/CARLAENCINAS/.codex/signos-unificacion-20261017/`. No se incorporan capturas ni datos reales al repositorio.

## Límites

No se ejecutó integración PostgreSQL ni auditoría global de permisos; no hay migraciones ni modificaciones transaccionales en esta tarea. Las reglas sin interpretación aprobada siguen sin clasificarse: no se completa ese vacío mediante una inferencia de UI. La revisión visual se limita a la ficha y su consulta de referencias; los otros paneles tienen verificación PHP de integración.
