<x-filament::page>
    <x-filament-panels::form wire:submit.prevent="save"> {{-- Add wire:submit.prevent to the form --}}
        {{ $this->form }}

        {{-- This is the correct way to render form actions in Filament v3 pages --}}
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>
</x-filament::page>