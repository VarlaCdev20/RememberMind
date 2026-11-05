---
title: "País y tipo de documento, país de teléfono en Personal"
status: CURRENT
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [personal_international_identity]
---

# Autorización expresa

El04/11/2026 la propietaria respondió «Sí, autorizo las tres columnas» a la propuesta de conservar en Personal país emisor del documento, tipo de documento y país del teléfono. Sin tablas o permisos nuevos; sin rellenar datos antiguos por suposición.

## Extensión acotada

- personal.pais_documento: string(2), nullable; ISO3166-1 alfa2 seleccionado por la persona operadora.
- personal.tipo_documento: string(30), nullable; tipo capturado que determina el formato.
- personal.pais_telefono: string(2), nullable; país seleccionado independientemente del documento.

Se conserva grano de un trabajador, PK/FK y unicidad global de numero_documento, incluso entre países. No se duplica identidad en usuarios. Sin nuevas tablas, índices o permisos: inventario106 negocio+16 técnicas. DDL explícito: database/propuestas/gerencia/2026_11_04_000004_pais_documento_telefono_personal.php; fuera del descubrimiento automático del núcleo.

Teléfono nuevo/editado con país explícito se guarda en personal.telefono como prefijo internacional+numero nacional; el campo existente admite30 caracteres. No se modifica ni interpreta el teléfono previo durante instalación. La edición recupera país y elimina únicamente su prefijo conocido de la captura nacional. Un registro anterior sin metadatos muestra selección pendiente, no Bolivia inferida. Los nuevos formularios presentan Bolivia/CI/+591 como valores iniciales visibles y modificables.

La elección de país emisor no sustituye nacionalidad. No se añade país a usuarios ni se confunde tipo_documento con el catálogo de archivos adjuntos. Expedido en solo corresponde a CI de Bolivia; en otros casos debe quedar nulo. No se antepone un código telefónico al número de identidad.

## Reglas y límites

Se recupera la lista de13 países concretos del selector de Usuarios. Se omite el antiguo SSN como documento de identidad estadounidense; se ofrece pasaporte. No se infiere operadora por prefijo. Celular boliviano:8 dígitos y comienzo5/6/7, solicitado por la propietaria y compatible con el plan ATT; no equivale a comprobar asignación o titularidad. Los formatos se validan localmente, sin enviar identidad a terceros.

País/tipo determinan captura numérica o alfanumérica y longitud: CI Bolivia5–10; DNI Perú8; DNI Argentina7–8; CPF Brasil11; RUN Chile con dígito verificador en el texto; cédula Ecuador10; CURP México18; DNI España8+letra y NIE X/Y/Z+7+letra. En Colombia/Paraguay/Uruguay/Venezuela se restringe el número nacional a dígitos y máximo30 sin inventar longitudes no verificadas. Pasaporte: alfanumérico máximo30. No se comprueba emisión, vigencia, titularidad ni dígitos de control mediante algoritmos; el archivo documental y revisión institucional mantienen su flujo propio. Otros países no están incluidos en esta recuperación acotada.

Fuentes técnicas de formato: [DNI Perú](https://www.gob.pe/13432-encontrar-d-gito-verificador-en-el-dni), [CPF11 dígitos](https://www.gov.br/iti/pt-br/central-de-conteudo/manual-de-instrues-do-cadastro-de-agr-v1-2-pdf), [CURP18](https://sidof.segob.gob.mx/notas/docFuente/5526717), [DNI/NIE España](https://sede.agenciatributaria.gob.es/Sede/ayuda/manuales-videos-folletos/manuales-practicos/guia-practica-cumplimentacion-modelo-censal-036/anexos/anexo-01-solicitud-nif-documentacion-aportar/informacion-sobre-numero-identificacion-fiscal/composicion-nif/personas-fisicas.html), [cédula Ecuador](https://apps.registrocivil.gob.ec/portalCiudadano/login.jsf), [RUN Chile](https://deis.minsal.cl/norma-eis-estandares-de-datos-de-la-persona-identificacion-de-la-persona-responsivo/), [prefijos ITU](https://www.itu.int/ITU-T/inr/nnp/index.html), [Plan ATT](https://w2.att.gob.bo/sites/default/files/archivos_listados_pdf/2025-09-18/29.%20APROBAR%20EL%20PLAN%20T%C3%89CNICO%20FUNDAMENTAL%20DE%20NUMERACI%C3%93N.pdf). Argentina y estructura inicial de países: configuración previa de UsuariosPanel; no se presenta esa implementación como verificación de autenticidad.

## Instalación y reversión

Instalador explícito comprueba destino local remembermind_dev PostgreSQL, pruebas SQLite/PostgreSQL, respaldo privado con catálogo legible, hash de filas/columnas existentes e inventario de tablas sin cambios. No imprime secretos ni filas. La migración no hace backfill. El rollback técnico se admite únicamente si las tres columnas están completamente nulas; si existe metadato se rechaza para evitar pérdida. Desinstalar datos posteriormente exige otra decisión; no se ejecuta rollback operativo en esta tarea.

## Evidencia de instalación local

Instalada explícitamente mediante scripts/instalar-personal-paises.php después de48 pruebas/276 aserciones correctas en SQLite y el mismo alcance48/276 en PostgreSQL desechable. Registro migrations añadido solo para este DDL. Verificado en salida privada output/personal-paises-instalacion-20261104.json:122 tablas físicas, tres columnas nuevas, hash de filas anteriores idéntico, campos previos nulos y respaldo con catálogo legible. No se imprimieron ni alteraron datos personales previos. La salida de instalación contiene únicamente metadatos técnicos y referencias al respaldo privado; no se incorpora al commit.

## Ampliación de opciones sin DDL del05/11/2026

La propietaria acepta Otro país mediante búsqueda y selección de un código reconocido. Se amplía el catálogo local de opciones, conservando exactamente las tres columnas anteriores y sin campos adicionales de nombre/prefijo. El alcance actualizado y sus límites constan en [Personal: Otro país](../frontend/PERSONAL_OTRO_PAIS_2026_11_05.md). El límite anterior de13 países corresponde a la primera entrega; las reglas particulares de esos13 se conservan. No cambia el inventario de tablas, columnas ni unicidad.
