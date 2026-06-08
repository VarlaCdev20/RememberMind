<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-fondo-panel border border-transparent rounded-md font-semibold text-xs text-inverso uppercase tracking-widest hover:bg-fondo-panel focus:bg-fondo-panel active:bg-fondo-panel focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150']) }}>
 {{ $slot }}
</button>
