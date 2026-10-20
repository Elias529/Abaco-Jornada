@props(['tipo' => 'fuera'])

<span {{ $attributes->merge(['class' => 'chip chip-'.$tipo]) }}>
    <svg viewBox="0 0 24 24" aria-hidden="true">
        @switch($tipo)
            @case('en-curso')
            @case('abierta')
            @case('en_curso')
                <path d="M9 7l8 5-8 5z" fill="currentColor"/>
                @break
            @case('en-pausa')
                <path d="M8 6h3v12H8zM13 6h3v12h-3z" fill="currentColor"/>
                @break
            @case('pendiente')
            @case('tardia')
            @case('abierto')
                <circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.8"/>
                <path d="M12 8v4.5l2.5 1.5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                @break
            @case('cerrada')
            @case('resuelta')
            @case('resuelto')
                <path d="M6 12.5l4 4 8-9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                @break
            @case('incidencia')
                <rect x="6" y="4" width="12" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/>
                <path d="M9 9h6M9 13h6M9 17h3" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                @break
            @default
                <circle cx="12" cy="12" r="4" fill="currentColor"/>
        @endswitch
    </svg>
    {{ $slot }}
</span>
