<template>
  <div class="container-fluid py-4">
    <div class="col-12 col-lg-9 m-auto">

      <!-- Header Banner -->
      <div class="profile-banner mb-n6">
        <div class="profile-banner__overlay"></div>
        <div class="profile-banner__content">
          <span class="material-symbols-rounded profile-banner__icon">shield</span>
          <div>
            <h4 class="profile-banner__title">My Profile</h4>
            <p class="profile-banner__sub">BFP Region II — Bureau of Fire Protection</p>
          </div>
        </div>
      </div>

      <!-- Main Card -->
      <div class="card profile-card">
        <!-- Avatar Section -->
        <div class="profile-card__avatar-row">
          <div class="profile-card__avatar">
            <span class="material-symbols-rounded">account_circle</span>
          </div>
          <div class="profile-card__identity">
            <h5 class="profile-card__name">{{ userName }}</h5>
            <span class="profile-card__role">{{ userRole }}</span>
          </div>
        </div>
        <div class="card-body pt-2 pb-4 px-4">
          <info />
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import Info from "./Info.vue";

export default {
  name: "User Profile",
  components: { Info },
  computed: {
    profile() {
      return this.$store.getters["profile/getUserProfile"] || {};
    },
    userName() {
      return this.profile.name || "—";
    },
    userRole() {
      return (this.profile.position || this.profile.role || "BFP Personnel").toUpperCase();
    },
  },
};
</script>

<style scoped>
/* Banner */
.profile-banner {
  position: relative;
  height: 140px;
  border-radius: 16px 16px 0 0;
  overflow: hidden;
  background: linear-gradient(135deg, #850000 0%, #4a0000 60%, #1c223a 100%);
  display: flex;
  align-items: flex-end;
  padding: 1.25rem 1.75rem;
  z-index: 0;
}

.profile-banner__overlay {
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  z-index: 0;
}

.profile-banner__content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.profile-banner__icon {
  font-size: 2.5rem;
  color: rgba(255,255,255,0.85);
}

.profile-banner__title {
  color: #fff;
  font-size: 1.3rem;
  font-weight: 800;
  margin: 0;
  line-height: 1.2;
}

.profile-banner__sub {
  color: rgba(255,255,255,0.7);
  font-size: 0.78rem;
  font-weight: 600;
  margin: 0;
  letter-spacing: 0.04em;
}

/* Card */
.profile-card {
  border-radius: 0 0 16px 16px;
  border: 1px solid #eef0f4;
  box-shadow: 0 8px 32px rgba(15,23,42,0.08);
  overflow: visible;
}

.profile-card__avatar-row {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  padding: 1rem 1.75rem 0;
  margin-top: -2rem;
  position: relative;
  z-index: 2;
}

.profile-card__avatar {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #f5e6e6;
  border: 3px solid #fff;
  box-shadow: 0 4px 14px rgba(133,0,0,0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #850000;
  flex-shrink: 0;
}

.profile-card__avatar .material-symbols-rounded {
  font-size: 3rem;
}

.profile-card__identity {
  padding-top: 1.5rem;
}

.profile-card__name {
  font-size: 1.1rem;
  font-weight: 800;
  color: #1f140f;
  margin: 0 0 2px;
}

.profile-card__role {
  font-size: 0.72rem;
  font-weight: 700;
  color: #850000;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  background: #fdf0f0;
  padding: 2px 10px;
  border-radius: 999px;
}

.profile-divider {
  border: none;
  border-top: 1px solid #eef0f4;
  margin: 1rem 0 0;
}
</style>