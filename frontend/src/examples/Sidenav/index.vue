<template>
  <aside
    id="sidenav-main"
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0"
    :class="`${
      isRTL ? 'rotate-caret fixed-end' : 'fixed-start'
    } ${sidebarType} ${isCollapsed ? 'sidenav-collapsed' : ''}`"
    :style="{ backgroundImage: backgroundImageUrl }"
  >
    <div class="sidenav-header">
      <i
        class="top-0 p-3 cursor-pointer material-symbols-rounded text-secondary opacity-5 position-absolute end-0 d-none d-xl-none"
        aria-hidden="true"
        id="iconSidenav"
        @click="closeMobileSidenav"
      >close</i>
      <div class="sidenav-header-row">
        <a class="m-0 navbar-brand d-flex align-items-center" href="/dashboard">
          <div class="logo-wrapper">
            <img
              :src="logo"
              class="navbar-brand-img"
              alt="main_logo"
            />
            <div class="logo-glow"></div>
          </div>
          <div class="brand-text-wrapper">
            <span class="font-weight-bold text-white sidebar-brand-text">LGU Tuao</span>
            <span class="sidebar-brand-sub">Municipality of Tuao</span>
          </div>
        </a>
        <button
          type="button"
          class="sidenav-toggle-btn d-none d-xl-flex"
          :aria-expanded="(!isCollapsed).toString()"
          aria-label="Toggle sidebar"
          @click="toggleSidenav"
        >
          <span class="material-symbols-rounded">{{ isCollapsed ? 'chevron_right' : 'chevron_left' }}</span>
        </button>
      </div>
    </div>
    <hr class="horizontal light mt-0 mb-2" />
    <sidenav-list />
  </aside>
</template>

<script>
import SidenavList from "./SidenavList.vue";
import logo from "@/assets/img/system-logo/logo.jpg";
import bgImage from "@/assets/img/LGU Tuao bg.jpg";
import { mapState } from "vuex";

export default {
  name: "index",
  components: {
    SidenavList,
  },
  data() {
    return {
      logo,
      bgImage,
      isCollapsed: false,
    };
  },
  computed: {
    ...mapState(["isRTL", "sidebarType", "isDarkMode"]),
    backgroundImageUrl() {
      return `url("${this.bgImage}")`;
    },
  },
  mounted() {
    const stored = localStorage.getItem("contrack_sidenav_collapsed");
    this.isCollapsed = stored === "true";
    this.applySidebarWidth();
  },
  methods: {
    closeMobileSidenav() {
      this.$store.commit("closeMobileSidenav");
    },
    toggleSidenav() {
      this.isCollapsed = !this.isCollapsed;
      localStorage.setItem("contrack_sidenav_collapsed", this.isCollapsed);
      this.applySidebarWidth();
    },
    applySidebarWidth() {
      document.documentElement.style.setProperty(
        "--contrack-sidebar-width",
        this.isCollapsed ? "5.25rem" : "17.125rem"
      );
    },
  },
};
</script>

<style>
.sidenav {
  background-size: cover !important;
  background-position: center !important;
  background-attachment: fixed !important;
  position: fixed;
  top: 0 !important;
  bottom: 0 !important;
  height: 100vh !important;
  margin: 0 !important;
  border-radius: 0 !important;
  overflow-y: auto;
  overflow-x: hidden;
  scrollbar-width: thin;
  color: #fff;
  box-shadow: none !important;
  width: var(--contrack-sidebar-width, 17.125rem) !important;
  max-width: var(--contrack-sidebar-width, 17.125rem) !important;
  transition: width 0.3s ease, max-width 0.3s ease;
}

/* Overlay layer */
.sidenav::before {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(
    175deg,
    rgba(21, 101, 192, 0.82) 0%,
    rgba(13, 59, 120, 0.78) 35%,
    rgba(28, 34, 58, 0.80) 100%
  );
  backdrop-filter: blur(1px);
  -webkit-backdrop-filter: blur(1px);
  z-index: 0;
  pointer-events: none;
}

/* All direct children sit above the overlay */
.sidenav > * {
  position: relative;
  z-index: 1;
}

.sidenav .sidenav-header {
  height: auto !important;
  min-height: 76px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

/* ── Sidebar toggle button ────────────────────── */
.sidenav-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  gap: 8px;
}
.sidenav-toggle-btn {
  flex-shrink: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  color: #fff;
  cursor: pointer;
  opacity: 0.85;
  transition: opacity 0.2s ease;
}
.sidenav-toggle-btn:hover {
  opacity: 1;
}
.sidenav-toggle-btn .material-symbols-rounded {
  font-size: 20px;
  color: #fff;
}

/* ── Logo wrapper ─────────────────────────────── */
.logo-wrapper {
  margin-top: 0;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 56px;
  height: 56px;
  min-width: 56px;
  aspect-ratio: 1 / 1;
  overflow: hidden;
  border-radius: 50%;
  flex-shrink: 0;
  background: #ffffff;
  box-shadow: 0 0 0 2px rgba(21, 101, 192, 0.92), 0 4px 12px rgba(0, 0, 0, 0.18);
}

.navbar-brand-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
  border-radius: 50%;
  padding: 6px;
  margin: 0;
  border: none;
  transition: transform 0.25s ease;
}

.navbar-brand:hover .navbar-brand-img {
  transform: scale(1.06);
}

/* Soft radial glow behind the logo */
.logo-glow {
  display: none;
}

/* ── Brand text ───────────────────────────────── */
.brand-text-wrapper {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 4px;
  min-width: 0;
  flex: 0 1 148px;
  max-width: 148px;
  text-align: left;
}

.sidebar-brand-text {
  font-size: 1.05rem;
  font-weight: 800;
  line-height: 1.05;
  letter-spacing: 0.01em;
  color: #fff !important;
  text-shadow: 0 1px 3px rgba(0,0,0,0.3);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-brand-sub {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.95) !important;
  font-weight: 800;
  letter-spacing: 0;
  line-height: 1.05;
  text-transform: uppercase;
  white-space: normal;
  overflow: visible;
  text-overflow: clip;
  margin-top: 2px;
}

/* ── Navbar brand padding ─────────────────────── */
.sidenav .navbar-brand {
  min-height: 76px;
  padding: 0.6rem 1.25rem;
  gap: 1rem;
  overflow: visible;
  width: 100%;
  justify-content: center !important;
  align-items: center !important;
  border-bottom: 1px solid rgba(255, 255, 255, 0.14);
}

/* ── Global color overrides ───────────────────── */
.sidenav .navbar-brand,
.sidenav .navbar-brand span,
.sidenav .navbar-brand .navbar-brand-img,
.sidenav .navbar-heading,
.sidenav .nav-link,
.sidenav .navbar-nav .nav-link .nav-link-text,
.sidenav .navbar-nav .nav-link i,
.sidenav .navbar-nav .nav-link .material-symbols-rounded {
  color: #fff !important;
}

/* ── Nav links ────────────────────────────────── */
.sidenav .sidenav-icon {
  width: 24px;
  height: 24px;
  font-size: 22px;
}

.sidenav .nav-link {
  border-radius: 4px;
  min-height: 46px;
  margin: 0 1rem 0.125rem;
  transition: background-color 0.2s ease, color 0.2s ease, transform 0.15s ease;
}

.sidenav .nav-link:hover {
  background-color: rgba(255, 255, 255, 0.10);
  transform: translateX(2px);
}

.sidenav .nav-link.active {
  background: linear-gradient(
    90deg,
    rgba(255, 255, 255, 0.22) 0%,
    rgba(255, 255, 255, 0.10) 100%
  ) !important;
  border-left: 3px solid rgba(255, 255, 255, 0.75);
  color: #ffffff !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
}

.sidenav .nav-link.active i,
.sidenav .nav-link.active .material-symbols-rounded,
.sidenav .nav-link.active .nav-link-text {
  color: #ffffff !important;
}

/* ── Divider ──────────────────────────────────── */
.sidenav hr.horizontal.light {
  border-color: rgba(255, 255, 255, 0.18) !important;
}

/* ── Collapsed state ──────────────────────────── */
.sidenav.sidenav-collapsed {
  width: 5.25rem !important;
  max-width: 5.25rem !important;
}

.sidenav.sidenav-collapsed .navbar-brand {
  padding: 1rem 0.5rem;
  gap: 0;
}

.sidenav.sidenav-collapsed .brand-text-wrapper {
  display: none;
}

.sidenav.sidenav-collapsed .sidenav-header-row {
  flex-direction: column;
  gap: 6px;
}
.sidenav.sidenav-collapsed .sidenav-toggle-btn {
  width: 22px;
  height: 22px;
}

.sidenav.sidenav-collapsed .logo-wrapper {
  width: 44px;
  height: 44px;
  min-width: 44px;
}

.sidenav.sidenav-collapsed .nav-link-button {
  justify-content: center;
}

.sidenav.sidenav-collapsed .nav-link-text {
  display: none;
}

.sidenav.sidenav-collapsed .nav-link-button .me-2,
.sidenav.sidenav-collapsed .nav-link-button .ms-2 {
  margin: 0 !important;
}

@media (max-width: 1199.98px) {
  .sidenav {
    top: var(--contrack-shell-header-height) !important;
    height: calc(100vh - var(--contrack-shell-header-height)) !important;
    width: min(86vw, var(--contrack-sidebar-width, 17.125rem)) !important;
    max-width: min(86vw, var(--contrack-sidebar-width, 17.125rem)) !important;
    z-index: 1030;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
  }

  .sidenav .sidenav-header {
    min-height: 88px;
  }

  .sidenav .navbar-brand {
    padding: 0.9rem 1rem;
    gap: 0.75rem;
  }

  .brand-text-wrapper {
    flex: 1 1 auto;
    max-width: 100%;
  }

  .sidebar-brand-text {
    white-space: normal;
    overflow-wrap: anywhere;
  }

  .sidenav .nav-link {
    margin: 0 0.75rem 0.25rem;
  }

  .sidenav .nav-link-text,
  .sidenav .sidenav-normal {
    min-width: 0;
    white-space: normal;
    overflow-wrap: anywhere;
    line-height: 1.2;
  }
}

@media (max-width: 430px) {
  .sidenav {
    width: min(84vw, 15.75rem) !important;
    max-width: min(84vw, 15.75rem) !important;
  }

  .sidenav .navbar-brand {
    padding: 0.75rem 0.875rem;
  }

  .logo-wrapper {
    width: 48px;
    height: 48px;
    min-width: 48px;
  }

  .sidebar-brand-text {
    font-size: 0.95rem;
  }

  .sidebar-brand-sub {
    font-size: 0.6rem;
  }
}
</style>