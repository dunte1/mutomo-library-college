@props(['mobileCards' => true])

<div class="overflow-x-auto {{ $mobileCards ? 'table-mobile-cards' : '' }}">
    <table {{ $attributes->merge(['class' => 'w-full']) }}>
        {{ $slot }}
    </table>
</div>
