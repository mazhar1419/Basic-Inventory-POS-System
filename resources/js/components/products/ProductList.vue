<template>
  <div class="panel">
    <div class="panel-head">
      <h2>Products</h2>
      <div class="controls">
        <input v-model="search" @keyup.enter="fetchData" placeholder="Search by name or SKU" />
        <button class="btn" @click="openCreate">+ New Product</button>
        <button class="btn ghost" @click="exportCsv">Export CSV</button>
      </div>
    </div>

    <div v-if="showForm">
      <product-form :product="editing" @saved="onSaved" @cancel="closeForm" />
    </div>

    <table class="table">
      <thead><tr><th>Name</th><th>SKU</th><th>Stock</th><th>Cost</th><th>Sell</th><th></th></tr></thead>
      <tbody>
        <tr v-for="p in products.data || []" :key="p.id">
          <td>{{ p.name }}</td>
          <td>{{ p.sku }}</td>
          <td>{{ p.stock }}</td>
          <td>{{ formatMoney(p.cost_price) }}</td>
          <td>{{ formatMoney(p.sell_price) }}</td>
          <td class="actions">
            <button @click="edit(p)">Edit</button>
            <button class="danger" @click="remove(p)">Delete</button>
          </td>
        </tr>
        <tr v-if="(products.data || []).length===0"><td colspan="6" class="muted">No products</td></tr>
      </tbody>
    </table>

    <div class="pager" v-if="products.last_page">
      <button :disabled="!products.prev_page_url" @click="fetchData(products.current_page-1)">Prev</button>
      <span>Page {{ products.current_page }} / {{ products.last_page }}</span>
      <button :disabled="!products.next_page_url" @click="fetchData(products.current_page+1)">Next</button>
    </div>
  </div>
</template>

<script>
import ProductForm from './ProductForm.vue'
export default {
  components:{ ProductForm },
  data(){ return { products: {}, search:'', showForm:false, editing:null } },
  mounted(){ this.fetchData() },
  methods:{
    async fetchData(page=1){
      const url = new URL('/api/products', location.origin)
      url.searchParams.set('page', page)
      if(this.search) url.searchParams.set('search', this.search)
      const res = await fetch(url); this.products = await res.json()
    },
    openCreate(){ this.editing = null; this.showForm = true },
    edit(p){ this.editing = p; this.showForm = true },
    closeForm(){ this.showForm = false; this.editing = null },
    onSaved(){ this.showForm = false; this.fetchData() },
    async remove(p){
      if(!confirm('Delete product?')) return
      await fetch(`/api/products/${p.id}`, { method:'DELETE' })
      this.fetchData()
    },
    exportCsv(){ window.location = '/api/products-export' },
    formatMoney(v){ return Number(v).toFixed(2) }
  }
}
</script>

<style scoped>
.panel { padding:12px; }
.panel-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.controls { display:flex; gap:8px; align-items:center; }
.controls input { padding:8px 10px; border-radius:8px; border:1px solid #e6e9ef; min-width:220px; }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer; }
.btn.ghost { background:transparent; border:1px solid #cbd5e1; color:#111827; }
.table { width:100%; border-collapse:collapse; }
.table th, .table td { padding:10px 8px; text-align:left; border-bottom:1px solid #f3f4f6; }
.actions button { margin-right:6px; padding:6px 8px; border-radius:6px; }
.actions .danger { background:#ef4444; color:#fff; border:none; }
.muted { color:#6b7280; padding:12px 0; text-align:center }
.pager { display:flex; gap:8px; align-items:center; justify-content:center; margin-top:12px; }
</style>
