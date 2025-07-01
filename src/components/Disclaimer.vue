<script setup>
// No props or data needed for this component since it's static
</script>

<template>
  <div class="mt-8">
    <div class="p-6 rounded-md shadow-md border border-amber-200 bg-gradient-to-r from-amber-50 via-yellow-50 to-orange-50">
      <h2 class="text-xl font-semibold mb-3 text-amber-700">Disclaimer</h2>
      <p class="text-gray-700 text-sm italic leading-relaxed disclaimer-text">
        Informasi yang ada pada halaman ini bukan merupakan edukasi apalagi ajakan membeli saham, 
        hanya persentasi sederhana dari portfolio pribadi yang merupakan retail kecil dan iseng 
        tanpa background keuangan ;) 
      </p>
    <a href="https://yogasukma.web.id" target="_blank" class="inline-block mt-4 px-4 py-2 bg-amber-500 text-white text-xs rounded hover:bg-blue-600 transition-colors">kenalan lebih jauh dengan saya</a>
    </div>
  </div>
</template>

<style scoped>
.disclaimer-text {
  position: relative;
  padding: 0 8px;
}

.disclaimer-text::before {
  content: '"';
  position: absolute;
  left: -5px;
  top: -8px;
  font-size: 24px;
  color: #d97706; /* amber-600 */
  opacity: 0.6;
}

.disclaimer-text::after {
  content: '"';
  position: absolute;
  right: -5px;
  bottom: -15px;
  font-size: 24px;
  color: #d97706; /* amber-600 */
  opacity: 0.6;
}
</style>
