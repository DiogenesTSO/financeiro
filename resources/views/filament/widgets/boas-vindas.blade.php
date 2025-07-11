<x-filament::widget>
    <x-filament::card class="flex justify-center items-center min-h-[300px]">
        <div class="text-center space-y-4">
            <p class="text-2xl font-bold text-gray-800">📭 Nenhuma informações para serem exibidas</p>
            <p class="text-gray-400 text-lg">Faça seus primeiros lançamentos para visualizar a dashboard financeira.</p>
            <p class="text-gray-300 text-lg">Começe criando uma nova conta.</p>

            <x-filament::button
                tag="a"
                href="{{ route('filament.admin.resources.contas.create') }}"
            >
                Nova conta
            </x-filament::button>
        </div>
    </x-filament::card>
</x-filament::widget>
