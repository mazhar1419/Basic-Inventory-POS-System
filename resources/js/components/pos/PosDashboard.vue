<template>
  <div class="panel">

    <!-- Top Bar -->
    <div class="top">

      <!-- Search -->
      <div class="search field">
        <label>Search Products</label>
        <input
          v-model="query"
          @input="debouncedSearch"
          placeholder="Search by name or SKU"
        />
      </div>

      <!-- Customer -->
      <div class="customer field">
        <label>Select Customer</label>
        <select v-model="customerId">
          <option :value="null">-- Select customer (required) --</option>
          <option v-for="c in customers" :value="c.id" :key="c.id">
            {{ c.name }} — {{ c.phone || '—' }}
          </option>
        </select>
      </div>

      <!-- Cart Summary -->
      <div class="cart-summary">
        <div class="total">
          Grand total: <strong>{{ formatMoney(total) }}</strong>
        </div>
        <button class="btn" @click="checkout" :disabled="!cart.length">Checkout</button>
        <button class="btn ghost" @click="clearCart">Clear</button>
      </div>
    </div>

    <!-- Main Content -->
    <div class="content">

      <!-- Products -->
      <div class="products">
        <div
          v-for="p in products.data || []"
          :key="p.id"
          class="product-card"
        >
          <div class="p-head">{{ p.name }}</div>
          <div class="p-body">
            Price: {{ formatMoney(p.sell_price) }} • Stock: {{ p.stock }}
          </div>
          <div class="p-actions">
            <button
              type="button"
              @click="addToCart(p)"
              :disabled="p.stock <= 0 || willExceedStockIfAdded(p) || !customerId"
            >
              Add
            </button>
            <div v-if="!customerId" class="warn">Select a customer first</div>
            <div v-else-if="isBelowCost(p)" class="warn">Below cost</div>
            <div v-if="p.stock <= 0" class="warn">Out of stock</div>
            <div v-else-if="willExceedStockIfAdded(p)" class="warn">Not enough stock</div>
          </div>
        </div>

        <!-- Empty -->
        <div v-if="(products.data || []).length === 0" class="muted">
          No products
        </div>
      </div>

      <!-- Cart -->
      <div class="cart">
        <h3>Cart</h3>

        <div class="cart-table" v-if="cart.length">
          <div class="cart-row head">
            <div>Product</div>
            <div>Unit</div>
            <div>Qty</div>
            <div>Line</div>
            <div></div>
          </div>

          <div
            v-for="(c, i) in cart"
            :key="c.product.id"
            class="cart-row"
          >
            <div class="cell name">{{ c.product.name }}</div>

            <div class="cell unit">
              <label>Unit price</label>
              <div class="unit-val">{{ formatMoney(c.unit_price) }}</div>
            </div>

            <div class="cell qty">
              <label>Qty</label>
              <input
                type="number"
                v-model.number="c.qty"
                min="1"
                @change="onQtyChange(i)"
                style="width:72px"
              />
              <div class="stock-note">Stock: {{ c.product.stock }}</div>
            </div>

            <div class="cell line">{{ formatMoney(c.qty * c.unit_price) }}</div>

            <div class="cell actions">
              <button class="danger" @click="remove(i)">Remove</button>
            </div>
          </div>

          <div class="cart-footer">
            <div class="left-muted">Items: {{ cart.length }}</div>
            <div class="right-total">Grand: <strong>{{ formatMoney(total) }}</strong></div>
          </div>
        </div>

        <div v-else class="muted">Cart is empty</div>
      </div>
    </div>
  </div>
</template>

<script>
// Local lightweight debounce
function createDebounce(fn, wait = 300) {
  let timer = null
  return function (...args) {
    const ctx = this
    clearTimeout(timer)
    timer = setTimeout(() => fn.apply(ctx, args), wait)
  }
}

