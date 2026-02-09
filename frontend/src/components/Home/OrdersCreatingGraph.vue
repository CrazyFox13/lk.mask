<template>
  <canvas :id="graphKey"></canvas>
</template>

<script>
import Chart from 'chart.js/auto'

export default {
  name: "OrdersCreatingGraph",
  props: ['orders'],
  data() {
    return {
      graphKey: 'orders_creating_chart',
      ctx: undefined,
      chart: undefined,
      labels: this.orders.map(i => i.date),
      data: this.orders.map(i => i.count),
      additional_graphs: [
        {
          id: "orders_18",
          title: "МАСК ГРУПП"
        },
        {
          id: "orders_44",
          title: "МАГИРУС"
        },
      ],
      colors: [
        'rgb(99,255,250, .2)',
        'rgb(99,255,107, .2)',
      ],
      borders: [
        'rgb(99,255,250)',
        'rgb(245,255,99)',
      ]
    }
  },
  mounted() {
    this.draw();
  },
  computed: {
    additional_datasets() {
      return this.additional_graphs.map((graph, i) => {
        return {
          label: graph.title,
          data: this.orders.map(x => x[graph.id]),
          backgroundColor: this.colors[i],
          borderColor: this.borders[i],
          borderWidth: 1
        }
      })
    },
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
          datasets: [
            {
              label: 'Все заявки',
              data: this.data,
              backgroundColor: [
                'rgba(239,99,255,0.2)',
              ],
              borderColor: [
                'rgb(174,99,255)',
              ],
              borderWidth: 1
            },
            ...this.additional_datasets,
            {
              label: 'Прочие заявки',
              data: this.orders.map(x => x['except_additional']),
              backgroundColor: [
                'rgba(255,99,99,0.2)',
              ],
              borderColor: [
                'rgb(255,99,99)',
              ],
              borderWidth: 1
            }
          ]
        },
        options: {
          plugins: {
            legend: {
              display: true
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