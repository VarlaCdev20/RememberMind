const print = () => window.print();
document.querySelector('[data-report-print]')?.addEventListener('click', print);
if (document.body.dataset.printRequested === 'true') {
    document.fonts.ready.then(() => requestAnimationFrame(print));
}
