<div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl shadow p-4">
        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300">Saldo Devedor</h4>
        <p class="text-xl font-bold text-danger-600 dark:text-danger-400">R$ {{ number_format($saldoDevedor, 2, ',', '.') }}</p>
    </div>
    <div class="bg-gray-100 dark:bg-gray-800 rounded-xl shadow p-4">
        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300">Total Pago</h4>
        <p style="font-size: 1.25rem; font-weight: bold; color: #16a34a;">R$ {{ number_format($totalPago, 2, ',', '.') }}</p>
    </div>
</div>
