# Ficha de fidelidad

```text
REFERENCE ANALYSIS
Referencia: archivo/imagen y alcance de aprobación.
Layout: grid, columnas, orden y regiones.
Proporciones: anchos, alturas y relación entre bloques.
Surface hierarchy: fondos, cards, paneles y overlays.
Typography: familias, jerarquía, tamaños, pesos y densidad.
Components: botones, inputs, cards, badges e iconos.
Depth: sombras, relieve, bordes y niveles de elevación.
Glass: ubicación, transparencia, blur y legibilidad.
Motion implied: estados sugeridos; marcar inferencia si solo hay imagen estática.
Elements to replicate exactly: identidad y estructura aprobadas.
Elements that must adapt to RememberMind: contenido, contratos, permisos,
reglas clínicas, datos, responsive y accesibilidad, con motivo por cambio.
```

Añadir spacing/alineación/radius/gradients donde corresponda. Una imagen estática no prueba una animación: documentar la hipótesis y usar el contrato funcional de motion.

## Comparación

Comparar captura renderizada y referencia a dimensiones equivalentes. Revisar de mayor a menor: composición → proporciones → tipografía → geometría → profundidad/glass → detalle. Anotar referencia, resultado actual, diferencia y motivo; no inventar un porcentaje de fidelidad ni una tolerancia numérica sin método.

En otros anchos conservar prioridades y lenguaje visual, documentando reflow. La referencia no autoriza conservar contraste ilegible ni ocultar acciones detrás de sticky controls. Si una fuente/asset no está disponible, describir la limitación técnica y usar la alternativa local más cercana; no instalar servicios ni enviar datos clínicos.

Si no existe referencia aprobada, esta etapa es no aplicable. Trabajar desde el contrato visual y la dirección artística, sin producir un análisis ficticio.
