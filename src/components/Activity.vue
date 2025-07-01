<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  activity: {
    type: Array,
    required: true
  }
})

// Filter state
const activeFilter = ref('all')
const filters = [
  { id: 'all', label: 'All' },
  { id: 'topup', label: 'Top Up' },
  { id: 'dividend', label: 'Dividend' },
  { id: 'buy', label: 'Buy' },
  { id: 'sell', label: 'Sell' }
]

// Detect activity type from description
const getActivityType = (description) => {
  if (description.includes('<strong>topup</strong>')) return 'topup'
  if (description.includes('<strong>dividen</strong>')) return 'dividend'
  if (description.includes('<strong>pembelian</strong>')) return 'buy'
  if (description.includes('<strong>penjualan</strong>')) return 'sell'
  return 'other'
}

// Filtered activities based on selected filter
const filteredActivities = computed(() => {
  if (activeFilter.value === 'all') return props.activity
  
  return props.activity.filter(item => {
    const type = getActivityType(item.description)
    return type === activeFilter.value
  })
})

// Helper function to get the color class for each activity type
const getTypeClass = (type) => {
  switch (type) {
    case 'topup':
      return 'bg-green-100 text-green-800'
    case 'dividend':
      return 'bg-purple-100 text-purple-800'
    case 'buy':
      return 'bg-blue-100 text-blue-800'
    case 'sell':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

// Helper function to get a display label for each activity type
const getTypeLabel = (type) => {
  switch (type) {
    case 'topup':
      return 'Top Up'
    case 'dividend':
      return 'Dividend'
    case 'buy':
      return 'Buy'
    case 'sell':
      return 'Sell'
    default:
      return 'Other'
  }
}
</script>

<template>
  <div class="mt-8">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold">Activity Feed</h2>
      <div class="flex space-x-1">
        <button 
          v-for="filter in filters" 
          :key="filter.id"
          @click="activeFilter = filter.id"
          :class="[
            'px-2 py-1 text-xs rounded transition-colors',
            activeFilter === filter.id 
              ? 'bg-blue-500 text-white' 
              : 'bg-gray-100 hover:bg-gray-200 text-gray-700'
          ]"
        >
          {{ filter.label }}
        </button>
      </div>
    </div>
    
    <div class="rounded-md border border-gray-300 shadow-sm">
      <div 
        v-for="(item, index) in filteredActivities" 
        :key="index" 
        class="p-4"
        :class="{ 'border-b border-gray-200': index !== filteredActivities.length - 1 }"
      >
        <div class="flex justify-between items-start">
          <span class="text-xs font-medium text-gray-400">{{ item.time }}</span>
          <span class="text-xs px-2 py-0.5 rounded-full" 
                :class="getTypeClass(getActivityType(item.description))">
            {{ getTypeLabel(getActivityType(item.description)) }}
          </span>
        </div>
        <div class="mt-2">
          <p class="text-sm text-gray-600" v-html="item.description"></p>
        </div>
      </div>
      <div v-if="filteredActivities.length === 0" class="p-4">
        <p class="text-sm text-gray-500 italic">No activities found for the selected filter.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Smooth transitions for filter buttons */
button {
  transition: all 0.2s ease;
}

/* Add some spacing between filter buttons on smaller screens */
@media (max-width: 640px) {
  .flex.space-x-1 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.25rem;
    margin-top: 0.5rem;
  }
  
  .flex.items-center.justify-between {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>