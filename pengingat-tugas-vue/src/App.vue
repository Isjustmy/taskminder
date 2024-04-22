<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { IStaticMethods } from 'preline/preline';

onMounted(() => {
  setTimeout(() => {
    if (window.HSStaticMethods) {
      window.HSStaticMethods.autoInit();
    }
  }, 100);

});
</script>

<style>
/* CSS Anda dapat ditambahkan di sini */
</style>
