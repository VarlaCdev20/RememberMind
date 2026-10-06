# Matriz de aceptación visual y funcional

## Cobertura

Viewports: 1440, 1280, 1024, 768 y 390 CSS px. Registrar tema y superficie/versionado revisado. Usar datos sintéticos, sin cambiar permisos ni crear registros clínicos reales para lograr cobertura.

Estados a evaluar: empty, loading, focus, filled, normal, warning, high, critical, validation-error, backend-error, success y disabled. Estado inexistente en ese flujo es N/A con motivo verificable; estado requerido que no pudo reproducirse es pendiente y no permite PASS. En un sidebar no se inventa un formulario crítico.

Dimensiones: REFERENCE FIDELITY, LAYOUT, TYPOGRAPHY, SPACING, COLORS, DEPTH, GLASS, MOTION, RESPONSIVE, ACCESSIBILITY, STATES, DATA, FORMS y CHARTS. Para alcance puntual revisar la superficie afectada y consumidores realmente impactados; explicitar exclusiones, sin afirmar aceptación global.

## Fallos a buscar

- Literales visibles: null, NaN, undefined, Infinity, [object Object].
- Input dentro de input, doble borde, input desproporcionado.
- Overflow/scroll horizontal, truncación esencial, grid roto.
- Radius/button inconsistente, geometría duplicada sin justificación.
- Critical débil, foco ausente/oculto, móvil inutilizable.
- Chart oculto pese a datos, PA con una sola serie, objetivo inventado.
- Empty incorrecto frente a datos/error, success anticipado, captura borrada al fallar.
- Glass ilegible, sombras pesadas, motion distractor o sin reduced-motion.
- Referencia aprobada reinterpretada sin una adaptación necesaria documentada.

Buscar literales en texto renderizado, etiquetas, tooltips, ejes, mensajes y cambios interactivos. Una coincidencia en código de control de errores no es por sí sola un defecto visible. Una búsqueda sin coincidencias no prueba que todos los estados estén cubiertos.

## Evidencia y dictamen

Matriz: criterio | viewport/tema | estado | método/evidencia | PASS/FAIL/N/A/pendiente | observación.

```text
PASS / FAIL
Superficie y alcance: ruta/componente, rol y versión revisada.
Referencia: imagen aprobada o no aplicable.
Cobertura: anchos/estados examinados y matriz.
Hallazgos observados: criterio, impacto, evidencia y ubicación.
Verificación pendiente: acceso/estado/evidencia que falta, si existe.
Pruebas/build: resultado y alcance real, si corresponden.
Implementación: sin edición durante QA.
Siguiente paso: especialidad responsable de cada corrección o aceptación.
```

FAIL puede significar defecto observado o verificación incompleta. Indicar cuál. Nunca declarar un defecto como observado si solo se sospecha por lectura de código. Tras correcciones realizadas fuera de QA, revisar criterios afectados y confirmar el dictamen.

Tests/build son evidencia complementaria, no sustituyen visual. Sin acceso a navegador o referencia requerida, conservar hallazgos verificables y emitir FAIL por cobertura incompleta, con lo que falta.
