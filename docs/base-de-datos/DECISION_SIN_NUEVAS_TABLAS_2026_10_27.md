---
title: "Decisión vigente: no crear más tablas"
status: CURRENT
last_reviewed: 2026-10-27
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [new_table_creation]
---

# Decisión expresa del27/10/2026

La propietaria respondió **«NO APRUEBO LA CREACION DE NINGUNA TABLA MAS,
PUEDES AMPLIAR CAMPOS EN TABLAS NO CREAR OTRAS»** a la propuesta42 de
seguimiento de avisos de planificación.

No se crean ni instalan tablas adicionales. El inventario institucional
permanece **106 de negocio +16 técnicas**, después de la extensión35/37.
El techo de diseño110 de la decisión anterior no es un cupo que permita
ignorar esta nueva restricción.

Se permite desarrollar ampliaciones de campos sobre relaciones existentes
cuando respeten su grano, normalización, historia, FK y contratos. No autoriza
listas JSON/EAV, columnas repetidas de destinatarios o entidades de dominio
codificadas en texto. Tampoco aprueba por implicación cambios clínicos,
responsabilidades o permisos sensibles, ni permite alterar relaciones o
borrar datos para eludir el límite.

La [propuesta42](../especificacion-funcional/vistas/gerencia/42-PROPUESTA-AVISOS-PLANIFICACION.md)
está rechazada y nunca instalada; su código de tabla se retiró. La
[alternativa43](../especificacion-funcional/vistas/gerencia/43-AVISOS-SIN-TABLAS-NUEVAS.md)
ensaya seis campos sobre eventos sin añadir tablas. Su integración funcional
fue adoptada después mediante la respuesta «Aviso compartido por cambio».
La [decisión específica de campos y alcance](DECISION_AVISO_COMPARTIDO_CAMPOS_2026_10_27.md)
resuelve esa consulta; el ensayo estructural previo no fue por sí solo aprobación.

Una instrucción posterior expresa puede sustituir esta restricción. Hasta
entonces, no pedir ni instalar otra tabla como consecuencia automática de
«continúa», del techo110 o de una prueba técnica satisfactoria.
