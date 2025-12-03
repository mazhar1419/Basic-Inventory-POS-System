<template>
  <div class="wrap" role="application">
    <header class="topbar">
      <h1 class="brand">Inventory & POS</h1>

      <div class="actions">
        <button class="btn" @click="refreshAll" type="button">Refresh</button>

        <!-- LOGOUT FORM: uses hidden _token (set from meta/dataset) -->
        <!-- This form posts to server-side /logout route (session auth) -->
        <form method="POST" action="/logout" class="logout-form" @submit="onLogoutSubmit">
          <input type="hidden" name="_token" :value="csrf">
          <button type="submit" class="btn logout-btn">Logout</button>
        </form>
      </div>
    </header>

    <nav class="tabs" role="navigation" aria-label="Main tabs">
      <button :class="{active: tab==='products'}" @click="tab='products'">Products</button>
      <button :class="{active: tab==='pos'}" @click="tab='pos'">POS</button>
      <button :class="{active: tab==='purchases'}" @click="tab='purchases'">Purchases</button>
      <button :class="{active: tab==='damages'}" @click="tab='damages'">Damage / Write-off</button>
      <button :class="{active: tab==='customers'}" @click="tab='customers'">Customers</button>
      <button :class="{active: tab==='suppliers'}" @click="tab='suppliers'">Suppliers</button>
      <button :class="{active: tab==='reports'}" @click="tab='reports'">Reports</button>
    </nav>

    <main class="main" role="main" aria-live="polite">
      <!-- dynamic component; children should expose fetchData() when possible -->
      <component :is="currentComponent" ref="current" @saved="onChildSaved" />
    </main>

    <footer class="footer">Built with Laravel + Vue — lightweight POS</footer>
  </div>
</template>

<script>
/*
  InventoryApp.vue
  - Reads CSRF token from <meta name="csrf-token"> or data-csrf attribute on the <inventory-app> element
  - Exposes `refreshAll()` to call child.fetchData()
*/
import ProductList from './products/ProductList.vue'
import PosDashboard from './pos/PosDashboard.vue'
import PurchaseList from './purchases/PurchaseList.vue'
import DamageList from './damages/DamageList.vue'
import CustomerList from './customers/CustomerList.vue'
import SupplierList from './suppliers/SupplierList.vue'
import ReportsPage from './reports/ReportsPage.vue'

export default {
  name: 'InventoryApp',
  components: {
    ProductList, PosDashboard, PurchaseList, DamageList, CustomerList, ReportsPage, SupplierList
  },
  data() {
    return {
      tab: 'products',
      csrf: '' // filled in mounted()
    }
  },
  computed: {
    currentComponent() {
      return {
        products: 'ProductList',
        pos: 'PosDashboard',
        purchases: 'PurchaseList',
        damages: 'DamageList',
        customers: 'CustomerList',
        suppliers: 'SupplierList',
        reports: 'ReportsPage'
      }[this.tab] || 'ProductList'
    }
  },
  mounted() {
    // Try meta first, then fallback to data attribute on <inventory-app>
    const meta = document.querySelector('meta[name="csrf-token"]')
    if (meta && meta.getAttribute('content')) {
      this.csrf = meta.getAttribute('content')
    } else {
      // fallback: element dataset
      const el = document.querySelector('inventory-app')
      if (el && el.dataset && el.dataset.csrf) this.csrf = el.dataset.csrf
    }
  },
  methods: {
    refreshAll() {
      const child = this.$refs.current
      if (child && typeof child.fetchData === 'function') {
        try { child.fetchData() } catch (e) { console.error('child.fetchData error', e) }
      }
    },
    onChildSaved() {
      // child emitted 'saved' (e.g. after create/update)
      this.refreshAll()
    },
    onLogoutSubmit(e) {
      // fallback: ensure csrf is present before submit
      if (!this.csrf) {
        e.preventDefault()
        alert('CSRF token missing. Please reload the page.')
      }
      // otherwise let the form submit to server and log out
    }
  }
}
</script>

<style scoped>
.wrap {
  width:100%;
  max-width:1100px;
  margin:0 auto;
  padding:18px;
  font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto, "Helvetica Neue", Arial;
  background:#fff;
  box-shadow:0 6px 24px rgba(12,12,12,0.06);
  border-radius:10px;
  box-sizing:border-box;
}

/* header */
.topbar {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin-bottom:12px;
}
.brand { margin:0; font-size:20px; letter-spacing:0.2px; }

/* action buttons container */
.actions { display:flex; gap:8px; align-items:center; }

/* Buttons */
.btn {
  padding:8px 12px;
  border-radius:8px;
  background:#111827;
  color:#fff;
  border:none;
  cursor:pointer;
  font-size:14px;
}
.btn:active { transform:translateY(1px); }
.logout-btn { background:#b91c1c; }
.logout-btn:hover { background:#991b1b; }
.logout-form { display:inline; margin:0; }

/* tabs */
.tabs {
  display:flex;
  gap:8px;
  margin-bottom:12px;
  flex-wrap:wrap;
}
.tabs button {
  padding:8px 12px;
  border-radius:8px;
  border:1px solid #e6e9ef;
  background:transparent;
  cursor:pointer;
  font-size:14px;
}
.tabs button.active {
  background:#111827;
  color:#fff;
  border-color:#111827;
}

/* main and footer */
.main { padding:12px 0; min-height:520px; }
.footer { margin-top:18px; text-align:center; color:#6b7280; font-size:13px; }

/* responsive */
@media (max-width:760px) {
  .wrap { padding:12px }
  .main { min-height:420px }
  .tabs button { font-size:13px; padding:7px 10px }
}
</style>
