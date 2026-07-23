<template>
  <button
    type="button"
    :data-bs-toggle="collapse ? 'collapse' : ''"
    :aria-controls="collapseRef"
    :aria-expanded="isExpanded"
    :class="['nav-link', 'nav-link-button', { active: isActiveRoute }]"
    v-bind="$attrs"
    @click="handleClick"
  >
    <div
      class="text-center d-flex align-items-center justify-content-center"
      :class="isRTL ? ' ms-2' : 'me-2'"
    >
      <slot name="icon"></slot>
    </div>
    <span class="nav-link-text" :class="isRTL ? ' me-1' : 'ms-1'">{{
      navText
    }}</span>
  </button>
  <div :class="isExpanded ? 'collapse show' : 'collapse'">
    <slot name="list"></slot>
  </div>
</template>
<script>
import { mapState } from "vuex";

export default {
  name: "SidenavCollapse",
  props: {
    collapseRef: {
      type: String,
      required: true
    },
    navText: {
      type: String,
      required: true
    },
    collapse: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      isExpanded: false
    };
  },
  computed: {
    ...mapState(["isRTL", "color"]),
    routePath() {
      if (this.collapse) {
        return `#${this.collapseRef}`;
      }
      return this.collapseRef.startsWith("/") ? this.collapseRef : `/${this.collapseRef}`;
    },
    isActiveRoute() {
      return this.$route.path === this.routePath;
    }
  },
  methods: {
    handleClick() {
      if (this.collapse) {
        this.isExpanded = !this.isExpanded;
        return;
      }

      if (this.$route.path !== this.routePath) {
        this.$router.push(this.routePath).catch(() => {});
      }

      if (window.innerWidth <= 1199.98) {
        this.$store.commit("closeMobileSidenav");
      }
    }
  }
};
</script>

<style scoped>
.nav-link-button {
  width: calc(100% - 2rem);
  margin: 0 1rem 0.25rem;
  padding: 0;
  border: 0;
  background: transparent;
  text-align: left;
  appearance: none;
  -webkit-appearance: none;
  min-width: 0;
}

.nav-link-button .nav-link-text {
  min-width: 0;
  overflow-wrap: anywhere;
  white-space: normal;
  line-height: 1.2;
}

@media (max-width: 1199.98px) {
  .nav-link-button {
    width: calc(100% - 1.5rem);
    margin: 0 0.75rem 0.25rem;
  }
}
</style>
