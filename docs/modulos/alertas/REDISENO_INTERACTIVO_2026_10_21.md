# Alertas clínicas y cuidados: entrega local del 21/10/2026

## Alcance y fuentes

Implementación local sobre la rama existente, sin staging, commit, push, cambio de rama, despliegue ni migraciones. Se conservaron las modificaciones anteriores del propietario. El diff del árbol incluye trabajo previo y no debe atribuirse íntegramente a esta entrega.

Se revisaron el prompt aportado, el ZIP de referencia, las instrucciones por ámbito, el contrato visual, la documentación vigente del módulo, el baseline y sus decisiones, las consultas, servicios, modelos, rutas y componentes reales. La referencia visual se empleó como punto de partida, con las mejoras posteriores solicitadas. Las propuestas del material adjunto no sustituyen permisos, reglas ni contratos aprobados.

## Implementado

- Bandeja antes del análisis, tarjetas legibles, residente/tipo/motivo separados y acciones visibles según competencia. Se conservan los motivos completos.
- Resúmenes accesibles y accionables: urgentes, por atender, en atención, cerradas, registros anteriores y total. Contador, filtro y resultado comparten estados y prioridades existentes.
- Búsqueda al escribir con 300 ms de debounce, conservación del foco e indicador localizado, sin botón de actualización ni navegación completa. Admite nombres repartidos entre nombres y apellidos, habitación, cama, tipo y motivo.
- Filtros mediante chips, selección de residentes y profesionales mediante búsqueda y tarjetas, y calendario canónico. No hay select, combobox ni details/summary nativos en la bandeja rediseñada.
- Trazabilidad SVG con selección, foco, flechas y zoom, más alternativa «Relaciones en tarjetas» con controles propios. Solo representa vínculos guardados y consultables; no atribuye causalidad clínica.
- Ventanas excluyentes, protección de capturas, errores por campo y prevención de doble envío. El alta dispone de clave de solicitud y bloqueo con las capacidades existentes, sin tablas nuevas.
- Gráficas de consulta bajo demanda y mediciones con fechas/unidades, sin evolución inventada ni sustitución de ausencias por cero.
- Tipo y estado con lenguaje legible. Las claves de tablas dejaron de imprimirse en tarjetas, detalle, histórico, fuentes y cabecera de mediciones. Las referencias internas necesarias para consultas y rutas se conservan.
- «Responsable del seguimiento» muestra el nombre completo y la profesión guardada, o la ausencia explícita de asignación/profesión. Se distingue de la autoría de cada actuación.
- Guía de registro de Enfermería: observación, actuación, respuesta, comunicación y continuidad. Conserva el contrato de nota existente; véase [investigación, persistencia y entrada experta](REGISTRO_ENFERMERIA_Y_ENTRADA_EXPERTA_2026_10_21.md).

## Integración y seguridad

Se conectaron bandeja, pendientes, campana, historial, ficha clínica y accesos desde Cuidados. El grafo y los detalles usan el mismo ámbito autorizado. Un registro de signos solo aparece como origen si pertenece al mismo residente y el actor tiene permiso. Se mantienen las restricciones de familia, turno, asignación, competencia y cuentas activas.

Superadministración consulta el conjunto autorizado; no adquiere intervención clínica por su rol. No se modificaron permisos, reglas clínicas, estados persistidos ni información institucional. La relectura de un origen inaccesible no reconstruye datos ni simula relaciones.

La búsqueda de responsables quedó corregida para consultar y ordenar por `personal.nombres`, en lugar de una columna inexistente en `usuarios`. PostgreSQL detectó el defecto que SQLite no señalaba al ordenar; se corrigió y se repitió la regresión en PostgreSQL.

## Datos institucionales y separación de pruebas

En la instalación real se comprobaron 33 alertas: 25 con estado anterior `ACTIVO` y prioridad media, y 8 cerradas con prioridad alta. Por ello los pendientes y urgentes actuales son cero. No se convirtió `ACTIVO` en `ABIERTA` ni se reabrió una alerta cerrada. El grupo «Registros anteriores» permite encontrarlas y explica su situación.

Los nombres «Elena Sintética Demostración Wilkinson» y «Profesional Sintético…» pertenecían a la instancia aislada de QA del puerto 8895. No son padecimientos. Esa pestaña se cerró, el servidor de prueba se detuvo y se retiró la contraseña temporal del archivo de acceso. La consulta institucional final se ejecutó en transacción PostgreSQL READ ONLY y se revirtió: cero coincidencias de las marcas «Sintétic» y «Demostración» en residentes, personal y contactos. No se crearon ni renombraron personas en la base real.

