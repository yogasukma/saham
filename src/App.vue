<script setup>
import { ref, onMounted } from 'vue'
import ChartRatio from './components/ChartRatio.vue'
import TablePortfolio from './components/TablePortfolio.vue'
import TableProfit from './components/TableProfit.vue'
import TableFund from './components/TableFund.vue'
import Activity from './components/Activity.vue'
import Blog from './components/Blog.vue'
import Disclaimer from './components/Disclaimer.vue'

const portfolioData = ref([])
const profitData = ref({})
const fundsData = ref({})
const activityData = ref([])
const lastUpdate = ref('')

onMounted(async () => {
  const res = await fetch('/data.json')
  const data = await res.json()
  portfolioData.value = data.portfolio
  profitData.value = data.profit || {}
  fundsData.value = data.funds || {}
  activityData.value = data.activity || []
  lastUpdate.value = data.last_update || ''
})
</script>

<template>
  <main>
    <div class="w-[70%] md:w-2/5 mx-auto my-10">
      <div style="margin: 50px auto 100px; max-width: 600px;">
        <h1 class="text-center m-0 p-0">Equity Dashboard</h1>
        <p class="text-center text-gray-600">My personal dashboard showing my holdings, profits (or losses), and investing activity.</p>
      </div>
      <ChartRatio :portfolio="portfolioData" />
      <TablePortfolio :portfolio="portfolioData" :lastUpdate="lastUpdate" />
      <TableFund :funds="fundsData" />
      <TableProfit :profit="profitData" />
      <Blog />
      <Activity :activity="activityData" />
      <Disclaimer />
    </div>
  </main>
</template>

<style>
/* Component-specific styles can go here */
</style>
