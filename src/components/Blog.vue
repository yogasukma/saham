<script setup>
import { ref, onMounted } from 'vue'

const latestPost = ref(null)
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    loading.value = true
    // Use a CORS proxy to access the feed
    const corsProxy = 'https://api.allorigins.win/raw?url='
    const feedUrl = 'https://yogasukma.web.id/c/ekuitas/feed/'
    const encodedFeedUrl = encodeURIComponent(feedUrl)
    
    const response = await fetch(`${corsProxy}${encodedFeedUrl}`)
    if (!response.ok) {
      throw new Error('Failed to fetch blog feed')
    }
    
    const xmlText = await response.text()
    const parser = new DOMParser()
    const xmlDoc = parser.parseFromString(xmlText, 'text/xml')
    
    // Get the first (latest) item
    const item = xmlDoc.querySelector('item')
    
    if (item) {
      latestPost.value = {
        title: item.querySelector('title')?.textContent || 'No title',
        date: new Date(item.querySelector('pubDate')?.textContent || '').toLocaleDateString('id-ID', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        }),
        description: item.querySelector('description')?.textContent || '',
        link: item.querySelector('link')?.textContent || '#'
      }
    }
  } catch (e) {
    console.error('Error fetching blog post:', e)
    error.value = e.message
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="mt-8">
    <h2 class="text-xl font-semibold mb-4 text-center">Latest Blog Post</h2>

    <div v-if="loading" class="bg-white p-4 rounded-md border border-gray-300 shadow-sm">
      <p class="text-gray-500">Loading latest post...</p>
    </div>
    
    <div v-else-if="error" class="bg-white p-4 rounded-md border border-gray-300 shadow-sm">
      <p class="text-red-500">Error loading blog post: {{ error }}</p>
    </div>
    
    <div v-else-if="latestPost" class="bg-white p-6 rounded-md border border-gray-300 shadow-sm">
      <div class="text-sm text-gray-500 mb-2">{{ latestPost.date }}</div>
      <h3 class="text-lg font-semibold text-gray-800 mb-3">{{ latestPost.title }}</h3>
      
      <div class="text-gray-600 mb-4 blog-description" v-html="latestPost.description"></div>
      
      <a :href="latestPost.link" target="_blank" rel="noopener noreferrer" 
         class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
        Read More
      </a>
    </div>
    
    <div v-else class="bg-white p-4 rounded-md border border-gray-300 shadow-sm">
      <p class="text-gray-500">No blog posts found.</p>
    </div>
  </div>
</template>

<style scoped>
.blog-description {
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}
</style>
