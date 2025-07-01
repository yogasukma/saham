<script setup>
import { computed } from 'vue'

const props = defineProps({
  portfolio: {
    type: Array,
    required: true
  },
  lastUpdate: {
    type: String,
    default: ''
  }
})

// Computed property to sort portfolio by ratio in descending order
const sortedPortfolio = computed(() => {
  if (!props.portfolio || props.portfolio.length === 0) return []
  
  return [...props.portfolio].sort((a, b) => {
    // Convert ratio strings to numbers for proper numeric sorting
    const ratioA = parseFloat(a.ratio)
    const ratioB = parseFloat(b.ratio)
    // Sort in descending order (largest first)
    return ratioB - ratioA
  })
})
</script>

<template>
  <div class="overflow-x-auto mt-8">
    <table class="border border-gray-300 divide-y divide-gray-300 w-full">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">Code</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">Avg Price</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">Latest Price</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">Profit (%)</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-300">
            Fund Ratio (%) 
            <span class="ml-1 text-blue-500" title="Sorted by highest ratio">▼</span>
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-300">
        <tr v-for="item in sortedPortfolio" :key="item.code">
          <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 border border-gray-300">{{ item.code }}</td>
          <td class="px-6 py-4 whitespace-nowrap border border-gray-300">{{ item.avg_price }}</td>
          <td class="px-6 py-4 whitespace-nowrap border border-gray-300">{{ item.latest_price }}</td>
          <td class="px-6 py-4 whitespace-nowrap border border-gray-300">{{ item.profit }}</td>
          <td class="px-6 py-4 whitespace-nowrap border border-gray-300">{{ item.ratio }}</td>
        </tr>
      </tbody>
    </table>
    <p v-if="lastUpdate" class="mt-2 text-xs text-gray-500 italic text-right">
      * Prices last updated at {{ lastUpdate }}
    </p>
  </div>
</template>
