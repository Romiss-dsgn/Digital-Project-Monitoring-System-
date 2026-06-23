<template>
  <div class="w-auto h-auto collapse navbar-collapse max-height-vh-100 h-100" id="sidenav-collapse-main">
    <ul class="navbar-nav">

      <li class="nav-item">
        <sidenav-collapse :collapse="false" collapseRef="dashboard" navText="Dashboard">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">dashboard</span>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse :collapse="false" collapseRef="infrastructure-plans" navText="Infrastructure Plans">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">architecture</span>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse :collapse="false" collapseRef="contract-management" navText="Contract Management">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">contract</span>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse :collapse="false" collapseRef="cashflow" navText="Cashflows Management">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">payments</span>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse :collapse="false" collapseRef="engineering-plans" navText="Engineering Plans">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">engineering</span>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse :collapse="false" collapseRef="variation-orders" navText="Variation Orders">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">pending_actions</span>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse :collapse="false" collapseRef="accomplishments" navText="Project Accomplishments">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">check_circle</span>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse :collapse="false" collapseRef="reports" navText="Reports">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">analytics</span>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse :collapse="false" collapseRef="audit-trail" navText="Audit Logs">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">manage_search</span>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item" v-if="isAdmin">
        <sidenav-collapse :collapse="false" collapseRef="user-management" navText="User Management">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">groups</span>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse :collapse="false" collapseRef="settings" navText="Settings">
          <template v-slot:icon>
            <span class="material-symbols-rounded sidenav-icon">settings</span>
          </template>
        </sidenav-collapse>
      </li>
    </ul>
  </div>
</template>
<script>
import SidenavCollapse from "./SidenavCollapse.vue";

export default {
  name: "SidenavList",
  props: {
    cardBg: String
  },
  data() {
    return {
      title: "Soft UI Dashboard PRO",
      controls: "dashboardsExamples",
      isActive: "active"
    };
  },
  components: {
    SidenavCollapse
  },
  async mounted() {
    if (localStorage.getItem("user_free") && !this.$store.getters["profile/getUserProfile"]) {
      try {
        await this.$store.dispatch("profile/getProfile");
      } catch (error) {
        void error;
      }
    }
  },
  computed: {
    isAdmin() {
      const profile = this.$store.getters["profile/getUserProfile"];
      return profile?.role === "System Administrator";
    }
  },
  methods: {
    getRoute() {
      const routeArr = this.$route.path.split("/");
      return routeArr[1];
    },
    logout() {
      this.$store.dispatch("auth/logout");
    }
  }
};
</script>
