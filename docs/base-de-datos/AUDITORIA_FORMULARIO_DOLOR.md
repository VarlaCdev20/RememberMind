# Auditoría: Nuevo registro de Enfermería / Valoración de dolor

Fuentes: BDD Operativa V2.1 (`REMEMBERMIND_BDD_70_TABLAS.md`, tabla 27),
`2026_09_18_001029_create_valoraciones_dolor_table.php`, modelo
`ValoracionDolor`, Livewire `MisPacientes`, servicio
`CuidadosEnfermeriaService` y pruebas de `MisPacientesRedisenadaTest`.

La tabla real es `valoraciones_dolor`; no se modificó su estructura.

| Campo | Situación real | Captura en el popup |
| --- | --- | --- |
| `fecha_hora` | `datetime` | Fecha y hora de valoración, prellenadas según zona horaria Laravel. |
| `intensidad` | `unsignedTinyInteger`, escala de aplicación 0–10 | Radio group EVA inicial; selección explícita. |
| `ubicacion` | `string(120)` | Texto libre; no existe catálogo ni mapa corporal. |
| `duracion` | `string(80)` | Valor numérico positivo y unidad textual por separado en pantalla; se guardan juntos en la columna existente. |
| `desencadenante` | `text` | Texto opcional, con trim. |
| `intervencion` | `text` | Texto opcional, con trim. |
| `tipo_dolor` | `string(60)` | No se ofrece porque no existe enum o catálogo validado. |
| `respuesta` | `text` | No equivale a EVA posterior ni tiene hora asociada; queda sin capturar en esta valoración inicial. |
| `cod_residente` | FK | Se deriva del residente autorizado en el popup y se revalida en servidor. |
| `cod_personal` | FK | Se resuelve desde el usuario autenticado y personal activo; no se recibe del cliente. |
| `cod_atencion` | FK opcional | Queda nulo; el popup no abre una atención. |
| `estado` | `string(20)` | Se guarda `VIGENTE`. |

La tabla no contiene campos separados para inicio del dolor, EVA posterior,
hora de reevaluación ni observaciones generales. Son **propuestos** y no se
guardan. Por ello no aplica la regla de comparar hora de reevaluación con hora
de valoración; enviar esos campos a este flujo se rechaza. La columna
`tipo_dolor` existe, pero no hay catálogo para validar opciones.

Validaciones del flujo: `intensidad` obligatoria, entera y entre 0 y 10;
fecha/hora con formato estricto y no futura; ubicación hasta 120 caracteres;
duración numérica mayor que cero con unidad separada y cadena final hasta 80
caracteres; textos `desencadenante` e `intervencion` opcionales, recortados de
espacios y sin truncamiento. Estas dos columnas son `text` y no tienen un
límite de caracteres definido por la BDD. El servicio comprueba rol
ENFERMEROS, permiso `valoraciones_dolor.crear`, turno, asignación del
residente y personal activo antes de persistir.
