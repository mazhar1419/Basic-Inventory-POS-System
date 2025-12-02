<template>
  <div class="formcard">
    <h3>{{ product ? 'Edit Product' : 'Create Product' }}</h3>

    <div class="grid">
      <div class="field">
        <label>Name</label>
        <input v-model="form.name" placeholder="Product name" />
      </div>

      <div class="field">
        <label>SKU</label>
        <input v-model="form.sku" placeholder="Optional SKU" />
      </div>

      <div class="field">
        <label>Stock</label>
        <input
          v-model.number="form.stock"
          type="number"
          min="0"
          placeholder="0"
        />
      </div>

      <div class="field">
        <label>Cost Price</label>
        <input
          v-model.number="form.cost_price"
          type="number"
          step="0.01"
          min="0"
          placeholder="0.00"
        />
      </div>

      <div class="field">
        <label>Selling Price</label>
        <input
          v-model.number="form.sell_price"
          type="number"
          step="0.01"
          min="0"
          placeholder="0.00"
        />
      </div>

      <div class="field textarea-field">
        <label>Description</label>
        <textarea
          v-model="form.description"
          placeholder="Product description (optional)"
        ></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button class="btn" @click="submit">Save</button>
      <button class="btn ghost" @click="$emit('cancel')">Cancel</button>
    </div>

    <div v-if="errors.length" class="errors">
      <div v-for="(e, i) in errors" :key="i">• {{ e }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ProductForm",
  props: {
    product: { type: Object, default: null },
  },
  data() {
    return {
      form: {
        name: "",
        sku: "",
        stock: 0,
        cost_price: 0,
        sell_price: 0,
        description: "",
      },
      errors: [],
    };
  },
  watch: {
    product: {
      immediate: true,
      handler(v) {
        if (v) {
          this.form = {
            name: v.name ?? "",
            sku: v.sku ?? "",
            stock: Number(v.stock ?? 0),
            cost_price: Number(v.cost_price ?? 0),
            sell_price: Number(v.sell_price ?? 0),
            description: v.description ?? "",
          };
        } else {
          this.form = {
            name: "",
            sku: "",
            stock: 0,
            cost_price: 0,
            sell_price: 0,
            description: "",
          };
        }
      },
    },
  },
  methods: {
    async submit() {
      this.errors = [];

      try {
        let res;
        const payload = { ...this.form };

        if (this.product && this.product.id) {
          res = await fetch(`/api/products/${this.product.id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
          });
        } else {
          res = await fetch("/api/products", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
          });
        }

        if (!res.ok) {
          const json = await res.json().catch(() => ({
            message: "Unknown error",
          }));
          this.errors = json.errors
            ? Object.values(json.errors).flat()
            : [json.message || "Error saving product"];
          return;
        }

        this.$emit("saved");
      } catch (e) {
        this.errors = [e.message || String(e)];
      }
    },
  },
};
</script>

<style scoped>
.formcard {
  border: 1px solid #eef2f7;
  padding: 12px;
  border-radius: 10px;
  margin-bottom: 12px;
  background: #fff;
}

.grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-top: 8px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

input,
textarea {
  padding: 8px;
  border-radius: 8px;
  border: 1px solid #e6e9ef;
  width: 100%;
  box-sizing: border-box;
}

.textarea-field {
  grid-column: 1 / -1;
}

textarea {
  min-height: 80px;
  resize: vertical;
}

.form-actions {
  margin-top: 12px;
  display: flex;
  gap: 8px;
}

.btn {
  padding: 8px 12px;
  border-radius: 8px;
  background: #111827;
  color: #fff;
  border: none;
  cursor: pointer;
}

.btn.ghost {
  background: transparent;
  border: 1px solid #cbd5e1;
  color: #111827;
}

.errors {
  color: #b91c1c;
  margin-top: 8px;
}
</style>
