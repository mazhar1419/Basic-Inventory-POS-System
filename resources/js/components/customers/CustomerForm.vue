<template>
  <div class="formcard">
    <h3>{{ customer ? 'Edit Customer' : 'Create Customer' }}</h3>

    <div class="grid">
      <input v-model="form.name" placeholder="Name" />
      <input v-model="form.email" placeholder="Email" />
      <input v-model="form.phone" placeholder="Phone" />
      <textarea v-model="form.address" placeholder="Address"></textarea>
    </div>

    <div class="form-actions">
      <button class="btn" @click="submit">Save</button>
      <button class="btn ghost" @click="$emit('cancel')">Cancel</button>
    </div>

    <div v-if="errors.length" class="errors">
      <div v-for="(e, index) in errors" :key="index">• {{ e }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CustomerForm',
  props: {
    customer: { type: Object, default: null }
  },
  data() {
    return {
      form: { name: '', email: '', phone: '', address: '' },
      errors: []
    };
  },
  watch: {
    customer: {
      immediate: true,
      handler(v) {
        if (v) {
          this.form = {
            name: v.name || '',
            email: v.email || '',
            phone: v.phone || '',
            address: v.address || ''
          };
        } else {
          this.form = { name: '', email: '', phone: '', address: '' };
        }
      }
    }
  },
  methods: {
    async submit() {
      this.errors = [];
      try {
        let res;

        if (this.customer) {
          res = await fetch(`/api/customers/${this.customer.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(this.form)
          });
        } else {
          res = await fetch('/api/customers', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(this.form)
          });
        }

        if (!res.ok) {
          const j = await res.json();
          this.errors = j.errors ? Object.values(j.errors).flat() : [j.message || 'Error'];
          return;
        }

        this.$emit('saved');
      } catch (e) {
        this.errors = [e.message];
      }
    }
  }
};
</script>

<style scoped>
.formcard { border:1px solid #eef2f7; padding:12px; border-radius:10px; margin-bottom:12px; }
.grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
input, textarea { padding:8px; border-radius:8px; border:1px solid #e6e9ef; }
textarea { grid-column:1 / -1; min-height:80px; }
.form-actions { display:flex; gap:8px; margin-top:8px; }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer; }
.btn.ghost { background:transparent; border:1px solid #cbd5e1; color:#111827; }
.errors { color:#b91c1c; margin-top:8px; }
</style>
