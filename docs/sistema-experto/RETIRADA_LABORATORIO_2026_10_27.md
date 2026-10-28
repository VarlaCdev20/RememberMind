---
title: "Retirada operativa de simulaciones y laboratorio"
status: CURRENT
last_reviewed: 2026-10-27
owner: ApoyoDecision
source_of_truth: false
verification_scope: RUNTIME_ENTRYPOINTS_REGRESSION_SQLITE_POSTGRES_AND_BROWSER
runtime_verified: true
---

# Retirada operativa del laboratorio

Petición de la propietaria: «todo lo simulado o laboratorio bórralo y desconéctalo». Esta entrega retira el recorrido experimental del producto; no cambia reglas clínicas, permisos, tablas ni columnas. Entrada vigente: [portal](README.md).

## Retirado del producto

- Secciones de ejecutar casos, casos sintéticos, historial de sesión y resultado integral del laboratorio; vistas de resultados simulados y sus trazas.
- Comandos `experto:simular-local`, `experto:probar-perfil`, `experto:archivo-perfil`, `experto:gestionar-conocimiento` y `sistema-experto:cargar-paquete-memoria`.
- Servicio, lector, contexto y persistencia local de simulación; binding operativo del piloto COG-MEM. El parámetro `simulacion` en resultados responde 404.
- Importador/seeder de la cohorte y scripts exclusivos de carga, exportación, comparación y comprobación del laboratorio.
- Incorporación automática de perfiles de la cohorte y reconstrucción de enlaces observacionales desde catálogos `TEST`. Los contratos operativos no convierten esas respuestas en evidencia real.
- Conocimiento ficticio de respaldo del explorador. Sin versión registrada se muestra ausencia de contenido; no se fabrica una red ni una conclusión.

Los algoritmos exclusivos y auxiliares históricos necesarios para regresión se trasladaron a `motor-experto/tests/fixtures/` y `tests/Support/Fixtures/`. Solo los ejecutores de pruebas incluyen sus autoloaders. Composer del producto no puede resolver las clases retiradas; se comprobó después de regenerar el autoload. Los tipos compartidos y codecs de instantáneas conservan su identidad histórica.

## Datos y límites de conservación

No se ejecutó DDL, migración ni borrado de filas. Los 26 instrumentos y las seis versiones del [inventario anterior](inventarios/README.md) siguen siendo conteos físicos históricos, no contenido operativo elegible.

Los filtros excluyen instrumentos/versiones técnicos, sus versiones sucesoras, fuentes marcadas y el lote de residentes identificado conjuntamente por documento `COH30-S01`–`COH30-S30` y observación de admisión exacta. No se identifica a una persona por su nombre ni se excluye una admisión normal por una sola coincidencia. Los directorios de valoración y Enfermería, la consulta de resultados y la captura Petersen aplican esta separación.

**Pendiente: eliminación física de datos sintéticos almacenados.** Se requiere inventario relacional por fila y verificación de referencias compartidas antes de borrar historias o versiones. No se borraron personas, documentos, ocupaciones, evaluaciones ni sus referencias a ciegas. Las pantallas administrativas generales que consultan historia física quedan fuera de esta exclusión específica. No se afirma que la base instalada esté purgada.

El sistema institucional conserva su limitación previa: el conjunto instrumental/Petersen no tiene conservación y recuperación institucional completas. Esta retirada no lo convierte en un experto clínicamente validado ni sustituye la ausencia de mediciones con simulaciones.

## Verificación

| Comprobación | Resultado |
|---|---|
| Unitarias `tests/Unit/SistemaExperto` | 576 aprobadas; 4539 aserciones |
| Retirada + formulario instrumental | 36 aprobadas en el primer corte; las guardas se ampliaron y repitieron por separado |
| Conocimiento institucional + informe cognitivo + informe neurocognitivo | 110 aprobadas; 864 aserciones |
| Navegación + cuatro guardas de retirada | 9 aprobadas; 70 aserciones |
| Repositorios expertos | 97 aprobadas; un caso omitido por requerir PostgreSQL |
| Flujo/configuración históricos y gestión en fixtures | 17 aprobadas; 158 aserciones |
| Guardas de retirada en PostgreSQL desechable propio | 4 aprobadas; 19 aserciones; sin usar la base instalada |
| Navegador privado E2E | Aprobado: Superadministración, Medicina y Enfermería; 1440, 1280, 1024, 768 y 390 px; grafo, teclado, denegación contextual y URL de simulación 404 |
| PHP de vistas compiladas | 650 archivos sin errores de sintaxis |
| `npm run build` | Aprobado; advertencias de tamaño de chunks y tiempos del bundler |

Los casos técnicos prueban software, no desempeño diagnóstico ni aprobación profesional. La evidencia detallada de ejecución está en los logs locales `output/retirada-experto-*`; no se incluyen datos operativos ni secretos en el commit.

Deuda independiente detectada al ampliar comprobaciones: `PreflightReduccionExpertaTest::test_referencia_entrante_fuera_del_mapa_bloquea_el_plan` intenta revertir una reducción sobre `clasificaciones_dcl_instantanea`, tabla física que ya fue consolidada. Ese método no cambió; la comprobación falla antes de ejercer su aserción. No se modificó el esquema ni se debilitó su regla para ocultarlo. La suite global no se declara íntegramente verde.
