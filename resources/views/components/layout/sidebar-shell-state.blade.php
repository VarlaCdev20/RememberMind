sidebarOpen: false,
sidebarCollapsed: false,
sidebarRange: null,
sidebarTemporaryExpanded: false,
initSidebar() {
    document.body.removeAttribute('data-rm-sidebar-ready');
    this.syncSidebarViewport();
    this.$nextTick(() => requestAnimationFrame(() => requestAnimationFrame(() => document.body.setAttribute('data-rm-sidebar-ready', ''))));
    this.$watch('sidebarOpen', () => this.syncSidebarScrollLock());
},
syncSidebarViewport() {
    const range = window.innerWidth >= 1280 ? 'desktop' : (window.innerWidth >= 1024 ? 'laptop' : 'drawer');
    if (range === this.sidebarRange) return;
    this.sidebarRange = range;
    this.sidebarOpen = false;
    this.sidebarTemporaryExpanded = false;
    this.sidebarCollapsed = range === 'laptop';
    if (range === 'desktop') {
        try { this.sidebarCollapsed = localStorage.getItem('remembermind-sidebar-collapsed') === 'true'; }
        catch (error) { this.sidebarCollapsed = false; }
    }
    this.syncSidebarScrollLock();
},
toggleSidebarCollapse() {
    if (this.sidebarRange === 'drawer') { this.sidebarOpen = !this.sidebarOpen; return; }
    this.sidebarCollapsed = !this.sidebarCollapsed;
    if (this.sidebarRange === 'desktop') {
        try { localStorage.setItem('remembermind-sidebar-collapsed', String(this.sidebarCollapsed)); }
        catch (error) { this.sidebarTemporaryExpanded = !this.sidebarCollapsed; }
    } else this.sidebarTemporaryExpanded = !this.sidebarCollapsed;
},
syncSidebarScrollLock() {
    document.documentElement.classList.toggle('rm-sidebar-scroll-locked', this.sidebarOpen && this.sidebarRange === 'drawer');
},
closeSidebarForNavigation() {
    this.sidebarOpen = false;
    if (this.sidebarRange === 'laptop') { this.sidebarCollapsed = true; this.sidebarTemporaryExpanded = false; }
    this.syncSidebarScrollLock();
},
