---
title: "Activación de MoCA, RUDAS-PE y FAQ: captura y puntuación"
status: CURRENT
version: "1.0"
last_reviewed: 2026-11-04
owner: ApoyoDecision y Valoraciones
source_of_truth: false
verification_scope: ORIGINAL_SCORE_CAPTURE_BACKEND_SCORING_SOURCE_REPLAY_AND_INSTALLED_CATALOG
runtime_verified: true
---

# Resultado

**Tres perfiles activos en la BDD local**, con35 campos en el catálogo existente. Reutilizan el formulario compartido, las aplicaciones/respuestas, el flujo de transcripción y el lector del experto. Son formularios profesionales de **captura de puntuaciones de todas las secciones**, con cálculo descriptivo: el profesional utiliza la hoja original de la edición identificada. No incluyen láminas, estímulos, consignas literales ni un examen autoadministrado por residentes.

La propietaria solicitó cerrar y activar estos perfiles, declaró **«Ya tengo los materiales y permisos para las pruebas completas»** y autorizó **«DIGITALIZARLOS SEGUN TU CRITERIO»**. Se registra su declaración y el alcance implementado. No se presenta como documento emitido por los titulares ni se envían consultas externas. No se pide aval externo ni estudio con residentes para desarrollar o probar.

## Ediciones y cálculos

| Perfil | Edición identificada | Captura |
|---|---|---|
| MoCA Full |8.1 Spain/Spanish, ID061021/Mapi19-02-2018 |17 campos: puntuaciones por secciones, aprendizaje sin puntos, recuerdos adicionales y confirmación del ajuste educativo. |
| RUDAS-PE |Protocolo español2020, suplemento `Table_2.DOCX` |7 campos: registro inicial y seis puntuaciones. |
| FAQ/Pfeffer |UDSv2 B7S, español, febrero2008, diez actividades |11 campos: diez actividades e información del informante. |

MoCA suma el bruto0–30 y aplica+1 cuando corresponde a esta edición, limitado a30. La confirmación educativa es un original de esa aplicación; no se transforma el nivel educativo textual del residente en años. Sin confirmación se conserva el bruto y el total final queda pendiente. El aprendizaje no puntúa; el índice de memoria0–15 usa3/2/1, sin solapar grupos ni añadirse a los30 puntos.

RUDAS suma0–30, exige confirmación del registro inicial y limita recuerdo a0/2/4/6/8. No añade ajuste educativo. FAQ suma0–30 únicamente con diez valores0–3 y participación confirmada del informante. El8=No aplica no se suma, imputa ni convierte en cero. No se introduce un código9 de otra edición o prorrateo no definido.

Cero es un valor válido; vacío conserva ausencia. Las capturas parciales mantienen sus respuestas, totalNULL y avisos humanos. Los enteros/rangos incompatibles, recuerdos solapados o una segunda puntuación manual provocan rechazo antes de guardar. No se instalan puntos de corte, baremos, probabilidades o clasificaciones diagnósticas.

## Fuentes cotejadas

