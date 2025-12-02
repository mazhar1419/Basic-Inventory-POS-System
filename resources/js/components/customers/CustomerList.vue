<template>
  <div class="panel">
    <div class="panel-head">
      <h2>Customers</h2>
      <div class="controls">
        <input v-model="search" @keyup.enter="fetchData" placeholder="Search by name, email or phone" />
        <button class="btn" @click="openCreate">+ New Customer</button>
        <button class="btn ghost" @click="exportCsv">Export CSV</button>
      </div>
    </div>

    <div v-if="showForm">
      <customer-form :customer="editing" @saved="onSaved" @cancel="closeForm" />
    </div>

    <table class="table">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th></th></tr></thead>
      <tbody>
        <tr v-for="c in customers.data || []" :key="c.id">
          <td>{{ c.name }}</td>
          <td>{{ c.email }}</td>
          <td>{{ c.phone }}</td>
          <td class="actions">
            <button @click="edit(c)">Edit</button>
            <button class="danger" @click="remove(c)">Delete</button>
          </td>
        </tr>
        <tr v-if="(customers.data||[]).length===0"><td colspan="4" class="muted">No customers</td></tr>
      </tbody>
    </table>

    <div class="pager" v-if="customers.last_page">
      <button :disabled="!customers.prev_page_url" @click="fetchData(customers.current_page-1)">Prev</button>
      <span>Page {{ customers.current_page }} / {{ customers.last_page }}</span>
      <button :disabled="!customers.next_page_url" @click="fetchData(customers.current_page+1)">Next</button>
    </div>
  </div>
</template>

<script>
import CustomerForm from './CustomerForm.vue'
export default {
  components:{ CustomerForm },
  data(){ return { customers:{}, search:'', showForm:false, editing:null } },
  mounted(){ this.fetchData() },
  methods:{
    async fetchData(page=1){
      const url = new URL('/api/customers', location.origin)
      url.searchParams.set('page', page)
      if(this.search) url.searchParams.set('search', this.search)
      const res = await fetch(url); this.customers = await res.json()
    },
    openCreate(){ this.editing=null; this.showForm=true },
    edit(c){ this.editing=c; this.showForm=true },
    closeForm(){ this.showForm=false; this.editing=null },
    onSaved(){ this.closeForm(); this.fetchData() },
    async remove(c){ if(!confirm('Delete customer?')) return; await fetch(`/api/customers/${c.id}`, { method:'DELETE' }); this.fetchData() },
    exportCsv(){ window.location = '/api/customers-export' }
  }
}
</script>

<style scoped>
.panel { padding:12px }
.panel-head { display:flex; justify-content:space-between; margin-bottom:12px; align-items:center }
.controls { display:flex; gap:8px; align-items:center }
.controls input { padding:8px; border-radius:8px; border:1px solid #e6e9ef; }
.table th, .table td { padding:10px 8px; border-bottom:1px solid #f3f4f6 }
.actions button { margin-right:6px }
.muted { color:#6b7280; text-align:center; padding:12px 0 }
.pager { display:flex; gap:8px; justify-content:center; margin-top:12px }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer }
.btn.ghost { background:transparent; border:1px solid #cbd5e1; color:#111827 }
.danger { background:#ef4444; color:#fff; border:none; padding:6px 8px; border-radius:6px }
</style>
