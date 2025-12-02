<template>
  <div class="formcard">
    <h3>{{ supplier ? 'Edit Supplier' : 'Create Supplier' }}</h3>
    <div class="grid">
      <div class="field"><label>Name</label><input v-model="form.name" placeholder="Supplier name" /></div>
      <div class="field"><label>Email</label><input v-model="form.email" placeholder="Email" /></div>
      <div class="field"><label>Phone</label><input v-model="form.phone" placeholder="Phone" /></div>
      <div class="field textarea-field"><label>Address</label><textarea v-model="form.address" placeholder="Address"></textarea></div>
      <div class="field textarea-field"><label>Notes</label><textarea v-model="form.notes" placeholder="Notes (optional)"></textarea></div>
    </div>

    <div class="form-actions">
      <button class="btn" @click="submit">Save</button>
      <button class="btn ghost" @click="$emit('cancel')">Cancel</button>
    </div>

    <div v-if="errors.length" class="errors">
      <div v-for="(e,i) in errors" :key="i">• {{ e }}</div>
    </div>
  </div>
</template>

<script>
export default {
  props: { supplier: { type: Object, default: null } },
  data() {
    return {
      form: { name:'', email:'', phone:'', address:'', notes:'' },
      errors: []
    }
  },
  watch: {
    supplier:{ immediate:true, handler(v){
      if(v) this.form = { name:v.name||'', email:v.email||'', phone:v.phone||'', address:v.address||'', notes:v.notes||'' }
      else this.form = { name:'', email:'', phone:'', address:'', notes:'' }
    }}
  },
  methods:{
    async submit(){
      this.errors = []
      try{
        let res
        if(this.supplier && this.supplier.id){
          res = await fetch(`/api/suppliers/${this.supplier.id}`, { method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify(this.form) })
        } else {
          res = await fetch('/api/suppliers', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(this.form) })
        }
        if(!res.ok){ const j = await res.json().catch(()=>({ message:'Error' })); this.errors = j.errors ? Object.values(j.errors).flat() : [j.message||'Error']; return }
        this.$emit('saved')
      } catch(e){
        this.errors = [e.message || String(e)]
      }
    }
  }
}
</script>

<style scoped>
.formcard { border:1px solid #eef2f7; padding:12px; border-radius:10px; background:#fff; }
.grid { display:grid; grid-template-columns: repeat(2, 1fr); gap:12px; }
.field { display:flex; flex-direction:column; gap:6px }
label { font-weight:600; color:#374151; font-size:13px }
input, textarea { padding:8px; border-radius:8px; border:1px solid #e6e9ef }
.textarea-field { grid-column: 1 / -1; }
.form-actions { margin-top:10px; display:flex; gap:8px }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer }
.btn.ghost { background:transparent; border:1px solid #cbd5e1; color:#111827 }
.errors { color:#b91c1c; margin-top:8px }
</style>
