<template>
  <nav class="contrack-header" v-bind="$attrs" id="navbarBlur">
    <div class="contrack-header__inner">
      <button class="contrack-menu d-xl-none" type="button" @click="toggleSidebar" aria-label="Toggle sidebar">
        <span class="material-symbols-rounded">menu</span>
      </button>

      <h1 class="contrack-title">ConTrackPro</h1>

      <label class="contrack-search" for="global-search">
        <span class="material-symbols-rounded">search</span>
        <input id="global-search" type="search" placeholder="Search contracts or projects..." />
      </label>

      <div class="contrack-actions">
        <button class="contrack-icon" type="button" aria-label="Notifications">
          <span class="material-symbols-rounded">notifications</span>
        </button>
        <button class="contrack-icon" type="button" aria-label="Help">
          <span class="material-symbols-rounded">help</span>
        </button>

        <div class="contrack-profile">
          <div class="contrack-profile__text">
            <strong>{{ displayName }}</strong>
            <span>{{ displayRole }}</span>
          </div>
          <router-link :to="{ name: 'Settings' }" class="contrack-avatar" aria-label="Open account settings">
            <img :src="avatarImage" alt="User avatar" />
          </router-link>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import avatarImage from "@/assets/img/team-2.jpg";
import { mapMutations, mapState } from "vuex";

export default {
  name: "navbar",
  props: ["minNav", "color"],
  data() {
    return {
      avatarImage,
    };
  },
  computed: {
    ...mapState(["isRTL"]),
    profile() {
      return this.$store.getters["profile/getUserProfile"] || {};
    },
    displayName() {
      return this.profile.name || "Admin User";
    },
    displayRole() {
      return (this.profile.position || this.profile.office_unit || "Region II Commander").toUpperCase();
    },
  },
  async mounted() {
    if (localStorage.getItem("user_free") && !this.profile.id) {
      try {
        await this.$store.dispatch("profile/getProfile");
      } catch (error) {
        void error;
        // Header still renders with fallback identity if the profile request fails.
      }
    }
  },
  methods: {
    ...mapMutations(["navbarMinimize"]),
    toggleSidebar() {
      this.navbarMinimize();
    },
  },
};
</script>

<style scoped>
.contrack-header {
  width: 100%;
  min-height: 80px;
  background: #ffffff;
  border-radius: 0;
  border-bottom: 1px solid #eef0f4;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
  z-index: 20;
}

.contrack-header__inner {
  display: grid;
  grid-template-columns: max-content minmax(220px, 560px) 1fr;
  align-items: center;
  gap: 2rem;
  min-height: 80px;
  padding: 0 1.75rem;
}

.contrack-title {
  margin: 0;
  color: #850000;
  font-size: 1.9rem;
  font-weight: 900;
  line-height: 1;
  white-space: nowrap;
}

.contrack-search {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  width: 100%;
  max-width: 560px;
  height: 44px;
  padding: 0 1rem;
  margin: 0;
  background: #f1f2f5;
  border-radius: 999px;
  color: #5f463f;
}

.contrack-search input {
  width: 100%;
  border: 0;
  outline: 0;
  background: transparent;
  color: #1f2937;
  font-size: 0.95rem;
}

.contrack-search input::placeholder {
  color: #718096;
}

.contrack-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 1.25rem;
  min-width: 0;
}

.contrack-icon,
.contrack-menu {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  padding: 0;
  border: 0;
  background: transparent;
  color: #5a332d;
}

.contrack-icon .material-symbols-rounded {
  font-size: 1.6rem;
}

.contrack-profile {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding-left: 1.35rem;
  border-left: 1px solid #e6c9c9;
}

.contrack-profile__text {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  line-height: 1.15;
}

.contrack-profile__text strong {
  color: #1f140f;
  font-size: 0.95rem;
  font-weight: 800;
}

.contrack-profile__text span {
  color: #5a332d;
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.contrack-avatar {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.22);
  flex: 0 0 auto;
}

.contrack-avatar img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

@media (max-width: 1199.98px) {
  .contrack-header__inner {
    grid-template-columns: 38px max-content 1fr;
    gap: 1rem;
  }

  .contrack-search {
    display: none;
  }
}

@media (max-width: 767.98px) {
  .contrack-header__inner {
    padding: 0 1rem;
  }

  .contrack-title {
    font-size: 1.35rem;
  }

  .contrack-actions {
    gap: 0.65rem;
  }

  .contrack-profile__text,
  .contrack-icon {
    display: none;
  }
}
</style>
