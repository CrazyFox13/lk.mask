<template>
  <canvas :id="graphKey"></canvas>
</template>

<script>
import Chart from 'chart.js/auto'

export default {
  name: "NewMessagesGraph",
  props: ['new_messages'],
  data() {
    return {
      graphKey: 'messages',
      ctx: undefined,
      chart: undefined,
      labels: this.new_messages.map(x => x.date),
      count: this.new_messages.map(x => x.count),
    }
  },
  mounted() {
    this.draw();
  },
  methods: {
    draw() {
      this.ctx = document.getElementById(this.graphKey);
      if (this.chart) {
        this.chart.destroy();
      }
      this.chart = new Chart(this.ctx, {
        type: 'line',
        data: {
          labels: this.labels,
          datasets: [{
            label: 'Новые сообщения',
            data: this.count,
            backgroundColor: [
              'rgba(239,99,255,0.2)',
            ],
            borderColor: [
              'rgb(174,99,255)',
            ],
            borderWidth: 1
          }]
        },
        options: {
          plugins: {
            legend: {
              display: false
            },
            title: {
              display: false,
            },
          },
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    }
  }
}
</script>

<style scoped>

</style>