<x-app-layout>
 <div class="relative min-h-screen bg-fondo-app font-outfit text-titulo">
 <div class="dash-noise pointer-events-none fixed inset-0 z-[60] opacity-[0.14] mix-blend-overlay"></div>
 
 <main class="relative z-10 mx-auto max-w-5xl px-4 pb-12 pt-8 sm:px-6 lg:px-8">
 <header class="mb-8">
 <nav class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-terracota">
 <a href="{{ route('admin.usuarios.index') }}" class="transition hover:text-titulo">Usuarios</a>
 <i class="ph-bold ph-caret-right text-[10px]"></i>
 <span>Expediente de Usuario</span>
 </nav>
 <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
 <h1 class="text-3xl font-black text-titulo">
 Ficha <span class="text-terracota">Institucional</span>
 </h1>
 <div class="flex items-center gap-3">
 <a href="{{ route('admin.usuarios.index') }}" 
 class="inline-flex items-center gap-2 rounded-full border border-borde-suave px-5 py-2 text-xs font-bold text-titulo transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-arrow-left"></i>
 Volver
 </a>
 </div>
 </div>
 </header>

 <section class="animate-in fade-in duration-500">
 <livewire:admin.usuarios.usuario-ficha-panel :usuario="$usuario" />
 </section>
 </main>
 </div>
</x-app-layout>
