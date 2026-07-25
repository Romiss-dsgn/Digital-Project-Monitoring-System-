<template>
  <footer class="app-footer">
    <div class="container-fluid">
      <span>&copy; {{ footerYear }} Municipality of Tuao. ConTrackPro v4.2.0. All Rights Reserved.</span>
    </div>
  </footer>
</template>

<script>
export default {
  name: "app-footer",
  computed: {
    footerYear() {
      return new Date().getFullYear();
    },
  },
};
</script>

<style scoped>
.app-footer {
  background: #f7fafc;
  color: #9ca3af;
  font-size: 0.75rem;
  padding: 1.5rem 0 1.25rem;
  text-align: center;
}
</style>
