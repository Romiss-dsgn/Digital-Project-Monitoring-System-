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
        <img
          :src="logo"
          class="navbar-brand-img"
          alt="main_logo"
        />
        <span class="ms-2 font-weight-bold text-white sidebar-brand-text">
          BFP Region II
        </span>
      </a>
    </div>
    <hr class="horizontal light mt-0 mb-2" />
    <sidenav-list />
  </aside>
</template>
<script>
import SidenavList from "./SidenavList.vue";
import logo from "@/assets/img/BFP 11.png";
import { mapState } from "vuex";

export default {
  name: "index",
  components: {
    SidenavList,
  },
  data() {
    return {
      logo,
    };
  },
  computed: {
    ...mapState(["isRTL", "sidebarType", "isDarkMode"]),
  },
};
</script>

<style>
.sidenav {
  background: linear-gradient(180deg, rgba(200,17,32,.95) 0%, rgba(32,39,65,.92) 70%) !important;
  color: #fff;
}

.sidenav .navbar-brand,
.sidenav .navbar-brand span,
.sidenav .navbar-brand .navbar-brand-img,
.sidenav .navbar-heading,
.sidenav .nav-link,
.sidenav .navbar-nav .nav-link .nav-link-text,
.sidenav .navbar-nav .nav-link i {
  color: #fff !important;
}

.sidenav .navbar-brand {
  padding-left: 1.5rem;
}

.navbar-brand-img {
  width: 40px;
  height: 40px;
  object-fit: contain;
}

.sidebar-brand-text {
  font-size: 0.9rem;
  line-height: 1.2;
}

.sidenav .nav-link {
  border-radius: 0.75rem;
  transition: background-color 0.25s ease, color 0.25s ease;
}

.sidenav .nav-link:hover {
  background-color: rgba(255,255,255,0.08);
}

.sidenav .nav-link.active {
  background-color: rgba(255,255,255,0.18) !important;
  color: #ffffff !important;
}

.sidenav .nav-link.active i,
.sidenav .nav-link.active .nav-link-text {
  color: #ffffff !important;
}
</style>
