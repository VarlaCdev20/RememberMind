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
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        'azul-profundo': '#2F3E5C',
                        'terracota': '#E27D60',
                        'arena': '#F8F3ED',
                        'crema-oscura': '#E6DDD3',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-[#F8F3ED] font-sans antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden select-none">
    
    {{-- ELEMENTOS DECORATIVOS DE FONDO --}}
    <div class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] rounded-full bg-[#E27D60]/10 blur-[120px]"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[450px] h-[450px] rounded-full bg-[#2F3E5C]/10 blur-[100px]"></div>

    <div class="relative w-full max-w-lg bg-white/70 backdrop-blur-xl border border-white/50 rounded-[2.3rem] shadow-[0_20px_50px_rgba(47,62,92,0.11)] p-8 md:p-12 text-center transform transition duration-500 hover:scale-[1.01]">
        
        {{-- LOGO DE LA CASA --}}
        <div class="flex justify-center mb-6">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#E27D60] text-white font-black shadow-lg shadow-[#E27D60]/20">
                <span class="text-2xl font-outfit">C</span>
            </div>
        </div>

        {{-- ICONO DE SEGURIDAD --}}
        <div class="relative inline-flex items-center justify-center w-24 h-24 bg-[#FDF1ED] rounded-full mb-8">
            <i class="ph-bold ph-shield-warning text-5xl text-[#E27D60] animate-pulse"></i>
            <span class="absolute top-0 right-0 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#E27D60] opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-[#E27D60]"></span>
            </span>
        </div>

        {{-- CONTENIDO DEL MENSAJE --}}
        <p class="text-[11px] font-black uppercase tracking-[0.25em] text-[#E27D60] mb-2 font-outfit">Código 403</p>
        <h1 class="text-3xl font-black text-[#2F3E5C] tracking-tight mb-4 font-outfit md:text-4xl">Acceso restringido</h1>
        
        <p class="text-[#2F3E5C]/75 text-sm font-semibold leading-relaxed max-w-md mx-auto mb-10">
            Tu rol institucional no dispone de los privilegios necesarios para ver esta sección. Si consideras que se trata de un error, comunícate con el administrador.
        </p>

        {{-- BOTÓN REGRESAR --}}
        <a href="/dashboard" class="inline-flex items-center gap-2.5 bg-[#2F3E5C] hover:bg-[#E27D60] text-white text-xs font-black uppercase tracking-wider px-8 py-4 rounded-full shadow-lg shadow-[#2F3E5C]/15 transition duration-300 active:scale-95">
            <i class="ph-bold ph-arrow-left text-base"></i>
            Volver al panel principal
        </a>

        {{-- PIE DE PÁGINA --}}
        <div class="mt-12 pt-6 border-t border-[#C7B5A3]/30 text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/40">
            CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS - RememberMind
        </div>

    </div>
</body>
</html>
