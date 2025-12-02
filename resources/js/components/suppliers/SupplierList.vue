<template>
  <div class="panel">
    <div class="panel-head">
      <h2>Suppliers</h2>
      <div class="controls"><button class="btn" @click="openCreate">+ New Supplier</button></div>
    </div>

    <div v-if="showForm">
      <supplier-form :supplier="editing" @saved="onSaved" @cancel="closeForm" />
    </div>

    <div class="search-row">
      <input v-model="q" @input="debounced" placeholder="Search suppliers by name, email or phone" />
    </div>

    <table class="table">
      <thead><tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Actions</th></tr></thead>
      <tbody>
        <tr v-for="s in suppliers.data || []" :key="s.id">
          <td>{{ s.id }}</td>
          <td>{{ s.name }}</td>
          <td>{{ s.phone }}</td>
          <td>{{ s.email }}</td>
          <td>
            <button class="btn" @click="edit(s)">Edit</button>
            <button class="danger" @click="del(s)">Delete</button>
          </td>
        </tr>
        <tr v-if="(suppliers.data || []).length===0"><td colspan="5" class="muted">No suppliers</td></tr>
      </tbody>
    </table>

    <div class="pager" v-if="suppliers.last_page">
      <button :disabled="!suppliers.prev_page_url" @click="fetch(suppliers.current_page-1)">Prev</button>
      <span>Page {{ suppliers.current_page }} / {{ suppliers.last_page }}</span>
      <button :disabled="!suppliers.next_page_url" @click="fetch(suppliers.current_page+1)">Next</button>
    </div>
  </div>
</template>

<script>
import SupplierForm from './SupplierForm.vue'
function debounce(fn, wait=300){ let t=null; return function(...a){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,a), wait) } }
export default {
  components:{ SupplierForm },
  data(){ return { suppliers:{}, showForm:false, editing:null, q:'', page:1 } },
  mounted(){ this.fetch() },
  created(){ this.debounced = debounce(()=>this.fetch(1), 300) },
  methods:{
    async fetch(page=1){
      this.page = page
      const url = new URL('/api/suppliers', location.origin)
      url.searchParams.set('page', page)
      if(this.q) url.searchParams.set('search', this.q)
      const res = await fetch(url); this.suppliers = await res.json()
    },
    openCreate(){ this.editing = null; this.showForm = true },
    edit(s){ this.editing = s; this.showForm = true },
    closeForm(){ this.showForm = false; this.editing = null },
    onSaved(){ this.showForm = false; this.fetch(this.page) },
    async del(s){
      if(!confirm(`Delete supplier "${s.name}"?`)) return
      const res = await fetch(`/api/suppliers/${s.id}`, { method:'DELETE' })
      if(!res.ok) return alert('Delete failed')
      this.fetch(this.page)
    }
  }
}
</script>

<style scoped>
.panel { padding:12px }
.panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px }
.search-row { margin-bottom:10px }
.search-row input { padding:8px; border-radius:8px; border:1px solid #e6e9ef; width:320px }
.table th, .table td { padding:8px; border-bottom:1px solid #f3f4f6; text-align:left }
.muted { color:#6b7280; padding:8px 0 }
.pager { display:flex; gap:8px; justify-content:center; margin-top:12px }
.btn { padding:6px 10px; border-radius:6px; background:#111827; color:#fff; border:none; cursor:pointer }
.danger { background:#ef4444; color:#fff; border:none; padding:6px 8px; border-radius:6px }
</style>
