<template>
  <div class="formcard">
    <h3>{{ purchaseId ? 'Edit Purchase' : 'Create Purchase' }}</h3>

    <div class="grid">
      <!-- Supplier select (mandatory) -->
      <div class="field">
        <label for="supplier-select">Supplier <span class="required">*</span></label>
        <select id="supplier-select" v-model.number="supplier_id">
          <option :value="null">Select supplier</option>
          <option v-for="s in suppliers" :key="s.id" :value="s.id">
            {{ s.name }} — {{ s.phone || '—' }}
          </option>
        </select>
      </div>

      <!-- Product select / line inputs -->
      <div class="product-select">
        <label>Add product</label>
        <div style="display:flex;gap:8px;align-items:center">
          <div style="display:flex;flex-direction:column">
            <label for="product-select-input" class="small-label">Product</label>
            <select id="product-select-input" v-model.number="selectedId">
              <option :value="null">Select product to add</option>
              <option v-for="p in products" :key="p.id" :value="p.id">
                {{ p.name }} — stock: {{ p.stock }}
              </option>
            </select>
          </div>

          <div style="display:flex;flex-direction:column">
            <label for="line-qty-input" class="small-label">Qty</label>
            <input id="line-qty-input" v-model.number="lineQty" type="number" min="1" placeholder="Qty" style="width:80px" />
          </div>

          <div style="display:flex;flex-direction:column">
            <label for="line-cost-input" class="small-label">Unit cost</label>
            <input id="line-cost-input" v-model.number="lineCost" type="number" step="0.01" min="0" placeholder="Unit cost" style="width:120px" />
          </div>

          <div style="display:flex;align-items:flex-end">
            <button type="button" class="btn" @click="addLine">Add</button>
          </div>
        </div>
      </div>

      <!-- Lines -->
      <div class="lines" v-if="lines.length">
        <h4>Purchase Lines</h4>
        <div v-for="(l,i) in lines" :key="i" class="line">
          <div class="line-left">{{ getProductName(l.product_id) }}</div>
          <div class="line-mid">Qty: {{ l.qty }}</div>
          <div class="line-mid">Unit: {{ formatMoney(l.unit_cost) }}</div>
          <div class="line-mid">Total: {{ formatMoney(l.qty * l.unit_cost) }}</div>
          <div class="line-right"><button type="button" class="danger" @click="removeLine(i)">Remove</button></div>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <button type="button" class="btn" @click="submit">Save Purchase</button>
      <button type="button" class="btn ghost" @click="$emit('cancel')">Cancel</button>
      <div class="muted">Total: {{ formatMoney(total) }}</div>
    </div>

    <div v-if="errors.length" class="errors">
      <div v-for="(e, idx) in errors" :key="idx">• {{ e }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PurchaseForm',
  props: { purchaseId: { type: Number, default: null } }, // optional
  data(){
    return {
      supplier_id: null,
      supplier: '',
      products: [],
      suppliers: [],
      selectedId: null,
      lineQty: 1,
      lineCost: 0,
      lines: [],
      errors: []
    }
  },
  computed: {
    total(){
      const ls = Array.isArray(this.lines) ? this.lines : []
      return ls.reduce((s,l) => s + (Number(l.qty || 0) * Number(l.unit_cost || 0)), 0)
    }
  },
  mounted(){
    this.loadProducts()
    this.loadSuppliers()
  },
  methods: {
    async safeJson(res){
      const contentType = res.headers.get('content-type') || ''
      if (!res.ok) {
        if (contentType.includes('application/json')) {
          const j = await res.json().catch(()=>null)
          return { ok:false, json:j }
        }
        const text = await res.text().catch(()=>null)
        return { ok:false, text }
      }
      if (contentType.includes('application/json')) {
        const j = await res.json().catch(()=>null)
        return { ok:true, json:j }
      }
      const text = await res.text().catch(()=>null)
      return { ok:true, text }
    },

    async loadProducts(){
      try {
        const res = await fetch('/api/products?per_page=9999')
        const out = await this.safeJson(res)
        if (!out.ok) { this.products = []; return }
        this.products = Array.isArray(out.json) ? out.json : (out.json?.data || [])
      } catch(e){ this.products = [] }
    },

    async loadSuppliers(){
      try {
        const res = await fetch('/api/suppliers?per_page=9999')
        const out = await this.safeJson(res)
        if (!out.ok) { this.suppliers = []; return }
        this.suppliers = Array.isArray(out.json) ? out.json : (out.json?.data || [])
      } catch(e){ this.suppliers = [] }
    },

    // PURCHASE: do NOT restrict by stock — purchases increase stock.
    addLine(){
      this.errors = []
      if(!this.selectedId) {
        this.errors.push('Select product to add')
        return
      }
      if(!this.lineQty || this.lineQty <= 0) {
        this.errors.push('Invalid quantity')
        return
      }

      this.lines.push({
        product_id: this.selectedId,
        qty: Number(this.lineQty),
        unit_cost: Number(this.lineCost || 0)
      })

      // reset inputs
      this.lineQty = 1
      this.lineCost = 0
      this.selectedId = null
    },

    removeLine(i){ this.lines.splice(i,1) },

    getProductName(id){ const p = this.products.find(x=>x.id===id); return p ? p.name : '—' },

    formatMoney(v){ return Number(v || 0).toFixed(2) },

    async submit(){
      this.errors = []
      if (!this.supplier_id) { this.errors.push('Supplier is required'); return }
      if (!this.lines.length) { this.errors.push('Add at least one line'); return }

      try {
        const res = await fetch('/api/purchases', {
          method:'POST',
          headers:{ 'Content-Type':'application/json' },
          body: JSON.stringify({ supplier_id: this.supplier_id, items: this.lines })
        })
        const out = await this.safeJson(res)
        if (!out.ok) {
          const j = out.json
          if (j && j.errors) this.errors = Object.values(j.errors).flat()
          else if (j && j.message) this.errors = [j.message]
          else this.errors = [out.text || 'Server error']
          return
        }
        this.$emit('saved')
      } catch(e){ this.errors = [e.message || String(e)] }
    }
  }
}
</script>

<style scoped>
.formcard { border:1px solid #eef2f7; padding:12px; border-radius:10px; margin-bottom:12px }
.grid { display:flex; flex-direction:column; gap:8px }
.product-select { display:flex; gap:8px; align-items:center }
.field { display:flex; flex-direction:column; gap:4px }
.required { color:#b91c1c; margin-left:4px }
.lines { margin-top:8px; border-top:1px dashed #eef2f7; padding-top:8px; display:flex; flex-direction:column; gap:6px }
.line { display:flex; gap:10px; align-items:center; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f3f4f6 }
.form-actions { display:flex; align-items:center; gap:12px; margin-top:8px }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer }
.btn.ghost { background:transparent; border:1px solid #cbd5e1; color:#111827 }
.danger { background:#ef4444; color:#fff; border:none; padding:6px 8px; border-radius:6px }
.muted { color:#374151; margin-left:auto }
.errors { color:#b91c1c; margin-top:8px }
.small-label { font-size:12px; color:#6b7280; margin-bottom:4px }
</style>
