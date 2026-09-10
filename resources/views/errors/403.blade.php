<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title>Acceso Restringido - CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</title>
 
 <!-- Fonts -->
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
 
 <!-- Tailwind CSS (Direct CDN for the static error page) -->
 <script src="https://cdn.tailwindcss.com"></script>
 
 <!-- Phosphor Icons -->
 <script src="https://unpkg.com/@phosphor-icons/web"></script>
 
 <script>

{!! file_get_contents(resource_path('frontend/scripts/modules/errors-403.js')) !!}
</script>
</head>
<body class="bg-fondo-app antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden select-none">
 
 {{-- ELEMENTOS DECORATIVOS DE FONDO --}}
 <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-estado-peligroBg blur-[120px]"></div>
 <div class="absolute bottom-[-10%] right-[-10%] w-[450px] h-[450px] rounded-full bg-fondo-panel blur-[100px]"></div>

 <div class="relative w-full max-w-lg bg-fondo-card/70 backdrop-blur-xl border border-white/50 rounded-[2.3rem] shadow-[0_20px_50px_rgba(47,62,92,0.11)] p-8 md:p-12 text-center transform transition duration-500 hover:scale-[1.01]">
 
 {{-- LOGO DE LA CASA --}}
 <div class="flex justify-center mb-6">
 <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-boton-acento text-inverso font-black shadow-lg shadow-[#E27D60]/20">
 <span class="text-2xl font-outfit">C</span>
 </div>
 </div>

 {{-- ICONO DE SEGURIDAD --}}
 <div class="relative inline-flex items-center justify-center w-24 h-24 bg-fondo-panel rounded-full mb-8">
 <i class="ph-bold ph-shield-warning text-5xl text-boton-acento animate-pulse"></i>
 <span class="absolute top-0 right-0 flex h-4 w-4">
 <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-boton-acento opacity-75"></span>
 <span class="relative inline-flex rounded-full h-4 w-4 bg-boton-acento"></span>
 </span>
 </div>

 {{-- CONTENIDO DEL MENSAJE --}}
 <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-boton-acento mb-2 font-outfit">Código 403</p>
 <h1 class="text-3xl font-black text-titulo tracking-tight mb-4 font-outfit md:text-4xl">Acceso restringido</h1>
 
 <p class="text-apoyo text-sm font-semibold leading-relaxed max-w-md mx-auto mb-10">
 Tu rol institucional no dispone de los privilegios necesarios para ver esta sección. Si consideras que se trata de un error, comunícate con el administrador.
 </p>

 {{-- BOTÓN REGRESAR --}}
 <a href="/dashboard" class="inline-flex items-center gap-2.5 bg-boton-principal hover:bg-boton-acento text-inverso text-xs font-bold uppercase tracking-wider px-8 py-4 rounded-full shadow-lg shadow-[#2F3E5C]/15 transition duration-300 active:scale-95">
 <i class="ph-bold ph-arrow-left text-base"></i>
 Volver al panel principal
 </a>

 {{-- PIE DE PÁGINA --}}
 <div class="mt-12 pt-6 border-t border-borde-suave text-[10px] font-bold uppercase tracking-widest text-apoyo">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS - RememberMind
 </div>

 </div>
</body>
</html>
