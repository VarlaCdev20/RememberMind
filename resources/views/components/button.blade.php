<button {{ $attributes->merge(['type' => 'submit', 'class' => 'rm-btn rm-btn-primary shadow-xs']) }}>
    {{ $slot }}
</button>
