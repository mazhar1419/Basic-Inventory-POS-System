<template>
  <div class="panel">
    <h2>Reports</h2>

    <div class="controls-row">
      <div class="field">
        <label>Report type</label>
        <select v-model="type">
          <option value="sales_by_date">Sales by Date</option>
          <option value="sales_by_product">Sales by Product</option>
          <option value="purchases_by_date">Purchases by Date</option>
          <option value="purchases_by_product">Purchases by Product</option>
          <option value="damage">Damage / Write-off</option>
          <option value="qty">Stock / Qty</option>
        </select>
      </div>

      <div class="field">
        <label>Product (optional)</label>
        <select v-model.number="productId">
          <option :value="null">All products</option>
          <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} — {{ p.sku || '—' }}</option>
        </select>
      </div>

      <div class="field">
        <label>Date from</label>
        <input type="date" v-model="dateFrom" />
      </div>

      <div class="field">
        <label>Date to</label>
        <input type="date" v-model="dateTo" />
      </div>

      <div style="align-self:end; display:flex; gap:8px">
        <button class="btn" @click="load">Load</button>
        <button class="btn ghost" @click="exportCsv">Export CSV</button>
      </div>
    </div>

    <div v-if="type === 'sales_by_date'" class="report-card">
      <h3>Sales by Date</h3>
      <table class="table small">
        <thead><tr><th>Date</th><th>Sales Count</th><th>Total Amount</th><th>Total Paid</th></tr></thead>
        <tbody>
          <tr v-for="r in rows" :key="r.date">
            <td>{{ r.date }}</td>
            <td>{{ r.sales_count }}</td>
            <td>{{ formatMoney(r.total_amount) }}</td>
            <td>{{ formatMoney(r.total_paid) }}</td>
          </tr>
          <tr v-if="!rows.length"><td colspan="4" class="muted">No data</td></tr>
        </tbody>
      </table>
    </div>

    <div v-if="type === 'sales_by_product'" class="report-card">
      <h3>Sales by Product</h3>
      <table class="table small">
        <thead><tr><th>Product</th><th>SKU</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
        <tbody>
          <tr v-for="r in rows" :key="r.product_id">
            <td>{{ r.name }}</td><td>{{ r.sku }}</td><td>{{ r.qty_sold }}</td><td>{{ formatMoney(r.revenue) }}</td>
          </tr>
          <tr v-if="!rows.length"><td colspan="4" class="muted">No data</td></tr>
        </tbody>
      </table>
    </div>

    <div v-if="type === 'purchases_by_date'" class="report-card">
      <h3>Purchases by Date</h3>
      <table class="table small">
        <thead><tr><th>Date</th><th>Purchase Count</th><th>Total Amount</th></tr></thead>
        <tbody>
          <tr v-for="r in rows" :key="r.date">
            <td>{{ r.date }}</td><td>{{ r.purchase_count }}</td><td>{{ formatMoney(r.total_amount) }}</td>
          </tr>
          <tr v-if="!rows.length"><td colspan="3" class="muted">No data</td></tr>
        </tbody>
      </table>
    </div>

    <div v-if="type === 'purchases_by_product'" class="report-card">
      <h3>Purchases by Product</h3>
      <table class="table small">
        <thead><tr><th>Product</th><th>SKU</th><th>Qty Purchased</th><th>Cost Total</th></tr></thead>
        <tbody>
          <tr v-for="r in rows" :key="r.product_id">
            <td>{{ r.name }}</td><td>{{ r.sku }}</td><td>{{ r.qty_purchased }}</td><td>{{ formatMoney(r.cost_total) }}</td>
          </tr>
          <tr v-if="!rows.length"><td colspan="4" class="muted">No data</td></tr>
        </tbody>
      </table>
    </div>

    <div v-if="type === 'damage'" class="report-card">
      <h3>Damage / Write-off</h3>
      <table class="table small">
        <thead><tr><th>Product</th><th>SKU</th><th>Qty Damaged</th><th>Incidents</th></tr></thead>
        <tbody>
          <tr v-for="r in rows" :key="r.product_id">
            <td>{{ r.name }}</td><td>{{ r.sku }}</td><td>{{ r.qty_damaged }}</td><td>{{ r.incidents }}</td>
          </tr>
          <tr v-if="!rows.length"><td colspan="4" class="muted">No data</td></tr>
        </tbody>
      </table>
    </div>

    <div v-if="type === 'qty'" class="report-card">
      <h3>Stock / Qty</h3>
      <table class="table small">
        <thead><tr><th>Product</th><th>SKU</th><th>Stock</th><th>Cost</th><th>Sell</th></tr></thead>
        <tbody>
          <tr v-for="r in rows" :key="r.id">
            <td>{{ r.name }}</td><td>{{ r.sku }}</td><td>{{ r.stock }}</td><td>{{ formatMoney(r.cost_price) }}</td><td>{{ formatMoney(r.sell_price) }}</td>
          </tr>
          <tr v-if="!rows.length"><td colspan="5" class="muted">No data</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  data(){ return {
    type: 'sales_by_date',
    productId: null,
    dateFrom: '',
    dateTo: '',
    products: [],
    rows: []
  }},
  mounted(){ this.loadProducts() },
  methods:{
    async loadProducts(){
      try {
        const res = await fetch('/api/products?per_page=9999')
        const json = await res.json()
        this.products = json.data || json || []
      } catch(e){ this.products = [] }
    },

    apiBaseForType() {
      switch(this.type) {
        case 'sales_by_date': return '/api/reports/sales-by-date'
        case 'sales_by_product': return '/api/reports/sales-by-product'
        case 'purchases_by_date': return '/api/reports/purchases-by-date'
        case 'purchases_by_product': return '/api/reports/purchases-by-product'
        case 'damage': return '/api/reports/damage'
        case 'qty': return '/api/reports/qty'
        default: return '/api/reports/sales-by-date'
      }
    },

    apiExportForType() {
      switch(this.type) {
        case 'sales_by_date': return '/api/reports/sales-by-date-export'
        case 'sales_by_product': return '/api/reports/sales-by-product-export'
        case 'purchases_by_date': return '/api/reports/purchases-by-date-export'
        case 'purchases_by_product': return '/api/reports/purchases-by-product-export'
        case 'damage': return '/api/reports/damage-export'
        case 'qty': return '/api/reports/qty-export'
        default: return '/api/reports/sales-by-date-export'
      }
    },

    buildParams(url) {
      if (this.productId) url.searchParams.set('product_id', this.productId)
      if (this.dateFrom) url.searchParams.set('date_from', this.dateFrom)
      if (this.dateTo) url.searchParams.set('date_to', this.dateTo)
    },

    async load(){
      const url = new URL(this.apiBaseForType(), location.origin)
      this.buildParams(url)
      try {
        const res = await fetch(url)
        if (!res.ok) throw new Error('Failed to load')
        this.rows = await res.json()
      } catch (e) {
        console.error('Load failed', e)
        this.rows = []
        alert('Failed to load report: ' + e.message)
      }
    },

    exportCsv(){
      const url = new URL(this.apiExportForType(), location.origin)
      this.buildParams(url)
      // navigate to export (streamed download)
      window.location = url
    },

    formatMoney(v){ return Number(v||0).toFixed(2) }
  }
}
</script>

<style scoped>
.panel { padding:12px }
.controls-row { display:flex; gap:12px; align-items:flex-end; margin-bottom:12px; flex-wrap:wrap }
.field { display:flex; flex-direction:column }
.field label { font-size:13px; color:#374151; font-weight:600; margin-bottom:4px }
.report-card { border:1px solid #eef2f7; padding:12px; border-radius:10px; margin-bottom:12px }
.table.small th, .table.small td { padding:8px 6px; border-bottom:1px solid #f3f4f6 }
.muted { color:#6b7280; text-align:center }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer }
.btn.ghost { background:transparent; border:1px solid #cbd5e1; color:#111827 }
</style>
