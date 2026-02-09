<template>
  <canvas :id="graphKey"></canvas>
</template>

<script>
import Chart from 'chart.js/auto'

export default {
  name: "OrdersGraph",
  props: ['types'],
  data() {
    return {
      graphKey: 'orders_chart',
      ctx: undefined,
      chart: undefined,
      labels: this.types.map(i => i.title),
      data: this.types.map(i => i.active_orders_count),
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
        type: 'bar',
        data: {
          labels: this.labels,
          datasets: [{
            label: 'Распределение заявок',
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
              'rgb(245,255,99)',
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
          indexAxis: 'y',
          /*scales: {
            yAxes: [{
              gridLines: {
                display: false,
                drawBorder: false
              },
              ticks: {
                mirror: true,
                fontSize: 16,
                padding: -10
              }
            }],
            xAxes: [{
              gridLines: {
                display: false,
                drawBorder: false
              },
              ticks: {
                display: false
              }
            }]
          },*/
        }
      });
    }
  }
}
</script>

<style scoped>

</style>