<button {{ $attributes->merge(['type' => 'button', 'class' => 'rm-btn rm-btn-secondary shadow-xs']) }}>
    {{ $slot }}
</button>
