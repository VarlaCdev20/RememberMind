# Petersen institucional: contrato de acreditación propuesto

Fecha: 19/10/2026. Estado: **ADOPTADO por la propietaria: «Sí, adoptar E–H»**.
La decisión adopta el contenido E–H; no declara terminada su implementación.
No modifica la BDD, permisos, catálogos ni historias. Complementaría A–D;
no atribuye una aprobación anterior a este alcance.

## Problema que resuelve

El laboratorio ya clasifica cuatro patrones desde estados sintéticos. El paquete
institucional obtiene observaciones y hallazgos instrumentales, pero no tiene
una regla adoptada para acreditar los cinco requisitos y los cinco dominios de
Petersen. Quitar las guardas sintéticas no resuelve esa diferencia.

Se propone `PETERSEN-INSTITUCIONAL-1`: clasificación orientativa a partir de
evidencia original interpretada, conservando su alcance, contexto y procedencia.
No exige firmas profesionales previas para desarrollar o ejecutar pruebas. La
valoración original que aporta un juicio clínico y la revisión posterior del
resultado son actos distintos; el sistema no puede fabricar el primero.

## E. Admisibilidad común

Cada soporte identifica residente, registro original y tipo de fuente, autor,
fecha clínica, estado válido, localizador cuando exista y episodio explícito.
El corte es único. Un registro futuro, anulado, ajeno o incompatible no contribuye.
La fecha de transcripción no reemplaza la fecha clínica. Las rectificaciones
enlazadas conservan el antecedente y se explican; no se elige por mayoría.

Un mismo soporte o episodio no cuenta como corroboración independiente de sí
mismo. Puede contextualizar varios apartados, pero se mantiene esa dependencia.
Sin episodio o independencia acreditable no se inventan episodios por distancia
temporal ni por número de formularios. Los soportes contradictorios se conservan.

## F. Acreditación y límites

| Elemento | Condición positiva propuesta | Condición insuficiente |
|---|---|---|
| C01, cambio corroborado | Valoración original explícita de cambio respecto al funcionamiento previo, con historia/contexto y corroboración identificable independiente. | Booleano aislado de cambio, repetición de observaciones, comparación de instrumentos incompatibles. |
| C02, alteración objetiva | Al menos un dominio A con interpretación original de alcance de dominio, medida objetiva y método aplicable identificados; coherencia con los cinco dominios. Se deriva del perfil, no de un campo «DCL esperado». | z aislado, observación nominal, D-131 o una conclusión G09. |
| C03, independencia instrumental relativa | Valoración original integrada de IADL que identifica actividades pertinentes, oportunidad, ayuda, causa, nivel previo y contexto; conclusión explícita sobre independencia relativa. | Barthel/BADL, falta de oportunidad en la residencia, ausencia de ayuda registrada o un único código de causa. |
| C04, evaluación diferencial | Revisión original explícita, fechada y atribuida, con factores pertinentes considerados y conclusión sobre impedimentos para interpretar el perfil actual. | No tener diagnósticos, notas vacías o una firma general posterior de la evaluación experta. |
| C05, calidad/interpretabilidad | Valoración original de suficiencia del conjunto, con condiciones y exclusiones por aplicación; los cinco dominios requeridos tienen evidencia interpretable de alcance suficiente. | Estado COMPLETA de una aplicación o cobertura porcentual del sistema. |
| Dominio A | Interpretación original explícita de alteración objetiva del dominio, sustentada en instrumento, edición/idioma, mediciones, método y contexto aplicables. | Un hallazgo de subcomponente, señal observacional o sustitución de normas entre poblaciones. |
| Dominio P | Interpretación original positiva de rendimiento esperado del dominio, con alcance y soportes equivalentes a los exigidos para A. | Ausencia de dificultad, regla no activada, FALSE, NULL o falta de evaluación. |

La propuesta no selecciona umbrales clínicos universales ni convierte los métodos
de referencia en un protocolo administrado. La interpretación original de
dominio es una entrada clínica rastreable, no una clasificación DCL precargada.
Si solo hay observaciones por componente, se muestran íntegramente y la
clasificación queda pendiente de los requisitos originales que falten.

Estados de requisito: acreditado, no acreditado con soporte explícito, pendiente
o contradictorio. Estados de dominio: A/P, U sin información, I no interpretable,
X con interpretaciones competidoras. Una tarea inválida se excluye con su motivo;
no invalida por sí sola todo el perfil.

## G. Inferencia y explicación

Se exige C01–C05 acreditados y cinco dominios A/P coherentes. Memoria A sin otro A
produce patrón amnésico de dominio único; con otro A, amnésico multidominio.
Memoria P con un único dominio no mnésico A produce no amnésico de dominio único;
con más de uno, no amnésico multidominio. Todos P no producen DCL.

Se conserva la precedencia metodológica existente: contradicción, impedimento
diferencial, información insuficiente/no interpretable, incoherencia C02,
requisito no acreditado y, finalmente, distribución del patrón. Se muestran
todas las causas secundarias y soportes; nunca solo el motivo principal.

## H. Operación, revisión y aceptación

Se conservan las competencias aprobadas de Medicina y Psicología y sus cadenas
posteriores de revisión separadas. No se amplían permisos por esta decisión.
La captura de juicios originales debe usar un contrato estructurado específico;
su representación física se propondrá aparte si el esquema vigente no la admite.
No se persiste en observaciones libres, JSON/EAV ni resultados esperados.

Pruebas requeridas: cuatro patrones desde soportes independientes; todos P;
U/I/X; criterios negativos explícitos; C02 incoherente; IADL sin oportunidad;
fuentes fuera de corte, de otro residente o anuladas; reutilización de soportes;
rectificación e historia; autorización; guardado y reproducción íntegros.

La aprobación de este contenido permite implementar su flujo positivo; no
certifica desempeño clínico, licencia de instrumentos ni disponibilidad de
mediciones históricas. La fase 5 no se declarará cerrada con un preparador que
solo devuelve abstenciones.

## Fuentes contrastadas

- Fuente local DCL 03, §§2–4 y 7–8: definiciones y prohibiciones de D-131→A,
  G09→A/P y BADL→C03; cotejo estático independiente del 19/10/2026.
- [Contrato A–D adoptado](PROPUESTA_CONTRATO_INSTITUCIONAL_FASE_4_2026_10_19.md).
- [Guía DETeCD-ADRD de atención primaria, 2024/2025](https://pmc.ncbi.nlm.nih.gov/articles/PMC12173843/):
  integración de historia fiable, evaluación cognitiva y contexto; un score
  aislado no constituye la formulación clínica.
- [DETeCD-ADRD, instrumentos clínicos, 2024/2025](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772712/):
  selección e interpretación contextual de herramientas cognitivas y funcionales.
- [DETeCD-ADRD de atención especializada, 2024/2025](https://pmc.ncbi.nlm.nih.gov/articles/PMC11772716/):
  evaluación neuropsicológica de memoria, atención, función ejecutiva, lenguaje
  y función visuoespacial cuando corresponde.

Las fuentes sustentan el análisis; E–H son una propuesta de decisión institucional,
no recomendaciones publicadas literalmente ni normas clínicas universales.
