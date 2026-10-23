# Sistema experto: siete referencias, 22/10/2026

## ANTES — fuentes y alcance

Trabajo directo en `feature/valoraciones-explorador-guias`, por instrucción expresa. Se preservan los cambios previos de Alertas y Valoraciones. Sin cambios de estructura, migraciones, contratos clínicos, permisos o metodología del motor. Sin publicación remota.

**SOURCE OF TRUTH.** AGENTS raíz/app/resources/tests; contrato visual y tokens instalados; `docs/base-de-datos/README.md`, baseline/diccionario y decisión `DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md`; `docs/sistema-experto/README.md`, `INTERFAZ_MEDICA_RESULTADOS.md`, `CONSULTA_OPERATIVA_SIN_SIMULACIONES_2026_10_17.md`. Código y tests son evidencia de implementación. El inventario antiguo 71/23 del pedido y las notas históricas no autorizan retroceder las extensiones aprobadas. No se modifica ni se fija un nuevo inventario desde esta entrega.

**Conflicto resuelto:** el prompt describe contratos anteriores al backend actual. `lectura_clinica=[]` impide publicar resultados D-137 por esa vía; no invalida por sí mismo los contratos posteriores A–H. Se conservan sus capacidades y permisos existentes, sin ampliar alcance. Los identificadores técnicos heredados se mantienen donde afectan compatibilidad; los textos visibles dicen Sistema experto.

| Entrada real | Componente/consulta | Autorización y alcance |
|---|---|---|
| `admin.sistema-experto.index` `/admin/sistema-experto` | ConocimientoPanel → ConsultarConocimientoAdministrativo / ExplorarConocimientoExperto | SUPERADMINISTRADOR ACTIVO + auditoria.ver + Policy consultar; sin preview; consulta, laboratorio artificial segregado |
| `admin.sistema-experto.reglas` | GobiernoReglasPanel | Policy propia; Superadministración consulta, profesionales competentes gestionan según permisos actuales |
| `admin.sistema-experto.residentes` | ResidentesSistemaExperto | Medicina/Psicología activas, personal competente y viewAny; búsqueda y paginación backend |
| `admin.medico.residente.resultados-experto` `/admin/medico/residente/{residente}/evaluacion-cognitiva/resultados-experto` | ResultadosExpertoResidente / LecturaResultadosExperto / PreparacionFuentesCOGMEM | consultarResultados + permisos + view residente; sin inferencia inventada |
| `admin.enfermeria.pacientes.resultados-experto` `/admin/enfermeria/pacientes/{residente}/resultados-experto` | Mismo coordinador, composición Enfermería y ConsultaCognitivaEnfermeria | Cuenta activa + permisos + residente asignado en jornada; solo lectura experta |

**Riesgos iniciales:** la portada prioriza el laboratorio; la red es una lista de dominios sin lienzo; catálogo masivo de campos; tabla de residentes con ancho mínimo de 800 px; estado vacío de Enfermería incluye consejos estáticos sin procedencia. El grafo histórico existente utiliza datos preservados y se conserva separado del grafo de conocimiento.

## PLAN — flujo y referencia

**UX FLOW.** Superadministración: síntesis de conocimiento → versión → explorador/catálogo → nodo/registro → relaciones/fuente; laboratorio en sección independiente. Medicina: listado autorizado → identidad → disponibilidad → cinco dominios → evidencia/fundamento/historial. Enfermería: identidad y ubicación → estado de información → registros pertinentes → continuidad autorizada → detalle; regreso a Mis residentes.

**REFERENCE ANALYSIS.** Las siete PNG finales fueron abiertas individualmente. Las referencias desktop presentan una cabecera horizontal, shell lateral existente, contenido mayoritario y detalle lateral de aproximadamente un cuarto del espacio disponible; tarjetas redondeadas, divisores ligeros y jerarquía clara. En el explorador, lienzo de aproximadamente dos tercios e inspector de un tercio; filtros encima, catálogos debajo. Medicina prioriza identidad y resumen antes de evidencia. Enfermería agrupa información operacional en tres columnas y reduce densidad técnica. La referencia móvil concentra identidad, navegación breve y tarjetas en una columna con controles táctiles. Son proporciones aproximadas de imágenes ilustrativas, no mediciones CSS.

