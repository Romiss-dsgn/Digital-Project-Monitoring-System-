import { createStore } from "vuex";
import { auth } from "./auth.module";
import { profile } from "./profile.module";

export default createStore({
  modules: {
    auth,
    profile
  },
  state: {
    hideConfigButton: false,
    isPinned: true,
    showConfig: false,
    sidebarType: "bg-gradient-dark",
    isRTL: false,
    color: "success",
    isNavFixed: false,
    isAbsolute: false,
    showNavs: true,
    showSidenav: true,
    showNavbar: true,
    showFooter: true,
    showMain: true,
    isDarkMode: false,
    mobileSidenavOpen: false,
    navbarFixed:
      "position-sticky blur shadow-blur left-auto top-1 z-index-sticky px-0 mx-4",
    absolute: "position-absolute px-4 mx-0 w-100 z-index-2",
  },
  mutations: {
    toggleConfigurator(state) {
      state.showConfig = !state.showConfig;
    },
    navbarMinimize(state) {
      const sidenav_show = document.querySelector(".g-sidenav-show");
      const isMobile = window.innerWidth <= 1199.98;

      if (!sidenav_show) {
        return;
      }

      if (isMobile) {
        state.mobileSidenavOpen = !state.mobileSidenavOpen;
        sidenav_show.classList.toggle("mobile-sidenav-open", state.mobileSidenavOpen);
        sidenav_show.classList.remove("g-sidenav-pinned");
        document.body.classList.toggle("mobile-sidenav-lock", state.mobileSidenavOpen);
        document.body.classList.remove("app-shell-sidenav-pinned");
        return;
      }

      state.mobileSidenavOpen = false;
      document.body.classList.remove("mobile-sidenav-lock");
      sidenav_show.classList.remove("mobile-sidenav-open");

      if (sidenav_show.classList.contains("g-sidenav-pinned")) {
        sidenav_show.classList.remove("g-sidenav-pinned");
        state.isPinned = true;
        document.body.classList.remove("app-shell-sidenav-pinned");
      } else {
        sidenav_show.classList.add("g-sidenav-pinned");
        state.isPinned = false;
        document.body.classList.add("app-shell-sidenav-pinned");
      }
    },
    closeMobileSidenav(state) {
      state.mobileSidenavOpen = false;
      const sidenav_show = document.querySelector(".g-sidenav-show");

      if (sidenav_show) {
        sidenav_show.classList.remove("mobile-sidenav-open");
      }
      document.body.classList.remove("mobile-sidenav-lock");
      document.body.classList.remove("app-shell-sidenav-pinned");
    },
    navbarFixed(state) {
      if (state.isNavFixed === false) {
        state.isNavFixed = true;
      } else {
        state.isNavFixed = false;
      }
    },
    toggleEveryDisplay(state) {
      state.showNavbar = !state.showNavbar;
      state.showSidenav = !state.showSidenav;
      state.showFooter = !state.showFooter;
    },
    toggleHideConfig(state) {
      state.hideConfigButton = !state.hideConfigButton;
    },
    color(state, payload) {
      state.color = payload;
    },
  },
  actions: {
    setColor({ commit }, payload) {
      commit("color", payload);
    },
  },
  getters: {},
});
