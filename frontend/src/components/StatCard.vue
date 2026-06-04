<template>
  <div class="card">
    <div class="card-body d-flex align-items-center">
      <div class="icon-wrapper me-3">
        <div :class="['icon-bg', `bg-${icon_color}`]">
          <i :class="['material-icons-round', `text-${text_color}`]">{{ icon }}</i>
        </div>
      </div>
      <div class="stat-content">
        <p class="text-secondary small mb-1">{{ title }}</p>
        <h5 class="mb-0">{{ value }}</h5>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "StatCard",
  props: {
    title: String,
    value: String,
    icon: String,
    icon_color: {
      type: String,
      default: "warning"
    },
    text_color: {
      type: String,
      default: "white"
    }
  }
};
</script>

<style scoped>
.card {
  border: none;
  border-radius: 1rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.1);
}

.card-body {
  padding: 1.5rem;
}

.icon-wrapper {
  min-width: 60px;
  min-height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-bg {
  width: 60px;
  height: 60px;
  border-radius: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-bg .material-icons-round {
  font-size: 2rem;
}

.stat-content p {
  font-size: 0.875rem;
  font-weight: 500;
}

.stat-content h5 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2633;
}

.bg-primary {
  background: #2c5aa0;
}

.bg-warning {
  background: #fb8500;
}

.bg-danger {
  background: #d32f2f;
}

.bg-success {
  background: #4caf50;
}

.bg-info {
  background: #0288d1;
}
</style>