export default {
  data() {
    return {
      query: "",
      products: {},
      customers: [],
      customerId: null,
      cart: []
    }
  },
  computed: {
    // grand total
    total() {
      return this.cart.reduce((s, i) => s + Number(i.qty || 0) * Number(i.unit_price || 0), 0)
    }
  },
  created() {
    this.debouncedSearch = createDebounce(() => this.search(), 300)
  },
  mounted() {
    this.search()
    this.loadCustomers()
  },
  methods: {
    async loadCustomers() {
      try {
        const res = await fetch("/api/customers")
        const json = await res.json()
        this.customers = json.data || json || []
      } catch (e) { this.customers = [] }
    },

    async search(page = 1) {
      const url = new URL("/api/products", location.origin)
      url.searchParams.set("page", page)
      if (this.query) url.searchParams.set("search", this.query)
      const res = await fetch(url)
      this.products = await res.json()
    },

    // return true if product's sell_price is below cost_price
    isBelowCost(product) {
      return Number(product.sell_price || 0) < Number(product.cost_price || 0)
    },

    // true if adding one more would exceed stock considering current cart quantity
    willExceedStockIfAdded(product) {
      const inCart = this.cart.find(c => c.product.id === product.id)
      const currentQty = inCart ? Number(inCart.qty || 0) : 0
      return currentQty + 1 > Number(product.stock || 0)
    },

    addToCart(p) {
      // require customer first
      if (!this.customerId) {
        return alert('Please select a customer before adding items.')
      }

      // don't add if selling below cost
      if (this.isBelowCost(p)) {
        return alert(`Cannot add "${p.name}" — sell price is below cost.`)
      }

      // check stock limit
      const item = this.cart.find((c) => c.product.id === p.id)
      const currentQty = item ? Number(item.qty || 0) : 0
      if (currentQty + 1 > Number(p.stock || 0)) {
        return alert(`Cannot add more "${p.name}". Only ${p.stock} in stock.`)
      }

      if (item) {
        item.qty = currentQty + 1
      } else {
        // store unit_price explicitly (read-only in UI)
        this.cart.push({ product: p, qty: 1, unit_price: Number(p.sell_price || 0) })
      }
    },

    remove(i) { this.cart.splice(i, 1) },

    onQtyChange(index) {
      const item = this.cart[index]
      if (!item) return
      // ensure qty is at least 1
      if (!item.qty || item.qty < 1) item.qty = 1

      // clamp to stock
      const available = Number(item.product.stock || 0)
      if (Number(item.qty) > available) {
        // clamp and inform user
        item.qty = available
        alert(`Quantity for "${item.product.name}" adjusted to available stock: ${available}`)
      }
    },

    clearCart() {
      if (confirm("Clear cart?")) this.cart = []
    },

    formatMoney(v) {
      return Number(v || 0).toFixed(2)
    },

    async checkout() {
      if (!this.cart.length) return alert("Cart empty")

      // require customer
      if (!this.customerId) return alert('Please select a customer before checkout.')

      // Validation: no item unit_price below product.cost_price
      const below = this.cart.filter(c => Number(c.unit_price) < Number(c.product.cost_price || 0))
      if (below.length) {
        const names = below.map(b => b.product.name).join(", ")
        return alert(`Cannot checkout. The following items would be sold below cost: ${names}`)
      }

      // Validation: qty does not exceed stock
      const over = this.cart.filter(c => Number(c.qty) > Number(c.product.stock || 0))
      if (over.length) {
        const names = over.map(b => `${b.product.name} (max ${b.product.stock})`).join(", ")
        return alert(`Cannot checkout. The following items exceed stock: ${names}`)
      }

      // Build payload
      const items = this.cart.map(c => ({
        product_id: c.product.id,
        qty: c.qty,
        unit_price: c.unit_price
      }))

      // Post sale
      try {
        const res = await fetch("/api/sales", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ customer_id: this.customerId, items, paid: this.total })
        })

        if (!res.ok) {
          const j = await res.json().catch(() => ({ message: "Unknown" }))
          return alert(j.message || "Failed to record sale")
        }

        const sale = await res.json()
        // success: clear cart and refresh products
        this.cart = []
        this.search()

        // open printable invoice using sale data (backend returns sale with items/customer)
        this.openInvoiceWindow(sale)

      } catch (e) {
        console.error("Checkout failed", e)
        alert("Checkout failed")
      }
    },

    // Opens a new window and writes a printable invoice for the sale object returned by the API.
    openInvoiceWindow(sale) {
      const w = window.open("", "_blank", "width=800,height=900,scrollbars=yes")
      if (!w) { alert("Popup blocked — allow popups for this site to print invoice."); return }

      const saleDate = new Date(sale.created_at || Date.now()).toLocaleString()
      const customer = sale.customer || { name: "Walk-in", phone: "" }

      // Build rows
      const rows = (sale.items || []).map(it => {
        const name = it.product?.name || it.product_name || ("Product #" + it.product_id)
        const unit = Number(it.unit_price).toFixed(2)
        const qty = Number(it.qty)
        const line = (qty * Number(it.unit_price)).toFixed(2)
        return `<tr>
          <td style="padding:6px 8px;border:1px solid #ddd">${name}</td>
          <td style="padding:6px 8px;border:1px solid #ddd;text-align:right">${unit}</td>
          <td style="padding:6px 8px;border:1px solid #ddd;text-align:right">${qty}</td>
          <td style="padding:6px 8px;border:1px solid #ddd;text-align:right">${line}</td>
        </tr>`
      }).join("")

      const html = `
        <html>
        <head>
          <title>Invoice #${sale.id || ''}</title>
          <style>
            body{font-family: Arial, Helvetica, sans-serif; padding:20px; color:#111}
            .header{display:flex; justify-content:space-between; align-items:center}
            h1{margin:0}
            table{border-collapse:collapse; width:100%; margin-top:12px}
            th{background:#f3f4f6; padding:8px; text-align:left; border:1px solid #ddd}
            .totals{margin-top:12px; float:right; border:1px solid #ddd; padding:8px; width:260px}
            .note{margin-top:20px; color:#666}
            @media print { .no-print{ display:none } }
          </style>
        </head>
        <body>
          <div class="header">
            <div>
              <h1>Invoice</h1>
              <div>Invoice #: ${sale.id || ''}</div>
              <div>Date: ${saleDate}</div>
            </div>
            <div>
              <strong>Customer</strong>
              <div>${customer.name || 'Walk-in'}</div>
              <div>${customer.phone || ''}</div>
            </div>
          </div>

          <table>
            <thead>
              <tr>
                <th>Product</th>
                <th style="text-align:right">Unit</th>
                <th style="text-align:right">Qty</th>
                <th style="text-align:right">Line</th>
              </tr>
            </thead>
            <tbody>
              ${rows}
            </tbody>
          </table>

          <div class="totals">
            <div style="display:flex; justify-content:space-between"><div>Subtotal</div><div>${Number(sale.total || this.total).toFixed(2)}</div></div>
            <div style="display:flex; justify-content:space-between"><div>Paid</div><div>${Number(sale.paid || sale.total || this.total).toFixed(2)}</div></div>
            <div style="display:flex; justify-content:space-between; font-weight:bold; margin-top:8px"><div>Grand Total</div><div>${Number(sale.total || this.total).toFixed(2)}</div></div>
          </div>

          <div style="clear:both"></div>

          <div class="note">Thank you for your purchase.</div>

          <div style="margin-top:20px" class="no-print">
            <button onclick="window.print()">Print</button>
            <button onclick="window.close()">Close</button>
          </div>
        </body>
        </html>
      `

      w.document.open()
      w.document.write(html)
      w.document.close()
    }
  }
}
</script>

