# Unificación del frontend

## Criterio aplicado

- Los formularios operativos usan modales flotantes con `x-ui.modal-livewire` y superficies definidas por tokens.
- La información secundaria y de consulta usa paneles laterales con `x-ui.drawer-livewire`.
- Las tablas reutilizan `rm-table`, `rm-table-header` y `rm-table-row`.
- Los campos dentro de modales y paneles laterales comparten borde, fondo, foco, tipografía y espaciado.
- Las pantallas del residente comparten `x-residentes.navegacion-ficha`, con estado activo y acceso continuo entre expediente, atención de Enfermería, clínica, medicamentos, VGI, documentos y red de apoyo.

## Pantallas conectadas al diseño unificado

- Expediente administrativo del residente.
- Ficha 360° y atención de Enfermería.
- Ficha clínica integrada de Medicina.
- Resumen clínico, ficha médica y signos vitales.
- Prescripción y administración de medicamentos.
- Valoración funcional y evaluaciones geriátricas.
- Atenciones, notas/evolución y documentos.
- Detalle, ubicación y gráficos de alertas mediante panel lateral.

## Compatibilidad

Las rutas actuales permanecen disponibles para no romper favoritos, enlaces internos ni flujos con permisos distintos. La navegación compartida evita que cada módulo se comporte como un expediente aislado.

## Vistas extra

`Administración > Vistas extra` muestra solamente a Superadministración las pantallas duplicadas, provisionales u ocultas que requieren decisión humana. Ninguna se elimina automáticamente.

## Correcciones de navegación

- Las rutas generales de Salud abren su pestaña correcta.
- Los enlaces de documentos e historial del expediente ya apuntan a módulos reales.
- El cambio de estado del personal usa `usuarios.cambiar_estado` tanto en la interfaz como en el servidor.
