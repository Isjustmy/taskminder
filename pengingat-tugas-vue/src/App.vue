<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { IStaticMethods } from 'preline/preline'

// Call HSStaticMethods.autoInit() after the component is mounted
onMounted(() => {
  setTimeout(() => {
    if (window.HSStaticMethods) {
      window.HSStaticMethods.autoInit()
    }
  }, 100)
})
</script>

<style></style>
