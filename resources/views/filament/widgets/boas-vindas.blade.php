<x-filament::widget>
    <x-filament::card class="flex justify-center items-center min-h-[300px]">
        <div class="text-center space-y-4">
            @if ($etapa === 'familia')
                <p class="text-xl font-bold text-gray-800">👨‍👩‍👧 Nenhuma família encontrada</p>
                <p class="text-gray-500">Você precisa criar uma família para usar o sistema.</p>
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.familias.create') }}"
                >
                    Nova família
                </x-filament::button>
            @elseif ($etapa === 'conta')
                <p class="text-xl font-bold text-gray-800">💰 Crie a sua conta financeira</p>
                <p class="text-gray-500">Crie sua primeira conta financeira</p>
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.contas.create') }}"
                >
                    Nova conta
                </x-filament::button>
            @elseif ($etapa === 'categorias')
                <p class="text-xl font-bold text-gray-800">✨ Crie novas categorias</p>
                <p class="text-gray-500">Crie suas categorias que deseja usar</p>
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.categorias.create') }}"
                >
                    Nova categoria
                </x-filament::button>
            @elseif ($etapa === 'transacao')
                <p class="text-xl font-bold text-gray-800">📥 Nenhum lançamento encontrado</p>
                <p class="text-gray-500">Cadastre sua primeira receita ou despesa para visualizar a dashboard</p>
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.transacoes.create') }}"
                >
                    Novo Lançamento
                </x-filament::button>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>
