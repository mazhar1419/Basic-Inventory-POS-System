<template>
  <div class="formcard">
    <h3>Create Purchase</h3>
    <div class="grid">
      <input v-model="supplier" placeholder="Supplier name (optional)" />
      <div class="product-select">
        <select v-model.number="selectedId">
          <option :value="null">Select product to add</option>
          <option v-for="p in products" :value="p.id" :key="p.id">{{ p.name }} — stock: {{ p.stock }}</option>
        </select>
        <input v-model.number="lineQty" type="number" min="1" placeholder="Qty" style="width:80px" />
        <input v-model.number="lineCost" type="number" step="0.01" min="0" placeholder="Unit cost" style="width:120px" />
        <button class="btn" @click="addLine">Add</button>
      </div>

      <div class="lines" v-if="lines.length">
        <div v-for="(l,i) in lines" :key="i" class="line">
          <div>{{ getProductName(l.product_id) }}</div>
          <div>Qty: {{ l.qty }}</div>
          <div>Unit: {{ formatMoney(l.unit_cost) }}</div>
          <div>Total: {{ formatMoney(l.qty * l.unit_cost) }}</div>
          <div><button class="danger" @click="removeLine(i)">Remove</button></div>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <button class="btn" @click="submit">Save Purchase</button>
      <button class="btn ghost" @click="$emit('cancel')">Cancel</button>
      <div class="muted">Total: {{ formatMoney(total) }}</div>
    </div>

    <div v-if="errors.length" class="errors">
      <div v-for="e in errors" :key="e">• {{ e }}</div>
    </div>
  </div>
</template>

<script>
export default {
  data(){ return { supplier:'', products:[], selectedId:null, lineQty:1, lineCost:0, lines:[], errors:[] } },
  mounted(){ this.loadProducts() },
  methods:{
    async loadProducts(){ const res = await fetch('/api/products'); const json = await res.json(); this.products = json.data || json },
    addLine(){
      if(!this.selectedId || this.lineQty<=0) return alert('Select product and qty')
      this.lines.push({ product_id: this.selectedId, qty: this.lineQty, unit_cost: this.lineCost })
      this.lineQty = 1; this.lineCost = 0; this.selectedId = null
    },
    removeLine(i){ this.lines.splice(i,1) },
    getProductName(id){ const p = this.products.find(x=>x.id===id); return p ? p.name : '—' },
    formatMoney(v){ return Number(v).toFixed(2) },
    get total(){ return this.lines.reduce((s,l)=> s + (l.qty * l.unit_cost), 0) },
    async submit(){
      if(!this.lines.length) return alert('Add lines')
      try{
        const res = await fetch('/api/purchases', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ supplier: this.supplier, items: this.lines }) })
        if(!res.ok){ const j=await res.json(); this.errors = j.errors ? Object.values(j.errors).flat() : [j.message || 'Error']; return }
        this.$emit('saved')
      } catch(e){ this.errors = [e.message] }
    }
  }
}
</script>

<style scoped>
.formcard { border:1px solid #eef2f7; padding:12px; border-radius:10px; margin-bottom:12px }
.grid { display:flex; flex-direction:column; gap:8px }
.product-select { display:flex; gap:8px; align-items:center }
.lines { margin-top:8px; border-top:1px dashed #eef2f7; padding-top:8px; display:flex; flex-direction:column; gap:6px }
.line { display:flex; gap:10px; align-items:center; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6 }
.form-actions { display:flex; align-items:center; gap:12px; margin-top:8px }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer }
.btn.ghost { background:transparent; border:1px solid #cbd5e1; color:#111827 }
.danger { background:#ef4444; color:#fff; border:none; padding:6px 8px; border-radius:6px }
.muted { color:#374151; margin-left:auto }
.errors { color:#b91c1c; margin-top:8px }
</style>
