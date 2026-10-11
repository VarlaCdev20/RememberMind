# Gráficos y vistas según el proceso

Revisión del 2026-10-07 en `REINICIO`, sin commits ni publicación. Corrige la repetición de barras y listas de la entrega anterior. Mantiene permisos, formularios, filtros, modales, paginación, tema y fuentes de datos existentes; no modifica estructura ni registros de la base operativa.

## Cada ventana responde a una lectura diferente

| Ventana | Gráficos | Lectura y vista principal |
| --- | --- | --- |
| Jornadas | Bloques de horarios y columnas mensuales | Encontrar turnos; agenda de turnos con separadores de día |
| Asignaciones | Ranking por área y puntos mensuales | Comparar registros de asignación, no personal único; nueva vista Por área |
| Contactos | Anillo de cantidad de vínculos y franja de estados | Reconocer distribución de la red de apoyo; directorio de contactos |
| Documentación | Ranking de tipos y puntos de vencimiento | Localizar qué documentación existe y cuándo vence; lista o tabla |
| Consentimientos | Anillo por tipo y puntos por mes | Proporciones y fechas de registro; historial de firmas con eje cronológico |
| Seguros | Ranking de entidades y anillo de estados | Comparar registros de cobertura y su situación; fichas de cobertura |
| Actividades | Comparación por área con puntos al extremo y columnas mensuales | Distribución de programación; agenda con días destacados |
| Visitas | Bloques de situación y puntos mensuales | Abrir visitas de una situación registrada; agenda de visitas |
| Alertas | Bloques de prioridad y columnas mensuales | Consultar prioridades registradas; nuevo tablero agrupado por estado |
| Incidentes | Columnas por gravedad registrada y puntos mensuales | Cantidad por categoría y tiempo; línea de sucesos con eje vertical |
| Reportes | Ranking de procesos del periodo | Comparación de cantidades administrativas, con acceso a cada módulo |

Los bloques de Visitas no constituyen un embudo ni representan conversiones. El tablero de Alertas permite consultar, no arrastrar registros ni cambiar sus estados. Por área y Tablero agrupan **solo los registros de la página actual**, indicado expresamente en la pantalla. Las vistas anteriores de tarjetas/lista/tabla siguen disponibles cuando eran válidas.

## Interacción y representación fiel

Cada categoría y mes mantiene el enlace de filtro autorizado. Las leyendas de anillo muestran cantidad y porcentaje; el centro expresa el total **de los grupos mostrados**, no un total global cuando solo se muestran los ocho principales. La franja tiene leyenda textual; los colores no sustituyen nombres y cantidades. Las columnas parten de cero y expresan escala; los puntos muestran cantidades discretas por mes sin unir meses ausentes ni inferir tendencias.

El anillo resalta el segmento al acercarse o enfocar su leyenda y actualiza su centro con la cantidad y nombre del grupo. Con un único grupo/mes, columnas y puntos presentan un indicador compacto con enlace en lugar de un gráfico grande que aparenta una comparación inexistente. Los conjuntos vacíos muestran ausencia de datos sin dibujar ceros ficticios.

Los gráficos muestran hasta ocho categorías y ocho meses con datos. Los meses que no aparecen no se interpretan como cero. La búsqueda/rango y el resto del contexto siguen las reglas de la [guía de coordinación](06-COORDINACION-DIARIA.md). Los estados, prioridad y gravedad proceden del registro: no se crean umbrales clínicos. Los tonos de prioridad usan tokens semánticos existentes; el resto usa la paleta de gráficos institucional.

Se incorporan entradas breves de columnas, líneas y segmentos; el acercamiento y foco destacan los enlaces. Se respeta movimiento reducido. En móvil, el anillo apila leyenda y figura, las columnas anchas desplazan dentro de su gráfica y el tablero se adapta al ancho. La animación no provoca solicitudes adicionales: los datos llegan con la consulta existente.

## Archivos y verificación

- `resources/views/components/ui/grafico-operativo.blade.php`: representación de barras, anillo, franja, columnas, puntos, horarios y bloques; leyendas y estados vacíos.
- `partials/graficos-por-proceso.blade.php`: elección y etiquetado según ventana, enlaces al contexto de consulta.
- `partials/vista-agrupada-operativa.blade.php`: Por área/Tablero, sin mutaciones.
- `ExploradorAdministrativoService` y `OperacionController`: admiten las nuevas vistas únicamente en sus módulos correspondientes.
- `operaciones-interactivas.css`: presentación de gráficos, agenda, línea cronológica y agrupaciones con tokens existentes.

Pruebas relacionadas: 18 pruebas PHP con 467 verificaciones aprobadas en SQLite `:memory:`. Cubren las vistas de cada proceso, porcentajes 75/25, total de anillo, enlaces, cero registros sin división, vista incompatible y conservación de registros/permisos. Frontend: 46 pruebas aprobadas, incluida proporción visual de columnas 4:2, ancho de gráfico/tablero a 1200/760/390 px, objetivos táctiles y movimiento reducido. Build aprobado: 91 módulos. Pint y `git diff --check` aprobados.

Navegador real: las once ventanas cargaron su combinación de gráficos sin desbordamiento de página en escritorio. Se comprobó el anillo de Contactos con teclado, su destaque/valor central y el filtro real por cantidad de vínculos. En móvil, figura y leyenda se apilan, el tablero de Alertas no desborda y los gráficos pueden desplegarse. Alertas en Todas/Tablero mostró sus registros agrupados; Incidentes mostró su eje cronológico y los indicadores compactos de grupo/mes único.

Se restauró el acceso con una cuenta ADMINISTRADOR activa existente. El propietario autorizó expresamente usar la credencial local configurada; no se mostró, guardó, cambió ni se crearon usuarios o seeders. El servidor local se mantiene en 8010 y Vite en 5174. Los fallos de la suite global documentados en [Seguimiento](08-SEGUIMIENTO-Y-REPORTES.md) siguen pendientes y no se confunden con esta verificación acotada.
