<x-filament-panels::page>
    {{-- Form Container --}}
    <x-filament-panels::form wire:submit="cetak">
        
        {{-- Menampilkan Form yang didefinisikan di Class PHP --}}
        {{ $this->form }}

        {{-- Tombol Submit / Cetak --}}
        <div class="flex justify-end mt-4">
            <x-filament::button type="submit" icon="heroicon-o-printer" color="success">
                Cetak Laporan PDF
            </x-filament::button>
        </div>

    </x-filament-panels::form>
</x-filament-panels::page>