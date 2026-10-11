/** Shared inspection for CSS charts and their legends; values remain server-owned. */
export function rmInstallChartInteractions() {
    const layouts = '.rm-dashboard-data-panel__donut-layout, .rm-admin-dashboard__donut-layout, .rm-nursing-donut-layout';
    const rows = '.rm-dashboard-data-panel__legend > li, .rm-admin-dashboard__donut-legend > *, .rm-nursing-donut-legend > li';
    const bars = '.rm-admin-dashboard__bar-group, .rm-dashboard-data-panel__bars > li';
    const initialized = new WeakSet();
    let current = null;
    const tooltip = document.createElement('div');
    tooltip.className = 'rm-chart-inspect-tooltip';
    tooltip.id = 'rm-chart-inspect-tooltip';
    tooltip.setAttribute('role', 'tooltip');
    tooltip.hidden = true;
    document.body.append(tooltip);

    function clear() {
        if (current) {
            current.host.querySelectorAll('.is-chart-inspected').forEach(el => el.classList.remove('is-chart-inspected'));
            current.host.querySelector('.rm-chart-inspect-ring')?.remove();
            current.target.removeAttribute('aria-describedby');
            if (current.chart?.canvas) {
                current.chart.setActiveElements([]);
                current.chart.tooltip?.setActiveElements([], { x: 0, y: 0 });
                current.chart.update('active');
            }
        }
        current = null;
        tooltip.hidden = true;
    }

    function show(target, text, host, event) {
        target.classList.add('is-chart-inspected');
        target.setAttribute('aria-describedby', tooltip.id);
        tooltip.textContent = text;
        tooltip.hidden = false;
        const rect = target.getBoundingClientRect();
        const x = event?.clientX ?? rect.left + rect.width / 2;
        const y = event?.clientY ?? rect.top;
        tooltip.style.left = `${Math.max(8, Math.min(x + 12, innerWidth - tooltip.offsetWidth - 8))}px`;
        tooltip.style.top = `${Math.max(8, Math.min(y + 16, innerHeight - tooltip.offsetHeight - 8))}px`;
        current = { target, host };
    }

    function inspect(row, event) {
        clear();
        const host = row.closest(layouts);
        if (!host) {
            const label = row.querySelector('strong, span')?.textContent.trim();
            const value = row.querySelector('b')?.textContent.trim();
            show(row, row.dataset.chartDetail || (value ? `${label}: ${value}` : row.textContent.trim()), row.parentElement, event);
            return;
        }
        const entries = [...host.querySelectorAll(rows)];
        const values = entries.map(el => Number(el.querySelector('strong')?.textContent.trim()) || 0);
        const total = values.reduce((a, b) => a + b, 0);
        const index = entries.indexOf(row);
        const label = row.querySelector('span')?.textContent.trim() || '';
        const percent = total ? values[index] * 100 / total : 0;
        show(row, `${label}: ${values[index]} · ${percent.toLocaleString('es', { maximumFractionDigits: 1 })}%`, host, event);
        const canvas = host.querySelector('canvas');
        if (canvas) {
            const chart = window.Chart?.getChart(canvas);
            if (chart) {
                chart.setActiveElements([{ datasetIndex: 0, index }]);
                chart.update('active');
                current.chart = chart;
            }
            return;
        }
        const donut = host.querySelector('.rm-dashboard-data-panel__donut, .rm-admin-dashboard__donut');
        if (!donut || !total || !values[index]) return;
        const start = values.slice(0, index).reduce((a, b) => a + b, 0) / total;
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 100 100');
        svg.setAttribute('aria-hidden', 'true');
        svg.classList.add('rm-chart-inspect-ring');
        const circle = document.createElementNS(svg.namespaceURI, 'circle');
        const color = getComputedStyle(row.querySelector('span'), '::before').backgroundColor;
        for (const [key, value] of Object.entries({ cx: 50, cy: 50, r: 39, fill: 'none', stroke: color, 'stroke-width': 20, pathLength: 100, 'stroke-dasharray': `${percent} ${100 - percent}`, transform: `rotate(${start * 360 - 90} 50 50)` })) circle.setAttribute(key, value);
        svg.append(circle);
        donut.append(svg);
    }

    const segments = '.rm-dashboard-data-panel__segments > span';
    const targets = `${rows}, ${bars}`;
    function scan(root = document) {
        const matches = selector => [
            ...(root.matches?.(selector) ? [root] : []),
            ...root.querySelectorAll(selector),
        ];
        matches(segments).forEach((segment) => {
            if (initialized.has(segment)) return;
            initialized.add(segment);
            const activate = event => {
                const index = [...segment.parentElement.children].indexOf(segment);
                const row = segment.closest('section')?.querySelectorAll(rows)[index];
                if (row) inspect(row, event);
            };
            segment.addEventListener('pointerenter', activate);
            segment.addEventListener('pointerleave', clear);
        });
        matches(targets).forEach(el => {
            if (initialized.has(el)) return;
            initialized.add(el);
            if (!el.matches('a, button')) el.tabIndex = 0;
            if (el.title) { el.dataset.chartDetail = el.title; el.removeAttribute('title'); }
            el.addEventListener('pointerenter', event => inspect(el, event));
            el.addEventListener('focus', () => inspect(el));
            el.addEventListener('pointerleave', clear);
            el.addEventListener('blur', clear);
            el.addEventListener('click', event => { if (!el.matches('a')) inspect(el, event); });
        });
    }
    // Hit-test the existing conic gradient: zero values never acquire a segment.
    document.addEventListener('pointermove', event => {
        const donut = event.target.closest?.('.rm-dashboard-data-panel__donut, .rm-admin-dashboard__donut');
        if (!donut) return;
        const rect = donut.getBoundingClientRect();
        const x = event.clientX - rect.left - rect.width / 2;
        const y = event.clientY - rect.top - rect.height / 2;
        const radius = Math.hypot(x, y) / (rect.width / 2);
        if (radius < .55 || radius > 1) { clear(); return; }
        const entries = [...donut.closest(layouts).querySelectorAll(rows)];
        const values = entries.map(el => Number(el.querySelector('strong')?.textContent) || 0);
        const total = values.reduce((a, b) => a + b, 0);
        const position = ((Math.atan2(y, x) / (2 * Math.PI) + 1.25) % 1) * total;
        let sum = 0;
        const index = values.findIndex(value => { sum += value; return position < sum; });
        if (index >= 0 && current?.target !== entries[index]) inspect(entries[index], event);
    });
    document.addEventListener('pointerout', event => {
        if (event.target.closest?.('.rm-dashboard-data-panel__donut, .rm-admin-dashboard__donut') && !event.relatedTarget?.closest?.(layouts)) clear();
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') clear(); });
    document.addEventListener('livewire:navigating', clear);
    document.addEventListener('scroll', clear, true);
    scan();
    new MutationObserver(records => {
        for (const record of records) {
            for (const node of record.addedNodes) {
                if (node.nodeType === Node.ELEMENT_NODE && node.namespaceURI === 'http://www.w3.org/1999/xhtml') scan(node);
            }
        }
    }).observe(document.body, { childList: true, subtree: true });
}
