<template>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb py-0 px-0">
      <li class="breadcrumb-item active" :class="color ? `text-${color}` : 'text-dark'">
        <a href="#" class="opacity-7 text-decoration-none">
          <span class="ms-2">{{ formatPageName }}</span>
        </a>
      </li>
    </ol>
  </nav>
</template>

<script>
export default {
  name: "Breadcrumbs",
  props: {
    currentPage: {
      type: String,
      default: "Dashboard",
    },
    color: {
      type: String,
      default: "dark",
    },
  },
  computed: {
    formatPageName() {
      if (!this.currentPage) return "Dashboard";
      // Convert camelCase or route names to readable format
      return this.currentPage
        .replace(/([A-Z])/g, " $1") // Add space before capital letters
        .replace(/^./, (str) => str.toUpperCase()) // Capitalize first letter
        .trim();
    },
  },
};
</script>

<style scoped>
.breadcrumb {
  background-color: transparent;
  margin: 0;
}

.breadcrumb-item {
  font-size: 0.875rem;
  font-weight: 500;
}

.breadcrumb-item a {
  text-decoration: none;
  transition: all 0.2s ease-in-out;
}

.breadcrumb-item a:hover {
  opacity: 1 !important;
}

.opacity-7 {
  opacity: 0.7;
}
</style>
