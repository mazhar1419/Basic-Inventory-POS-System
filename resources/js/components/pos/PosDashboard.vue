<template>
  <div class="panel">
    <div class="top">
      <div class="search">
        <input v-model="query" @input="debouncedSearch" placeholder="Search product by name or SKU" />
      </div>
      <div class="cart-summary">
        <div class="total">Total: <strong>{{ formatMoney(total) }}</strong></div>
        <button class="btn" @click="checkout" :disabled="!cart.length">Checkout</button>
        <button class="btn ghost" @click="clearCart">Clear</button>
      </div>
    </div>

    <div class="content">
      <div class="products">
        <div v-for="p in products.data || []" :key="p.id" class="product-card">
          <div class="p-head">{{ p.name }}</div>
          <div class="p-body">Price: {{ formatMoney(p.sell_price) }} • Stock: {{ p.stock }}</div>
          <div class="p-actions">
            <button @click="addToCart(p)">Add</button>
          </div>
        </div>
        <div v-if="(products.data||[]).length===0" class="muted">No products</div>
      </div>

      <div class="cart">
        <h3>Cart</h3>
        <div v-for="(c,i) in cart" :key="c.product.id" class="cart-row">
          <div>{{ c.product.name }}</div>
          <div>
            <input type="number" v-model.number="c.qty" min="1" style="width:72px" />
          </div>
          <div>{{ formatMoney(c.qty * c.product.sell_price) }}</div>
          <div><button class="danger" @click="remove(i)">Remove</button></div>
        </div>
        <div v-if="!cart.length" class="muted">Cart is empty</div>
      </div>
    </div>
  </div>
</template>

<script>
// Local lightweight debounce — avoids adding lodash
function createDebounce(fn, wait = 300) {
  let timer = null
  return function (...args) {
    const ctx = this
    clearTimeout(timer)
    timer = setTimeout(() => fn.apply(ctx, args), wait)
  }
}

export default {
  data() { return { query: '', products: {}, cart: [] } },
  computed: {
    total() { return this.cart.reduce((s, i) => s + (i.qty * i.product.sell_price), 0) }
  },
  mounted() { this.search() },
  created() {
    // create debouncedSearch bound to component instance
    this.debouncedSearch = createDebounce(() => { this.search() }, 300)
  },
  methods: {
    async search(page = 1) {
      const url = new URL('/api/products', location.origin)
      url.searchParams.set('page', page)
      if (this.query) url.searchParams.set('search', this.query)
      const res = await fetch(url); this.products = await res.json()
    },
    addToCart(p) {
      const item = this.cart.find(c => c.product.id === p.id)
      if (item) item.qty++
      else this.cart.push({ product: p, qty: 1 })
    },
    remove(i) { this.cart.splice(i, 1) },
    clearCart() { if (confirm('Clear cart?')) this.cart = [] },
    formatMoney(v) { return Number(v).toFixed(2) },
    async checkout() {
      if (!this.cart.length) return alert('Cart empty')
      const items = this.cart.map(c => ({ product_id: c.product.id, qty: c.qty, unit_price: c.product.sell_price }))
      const res = await fetch('/api/sales', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ items, paid: this.total }) })
      if (res.ok) { alert('Sale recorded'); this.cart = []; this.search() } else { const j = await res.json(); alert(j.message || 'Error') }
    }
  }
}
</script>

<style scoped>
.panel { padding:12px; }
.top { display:flex; justify-content:space-between; gap:12px; margin-bottom:12px; align-items:center }
.search input { padding:8px; border-radius:8px; border:1px solid #e6e9ef; min-width:280px }
.cart-summary { display:flex; align-items:center; gap:8px }
.content { display:flex; gap:12px; }
.products { flex:2; display:grid; grid-template-columns: repeat(auto-fill,minmax(180px,1fr)); gap:10px; }
.product-card { border:1px solid #eef2f7; padding:10px; border-radius:8px; display:flex; flex-direction:column; gap:6px; }
.p-actions button { padding:6px 8px; border-radius:6px; }
.cart { flex:1; border-left:1px dashed #e6e9ef; padding-left:12px }
.cart-row { display:flex; justify-content:space-between; gap:8px; align-items:center; padding:6px 0; border-bottom:1px solid #f3f4f6 }
.muted { color:#6b7280; padding:8px 0 }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer; }
.btn.ghost { background:transparent; border:1px solid #cbd5e1; color:#111827; }
.danger { background:#ef4444; color:#fff; border:none; padding:6px 8px; border-radius:6px; }
</style>