Se reutilizan shell, tipografías locales, `x-ui.page-header`, `section-card`, `empty-state`, `drawer-livewire`, rm-btn y tokens. Las referencias no autorizan tres paletas, fotografías, diagnósticos, porcentajes, indicadores de salud o tendencias inventados; estos espacios usan disponibilidad/documentación real. No se copia el banner fotográfico ni la decoración botánica. La selección de nodo comunica relaciones mediante flechas y texto, con una alternativa equivalente en lista.

Skills aplicadas: module-delivery/source-of-truth; ui-review → ux-flow-architect → reference-fidelity → design-system → data-visualization-ux → responsive-accessibility; system-testing, security-review y release-check al verificar. Dirección artística nueva y captura clínica no aplican: se adapta la referencia al sistema ya aprobado y no se crean formularios clínicos.

## Matriz de implementación

| Imagen final | Superficie/componente | Capacidad backend | Estado y prueba |
|---|---|---|---|
| 01 Maestro | Tokens, cards, navegación y controles compartidos | Estados/versiones reales | Adaptación al DS actual; build/QA |
| 02 Gobierno | Portada ConocimientoPanel | Inventario por versión, sin métricas clínicas | Síntesis y navegación; Livewire |
| 03 Explorador | Red semántica + inspector + catálogo | Snapshot real versionado, relaciones tipadas | Filtros, selección, pan/zoom y lista; unit/feature/E2E |
| 04 Residentes | Lista existente, contexto/selección | Residente admitido y Policy actual | Búsqueda/paginación; navegación/QA |
| 05 Medicina | Detalle y traza existentes | Lectura autorizada y traza conservada | Matriz documental y navegación contextual; regresión/QA |
| 06 Enfermería | Consulta operativa propia | ConsultaCognitivaEnfermeria, controles y continuidad autorizados | Solo lectura, fuentes y enlaces reales; permisos/QA |
| 07 Móvil | Enfermería y detalle progresivo | Mismos datos/Policies | Cinco anchos, teclado/reflow y capturas E2E |

## Brechas reservadas

UX GAP DETECTADO
Necesidad: representar confianza, diagnóstico, riesgo y series clínicas como las imágenes.
Contrato actual: los contratos de lectura no aportan esas medidas comparables para cualquier residente.
Alternativas: estados documentales y valores originales con procedencia; no convertir categorías a porcentajes.
Cambio de BDD requerido: NO para la presentación autorizada.
Requiere aprobación: SÍ para nueva interpretación/metodología clínica.

UX GAP DETECTADO
Necesidad: nueva evaluación, exportar o activar desde Superadministración.
Contrato actual: consultar no otorga escritura clínica ni existe acción de exportación aprobada para esta pantalla.
Alternativas: consulta y navegación a gobierno real bajo su Policy; laboratorio artificial aislado.
Cambio de BDD requerido: NO para la consulta.
Requiere aprobación: SÍ para nuevas acciones institucionales.

## IMPLEMENTACIÓN y VERIFICACIÓN

Portada de conocimiento con métricas de inventario que abren su catálogo, acceso directo al grafo y laboratorio separado. El explorador permite búsqueda, criterio/entorno de dos saltos, tipo/estado/relación, 36 nodos por página, pan/zoom, arrastre, selección y vecinos, inspector de entradas/salidas y fuentes por mapeo. El catálogo presenta 12 categorías y seis registros por página; el detalle usa drawer compartido con Escape y recuperación de foco. Medicina conserva sus contratos actuales, agrega disponibilidad de cinco dominios y navegación de lectura; la lista adapta su tabla sin ancho mínimo fijo. Enfermería distingue resumen, controles recientes con autor/fecha y continuidad autorizada; el detalle técnico se despliega bajo demanda. Se retiraron los consejos clínicos fijos del estado vacío.

**Mapa de transiciones:** hover/foco de tarjetas comunica acceso al catálogo; color/borde usa `--rm-duration-fast`. Cambio de sección revela su contenido mediante opacidad y `--rm-duration-normal`, sin desplazar controles ni bloquear peticiones. Selección de nodo resalta sus relaciones y actualiza inspector/contador; el texto de los nodos permanece legible. Arrastre/pan actualiza posición directamente, sin animación interpolada. El drawer usa el componente compartido; Escape cierra y el foco vuelve al activador. Movimiento reducido elimina revelado y desplazamiento de flecha. No se incorporó librería ni dependencia.

