<template>
  <div class="panel">
    <div class="panel-head">
      <h2>Purchases</h2>
      <div class="controls">
        <button class="btn" @click="openCreate">+ New Purchase</button>
      </div>
    </div>

    <div v-if="showForm">
      <purchase-form @saved="onSaved" @cancel="closeForm" />
    </div>

    <table class="table">
      <thead><tr><th>ID</th><th>Supplier</th><th>Total</th><th>Date</th></tr></thead>
    <tbody>
  <tr v-for="p in purchases.data || []" :key="p.id">
    <td>{{ p.id }}</td>
    <td>{{ (p.supplier && p.supplier.name) ? p.supplier.name : (p.supplier || '—') }}</td>
    <td>{{ formatMoney(p.total) }}</td>
    <td>{{ p.created_at }}</td>
  </tr>
  <tr v-if="(purchases.data||[]).length===0"><td colspan="4" class="muted">No purchases</td></tr>
</tbody>

    </table>

    <div class="pager" v-if="purchases.last_page">
      <button :disabled="!purchases.prev_page_url" @click="fetchData(purchases.current_page-1)">Prev</button>
      <span>Page {{ purchases.current_page }} / {{ purchases.last_page }}</span>
      <button :disabled="!purchases.next_page_url" @click="fetchData(purchases.current_page+1)">Next</button>
    </div>
  </div>
</template>

<script>
import PurchaseForm from './PurchaseForm.vue'
export default {
  components:{ PurchaseForm },
  data(){ return { purchases:{}, showForm:false } },
  mounted(){ this.fetchData() },
  methods:{
  async fetchData(page=1){
  const url = new URL('/api/purchases', location.origin);
  url.searchParams.set('page', page);
  try {
    const res = await fetch(url);
    const ct = res.headers.get('content-type') || '';
    if (!res.ok) {
      // try parse json error
      if (ct.includes('application/json')) {
        const j = await res.json().catch(()=>null);
        throw new Error(j?.message || 'Server error');
      } else {
        const t = await res.text().catch(()=>null);
        throw new Error('Server error: ' + (t ? t.substring(0,200) : 'unknown'));
      }
    }
    this.purchases = ct.includes('application/json') ? await res.json() : {};
  } catch (e) {
    console.error('Failed to load purchases', e);
    this.purchases = { data: [], current_page:1, last_page:1 };
    alert('Failed to load purchases: ' + e.message);
  }
},

    openCreate(){ this.showForm = true },
    closeForm(){ this.showForm = false },
    onSaved(){ this.showForm = false; this.fetchData() },
    formatMoney(v){ return Number(v).toFixed(2) }
  }
}
</script>

<style scoped>
.panel { padding:12px }
.panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px }
.controls .btn { padding:8px 12px }
.table th, .table td { padding:10px 8px; border-bottom:1px solid #f3f4f6 }
.muted { color:#6b7280; text-align:center; padding:8px 0 }
.pager { display:flex; gap:8px; justify-content:center; margin-top:12px }
</style>
