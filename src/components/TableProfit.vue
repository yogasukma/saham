<script setup>
const props = defineProps({
  profit: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="overflow-x-auto mt-8">
    <h2 class="text-xl font-semibold mb-4 text-center">Profitability</h2>
    <table class="border border-gray-300 divide-y divide-gray-300 w-full">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">Profit Type</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">Value (%)</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-300">
        <tr>
          <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 border border-gray-300">Unrealized P/L</td>
          <td class="px-6 py-4 whitespace-nowrap border border-gray-300">{{ profit.unrealized_pl }}</td>
        </tr>
        <tr>
          <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 border border-gray-300">Realized P/L</td>
          <td class="px-6 py-4 whitespace-nowrap border border-gray-300">{{ profit.realized_pl }}</td>
        </tr>
        <tr>
          <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 border border-gray-300">Dividen</td>
          <td class="px-6 py-4 whitespace-nowrap border border-gray-300">{{ profit.dividend }}</td>
        </tr>
        <tr>
          <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 border border-gray-300">Total Profit</td>
          <td class="px-6 py-4 whitespace-nowrap border border-gray-300">{{ profit.total_profit }}</td>
        </tr>
      </tbody>
    </table>
      <p class="mt-2 text-xs text-gray-500 italic text-right">
      * Total profit is calculated as the sum of unrealized P/L, realized P/L, and dividends, relative to total funds.
    </p>
  </div>
</template>
