---
title: "Ficha documental I04 — FAQ/Pfeffer"
status: CURRENT
version: "1.1"
last_reviewed: 2026-10-28
owner: ApoyoDecision
source_of_truth: false
verification_scope: VERSION_DISAMBIGUATION_AND_STATIC_IADL_MAPPING_NOT_ACTIVATION
runtime_verified: false
---

# I04 — FAQ/Pfeffer

[Circuito inicial y captura compartida](CIRCUITO_INICIAL.md) · [Selección aprobada](SELECCION_ADOPTADA_2026_10_28.md).

## Identidad y finalidad

| Campo | Definición y estado |
|---|---|
| Familia seleccionada | Functional Activities Questionnaire, FAQ/Pfeffer. Aporta información de un informante sobre función instrumental; no es una prueba cognitiva directa. |
| Referencia original | Pfeffer y colaboradores, 1982, **10 ítems**, total de 0 a 30 cuando procede según su protocolo. [F1] |
| Referencia española localizada | Traducción/adaptación UDS por STAWG, descrita en Acevedo y colaboradores, 2009. Se obtuvo y cotejó como referencia histórica el **B7S español UDSv2, visita inicial, versión 2.0, febrero de 2008**, diez actividades, una página. No se adopta para instalación. [F2, F7] |
| Alternativa documental actual | Formulario B7 español de NACC UDSv4, febrero de 2026, identificado como **NACC Functional Assessment Scale (FAS)**, adaptado de Pfeffer. No renombrarlo automáticamente como FAQ original ni instalarlo por compartir diez actividades. [F3–F4] |
| Variantes que deben distinguirse | Hay cuestionarios de **11 ítems** denominados Pfeffer, como el de MINSAL Chile. No comparten necesariamente máximo, instrucciones o interpretación con FAQ de 10 ítems. [F5] |
| Estado de edición | Familia aprobada; **edición/adaptación española ejecutable todavía no cerrada**. La referencia FAQ de 10 ítems guía la comparación; no constituye adopción automática de un formulario concreto. |

## Aplicación y límites

Registrar quién informa, cómo conoce el funcionamiento previo y qué oportunidades ha tenido de observarlo. En la residencia, separar dificultad real de sustitución institucional, falta de oportunidad, hábito previo, limitación física/sensorial y desconocimiento.

No tratar «nunca lo hizo», «no aplica», «no sabe», falta de oportunidad y normalidad como sinónimos. La versión original y las adaptaciones manejan estas situaciones de formas distintas; el protocolo elegido debe definir el cálculo y la validez del total. No programar suma, imputación, prorrateo o conversión de códigos especiales a cero antes de cerrarlo.

La aprobación no fija un umbral de dependencia ni un diagnóstico de DCL. No aplicar un punto de corte de 11 ítems al total de diez, ni usar un total aislado para acreditar C03. Tampoco acredita causalidad cognitiva de una dificultad funcional.

## Capacitación y derechos

Aplicación y revisión por personal competente dentro de permisos vigentes. No se introduce un rol nuevo ni se considera que el informante haya realizado una valoración profesional por responder un cuestionario.

Las reproducciones consultadas citan derechos de la Gerontological Society of America y permiso al editor correspondiente. El formulario NACC de permisos, página 1, limita ese procedimiento a investigación y excluye el uso clínico diagnóstico [F6]. No es una vía de autorización asistencial suficiente para RememberMind. **No se ha obtenido autorización digital para RememberMind** ni se ha acreditado que un permiso concedido a un editor cubra esta implementación. Confirmar material/adaptación final y alcance de reproducción, digitalización, cálculo y almacenamiento. [F1, F3]

## Contrato de integración y brecha concreta

