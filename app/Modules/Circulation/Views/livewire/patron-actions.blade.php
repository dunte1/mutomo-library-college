@section('title', 'Patron Actions')
<div>
    <x-primary-button wire:click="renew({{ $borrowId }})" class="text-xs">
        Renew
    </x-primary-button>
</div>