<!-- LineChart.vue -->
<template>
  <div>
    <canvas ref="lineChartCanvas"></canvas>
  </div>
</template>

<script>
import Chart from 'chart.js/auto'

export default {
  data() {
    return {
      barChartData: {
        labels: [],
        datasets: [
          {
            label: 'Jumlah Tugas',
            backgroundColor: '#f87979',
            data: []
          }
        ]
      }
      // properti lainnya
    }
  },
  props: {
    data: {
      type: Object,
      required: true
    }
  },
  mounted() {
    this.renderChart()
  },
  methods: {
    renderChart() {
      const ctx = this.$refs.lineChartCanvas.getContext('2d')
      new Chart(ctx, {
        type: 'line',
        data: this.data,
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      })
    }
  }
}
</script>

<style>
canvas {
  height: 400px !important;
}
</style>
