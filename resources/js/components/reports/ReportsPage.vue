<template>
  <div class="panel">
    <h2>Reports</h2>

    <section class="report-card">
      <h3>Sales by Date</h3>
      <div class="controls">
        <input type="date" v-model="dateFrom" />
        <input type="date" v-model="dateTo" />
        <button class="btn" @click="fetchSales">Load</button>
        <button class="btn ghost" @click="exportSales">Export CSV</button>
      </div>

      <table class="table small">
        <thead><tr><th>Date</th><th>Sales Count</th><th>Total Amount</th><th>Total Paid</th></tr></thead>
        <tbody>
          <tr v-for="r in sales" :key="r.date">
            <td>{{ r.date }}</td>
            <td>{{ r.sales_count }}</td>
            <td>{{ formatMoney(r.total_amount) }}</td>
            <td>{{ formatMoney(r.total_paid) }}</td>
          </tr>
          <tr v-if="!sales.length"><td colspan="4" class="muted">No data</td></tr>
        </tbody>
      </table>
    </section>

    <section class="report-card">
      <h3>Stock Report</h3>
      <div class="controls">
        <input type="number" v-model.number="lowThreshold" placeholder="Low stock threshold" />
        <button class="btn" @click="fetchStock">Load</button>
        <button class="btn ghost" @click="exportStock">Export CSV</button>
      </div>

      <table class="table small">
        <thead><tr><th>Product</th><th>SKU</th><th>Stock</th><th>Cost</th><th>Sell</th></tr></thead>
        <tbody>
          <tr v-for="p in stock" :key="p.id">
            <td>{{ p.name }}</td><td>{{ p.sku }}</td><td>{{ p.stock }}</td><td>{{ formatMoney(p.cost_price) }}</td><td>{{ formatMoney(p.sell_price) }}</td>
          </tr>
          <tr v-if="!stock.length"><td colspan="5" class="muted">No data</td></tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script>
export default {
  data(){ return { dateFrom:'', dateTo:'', sales:[], lowThreshold:0, stock:[] } },
  methods:{
    async fetchSales(){
      const url = new URL('/api/reports/sales-by-date', location.origin)
      if(this.dateFrom) url.searchParams.set('date_from', this.dateFrom)
      if(this.dateTo) url.searchParams.set('date_to', this.dateTo)
      const res = await fetch(url); this.sales = await res.json()
    },
    exportSales(){
      const url = new URL('/api/reports/sales-by-date-export', location.origin)
      if(this.dateFrom) url.searchParams.set('date_from', this.dateFrom)
      if(this.dateTo) url.searchParams.set('date_to', this.dateTo)
      window.location = url
    },
    async fetchStock(){
      const url = new URL('/api/reports/stock', location.origin)
      if(this.lowThreshold) url.searchParams.set('low_stock_threshold', this.lowThreshold)
      const res = await fetch(url); this.stock = await res.json()
    },
    exportStock(){
      const url = new URL('/api/reports/stock-export', location.origin)
      if(this.lowThreshold) url.searchParams.set('low_stock_threshold', this.lowThreshold)
      window.location = url
    },
    formatMoney(v){ return Number(v || 0).toFixed(2) }
  }
}
</script>

<style scoped>
.panel { padding:12px }
.report-card { border:1px solid #eef2f7; padding:12px; border-radius:10px; margin-bottom:12px }
.controls { display:flex; gap:8px; align-items:center; margin-bottom:8px }
.table.small th, .table.small td { padding:8px 6px; border-bottom:1px solid #f3f4f6 }
.muted { color:#6b7280; text-align:center }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer }
.btn.ghost { background:transparent; border:1px solid #cbd5e1; color:#111827 }
</style>
