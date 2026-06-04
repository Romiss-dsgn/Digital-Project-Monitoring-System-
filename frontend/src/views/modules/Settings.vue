<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Settings</h4>
          <p class="text-secondary small">Update system preferences and user settings.</p>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="text-center py-5">
                <span class="text-secondary">Settings page is ready for configuration.</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Settings",
};
</script>

<style scoped>
.module-page {
  min-height: 100vh;
  background: #f8f9fc;
}
.card {
  border-radius: 1rem;
}
</style>
