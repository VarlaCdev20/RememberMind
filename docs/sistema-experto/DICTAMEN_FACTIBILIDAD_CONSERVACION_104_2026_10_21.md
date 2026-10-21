# O.R.I.O.N. — dictamen de factibilidad de conservación completa bajo 104 tablas

Fecha: 21/10/2026. Estado: **auditoría técnica; sin cambio estructural autorizado ni instalado**.

## Fuente de verdad y aceptación

- **Vigente:** la decisión de la propietaria [mantiene un máximo de 104 tablas de negocio](DECISION_MAXIMO_104_TABLAS_2026_10_20.md). La instalación cuenta 75 operativas y 29 expertas. El máximo no autoriza una fusión, columna, FK o trigger concreto.
- **Vigentes:** el [baseline congelado](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), el [diccionario operativo](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md) y `database/AGENTS.md` exigen preservar relaciones, historia, tipos, autorización estructural específica y ausencia de JSON/EAV.
- **Contrato funcional:** [Fase 6](FASE_6_PREPARACION_COMPUESTA_2026_10_20.md) define `EVALUACION-INSTITUCIONAL-1`: ambas ramas, originales, ausencia/NULL, orden, conocimiento, reglas y ejecutor retenido deben volver exactamente de SQL y reproducir las mismas huellas. `PrepararEvaluacionInstitucional` crea hoy el DTO en memoria con `persistido=false`.
- **Propuestas no aprobadas:** los diseños de [15](ENSAYO_CONSERVACION_REDUCIDA_FASE_6_2026_10_20.md) y [25](PROPUESTA_CONSERVACION_COMPUESTA_FASE_6_2026_10_20.md) tablas nuevas superan el máximo y no se instalan. El [ensayo de cinco relaciones](ENSAYO_CINCO_RELACIONES_104_2026_10_21.md) es candidato técnico, no diccionario instalable.

## Prueba ejecutada y alcance exacto

En una base PostgreSQL desechable del runner `scripts/probar-postgres.ps1` se ejecutó el ensayo completo de `prototipos/orion-conservacion-0_2/ensayo.php` mediante un puente PHPUnit temporal. **Pasó: 1 prueba envolvente, 4 aserciones, 45,745 segundos, sin fallos reportados por el ensayo interno.** El cluster se detuvo y retiró. El puente se eliminó al terminar porque los archivos de ese prototipo aún están sin seguimiento Git en este checkout. Esta ejecución demuestra la representación y reproducción del DTO sintético en su esquema de ensayo, **no** su reducción al inventario institucional.

El esquema generado del prototipo contiene **42 tablas de ensayo**: 39 modelos de entrada y 3 auxiliares. El mapa candidato de reducción registra 51 rutas de colección porque una definición compartida puede aparecer en ambas ramas; corresponden a esos 39 modelos distintos. El mapa de campos declara 783 rutas y conserva siete rutas de grano y 39 rutas/campos con brechas B01–B03. Los ensayos parciales SQLite y PostgreSQL de B01–B03 no sustituyen una ida y vuelta SQL integral de la variante reducida.

## Conflictos materiales del candidato reducido

1. **Destino de B01 no unificado.** `prototipos/orion-conservacion-100/mapa-granos-candidato.csv` envía los contratos y modalidades adquiridos a `relaciones_semanticas`; el diseño posterior de cinco relaciones propone `contratos_relaciones_instantanea`. La primera es definición de red semántica por versión y exige `cod_version_modelo`, nodos origen/destino, significado, autor y fecha, además de sus FK. Un contrato observado por evaluación no cumple ese grano. No se puede considerar equivalentes ambos destinos ni elegir uno tácitamente.
2. **Fuente y evidencia mezcladas.** El mapa envía seis modelos de captura, constancia, procedencia, juicio y soporte adquiridos a `evidencias_evaluacion`. La tabla vigente exige `cod_mapeo_variable_fuente`, `cod_registro_fuente`, estados de representación/admisibilidad y tiene unicidad propia de una evidencia extraída. Una captura o una constancia no posee automáticamente ese mapeo ni ese registro. Hacer sus columnas nullable sin subtipos, FK y lectores revisados debilitaría la integridad vigente.
3. **Presión de columnas y mantenimiento.** Al calcular la unión de rutas nominales de los 39 modelos del prototipo sobre los destinos del mapa, su codec genérico de seis columnas por escalar requeriría aproximadamente **445 columnas nuevas** en `evaluaciones_expertas`, **1.144 en `evidencias_evaluacion`** y **189 en `hechos_instantanea_experta`, antes de columnas e índices existentes. Son estimaciones del codec de ensayo, no un diccionario final ni una afirmación de límite físico del motor. Señalan que la reducción literal a tablas polimórficas anchas traslada el costo de mantenimiento de tablas a columnas, checks, FK y consumidores.
4. **Falta la transición poblada.** Los cinco retiros propuestos (`resultados_criterio`, `trazas_inferencia`, `evidencias_soporte_condicion`, `clasificaciones_dcl_instantanea`, `variables_expertas`) tienen pruebas aisladas, pero aún no una transición integral de todas sus filas y FK entrantes, con reversión y prueba PostgreSQL sobre el esquema institucional completo.

## Decisión técnica de este corte

**La conservación completa bajo 104 no está demostrada y no debe activarse.** No se crea una migración, no se guarda un DTO truncado, no se reutilizan campos ajenos, no se fabrica historia clínica y no se cambia `persistido=false` de la preparación en memoria. Las evaluaciones observacionales ya conservadas mantienen su contrato propio.

Para solicitar aprobación estructural específica hace falta una propuesta distinta y comprobable: asignar cada grano a una relación con nombre y columnas nominales, preservar los consumidores y FK de las tablas reutilizadas, demostrar `guardar → recuperar → replay` íntegro en SQLite y PostgreSQL desechables, probar transición poblada/reversión y cierre concurrente, y pasar revisión de 4FN y mantenibilidad. La recomendación técnica es separar los granos que no comparten significado; si esto exigiera superar 104, habría que someter el límite a una nueva decisión de la propietaria **antes** de cambiar la BDD.