<style scoped>
.panel { padding:12px; }

.top {
  display:flex;
  justify-content:space-between;
  gap:12px;
  margin-bottom:12px;
  align-items:flex-end;
}

.field {
  display:flex;
  flex-direction:column;
  gap:4px;
  min-width:200px;
}

label {
  font-size:13px;
  font-weight:600;
  color:#374151;
}

.search input,
.customer select {
  padding:8px;
  border-radius:8px;
  border:1px solid #e6e9ef;
}

.cart-summary {
  display:flex;
  align-items:center;
  gap:8px;
}

.content { display:flex; gap:12px; }

.products {
  flex:2;
  display:grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap:10px;
}

.product-card {
  border:1px solid #eef2f7;
  padding:10px;
  border-radius:8px;
  display:flex;
  flex-direction:column;
  gap:6px;
}

.p-actions {
  display:flex;
  gap:8px;
  align-items:center;
}
.p-actions button { padding:6px 8px; border-radius:6px; }
.warn { color:#b91c1c; font-weight:600; font-size:12px }

.cart {
  flex:1;
  border-left:1px dashed #e6e9ef;
  padding-left:12px;
}

.cart-table { width:100% }
.cart-row { display:grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap:8px; align-items:center; padding:8px 0; border-bottom:1px solid #f3f4f6 }
.cart-row.head { font-weight:700; border-bottom:2px solid #e6e9ef; }
.cell { padding:6px 0 }
.cell.unit label, .cell.qty label { display:block; font-size:11px; color:#6b7280 }
.unit-val { font-weight:600 }
.stock-note { font-size:11px; color:#6b7280; margin-top:4px }

.cart-footer { display:flex; justify-content:space-between; margin-top:8px; padding-top:8px; border-top:1px dashed #e6e9ef }

.muted { color:#6b7280; padding:8px 0 }
.btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer; }
.btn.ghost { background:transparent; border:1px solid #cbd5e1; color:#111827; }
.danger { background:#ef4444; color:#fff; border:none; padding:6px 8px; border-radius:6px; }

</style>
