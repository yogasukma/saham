<script setup>
const props = defineProps({
  funds: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <div class="overflow-x-auto mt-8">
    <h2 class="text-xl font-semibold mb-4 text-center">Funds</h2>
    <table class="border border-gray-300 divide-y divide-gray-300 w-full">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">Fund</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">Value (%)</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-300">
        <tr>
          <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 border border-gray-300">Total Invested</td>
          <td class="px-6 py-4 whitespace-nowrap border border-gray-300">{{ funds.totalInvested }}</td>
        </tr>
        <tr>
          <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 border border-gray-300">Total Cash</td>
          <td class="px-6 py-4 whitespace-nowrap border border-gray-300">{{ funds.totalCash }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
