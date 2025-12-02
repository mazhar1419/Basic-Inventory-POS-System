<template>
  <div class="formcard">
    <h3>Write-off / Damage</h3>
    <div class="grid">
      <select v-model.number="product_id">
        <option :value="null">Select product</option>
        <option v-for="p in products" :value="p.id" :key="p.id">{{ p.name }} — stock: {{ p.stock }}</option>
      </select>
      <input v-model.number="qty" type="number" min="1" placeholder="Quantity" />
      <textarea v-model="reason" placeholder="Reason (optional)"></textarea>
    </div>

    <div class="form-actions">
      <button class="btn" @click="submit">Save</button>
      <button class="btn ghost" @click="$emit('cancel')">Cancel</button>
    </div>

    <div v-if="errors.length" class="errors">
      <div v-for="e in errors" :key="e">• {{ e }}</div>
    </div>
  </div>
</template>

<script>
export default {
  data(){ return { products:[], product_id:null, qty:1, reason:'', errors:[] } },
  mounted(){ this.loadProducts() },
  methods:{
    async loadProducts(){ const res = await fetch('/api/products'); const json = await res.json(); this.products = json.data || json },
    async submit(){
      this.errors = []
      try{
        const res = await fetch('/api/damages', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ product_id:this.product_id, qty:this.qty, reason:this.reason }) })
        if(!res.ok){ const j = await res.json(); this.errors = j.errors ? Object.values(j.errors).flat() : [j.message || 'Error']; return }
        this.$emit('saved')
      } catch(e){ this.errors = [e.message] }
    }
  }
}
</script>

<style scoped>
.formcard { border:1px solid #eef2f7; padding:12px; border-radius:10px; margin-bottom:12px }
.grid { display:flex; flex-direction:column; gap:8px }
select, input, textarea { padding:8px; border-radius:8px; border:1px solid #e6e9ef }
textarea { min-height:80px }
.form-actions { display:flex; gap:8px; margin-top:8px }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer }
.btn.ghost{ background:transparent; border:1px solid #cbd5e1; color:#111827 }
.errors { color:#b91c1c; margin-top:8px }
</style>
