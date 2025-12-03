<template>
  <div class="login-container">
    <form class="login-card" @submit.prevent="login">
      <h2 class="title">User Login</h2>

      <div v-if="error" class="error">{{ error }}</div>

      <div class="field">
        <label for="email">Email</label>
        <input 
          type="email" 
          id="email" 
          v-model="form.email" 
          required 
          placeholder="Enter your email"
        >
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input 
          type="password" 
          id="password" 
          v-model="form.password" 
          required 
          placeholder="Enter your password"
        >
      </div>

      <button type="submit" class="btn">Log In</button>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import axios from 'axios';

const form = reactive({
  email: '',
  password: '',
});

const error = ref(null);

const login = async () => {
  error.value = null;

  try {
    await axios.post('/login', form);
    window.location.href = '/'; 
  } catch (e) {
    if (e.response && e.response.status === 422) {
      const validationErrors = e.response.data.errors;
      error.value = validationErrors.email 
        ? validationErrors.email[0] 
        : 'Authentication failed.';
    } else {
      error.value = 'Invalid email or password.';
    }
  }
};
</script>

<style scoped>
/* Outer container center */
.login-container {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #f8fafc;
}

/* Card */
.login-card {
  width: 350px;
  background: #ffffff;
  padding: 25px 28px;
  border-radius: 12px;
  box-shadow: 0 3px 18px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  gap: 18px;
}

/* Title */
.title {
  margin: 0;
  text-align: center;
  font-size: 22px;
  font-weight: 600;
  color: #111827;
  margin-bottom: 10px;
}

/* Error message */
.error {
  background: #fee2e2;
  color: #b91c1c;
  padding: 10px;
  border-radius: 6px;
  font-size: 14px;
  text-align: center;
}

/* Field wrapper */
.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

/* Label */
.field label {
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

/* Inputs */
.field input {
  padding: 10px 12px;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
}

.field input:focus {
  border-color: #6366f1;
}

/* Button */
.btn {
  background: #111827;
  color: white;
  border: none;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 15px;
  cursor: pointer;
  margin-top: 10px;
  transition: 0.2s;
}

.btn:hover {
  background: #000;
}

/* Responsive */
@media (max-width: 420px) {
  .login-card {
    width: 90%;
  }
}
</style>
