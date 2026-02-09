<template>
  <canvas :id="graphKey"></canvas>
</template>

<script>
import Chart from 'chart.js/auto'

export default {
  name: "UsersRegistrationsGraph",
  props: ['registrations','online_graph'],
  data() {
    return {
      graphKey: 'registrations_chart',
      ctx: undefined,
      chart: undefined,
      labels: this.registrations.map(x=>x.date),
      data: this.registrations.map(x=>x.count),
      online: this.online_graph.map(x=>x.count),
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
            label: 'Регистрации',
            data: this.data,
            backgroundColor: [
              'rgba(239,99,255,0.2)',
            ],
            borderColor: [
              'rgb(174,99,255)',
            ],
            borderWidth: 1
          },
            {
              label: 'Online',
              data: this.online,
              backgroundColor: [
                'rgba(128,255,99,0.2)',
              ],
              borderColor: [
                'rgba(128,255,99,1)',
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
          maintainAspectRatio:false,
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