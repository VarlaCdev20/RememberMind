# O.R.I.O.N. — G08: localizadores exactos de anexos normativos para Bolivia

> Documento de investigación importado selectivamente del worktree `pr15-ci-utf8`, HEAD `8085ec0e`, el 18/10/2026. Las referencias conservan su cotejo original; esta entrega no realiza aprobación normativa ni clínica. Véase [evidencia actual de Fase 01](FASE01_AUDITORIA_Y_EVIDENCIA_2026_10_18.md).

**Investigación documental fechada:** 17/10/2026.
**Relación con el proyecto:** [auditoría G08](G08_AUDITORIA_INSTRUMENTAL_EXPERIMENTAL.md), [fichas candidatas G08](G08_FICHAS_CANDIDATAS_PUBLICACIONES_Y_BRECHAS.md), [contrato DCL 03](https://docs.google.com/document/d/108-74YgGmw3JmA3Lba_bmwMcDeR39oVKIkamtyqJLgw/edit).
**Resultado:** se localizaron los **anexos bolivianos de 7 publicaciones para 6 subcomponentes G08**, con **12 localizadores tarea–medida–tabla**. Es un avance bibliográfico específico; **no** significa que exista edición clínica autorizada o que esté permitida su interpretación automática.

## Índice fuente / tarea / tabla boliviana verificada

| Componente G08 | Prueba / condición medida | Publicación original y tabla del anexo Bolivia | Qué evitar |
|---|---|---|---|
| ATE-S03 | Brief Test of Attention, BTA | [Rivera y cols. 2015, DOI 10.3233/NRE-151283](https://journals.sagepub.com/doi/10.3233/NRE-151283), **A2** | Confundir BTA con pruebas de atención sostenida o atribuir puntuación sin versión/protocolo auditivo |
| EJE-S01 | Stroop, **interferencia** | [Rivera y cols. 2015, DOI 10.3233/NRE-151281](https://journals.sagepub.com/doi/10.3233/NRE-151281), **A35** | Adoptar tablas Stroop Word/Color/Word-Color A2/A13/A24 para puntuar interferencia |
| EJE-S02 | Trail Making Test, **parte B** | [Arango-Lasprilla y cols. 2015, DOI 10.3233/NRE-151284](https://journals.sagepub.com/doi/10.3233/NRE-151284), **A14** | Usar TMT-A (su tabla boliviana es A2) como si acreditara flexibilidad |
| EJE-S02 | Modified Wisconsin, **número de categorías** | [Arango-Lasprilla y cols. 2015, DOI 10.3233/NRE-151280](https://journals.sagepub.com/doi/10.3233/NRE-151280), **A2** | Intercambiar con perseveraciones o errores totales |
| EJE-S02 | Modified Wisconsin, **errores perseverativos** | Misma publicación M-WCST, **A13** | Tratarlo como «número de categorías» |
| EJE-S02 | Modified Wisconsin, **errores totales** | Misma publicación M-WCST, **A24** | Inferir el mismo constructo a partir de métricas distintas |
| LEN-S01 | Boston Naming Test, **forma estándar** | [Olabarrieta-Landa y cols. 2015, DOI 10.3233/NRE-151278](https://journals.sagepub.com/doi/10.3233/NRE-151278), **A2** | Adoptar baremos de la forma estándar para BNT-15/BNT-2 |
| LEN-S02 | Fluidez verbal fonémica, **letra F** | [Olabarrieta-Landa y cols. 2015, DOI 10.3233/NRE-151279](https://journals.sagepub.com/doi/10.3233/NRE-151279), **A2** | Tratar letras y categorías como la misma prueba |
| LEN-S02 | Fluidez fonémica, **letra A** | Misma publicación, **A13** | Usar baremos de F o S para A |
| LEN-S02 | Fluidez fonémica, **letra S** | Misma publicación, **A24** | Cambiar letra o duración sin nuevo protocolo |
| LEN-S02 | Fluidez semántica, **animales** | Misma publicación, **A35** | Confundir animales con fluidez fonémica |
| VIS-S02 | Rey–Osterrieth, **fase de copia** | [Rivera y cols. 2015, DOI 10.3233/NRE-151285](https://journals.sagepub.com/doi/10.3233/NRE-151285), **A2** | Confundir con recuerdo inmediato, cuyo anexo Bolivia es A13 |

### Hallazgo metodológico central: los ajustes difieren entre publicaciones y tareas

Los anexos **no tienen un formato demográfico universal**. Por ejemplo, el artículo BTA presenta la **tabla A2 de Bolivia estratificada por edad y escolaridad**. El artículo TMT presenta **TMT-B A14 por edad y escolaridad**. Para el **BNT estándar de Bolivia**, la publicación presenta **A2 por escolaridad**, y la sección de resultados no identifica efecto lineal de edad en el modelo boliviano. Para **Stroop Interference Bolivia**, la publicación tiene la **tabla A35 de percentiles respecto al resultado bruto, sin estratificación por edad/escolaridad en dicha tabla**. El propio artículo distingue Word, Color y Word-Color de Interference. No se debe aplicar un método demográfico ajeno por analogía.

En las tablas bolivianas de **M-WCST** la publicación muestra por separado categorías (A2), perseveraciones (A13) y errores totales (A24), con estratificaciones etarias. La medida seleccionada debe concordar exactamente con la tarea de la aplicación. La disponibilidad bibliográfica del M-WCST sigue **sin acreditar EJE-S05 razonamiento general**.

La publicación de fluidez verbal incluye en los apéndices Bolivia A2 para letra F, A13 para A, A24 para S y A35 para categoría animales. No se ha decidido cuál de ellas constituye un perfil suficiente para LEN-S02 en RememberMind. Rey–Osterrieth separa copia de recuerdo; para VIS-S02 se investigará la fase de copia A2.

**Advertencia sobre edad avanzada:** la presencia de columnas `>77` en varios anexos identifica la forma de la **tabla publicada**, pero **no demuestra** que exista una muestra boliviana institucionalizada representativa de todos los mayores de esa edad, ni elimina la necesidad de revisar los criterios de selección del artículo, los análisis de extrapolación y la adecuación individual. No se autoriza calcular percentiles para residentes con esta revisión.

## Resultado técnico y controles de software

Se incorporó `LocalizadoresAnexosBoliviaG08Investigacion.php`, que almacena únicamente componente/tarea/instrumento/métrica/DOI/PMID/número de tabla/ajuste publicado, **sin importar puntuaciones, ecuaciones o tablas protegidas**. `contrastar()` revisa la identidad exacta del localizador para investigación, pero entrega siempre `perfil_g08_autorizado=false`, `puede_interpretar_puntuacion=false` y `puede_emitir_A_P=false`.

```bash
php motor-experto/tests/g08_anexos_bolivia.php
```

La prueba cubre los 12 pares de tabla, 7 publicaciones distintas, 6 componentes, veto de tablas de otro país o prueba, BNT-15, ROCF recuerdo, TMT-A y EJE-S05 (no autorizado por pertenencia al constructo). No se añaden reglas diagnósticas, cortes ni puntajes a la BDD institucional.

## Pendientes que no pueden resolverse de forma automática

1. Identificar **edición, administración y licencia exactas** de cada prueba disponible en la institución.
2. Comprobar **población de la muestra normativa boliviana** contra los criterios de exclusión y situación geriátrica concreta, y decidir con fundamento si se puede usar esa norma para cada aplicación.
3. Validar **captura operativa de ítems, métricas, modalidad de puntuación, fechas y contexto** con las cinco tablas instrumentales de RememberMind; hoy los perfiles operativos son simulados.
4. Elaborar y revisar **algoritmo interpretativo** por tarea y justificar, de acuerdo con fuentes autorizadas, cuándo un hallazgo aporta `A`, cuándo una prueba da evidencia positiva de `P` y cuándo se debe abstener.
5. Someter las fichas G08 a revisión profesional, derechos de uso y control G12/G13 antes de cualquier activación asistencial.

**Conclusión científica-operativa:** 12 localizadores de tablas documentados; **0/15 perfiles G08 clínicamente admitidos**. No se ha adoptado ningún baremo.
