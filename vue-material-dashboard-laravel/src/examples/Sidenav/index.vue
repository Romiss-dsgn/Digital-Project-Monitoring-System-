<template>
  <aside
    id="sidenav-main"
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 ms-3"
    :class="`${
      isRTL ? 'me-3 rotate-caret fixed-end' : 'fixed-start ms-3'
    } ${sidebarType}`"
  >
    <div class="sidenav-header">
      <i
        class="top-0 p-3 cursor-pointer fas fa-times text-secondary opacity-5 position-absolute end-0 d-none d-xl-none"
        aria-hidden="true"
        id="iconSidenav"
      ></i>
      <a class="m-0 navbar-brand d-flex align-items-center" href="/">
        <div class="logo-wrapper">
          <img
            :src="logo"
            class="navbar-brand-img"
            alt="main_logo"
          />
          <div class="logo-glow"></div>
        </div>
        <div class="brand-text-wrapper ms-2">
          <span class="font-weight-bold text-white sidebar-brand-text">BFP Region II</span>
          <span class="sidebar-brand-sub">Bureau of Fire Protection</span>
        </div>
      </a>
    </div>
    <hr class="horizontal light mt-0 mb-2" />
    <sidenav-list />
  </aside>
</template>

<script>
import SidenavList from "./SidenavList.vue";
import logo from "@/assets/img/BFP 11.png";
import bgImage from "@/assets/img/bg.png";
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
    };
  },
  computed: {
    ...mapState(["isRTL", "sidebarType", "isDarkMode"]),
    backgroundImageUrl() {
      return `url(${this.bgImage})`;
    },
  },
};
</script>

<style>
.sidenav {
  background-image: v-bind(backgroundImageUrl) !important;
  background-size: cover !important;
  background-position: center !important;
  background-attachment: fixed !important;
  position: relative;
  overflow: hidden;
  color: #fff;
}

/* Overlay layer — tweak opacity here to show more/less bg image */
.sidenav::before {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(
    175deg,
    rgba(180, 14, 26, 0.82) 0%,
    rgba(120, 10, 20, 0.78) 35%,
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


/* ── Logo wrapper ─────────────────────────────── */
.navbar-brand-img {
  width: 62px;
  height: 62px;
  object-fit: contain;
  flex-shrink: 0;
  border-radius: 16px;
  background: transparent;
  padding: 4px;
  border: 1.5px solid rgba(255, 255, 255, 0.30);
  box-shadow:
    0 0 0 1px rgba(255, 255, 255, 0.10),
    0 4px 18px rgba(0, 0, 0, 0.30);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.navbar-brand:hover .navbar-brand-img {
  transform: scale(1.06);
  box-shadow:
    0 0 0 2px rgba(255, 255, 255, 0.35),
    0 6px 22px rgba(0, 0, 0, 0.4);
}

/* Soft radial glow behind the logo */
.logo-glow {
  position: absolute;
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255,80,80,0.25) 0%, transparent 70%);
  pointer-events: none;
  z-index: -1;
}

.navbar-brand-img {
  width: 52px;
  height: 52px;
  object-fit: contain;
  flex-shrink: 0;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.15);
  padding: 6px;
  box-shadow:
    0 0 0 1.5px rgba(255, 255, 255, 0.25),
    0 4px 16px rgba(0, 0, 0, 0.35),
    0 1px 3px rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.navbar-brand:hover .navbar-brand-img {
  transform: scale(1.06);
  box-shadow:
    0 0 0 2px rgba(255, 255, 255, 0.4),
    0 6px 20px rgba(0, 0, 0, 0.45);
}

/* Soft radial glow behind the logo */
.logo-glow {
  position: absolute;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255,80,80,0.35) 0%, transparent 70%);
  pointer-events: none;
  z-index: -1;
}

/* ── Brand text ───────────────────────────────── */
.brand-text-wrapper {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.sidebar-brand-text {
  font-size: 0.88rem;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: 0.02em;
  color: #fff !important;
  text-shadow: 0 1px 4px rgba(0,0,0,0.3);
}

.sidebar-brand-sub {
  font-size: 0.62rem;
  color: rgba(255, 255, 255, 0.6) !important;
  font-weight: 400;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

/* ── Navbar brand padding ─────────────────────── */
.sidenav .navbar-brand {
  padding-left: 1.25rem;
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
}

/* ── Global color overrides ───────────────────── */
.sidenav .navbar-brand,
.sidenav .navbar-brand span,
.sidenav .navbar-brand .navbar-brand-img,
.sidenav .navbar-heading,
.sidenav .nav-link,
.sidenav .navbar-nav .nav-link .nav-link-text,
.sidenav .navbar-nav .nav-link i {
  color: #fff !important;
}

/* ── Nav links ────────────────────────────────── */
.sidenav .nav-link {
  border-radius: 0.75rem;
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
.sidenav .nav-link.active .nav-link-text {
  color: #ffffff !important;
}

/* ── Divider ──────────────────────────────────── */
.sidenav hr.horizontal.light {
  border-color: rgba(255, 255, 255, 0.18) !important;
}
</style>