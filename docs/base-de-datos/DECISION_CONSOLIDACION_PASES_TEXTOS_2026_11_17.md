---
title: "Consolidación de pases de turno en textos clínicos"
status: APPROVED_INSTALLED_LOCAL
last_reviewed: 2026-11-17
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [handover_text_consolidation]
verification_scope: SQLITE_POSTGRESQL_LOCAL_INSTALLATION
runtime_verified: true
---

# Unión de pases de turno y textos clínicos

La propietaria respondió «procede» después de revisar candidatas para reducir tablas sin perder integridad. Esta entrega aplica la primera unión recomendada: pases de turno dentro de registros textuales clínicos. La alternativa de controles cognitivos queda fuera de esta transición.

| Antes | Ahora |
|---|---|
| registros_textuales_clinicos, NOTA/INDICACION | registros_textuales_clinicos, NOTA/INDICACION |
| pases_turno | registros_textuales_clinicos, PASE |

Inventario instalado: **62 operativas + 22 expertas = 84 de negocio**, más **16 técnicas: 100 tablas físicas**. Se retira una relación física respecto al corte anterior de 63 operativas/101 físicas. El núcleo sin las extensiones explícitas de Gerencia tiene **95 tablas físicas**. Las cifras anteriores conservan su fecha histórica.

## Grano y normalización

Cada fila conserva un solo acto textual de un residente, identificado por su código original y clase. Una nota, una indicación y un pase siguen siendo actos distintos; no se combinan sus colecciones en una misma fila ni se introducen listas, JSON o EAV.

La PK física es cod_registro_textual. Los códigos anteriores no se renumeran. Las claves generadas cod_nota, cod_indicacion y cod_pase identifican exclusivamente su clase y permiten referencias tipadas. El CHECK de cada rama conserva obligatoriedad, longitud y exclusión de campos ajenos. No se asigna una atención ni un autor genérico ficticio a un pase: conserva sus propios profesionales saliente/entrante, jornadas y recepción, con sus FK originales.

Las notas conservan su referencia a la nota anterior mediante cod_nota; una corrección no puede apuntar a un pase. Se mantiene la FK compuesta atención/residente para NOTA e INDICACION. El análisis de 3FN/4FN es acotado a esta unión disjunta: no introduce hechos multivaluados independientes ni atributos derivados nuevos. No certifica todas las dependencias funcionales del sistema.

## Integración y seguridad

Los tres modelos conservan sus clases de dominio y filtran por tipo_evento. PaseTurno mantiene cod_pase como identificador público, el prefijo PAS, relaciones, estados y fechas de entrega/recepción. Los atributos exclusivos de las otras clases no se serializan con el pase. Las consultas correlacionadas del servicio usan el nuevo nombre físico.

Se reutiliza RegistrosConsumo para inserciones y consultas lógicas de seeders y pruebas; el catálogo de códigos conserva pases_turno como alias lógico. Esos nombres no representan una tabla retirada que siga siendo consultada. No se incorporan vistas SQL ni otra capa V1.

No se amplían permisos ni responsabilidades profesionales. Se mantienen cuenta activa, permisos, alcance por residente/jornada, transición de entrega/recepción y autoría. Las pruebas existentes incluyen acceso ajeno, lectura fuera de jornada y concurrencia de doble entrega/doble recepción en PostgreSQL. La auditoría técnica conserva su mecanismo existente.

## Migración y reversión

Migración automática del núcleo: database/migrations/2026_11_17_000001_consolidate_pases_textos.php. Reutiliza el soporte ConsolidacionTipada20261116, con el contrato de ambas fuentes congelado en la migración.

La transición bloquea las fuentes y referencias en PostgreSQL, verifica columnas/FK, copia en una transacción, compara los valores mediante EXCEPT en ambos sentidos y reconstruye las referencias e índices. El origen se retira únicamente después de verificar la copia. No había triggers propios de las dos fuentes que hubiera que trasladar.

La reversión reconstruye ambas fuentes con sus claves, restricciones, valores e índices. Primero debe revertirse esta unión del 17/11 y después la de textos del 16/11. El soporte ahora rechaza una reversión antigua si detecta columnas posteriores; también exige que la suma de filas restituidas cubra íntegramente el destino. Así se evita perder una clase añadida después. En SQLite, los nombres de índices UNIQUE incorporan el conjunto de fuentes para no colisionar al consolidar una relación previamente consolidada.

Despliegue y reversión requieren el código correspondiente al esquema y reiniciar los procesos persistentes que mantengan clases antiguas cargadas. El rollback del esquema no adapta automáticamente un proceso PHP que siga ejecutando el código nuevo.

## Instalación local y conservación

Destino comprobado: PostgreSQL local remembermind_dev. Antes de confirmar se realizó un ensayo poblado de ida y vuelta con igualdad exacta de contenido.