- Conservar el cuestionario original mediante aplicaciones/respuestas del [circuito compartido](CIRCUITO_INICIAL.md), con fecha, autor y documento cuando corresponda, sin inventar preguntas.
- Las ocho actividades descriptivas de `ControlFuncionalIadl::ACTIVIDADES` **no son los diez ni los once ítems de FAQ/Pfeffer**. No reemplazar el formulario con ese catálogo ni añadirle actividades por esta ficha.
- `ContratoFuentesPetersen` admite `cod_control_iadl` para el soporte IADL de C03. No admite la aplicación de FAQ como sustitución directa. La conexión requiere un recorrido profesional y procedencia verificables; no se declara implementado aquí.
- No crear otro registro clínico duplicado, nueva FK o una referencia libre dentro de `observacion` para fingir una relación estructural. Si el modelo no representa la procedencia necesaria, documentar la decisión pendiente antes de automatizarla.
- No hay un método FAQ registrado ni un perfil activado por esta ficha. La captura genérica debe comprobar cálculo y ausencias conforme a la edición que se adopte.

## Cotejo posterior del 28/10/2026

El B7S histórico se conserva fuera de Git con hash y procedencia, junto a una referencia HIGN en inglés cuyo permiso publicado es educativo sin lucro [F7–F8]. Ninguno acredita reproducción digital asistencial por RememberMind. [BiblioPRO](https://bibliopro.org/buscador/349/cuestionario-de-actividad-funcional) remite a autores y no gestiona sublicencia; se preparó una consulta de material/edición/derechos al contacto allí publicado, sin enviar. [Cotejo y documentos](COTEJO_MATERIALES_2026_10_28.md).

## Cierre y pendientes

**Concretado:** finalidad, discriminación de variantes, necesidades del informante, riesgo de confundir ausencia con normalidad y brecha FAQ–IADL. **Pendiente para operación:** elegir/cotejar material español exacto y derechos, protocolo de puntuación/ausencias y aplicabilidad, capacitación, captura y recorrido de procedencia profesional hacia C03. No fijar el nombre «FAQ/Pfeffer» como identidad suficiente.

## Fuentes consultadas el 28/10/2026

- F1: [Alzheimer's Association, Tools for Early Identification, anexo 3](https://www.alz.org/documents/national/CCN-AD03.pdf), referencia a Pfeffer y colaboradores, 1982; estructura, rango y aviso de reproducción con permiso. No se reproduce el cuestionario en esta ficha.
- F2: [Acevedo y colaboradores, traducción/adaptación española UDS, 2009](https://pmc.ncbi.nlm.nih.gov/articles/PMC2963171/). Identificación y alcance desde resultado indexado; el acceso directo devolvió verificación automática en esta consulta. No acredita material exacto ni permiso de uso.
- F3: [NACC, documentación UDSv3](https://nacc-prod.parallelpublicworks.com/data-forms-and-documentation/udsv3-forms-and-documentation/), formularios españoles y procedimiento de permisos. El procedimiento no cubre el uso clínico diagnóstico que excluye expresamente [F6].
- F4: [NACC, paquete español UDSv4 de seguimiento](https://files.alz.washington.edu/documentation/uds4-fvp-packet-es.pdf), B7 identificado como FAS, febrero de 2026, desde índice del PDF. Su autoría/adaptación no lo vuelve intercambiable con FAQ original.
- F5: [MINSAL Chile, orientación técnica, anexo 3](https://www.minsal.cl/wp-content/uploads/2019/01/RE.1992-del-11-de-diciembre-2018..pdf), variante de once preguntas. Sus reglas no se adoptan para la residencia.

- F6: [NACC, formulario de permisos, página 1](https://files.alz.washington.edu/nacc-permission-form.pdf), consultado y descargado el 28/10/2026. Alcance limitado a investigación y exclusión del uso clínico diagnóstico.
- F7: [B7S español UDSv2, visita inicial, febrero de 2008](https://files.alz.washington.edu/documentation/uds2-ivp-b7-sp.pdf), descargado y cotejado; referencia histórica de diez actividades, sin adopción ni permiso propio.
- F8: [HIGN/NYU, D13 revisado en 2016](https://hign.org/sites/default/files/2020-06/Try_This_Dementia_13.pdf), referencia inglesa y condiciones educativas, no permiso de software clínico.
