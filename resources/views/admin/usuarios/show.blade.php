<x-sistema-layout>
 <div class="relative w-full font-outfit text-titulo">
 
 <main class="relative z-10 w-full pb-8">
 <header class="mb-6">
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
</x-sistema-layout>
