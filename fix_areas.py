import re
import os

file_path = r"c:\laragon\www\RememberMind_F1\resources\views\livewire\admin\areas-institucionales\areas-institucionales-panel.blade.php"

with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

# 1. Root wrapper: bg-fondo-panel -> bg-transparent
content = re.sub(
    r'<div class="p-6 md:p-8 space-y-8 relative min-h-screen bg-fondo-panel',
    r'<div class="p-6 md:p-8 space-y-8 relative min-h-screen bg-transparent',
    content
)

# 2. Form Inputs/Selects/Textareas
content = re.sub(
    r'class="w-full rounded-xl border-borde-suave bg-fondo-card/80 py-2\.5 pl-10 pr-4 text-xs font-semibold text-titulo placeholder-\[#967B66\]/60 shadow-sm transition focus:border-borde-focus focus:ring-1 focus:ring-\[#E27D60\]"',
    r'class="rm-input w-full pl-10"',
    content
)
content = re.sub(
    r'class="w-full rounded-xl border-borde-suave bg-fondo-card py-2\.5 text-xs font-semibold text-titulo shadow-sm focus:border-borde-focus focus:ring-1 focus:ring-\[#E27D60\]"',
    r'class="rm-select w-full"',
    content
)
# other inputs
content = re.sub(
    r'class="w-full rounded-xl border-borde-suave py-2 text-xs font-semibold text-titulo shadow-sm focus:border-borde-focus focus:ring-\[#E27D60\]"',
    r'class="rm-input w-full"',
    content
)
content = re.sub(
    r'class="w-full rounded-xl border-borde-suave py-2 text-xs font-semibold text-titulo shadow-sm focus:border-borde-focus"',
    r'class="rm-input w-full"',
    content
)
content = re.sub(
    r'class="w-full rounded-xl border-borde-suave bg-fondo-panel py-2 text-xs font-semibold text-apoyo cursor-not-allowed"',
    r'class="rm-select w-full opacity-60 cursor-not-allowed"',
    content
)

# textareas
content = re.sub(
    r'class="w-full rounded-xl border-borde-suave py-2 text-xs font-semibold text-titulo shadow-sm focus:border-borde-focus focus:ring-\[#E27D60\]">',
    r'class="rm-textarea w-full">',
    content
)
content = re.sub(
    r'class="w-full rounded-xl border-borde-suave py-2 text-xs font-semibold text-titulo shadow-sm focus:border-borde-focus">',
    r'class="rm-textarea w-full">',
    content
)
content = re.sub(
    r'<input type="text"([^>]+)class="rm-textarea w-full">',
    r'<input type="text"\1class="rm-input w-full">',
    content
)
content = re.sub(
    r'<input type="number"([^>]+)class="rm-textarea w-full">',
    r'<input type="number"\1class="rm-input w-full">',
    content
)
content = re.sub(
    r'<select wire:model="([^"]+)"([^>]+)class="rm-textarea w-full">',
    r'<select wire:model="\1"\2class="rm-select w-full">',
    content
)

# 3. Slide-over / Modal backgrounds
content = re.sub(
    r'<div class="absolute inset-0 bg-fondo-panel backdrop-blur-sm transition-opacity" wire:click="cerrarFicha"></div>',
    r'<div class="rm-modal-overlay" wire:click="cerrarFicha"></div>',
    content
)
content = re.sub(
    r'<div class="fixed inset-0 bg-fondo-panel backdrop-blur-sm transition-opacity" wire:click="cerrarFormulario"></div>',
    r'<div class="rm-modal-overlay" wire:click="cerrarFormulario"></div>',
    content
)
content = re.sub(
    r'<div class="fixed inset-0 bg-fondo-panel backdrop-blur-sm transition-opacity no-print" wire:click="cerrarReportes"></div>',
    r'<div class="rm-modal-overlay no-print" wire:click="cerrarReportes"></div>',
    content
)

# 4. Slide-over panel container
content = re.sub(
    r'bg-fondo-panel shadow-\[0_20px_60px_rgba\(47,62,92,0\.25\)\]',
    r'rm-modal-panel',
    content
)
# modal container
content = re.sub(
    r'bg-fondo-card p-6 shadow-\[0_20px_50px_rgba\(47,62,92,0\.2\)\] border border-borde-suave',
    r'rm-modal-panel p-6',
    content
)

# 5. Form inner body (transparent)
content = re.sub(
    r'<div class="flex-1 overflow-y-auto bg-fondo-panel p-6 space-y-6"',
    r'<div class="flex-1 overflow-y-auto bg-transparent p-6 space-y-6"',
    content
)

# 6. Reemplazos generales no semánticos -> semánticos
content = re.sub(r'bg-slate-100', r'bg-[var(--surface-soft)]', content)

# bg-fondo-card/70 backdrop-blur-md -> rm-card-soft o rm-surface-glass
content = re.sub(
    r'bg-fondo-card/70 backdrop-blur-md p-4 shadow-\[0_8px_30px_rgba\(47,62,92,0\.04\)\] border border-borde-suave',
    r'rm-surface-glass p-4',
    content
)

# metric cards -> rm-card
content = re.sub(
    r'rounded-2xl border-none bg-fondo-card p-4 shadow-\[0_12px_24px_rgba\(47,62,92,0\.05\)\] transition duration-300 hover:shadow-lg',
    r'rm-card p-4',
    content
)

# Area Card background: bg-fondo-panel -> rm-card
content = re.sub(
    r'bg-fondo-panel shadow-\[0_14px_30px_rgba\(47,62,92,0\.06\)\] hover:shadow-\[0_20px_40px_rgba\(47,62,92,0\.12\)\] overflow-hidden border-2 border-transparent hover:border-borde-suave',
    r'rm-card overflow-hidden border-2 border-transparent hover:border-borde-suave',
    content
)
content = re.sub(
    r'opacity-70 grayscale bg-fondo-panel',
    r'opacity-70 grayscale bg-[var(--surface-soft)]',
    content
)

# Reemplazar botones
content = re.sub(
    r'rounded-full bg-boton-acento px-6 py-2\.5 text-xs font-bold text-inverso shadow-lg shadow-\[#E27D60\]/20 transition-all duration-300 hover:-translate-y-0\.5 hover:shadow-xl active:translate-y-0 active:scale-95',
    r'rm-btn-primary',
    content
)

# Boton secundario/reporte
content = re.sub(
    r'rounded-full border-2 border-borde-suave bg-fondo-card px-5 py-2\.5 text-xs font-bold text-meta shadow-md transition-all duration-300 hover:bg-fondo-panel active:scale-95',
    r'rm-btn-secondary',
    content
)


# Re-save
with open(file_path, "w", encoding="utf-8") as f:
    f.write(content)

print("Fixes applied successfully to areas-institucionales-panel.blade.php")
