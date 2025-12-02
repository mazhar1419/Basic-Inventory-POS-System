<template>
  <div class="panel">
    <div class="panel-head">
      <h2>Damage / Write-off</h2>
      <div class="controls">
        <button class="btn" @click="openCreate">Add Write-off</button>
      </div>
    </div>

    <div v-if="showForm">
      <damage-form @saved="onSaved" @cancel="closeForm" />
    </div>

    <table class="table">
      <thead><tr><th>Product</th><th>Qty</th><th>Reason</th><th>Date</th></tr></thead>
      <tbody>
        <tr v-for="d in damages.data || []" :key="d.id">
          <td>{{ d.product.name }}</td>
          <td>{{ d.qty }}</td>
          <td>{{ d.reason }}</td>
          <td>{{ d.created_at }}</td>
        </tr>
        <tr v-if="(damages.data||[]).length===0"><td colspan="4" class="muted">No write-offs</td></tr>
      </tbody>
    </table>

    <div class="pager" v-if="damages.last_page">
      <button :disabled="!damages.prev_page_url" @click="fetchData(damages.current_page-1)">Prev</button>
      <span>Page {{ damages.current_page }} / {{ damages.last_page }}</span>
      <button :disabled="!damages.next_page_url" @click="fetchData(damages.current_page+1)">Next</button>
    </div>
  </div>
</template>

<script>
import DamageForm from './DamageForm.vue'
export default {
  components:{ DamageForm },
  data(){ return { damages:{}, showForm:false } },
  mounted(){ this.fetchData() },
  methods:{
    async fetchData(page=1){
      const url = new URL('/api/damages', location.origin); url.searchParams.set('page', page)
      const res = await fetch(url); this.damages = await res.json()
    },
    openCreate(){ this.showForm = true },
    closeForm(){ this.showForm = false },
    onSaved(){ this.showForm = false; this.fetchData() }
  }
}
</script>

<style scoped>
.panel { padding:12px }
.panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px }
.table th, .table td { padding:10px 8px; border-bottom:1px solid #f3f4f6 }
.muted { color:#6b7280; text-align:center; padding:8px 0 }
.pager { display:flex; gap:8px; justify-content:center; margin-top:12px }
</style>
