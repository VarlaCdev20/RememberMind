# Dependencias frontend — revisión 2026-10-11

Lock verificado mediante npm ci; se mantiene Tailwind 3.4.19 y el diseño actual.

- concurrently 9.2.5 incorpora shell-quote 1.12.0.
- source-map-js 1.2.2 actualizado dentro del rango de PostCSS.
- Override explícito postcss-selector-parser 7.1.6: no existe parche de la rama 6. La rama 7 cruza los rangos originales de Tailwind/nesting/typography. Para este repositorio se verificaron 147 tests y build; los dos CSS generados son idénticos byte por byte respecto al build anterior.
- tailwindcss-animated es exclusivamente un plugin de tailwind.config.js y queda en devDependencies. No es una biblioteca utilizada en peticiones del servidor.

Auditoría completa: 5 paquetes afectados de desarrollo, 1 advisory único pendiente: GHSA-vfj7-8cjw-p6xm, braces <=3.0.3. Auditoría omit=dev: cero vulnerabilidades. Este último resultado no elimina la deuda del entorno de build.

braces 3.0.3 sigue siendo la última versión publicada al revisar. El reemplazo propuesto por npm exige Tailwind 4, una migración mayor fuera de este corte. No usar npm audit fix --force ni inventar una versión parcheada. Los patrones de Tailwind son literales locales; no aceptar patrones/CSS/configuración de usuarios o fuentes no confiables en el pipeline. Ejecutar builds sólo sobre código revisado y en entornos aislados. Esta restricción reduce exposición; no repara la biblioteca ni acredita ausencia de riesgo.

La auditoría completa debe conservarse visible como evidencia de CI. El gate de dependencias de ejecución permanece estricto y toda alerta nueva debe revisarse. La migración de Tailwind o un parche upstream siguen pendientes de implementación y validación.

Fuentes primarias:
- https://github.com/advisories/GHSA-vfj7-8cjw-p6xm
- https://github.com/advisories/GHSA-rj75-hqrm-r3gf
- https://github.com/postcss/postcss-selector-parser/releases/tag/v7.0.0
- https://github.com/open-cli-tools/concurrently/releases/tag/v9.2.5