| Origen local | Filas conservadas |
|---|---:|
| Textos clínicos previos, todos NOTA | 135 |
| Pases de turno | 1 |
| Total | 136 |

Se creó y verificó el catálogo del respaldo privado union-pases-textos-20261117-da67af5c5120b075.dump. El respaldo, expedientes y huellas de comparación quedan fuera de Git. Se contrastaron también tablas dependientes y asignaciones de roles/permisos sin cambios. Las seis versiones existentes de conocimiento se recuperaron sin modificarlas.

Tras instalar: 100 tablas físicas, 62 operativas, 22 expertas y 16 técnicas; 135 notas y un pase accesibles mediante sus modelos; cero FK pendientes de validación.

## Pruebas ejecutadas y límites

- SQLite, pase/consultas/códigos/inventario: **63 pruebas, 638 comprobaciones**, sin fallos.
- SQLite, unión nueva y regresión de consolidaciones previas: **9 pruebas, 117 comprobaciones**, sin fallos.
- SQLite, lectura fuera de jornada, consultas por capa, reportes de enfermería y otras consolidaciones: **47 pruebas, 403 comprobaciones**, sin fallos. Incluye el guard de consulta contra el nombre físico nuevo y exportaciones con alcance y volumen.
- SQLite, estructura, historia nominal y avisos de Gerencia: **28 pruebas completadas, cuatro omitidas por requerir PostgreSQL y 201 comprobaciones**, sin fallos.
- PostgreSQL desechable, entrega/recepción y concurrencia más consolidación previa: **38 pruebas, 216 comprobaciones**, sin fallos. Incluye dos procesos concurrentes para entrega y para recepción.
- PostgreSQL desechable, unión nueva, renumeración y regresión de consolidaciones: **6 pruebas, 80 comprobaciones**, sin fallos.
- PostgreSQL desechable, estructura, historia nominal y avisos de Gerencia: **32 pruebas y 240 comprobaciones**, sin fallos ni omisiones. Incluye los cuatro casos concurrentes omitidos en SQLite.
- Las pruebas nuevas verifican reversión poblada, todas las columnas del pase recibido, corrección de nota, códigos posteriores, separación de tipos, FK/CHECK por SQL directo y fallo intermedio de esquema sin transición parcial.

Una ejecución SQLite ampliada terminó con **80 pruebas, 539 comprobaciones, un error y tres fallos**; no se declara verde. Dos casos de SeguridadCriticaEnfermeriaTest crean una prescripción a las 10:00 e intentan registrar su dosis de las 08:00, que la agenda vigente excluye por ser anterior a la prescripción. MiTurnoServiceTest espera el texto «La evolución aparecerá con un turno activo.» y TurnoCompletoEnfermeroTest espera «Alertas clínicas», ausentes en las vistas actuales. Los servicios de medicación y los dos últimos archivos de prueba coinciden con sus preimágenes anteriores a esta tarea. No se modificaron estas reglas ni las vistas para forzar el resultado. La adaptación de SeguridadCriticaEnfermeriaTest se limita a las inserciones/recuentos de pases.

La revisión de persistencia del alcance resulta PASS: copia/reversión poblada, claves tipadas, rechazo de mezcla por SQL directo y concurrencia comprobados en PostgreSQL, además de cobertura SQLite. La revisión de seguridad del cambio resulta PASS para conservación de alcance, autoría y separación de tipos; no constituye una auditoría global de permisos. Los negativos de pase cubren cuenta desactivada, receptor ajeno, autor ajeno, residente fuera de alcance y ausencia de escritura por simple permiso de lectura.

Los grupos se ejecutaron mediante php artisan test --filter y scripts/probar-postgres.ps1 -Filtro con los nombres de las clases citadas. Los registros técnicos privados se identifican como pases-textos-inicial-sqlite, pases-textos-consolidaciones-sqlite, pases-textos-integracion-acotada-sqlite y pases-textos-gerencia-sqlite. Los informes PostgreSQL incluyen remembermind-pg-005bb368548c48199afbc08912756390, remembermind-pg-3ada8dec32094287a915189d6d85ab1c y remembermind-pases-gerencia-20261117, conservados fuera del repositorio.

Verificaciones realizadas sobre el árbol compartido, conservando cambios previos. Las adaptaciones de scripts/instalar-resultado-institucional.php, GerenciaCamposAvisoEstructuraTest, GerenciaAvisoCompartidoTest y el caso añadido previamente a ReporteEnfermeriaPeriodoTest permanecen con ese trabajo previo fuera del commit de esta entrega. No se ejecutó la CI global ni una aceptación visual; no hubo cambios de frontend que requieran build. Los cuatro fallos de la ejecución ampliada quedan pendientes fuera de esta consolidación, junto con las deudas anteriores no relacionadas.