**Entorno comprobado:** PHP 8.4.13; Laravel 13.33.0; Livewire 4.4.6; Spatie Permission 7.4.2; Vite 8.3.1; Tailwind 3.4.19. El servidor de RememberMind es `http://127.0.0.1:56154`, confirmado por proceso PHP y GET `/login` HTTP 200 con título RememberMind. `8000` pertenece a SAVP. La apertura del panel se solicitó mediante la app; la navegación autenticada permanece NO VERIFICADA porque Computer Use devuelve `Local browser use is disabled in Work settings`. No se inyectó una sesión en la aplicación instalada.

Capturas `output/experto-antes-*` y `output/experto-despues-*` provienen del Laravel real con SQLite temporal nueva y actores/residentes sintéticos. No se usan datos de la base institucional ni servicios externos. El router E2E vive fuera de public, exige testing, nombre/ruta desechables y token efímero; nunca es ruta operativa.


### Pruebas ejecutadas

| Comando / evidencia | Resultado | Alcance |
|---|---|---|
| `php motor-experto/tests/independencia.php` | PASS: cinco casos | Motor sin Laravel/Eloquent |
| `php motor-experto/tests/orion_independencia.php` | Ejecutado sin error/salida | Independencia adicional del motor |
| PHPUnit directo con extensiones SQLite, filtro `SistemaExperto` | 756 pruebas, 5622 aserciones, una omitida | Omisión existente de aislamiento transaccional: requiere PostgreSQL; no acreditado aquí |
| Filtro `LaboratorioExpertoFrontendTest` | 24 pruebas, 256 aserciones | Consulta, simulaciones segregadas, permisos, catálogo, versiones e integridad |
| Filtro `InformeCognitivoTest|NavegacionSistemaExpertoTest|ProyectarRedConocimientoTest` | 40 pruebas, 324 aserciones | Lectura, asignación/revocación de Enfermería, exclusión técnica, navegación y proyección |
| Filtros de cambios dinámicos + proyección | 9 pruebas, 39 aserciones | Acceso desde resumen y rechazo de categorías arbitrarias; fuente por mapeo |
| Frontend completo secuencial | 255 pruebas: 251 pasan, 4 omitidas opt-in | `output/experto-frontend-final.log`; incluye todas las pruebas ordinarias disponibles |
| `npm run build` | PASS | Aviso de bundle grande preexistente; sin error de compilación |

Los primeros ensayos de frontend coincidieron con la regeneración del manifest y fallaron por archivo ausente/timeouts. Se repitieron después de build y en secuencia: sin fallos. El ensayo inicial de interfaz detectó colisión con `rm-expert-workspace`; se corrigió mediante un nombre de composición propio. Un filtro inicial de memoria no existía en el paquete G09 (que solo declara otros cuatro criterios); se retiró para no ocultar el grafo ni inventar ese nodo.

`FrontendArchitectureTest` presenta tres fallos fuera de esta edición: contador fijo de componentes 77 frente a 82 existentes; contrato antiguo del panel de Alertas; divisor de dashboard de Psicología. No se debilitaron ni se tocaron esos módulos. La regresión inicial de Medicina esperaba el título de la gráfica sustituida por la matriz documental; se actualizó la expectativa conservando los controles de resultado y alcance, y la suite de lectura pasó. Evidencia inicial: `output/experto-regresion.log`.

### Límites de aceptación

Validación clínica/institucional, publicación de nuevas inferencias, exportación nueva, nuevos permisos y estructura: no aplican a esta implementación y no fueron habilitados. PostgreSQL y sesión operativa autenticada: NO VERIFICADOS. El test E2E opt-in verifica el Laravel real con actores sintéticos; no acredita una sesión en la instalación real. Las referencias se adaptan al shell y tokens vigentes: no se reproduce la fotografía decorativa, las tres paletas ni los porcentajes/diagnósticos ilustrativos.


## Consolidación solicitada posteriormente

El propietario solicitó reunir estos cambios en `feature/apoyo-decision-informe-neurocognitivo`
y publicarlos para el equipo. El registro conjunto y las instrucciones de actualización
están en `docs/sistema/PUBLICACION_CONJUNTA_2026_10_22.md`. Esta solicitud posterior
no modifica los límites de QA visual ni supone validación clínica.
