import { createApp } from "vue";
import App from "./App.vue";
import store from "./store";
import router from "./router";
import "./assets/css/nucleo-icons.css";
import "./assets/css/nucleo-svg.css";
import MaterialDashboard from "./material-dashboard";

const appInstance = createApp(App);
appInstance.use(store);
appInstance.use(router);
appInstance.use(MaterialDashboard);

const markIconFontReady = () => {
  document.documentElement.classList.add("contrack-icon-font-ready");
};

const waitForIconFont = () => {
  if (!document.fonts || typeof document.fonts.load !== "function") {
    markIconFontReady();
    return;
  }

  // Hide Material icon ligature text until the icon font is available.
  // Without this, refreshes can briefly show words like "settings" or "search".
  const fontReady = Promise.allSettled([
    document.fonts.load('24px "Material Symbols Rounded"'),
  ]);
  const fallback = new Promise((resolve) => window.setTimeout(resolve, 3000));

  Promise.race([fontReady, fallback]).finally(markIconFontReady);
};

waitForIconFont();

// Wait for the first route to resolve before mounting.
// This prevents protected layout components from flashing before `/login` is selected.
router.isReady().then(() => {
  appInstance.mount("#app");
});
