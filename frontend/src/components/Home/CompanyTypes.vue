<template>
  <canvas :id="graphKey"></canvas>
</template>

<script>
import Chart from 'chart.js/auto'

export default {
  name: "CompanyTypes",
  props: ['types'],
  data() {
    return {
      graphKey: 'users_chart',
      ctx: undefined,
      chart: undefined,
      labels: this.types.map(i => i.label),
      data: this.types.map(i => i.companies_count),
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
        type: 'pie',
        data: {
          labels: this.labels,
          datasets: [{
            label: 'Типы компаний',
            data: this.data,
            backgroundColor: [
              'rgba(255, 99, 132, .2)',
              'rgb(226,99,255, .2)',
              'rgb(120,99,255, .2)',
              'rgb(99,255,250, .2)',
              'rgb(99,255,107, .2)',
            ],
            borderColor: [
              'rgba(255, 99, 132, 1)',
              'rgb(226,99,255)',
              'rgb(120,99,255)',
              'rgb(99,255,250)',
              'rgb(99,255,107)',
            ],
            borderWidth: 1
          }]
        },
        options: {
          plugins: {
            legend: {
              //display: false
              position:'bottom'
            },
          },
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