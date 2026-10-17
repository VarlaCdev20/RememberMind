---
title: "Contrato compartido de UX/UI de RememberMind"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: true
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

# Contrato compartido de UX/UI de RememberMind

Dirección aprobada por la propietaria el 6 de octubre de 2026 para el stack de skills. Este documento define cómo trabajar; sus valores objetivo no están aplicados automáticamente al producto.

## Autoridad y alcance

1. Instrucción actual de la propietaria y referencia visual aprobada para la pantalla.
2. Baseline congelado, diccionario físico y decisiones aprobadas de extensión para el dominio/persistencia: [baseline](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [70 tablas](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [V2.2](../base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md). Inventario actual: 71 tablas operativas.
3. Documentación funcional vigente y [arquitectura actual](../arquitectura/README.md).
4. [Reglas del proyecto](../../AGENTS.md), [reglas de interfaz](../../resources/AGENTS.md), [Design System implementado](../../resources/frontend/styles/design-system/README.md), componentes, tokens y contratos reales del flujo.
5. Heurísticas auxiliares y documentación histórica, como contexto.

La imagen aprobada determina proporciones, composición, densidad, geometría, tipografía, profundidad, sombras y glass para esa pantalla. No se sustituye por otra estética del agente. Se adapta contenido, permisos, reglas clínicas, datos, responsive y requisitos de accesibilidad. Si la referencia entra en conflicto con legibilidad, seguridad o funcionamiento, señalar el elemento concreto y hacer la adaptación mínima; una imagen nunca autoriza datos ni reglas clínicas nuevas.

El código es evidencia del estado actual. Cuando contradice una decisión vigente, documentar la diferencia y planificar la corrección autorizada. No mezclar silenciosamente dos paletas ni considerar ya implementado un token descrito aquí.

## Límites de dominio

Una skill UX/UI no crea/modifica tablas, columnas, PK/FK, cardinalidades, catálogos, entidades ni relaciones; no introduce JSON/EAV como sustituto de persistencia relacional. Un DTO/JSON de transporte de datos planos no es autorización para cambiar el modelo persistido.

No inventar datos, métricas, objetivos, recomendaciones, severidades, responsabilidades o permisos. La interpretación clínica procede del backend y fuentes aprobadas. No deducir objetivos de texto libre. El frontend puede expresar una decisión autorizada y mostrar preview; no convierte un valor sin guardar en historia ni en operación realizada.

Ante una necesidad ausente, producir este registro con datos concretos:

```text
UX GAP DETECTADO
Necesidad: dato o acción requerida por el usuario y su propósito.
Contrato actual: fuente inspeccionada y capacidad que realmente ofrece.
Alternativas: opciones usando el contrato actual y propuesta separada, si existe.
Cambio de BDD requerido: SÍ / NO, con justificación.
Requiere aprobación: SÍ.
```

Detener solo la modificación estructural o decisión institucional pendiente. Continuar el trabajo seguro independiente. No pedir aprobación para decisiones técnicas reversibles ya autorizadas; no fabricar la aprobación faltante.

## Terminología y roles

| Término | Uso |
|---|---|
| Postulante / adulto mayor de preadmisión | Persona en preadmisión |
| Residente | Persona formalmente admitida |
| Usuario | Cuenta de acceso |
| Personal | Trabajador/profesional, con o sin cuenta |
| Contacto | Familiar, responsable o persona relacionada |

No utilizar estos términos como sinónimos ni sustituir universalmente residente por paciente. Mantener rutas/identificadores existentes al escribir copy; no introducir dependencias V1. UI por rol significa tareas y prioridades distintas, con componentes comunes y permisos reales. Familiar solo ve información autorizada del residente vinculado. Superadministración no implica escritura clínica.

## Lenguaje cálido geriátrico

Transmitir cuidado, tranquilidad, acompañamiento, seguridad y claridad mediante composición serena, contraste, superficies cálidas, espacio útil y profundidad. Evitar infantilización, hospital frío, SaaS genérico, minimalismo vacío, ilustraciones/plantas decorativas repetidas, pasteles sin contraste, blanco/negro puros dominantes y glass en todas las cards.

Las superficies ordinarias son neutras. Normal se representa con badge, icono, borde, tinte pequeño y texto. Una alerta crítica puede ocupar mayor superficie semántica y permanece reconocible; la calidez nunca la suaviza ni la oculta en un toast.

## Paleta objetivo y correspondencia con el producto

Actualización final aprobada el 14/10/2026: paleta completa institucional y posterior eliminación de degradados en fondos/controles/gráficas. Solo permanecen los degradados tipográficos. El relieve procede de bordes y sombras compartidas. La corrección posterior mantiene navbar y sidebar en capuchino (#D7CEC7) y aclara el fondo general (#D7CEC7), con trama de puntos menudos solo en el fondo. La instrucción vigente sustituye el corrugado y la propuesta orgánica por puntos de radio 0.6 px, separados 16 px, conservando la profundidad interior. Esta decisión sustituye la propuesta salvia #8DA18A/fondo #E7E0D9. Ver [paleta final y verificación](PALETA_FINAL_SOLIDA_Y_RELIEVE.md).

Corrección posterior vigente: fondo café grisáceo `#D8D2CD`, cards más claras `#ECE9E6`, apoyo `#DFD9D4`, ventanas `#F0EDEA` y campos `#E7E2DE`, sin dominante amarilla. Sustituye los valores de la propuesta anterior descrita arriba. Véanse [paleta cálida](PALETA_CALIDA_COMPOSICION_2026_10_14.md) y [composición global](COMPOSICION_GLOBAL_2026_10_14.md).

Estos valores vienen de la tarea de la propietaria. La futura implementación los consolida en el Design System, sin HEX locales ni una segunda paleta por rol.

| Nombre del brief | Objetivo | Contrato implementado a inspeccionar |
|---|---|---|
| `--rm-app-bg` | `#D8D2CD` | `--rm-bg-app` y trama solo en el lienzo |
| `--rm-shell-bg` | `#D7CEC7` | `--rm-bg-shell` |
| Navbar | `#D7CEC7` | `--rm-bg-shell` en topbar; headers de contenido conservan su superficie |
| `--rm-card-bg` | `#ECE9E6` | `--rm-surface` y tokens de cards |
| `--rm-surface-soft` | `#DFD9D4` | `--rm-surface-soft` |
| `--rm-text-primary` | `#4F4742` | `--rm-text-primary` |
| `--rm-text-secondary` | `#6B625C` | `--rm-text-secondary` |
| `--rm-text-muted` | `#8A8078` | `--rm-text-muted` |
| Acento Enfermería | `#3F8F76` | Acento de cuidado/acción compartido, según función |

Los nombres del brief son conceptuales; preferir evolucionar el token semántico existente y documentar alias realmente necesarios. No crear `--rm-enf-*` ni convertir todas las superficies en verdes. Sage, tonos tierra y acentos clínicos acompañan la base neutral.

Medir contraste de cada par sobre la superficie compuesta real, incluido glass y modo oscuro. El muted objetivo no se presupone válido para texto pequeño. Si un par no pasa, conservar la paleta en superficies y proponer una tinta semántica contrastada, documentando la adaptación. Ningún HEX de este documento promete accesibilidad por sí mismo.

## Tipografía por función

La tarea define esta jerarquía objetivo; `tokens/typography.css` y `resources/frontend/styles/tokens/tipografia.css` muestran la implementación actual. Usar fuentes locales existentes y comprobar pesos disponibles. No cargar las cuatro familias en cada bloque ni importar fuentes por CDN.

| Función | Familia objetivo | Tratamiento |
|---|---|---|
| Display | Outfit | Marca y saludos especiales; uso puntual |
| Page title | Nunito Sans / Nunito disponible | Título de tarea, jerarquía principal |
| Modal title | Nunito Sans | Identificar acción y contexto |
| Section title | Nunito Sans | Agrupación funcional |
| Card title | Nunito Sans | Identidad breve y escaneable |
| Clinical value | Nunito Sans | Valor dominante; unidad visible y cifras tabulares |
| Body | Inter | Lectura continua y datos |
| Label | Plus Jakarta Sans donde ya aplique; Inter en captura densa | Etiqueta visible, estable y asociada |
| Caption | Inter | Metadato secundario legible, nunca única ubicación de una alerta |

No inventar una nueva escala de tamaños por vista. Elegir tokens de tipo por función, revisar zoom y nombres largos, ajustar la escala compartida cuando corresponda. No reducir letra para ocultar overflow ni mantener tamaños históricos pequeños por inercia.

## Escalas comunes de profundidad, glass y movimiento

| Depth | Uso | Alias conceptual | Token existente de partida |
|---|---|---|---|
| 0 | Fondo | Sin elevación | `--rm-shadow-none` |
| 1 | Card básica | `--shadow-card` | `--rm-shadow-card` |
| 2 | Panel importante | `--shadow-panel` | `--rm-shadow-raised` |
| 3 | Control flotante | `--shadow-floating` | `--rm-shadow-floating` |
| 4 | Diálogo | `--shadow-dialog` | `--rm-shadow-modal` |

Los alias conceptuales no son tokens nuevos instalados. Consolidar futuras modificaciones en `tokens/shadows.css`, usando tinta cálida, sin sombras negras pesadas. La profundidad sigue jerarquía, no cantidad de cajas anidadas.

Glass soft: toolbar, segmented control y cabecera contextual. Glass elevated: control flotante, cabecera de modal y overlay secundario. Definir opacidad, borde, blur y sombra en tokens/componentes compartidos cuando se implemente. Proporcionar fallback opaco legible y verificar el fondo real; evitar glass en tablas, captura densa e historia larga.

| Motion | Objetivo del brief | Uso |
|---|---|---|
| Instant | 80–120 ms | Feedback inmediato |
| Fast | 120–150 ms | Hover/foco |
| Standard | 160–190 ms | Cambio de estado de componente |
| Panel | 180–240 ms | Modal/drawer |
| Layout | 200–280 ms máximo | Transición de página/layout |

`tokens/motion.css` contiene valores actuales distintos y duraciones largas para entrada/gráficas. La skill de motion registra esa diferencia y reutiliza/consolida tokens dentro del alcance autorizado; no copia tiempos antiguos fuera de los límites del brief ni pone duraciones manuales en cada vista. El movimiento siempre comunica estado, causalidad, jerarquía, dirección, feedback o relación espacial. Respetar `prefers-reduced-motion`.

## Reutilización y evidencias

Antes de crear: buscar componente, comparar responsabilidad, extender si procede y evitar duplicar geometría. `PersonEntityCard`, `ResidentCard`, `UserCard`, `StaffCard` y `ContactCard` pueden compartir geometría; datos, acciones y permisos conservan su identidad. Los nombres conceptuales `Rm*` no obligan a crear archivos si existe un equivalente Blade/CSS.

Cada ejecución informa, proporcionalmente al alcance: ANTES (diagnóstico), PLAN (qué conservar/cambiar y por qué), IMPLEMENTACIÓN (archivos, o «sin edición» si es análisis/QA), VERIFICACIÓN (desktop/tablet/mobile/estados realmente revisados y limitaciones). Usar datos sintéticos en pruebas y capturas. No exponer datos clínicos en servicios externos.

Para aceptación visual revisar 1440, 1280, 1024, 768 y 390 px; teclado, foco, touch aproximado mínimo de 44 × 44 CSS px, contraste, ausencia de scroll horizontal, loading/empty/error/success y estados pertinentes. Tests y build no sustituyen inspección visual. Si falta evidencia requerida, QA produce `FAIL` por verificación incompleta, sin inventar un defecto observado.

Corrección de superficies del 14/10/2026: la propietaria pide unificar las áreas y retirar blancos. En el shell operativo, cabeceras/filtros/cards/paneles/modales/menús comparten #E6DED6; campos #D6CCC3. Se conservan el fondo general aclarado, puntos menudos, chrome capuchino, texto legible y estados semánticos. El tema oscuro mantiene su reinterpretación petróleo.

Dirección vigente: neutros arena/cacao de intensidad media, superficies de apoyo #D2C5BB y elevadas #EBE3DC. Acentos salvia, petróleo, coral, oro, menta y lavanda más presentes en sus funciones existentes. Relieve con bordes y sombras cálidas; el color de estado sigue procediendo del dominio. No se cambian layout ni reglas clínicas.

Corrección vigente: sidebar y navbar se aclaran al mismo café grisáceo #D7CEC7 del fondo. El token capuchino reutiliza el de página, sin duplicar color; acentos y relieve de Inicio se conservan.
