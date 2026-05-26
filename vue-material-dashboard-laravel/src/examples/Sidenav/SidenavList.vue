<template>
  <div class="w-auto h-auto collapse navbar-collapse max-height-vh-100 h-100" id="sidenav-collapse-main">
    <ul class="navbar-nav">

      <li class="nav-item">
        <sidenav-collapse url="#" :aria-controls="''" v-bind:collapse="false" collapseRef="dashboard" navText="Dashboard">
          <template v-slot:icon>
            <i class="material-icons-round opacity-10 fs-5">dashboard</i>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse url="#" :aria-controls="''" v-bind:collapse="false" collapseRef="infrastructure-plans" navText="Infrastructure Plans">
          <template v-slot:icon>
            <i class="material-icons-round opacity-10 fs-5">account_tree</i>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse url="#" :aria-controls="''" v-bind:collapse="false" collapseRef="contract-management" navText="Contract Management">
          <template v-slot:icon>
            <i class="material-icons-round opacity-10 fs-5">description</i>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse url="#" :aria-controls="''" v-bind:collapse="false" collapseRef="cashflow" navText="Cashflows Management">
          <template v-slot:icon>
            <i class="material-icons-round opacity-10 fs-5">request_quote</i>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse url="#" :aria-controls="''" v-bind:collapse="false" collapseRef="engineering-plans" navText="Engineering Plans">
          <template v-slot:icon>
            <i class="material-icons-round opacity-10 fs-5">engineering</i>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse url="#" :aria-controls="''" v-bind:collapse="false" collapseRef="variation-orders" navText="Variation Orders">
          <template v-slot:icon>
            <i class="material-icons-round opacity-10 fs-5">fact_check</i>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse url="#" :aria-controls="''" v-bind:collapse="false" collapseRef="accomplishments" navText="Project Accomplishments">
          <template v-slot:icon>
            <i class="material-icons-round opacity-10 fs-5">inventory_2</i>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse url="#" :aria-controls="''" v-bind:collapse="false" collapseRef="reports" navText="Reports">
          <template v-slot:icon>
            <i class="material-icons-round opacity-10 fs-5">bar_chart</i>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse url="#" :aria-controls="''" v-bind:collapse="false" collapseRef="audit-trail" navText="Audit Logs">
          <template v-slot:icon>
            <i class="material-icons-round opacity-10 fs-5">history_toggle_off</i>
          </template>
        </sidenav-collapse>
      </li>

      <li class="nav-item">
        <sidenav-collapse url="#" :aria-controls="''" v-bind:collapse="false" collapseRef="settings" navText="Settings">
          <template v-slot:icon>
            <i class="material-icons-round opacity-10 fs-5">settings</i>
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