La aplicación institucional permanece en `http://127.0.0.1:8896`; las pestañas entregadas corresponden a esa instalación. Las capturas bajo `output/alertas-20261021/qa-sintetica/` son exclusivamente evidencia artificial y no deben presentarse como expedientes institucionales. El harness temporal se trasladó fuera del repositorio.

## Rendimiento

Se eliminaron tres agrupaciones de gráficas de cada render cuando el análisis está cerrado. Comparación aislada con el mismo fixture SQLite:

| Medida | Antes | Después |
|---|---:|---:|
| Consultas al renderizar la bandeja | 21 | 18 |
| Agrupaciones | 5 | 2 |
| Tiempo del componente en esa ejecución | 53,50 ms | 40,00 ms |

Los tiempos son muestras locales, no percentiles ni garantía de latencia institucional. Evidencias: `output/alertas-20261021/rendimiento-antes.json` y `rendimiento-despues.json`. No se añadieron índices, cachés de permisos ni infraestructura externa.

## Pruebas y build

| Comprobación ejecutada | Resultado y límite |
|---|---|
| Selección final SQLite: rediseño, resumen, formato visible y regresiones de jornada | 42 pruebas / 255 aserciones, PASS. El filtro de jornada incluyó pruebas relacionadas adicionales. |
| PostgreSQL aislado, nueva búsqueda por nombre completo, grupos y responsable | 1 prueba / 18 aserciones, PASS después de corregir la columna de nombres. Clúster temporal detenido. |
| PostgreSQL aislado, ciclo/traza/filtros del primer corte | 6 pruebas / 80 aserciones, PASS. |
| JavaScript: trazabilidad, signos e interacción | 6 pruebas, PASS. |
| Personal institucional: asignación → jornada → turno | 1 prueba / 7 aserciones, PASS. |
| QA visual/funcional del primer corte | 16 comprobaciones, cinco anchos de 1440/1280/1024/768/390 px sin desborde; ambos temas y sin errores JS en esa pasada. |
| Revisión adicional del último corte | Búsqueda en vivo conserva foco; mapa/tarjetas, ausencia de claves visibles y de desplegables nativos comprobados; resumen institucional leído sin escrituras. |
| Contraste de pares de texto muestreados | Claro: 6,33 / 5,99 / 6,14; oscuro: 4,80 / 5,58 / 8,33. No representa una auditoría AA de toda la aplicación. |
| `npm run build` | PASS, 110 módulos. |

Los cambios finales de etiquetas, la guía de Enfermería y el sexto KPI no tuvieron una segunda pasada visual completa de los cinco anchos. No se afirma QA visual total del último corte. La compilación y las regresiones funcionales se verificaron; no equivalen a validación clínica.

## Incidencia de Personal institucional

La excepción solicitada procedía de cargar `asignacionesTurno.turno`, relación inexistente. La relación documentada pasa por la jornada. Se corrigieron eager loading y lectores a `asignacionesTurno.jornada.turno`, sin añadir un alias ni cambiar el modelo. La página real se volvió a abrir correctamente; la regresión prueba la lectura sin alterar asignaciones.

## Legacy, commit y pendientes

La continuación de formularios, comparación de roles, permiso aprobado para tres especialidades y evidencias del último corte se documenta en [Alertas por especialidad](ALERTAS_POR_ESPECIALIDAD_2026_10_21.md). Su revisión visual sigue pendiente; las capturas de esta entrega anterior no la acreditan.

- No se introdujeron capas de compatibilidad ni dependencias nuevas. Se reutilizó el Design System y la arquitectura modular. El nombre histórico de algunas rutas/componentes existentes permanece fuera de esta entrega.
- No se realizaron operaciones de staging/commit/publicación, conforme al encargo.
- Reclasificar los estados anteriores requiere decisión institucional; no se hizo una migración de datos.
- La vigilancia automática sin sesión conserva su pendiente de autoría institucional; no se presenta como activa.
- El alta repetida se probó con solicitudes sucesivas. No se acredita una prueba de concurrencia real de dos clientes ni de todos los adaptadores de caché.
- Para alimentar el experto con nuevos hechos de Enfermería deben acordarse fuente, tipo, procedencia, fecha clínica y reglas consumidoras. No se transforma narrativa libre en hechos clínicos. El contrato concreto y sus brechas están documentados en la investigación enlazada.
- La revisión incidental del censo detectó etiquetas y consumidores históricos que requieren una tarea propia; no se extendió el rediseño a todo Residentes. El nombre artificial consultado no aparece en su página real ni se detectaron sus marcas en la base institucional.