- [Documento MoCA8.1 español](https://championsforhealth.org/wp-content/uploads/2018/12/MOCA-8.1-Spanish.pdf): documento primario alojado por Champions for Health, no descarga autenticada del portal del titular. [FAQ oficial](https://mocacognition.com/faq/) y [condiciones oficiales](https://mocacognition.com/terms-of-use/) diferencian puntuación, interpretación e incorporación.
- [Publicación RUDAS-PE2020](https://www.frontiersin.org/journals/neurology/articles/10.3389/fneur.2020.00374/full) y [suplementos originales EuropePMC](https://www.ebi.ac.uk/europepmc/webservices/rest/PMC7232574/supplementaryFiles). El contenido confirma el protocolo español en `Table_2.DOCX`; `Table_1.DOCX` es la planilla de jueces. Esto resuelve la recuperación fallida del28/10, sin sustituirlo por la guía inglesa.
- [FAQ B7S español, UDSv2](https://files.alz.washington.edu/documentation/uds2-ivp-b7-sp.pdf): identidad de diez actividades y código8. No se confunde con PFAQ de once actividades.

[Metadatos](instrumentos/METADATOS_MATERIALES_2026_11_04.json): procedencia, tamaños y SHA-256. Los originales de consulta permanecen en `output/materiales-instrumentos-20261104/`, fuera de assets públicos y del commit. Una huella identifica material, no acredita licencia.

## Integración, seguridad y conservación

`CircuitoInstrumentalInicial` concentra definición y puntuación; `RegistroMetodosInstrumentales` contiene ahora26 métodos:23 previos por componentes y3 iniciales descriptivos. `PerfilesInstrumentalesIniciales` resuelve selectores sobre PK reales del catálogo y coteja código, versión, nombre, tipo, puntuación máxima, campos, orden, tipos y ausencia de duplicados/opciones ajenas. No identifica por parecido ni fija PK ficticias en configuración.

El seeder instala tres instrumentos y35 preguntas de forma transaccional e idempotente. Un catálogo existente distinto provoca rechazo, sin sobrescritura o reactivación silenciosa. **Sin nuevas tablas, columnas, FK o permisos.** Se mantiene el grano aplicación→respuestas y el modelo normalizado; sin JSON/EAV, nuevas tablas por prueba o copias del contexto de atención.

El writer vuelve a cotejar catálogo y originales bajo transacción y calcula el total en backend. Se conserva autoría, fecha clínica, cuenta registradora y controles documentales de transcripción. Los permisos/competencias vigentes no se amplían por activar un catálogo. Cuenta inactiva, permiso revocado y Superadministración sin excepción local no producen escrituras.

El lector muestra resumen, bruto/ajuste, índice separado, faltantes y límites. La preparación/replay reconstruye desde los originales y el perfil adquirido aunque después cambie configuración. Los métodos devuelven `componentes=[]`: **no generan estadosA/P de dominio, C02 ni C03 desde los totales**. El DTO conjunto continúa transitorio; no se anuncia conservación institucional conjunta que aún no existe.

## Verificación

| Comprobación | Evidencia |
|---|---|
| Unitarias circuito/preparación/replay completo/perfil |30 pruebas/214 aserciones correctas. |
| Nueva integración SQLite |12 pruebas/88 aserciones correctas: tres recorridos SQL→captura→motor→replay, idempotencia, duplicados, ausencias, autorización y formulario→confirmación→guardado/reenvío. |
| Formulario compartido |54 casos existentes correctos en el grupo conjunto. Su único fallo fue un caso nuevo que heredaba la excepción local de Superadministración; tras fijar la configuración del test, los12 casos nuevos se repitieron completos y pasaron. No se cambió el permiso productivo para conseguir verde. |
| PostgreSQL18 desechable |12 pruebas/88 aserciones correctas, sin omisiones; clúster propio, rol sin privilegios y almacenamiento aislado. Apagado/limpieza confirmados. |
| Revisión independiente |PASS técnico y de seguridad; repitió14 unitarias/54 aserciones y12 casos SQLite/88 aserciones, sin editar. |
| Interfaz real aislada |1 recorrido correcto: tres ediciones×cinco anchos1440/1280/1024/768/390, labels, ausencia, puntuación y confirmación; sin desbordamiento ni erroresJS. Capturas390/1440 inspeccionadas y aviso móvil compactado. |
| Build |`npm run build` correcto; aviso previo de bundle>500kB. |
| Matriz |Anexo actualizado y comprobado offline con26 métodos; sin SQL ni inferencia. |

No se ejecutó la suite global ni se afirma exactitud diagnóstica desde pruebas técnicas. Los casos usan datos sintéticos aislados; no se reconectaron simulaciones operativas. Evidencias locales en `output/circuito-inicial-20261104-*`, fuera del commit.

## Activación y uso

Activación local realizada con `php scripts/activar-circuito-instrumental-inicial.php --activar`, seguida de `--comprobar` e inventario. Se confirmaron los tres perfiles, sin cambio de conteos de aplicaciones/respuestas, sin migraciones y sin datos clínicos nuevos. El comando revierte si no se confirman los tres catálogos o si detecta cambios en esos conteos.

Para otro despliegue, primero ejecutar `--comprobar` sobre su conexión prevista y luego la activación explícita. No usar `migrate:fresh` sobre una base operativa. La instalación ya no es un pendiente de estos perfiles en la BDD local.

En **Registrar evaluación**, elegir la edición, registrar fecha y puntuaciones de la hoja original, revisar y confirmar. Para una aplicación anterior, utilizar **Documento original** con sus controles existentes. El área cognitiva ofrece MoCA/RUDAS y la funcional ofrece FAQ; también están en el formulario general autorizado.

Pendiente separado: guardado e historial conjuntos instrumental/Petersen, conforme a la decisión estructural vigente. Esta entrega no reactiva la ampliación rechazada. El historial observacional permanece funcional. Commit local de alcance propio; sin push.
