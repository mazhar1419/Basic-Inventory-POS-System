<template>
  <div class="wrap">
    <header class="topbar">
      <h1 class="brand">Inventory & POS</h1>
      <div class="actions">
        <button class="btn" @click="refreshAll">Refresh</button>
      </div>
    </header>

    <nav class="tabs">
      <button :class="{active: tab==='products'}" @click="tab='products'">Products</button>
      <button :class="{active: tab==='pos'}" @click="tab='pos'">POS</button>
      <button :class="{active: tab==='purchases'}" @click="tab='purchases'">Purchases</button>
      <button :class="{active: tab==='damages'}" @click="tab='damages'">Damage / Write-off</button>
      <button :class="{active: tab==='customers'}" @click="tab='customers'">Customers</button>
      <button :class="{active: tab==='suppliers'}" @click="tab='suppliers'">Suppliers</button>
      <button :class="{active: tab==='reports'}" @click="tab='reports'">Reports</button>
    </nav>

    <main class="main">
      <component :is="currentComponent" ref="current" @saved="onChildSaved" />
    </main>

    <footer class="footer">Built with Laravel + Vue — lightweight POS</footer>
  </div>
</template>

<script>
import ProductList from './products/ProductList.vue'
import PosDashboard from './pos/PosDashboard.vue'
import PurchaseList from './purchases/PurchaseList.vue'
import DamageList from './damages/DamageList.vue'
import CustomerList from './customers/CustomerList.vue'
import SupplierList from './suppliers/SupplierList.vue'
import ReportsPage from './reports/ReportsPage.vue'

export default {
  name: 'InventoryApp',
  components: { ProductList, PosDashboard, PurchaseList, DamageList, CustomerList, ReportsPage, SupplierList},
  data() { return { tab: 'products' } },
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
      }[this.tab]
    }
  },
  methods: {
    refreshAll() {
      // tell current child to refresh (if it has fetchData method)
      const child = this.$refs.current;
      if (child && typeof child.fetchData === 'function') child.fetchData()
    },
    onChildSaved() { this.refreshAll() }
  }
}
</script>

<style scoped>
.wrap { max-width:1100px; margin:18px auto; padding:18px; font-family:Inter, system-ui, -apple-system, 'Segoe UI', Roboto; background:#fff; box-shadow:0 6px 24px rgba(12,12,12,0.06); border-radius:10px; }
.topbar { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:12px; }
.brand { margin:0; font-size:20px; letter-spacing:0.2px; }
.actions .btn { padding:8px 12px; border-radius:8px; background:#111827; color:#fff; border:none; cursor:pointer; }
.tabs { display:flex; gap:8px; margin-bottom:12px; flex-wrap:wrap; }
.tabs button { padding:8px 12px; border-radius:8px; border:1px solid #e6e9ef; background:transparent; cursor:pointer; }
.tabs button.active { background:#111827; color:#fff; border-color:#111827; }
.main { padding:12px 0; min-height:400px; }
.footer { margin-top:18px; text-align:center; color:#6b7280; font-size:13px; }
</style>
