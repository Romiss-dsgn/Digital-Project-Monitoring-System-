<template>
  <!-- Navbar -->
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

        <div class="contrack-profile" ref="profileRef">
          <div class="contrack-profile__text">
            <strong>{{ displayName }}</strong>
            <span>{{ displayRole }}</span>
          </div>
          <button class="contrack-avatar" type="button" @click="toggleDropdown" aria-label="Open account menu">
            <span class="material-symbols-rounded">account_circle</span>
          </button>

          <!-- Dropdown Modal -->
          <div class="contrack-dropdown" v-if="showDropdown">
            <div class="contrack-dropdown__header">
              <div class="contrack-dropdown__avatar">
                <span class="material-symbols-rounded">account_circle</span>
              </div>
              <div class="contrack-dropdown__info">
                <strong>{{ displayName }}</strong>
                <span>{{ displayRole }}</span>
              </div>
            </div>
            <hr class="contrack-dropdown__divider" />
            <ul class="contrack-dropdown__menu">
              <li>
                <router-link to="/user-profile" class="contrack-dropdown__item" @click="closeDropdown">
                  <span class="material-symbols-rounded">manage_accounts</span>
                  Edit Profile
                </router-link>
              </li>
              <li>
                <router-link to="/settings" class="contrack-dropdown__item" @click="closeDropdown">
                  <span class="material-symbols-rounded">security</span>
                  Settings & Privacy
                </router-link>
              </li>
            </ul>
            <hr class="contrack-dropdown__divider" />
            <ul class="contrack-dropdown__menu">
              <li>
                <button class="contrack-dropdown__item contrack-dropdown__item--danger" @click="logout">
                  <span class="material-symbols-rounded">logout</span>
                  Logout
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
  <!-- End Navbar -->
</template>

<script>
import { mapMutations, mapState } from "vuex";
import { hasStoredAuthToken } from "@/services/auth-token";

export default {
  name: "navbar",
  props: ["minNav", "color"],
  data() {
    return {
      showDropdown: false,
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
    if (hasStoredAuthToken() && !this.profile.id) {
      try {
        await this.$store.dispatch("profile/getProfile");
      } catch (error) {
        void error;
      }
    }
    document.addEventListener("click", this.handleOutsideClick);
  },
  beforeUnmount() {
    document.removeEventListener("click", this.handleOutsideClick);
  },
  methods: {
    ...mapMutations(["navbarMinimize"]),
    toggleSidebar() {
      this.navbarMinimize();
    },
    toggleDropdown() {
      this.showDropdown = !this.showDropdown;
    },
    closeDropdown() {
      this.showDropdown = false;
    },
    handleOutsideClick(e) {
      if (this.$refs.profileRef && !this.$refs.profileRef.contains(e.target)) {
        this.showDropdown = false;
      }
    },
  logout() {
  this.showDropdown = false;
  this.$store.dispatch("auth/logout");
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
  cursor: pointer;
}

.contrack-icon .material-symbols-rounded {
  font-size: 1.6rem;
}

.contrack-profile {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding-left: 1.35rem;
  border-left: 1px solid #e6c9c9;
  position: relative;
  min-width: 0;
}

.contrack-profile__text {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  line-height: 1.15;
  min-width: 150px;
  max-width: 220px;
  text-align: left;
}

.contrack-profile__text strong {
  color: #1f140f;
  font-size: 0.95rem;
  font-weight: 800;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.contrack-profile__text span {
  color: #5a332d;
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.contrack-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background: #f5e6e6;
  border: 2px solid #c0392b;
  color: #850000;
  cursor: pointer;
  transition: background 0.2s;
  flex-shrink: 0;
}

.contrack-avatar:hover {
  background: #ead5d5;
}

.contrack-avatar .material-symbols-rounded {
  font-size: 2rem;
}

/* Dropdown */
.contrack-dropdown {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  width: 240px;
  background: #ffffff;
  border: 1px solid #eef0f4;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(15, 23, 42, 0.12);
  z-index: 999;
  padding: 0.5rem 0;
}

.contrack-dropdown__header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
}

.contrack-dropdown__avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #f5e6e6;
  color: #850000;
  flex-shrink: 0;
}

.contrack-dropdown__avatar .material-symbols-rounded {
  font-size: 1.75rem;
}

.contrack-dropdown__info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.contrack-dropdown__info strong {
  font-size: 0.88rem;
  font-weight: 800;
  color: #1f140f;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.contrack-dropdown__info span {
  font-size: 0.68rem;
  font-weight: 700;
  color: #5a332d;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.contrack-dropdown__divider {
  border: none;
  border-top: 1px solid #eef0f4;
  margin: 0.25rem 0;
}

.contrack-dropdown__menu {
  list-style: none;
  padding: 0.25rem 0;
  margin: 0;
}

.contrack-dropdown__item {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  width: 100%;
  padding: 0.6rem 1rem;
  font-size: 0.88rem;
  font-weight: 600;
  color: #1f140f;
  background: transparent;
  border: none;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.15s;
}

.contrack-dropdown__item:hover {
  background: #f8f0f0;
  color: #850000;
}

.contrack-dropdown__item .material-symbols-rounded {
  font-size: 1.2rem;
  color: #5a332d;
}

.contrack-dropdown__item:hover .material-symbols-rounded {
  color: #850000;
}

.contrack-dropdown__item--danger {
  color: #c0392b;
}

.contrack-dropdown__item--danger .material-symbols-rounded {
  color: #c0392b;
}

.contrack-dropdown__item--danger:hover {
  background: #fdf0f0;
  color: #a93226;
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
