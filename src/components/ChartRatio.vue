<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import Chart from 'chart.js/auto'
import ChartDataLabels from 'chartjs-plugin-datalabels'

const props = defineProps({
  portfolio: {
    type: Array,
    required: true
  }
})

const chartRef = ref(null)
const pieChart = ref(null)

// Sort portfolio by ratio descending
const sortedPortfolio = computed(() => {
  if (!props.portfolio) return []
  return [...props.portfolio].sort((a, b) => parseFloat(b.ratio) - parseFloat(a.ratio))
})

function getRandomColor() {
  // Generate a pastel color
  const hue = Math.floor(Math.random() * 360)
  return `hsl(${hue}, 70%, 70%)`
}

function getColorArray(count) {
  return Array.from({ length: count }, getRandomColor)
}

onMounted(() => {
  renderChart()
})

watch(() => props.portfolio, () => {
  renderChart()
})

function renderChart() {
  if (pieChart.value) {
    pieChart.value.destroy()
  }
  if (!chartRef.value) return
  const labels = sortedPortfolio.value.map(item => item.code)
  const ratios = sortedPortfolio.value.map(item => Number(item.ratio))
  const backgroundColor = getColorArray(labels.length)
  pieChart.value = new Chart(chartRef.value, {
    type: 'pie',
    data: {
      labels,
      datasets: [{
        data: ratios,
        backgroundColor,
      }],
    },
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          enabled: false,
        },
        datalabels: {
          color: '#222',
          font: { weight: 'bold' },
          formatter: (value, context) => {
            const label = context.chart.data.labels[context.dataIndex]
            return label + '\n' + value + '%'
          },
        },
      },
    },
    plugins: [ChartDataLabels],
  })
}
</script>

<template>
  <div class="flex justify-center">
    <canvas ref="chartRef" width="400" height="400"></canvas>
  </div>
</template>
