<!--
=========================================================
* Vue Material Dashboard 2 Laravel - v3.0.0
=========================================================

* Product Page: https://creative-tim.com/product/vue-material-dashboard-2
* Copyright 2022 Creative Tim (https://www.creative-tim.com)

Coded by www.creative-tim.com

 =========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<template>
  <sidenav
    :custom_class="color"
    :class="[isRTL ? 'fixed-end' : 'fixed-start']"
    v-if="showAppShell && showSidenav"
  />
  <main
    class="main-content position-relative max-height-vh-100 h-100 overflow-x-hidden"
  >
    <!-- nav -->
    <navbar
      :class="[isNavFixed ? navbarFixed : '', isAbsolute ? absolute : '']"
      :color="isAbsolute ? 'text-white opacity-8' : ''"
      :minNav="navbarMinimize"
      v-if="showAppShell && showNavbar"
    />
    <router-view />
    <app-footer v-if="showAppShell && showFooter" />
  </main>
</template>
<script>
import Sidenav from "./examples/Sidenav";
import Navbar from "@/examples/Navbars/Navbar.vue";
import AppFooter from "@/examples/Footer.vue";
import { mapMutations, mapState } from "vuex";

export default {
  name: "App",
  components: {
    Sidenav,
    Navbar,
    AppFooter
  },
  methods: {
    ...mapMutations(["navbarMinimize"])
  },
  computed: {
    ...mapState([
      "isRTL",
      "color",
      "isAbsolute",
      "isNavFixed",
      "navbarFixed",
      "absolute",
      "showSidenav",
      "showNavbar",
      "showFooter"
    ]),
    showAppShell() {
      return !this.$route.matched.some((record) => record.meta.hideAppShell);
    }
  },
  beforeMount() {
    this.$store.state.isTransparent = "bg-transparent";

    const sidenav = document.getElementsByClassName("g-sidenav-show")[0];

    if (window.innerWidth > 1200) {
      sidenav.classList.add("g-sidenav-pinned");
    }
  }
};
</script>
