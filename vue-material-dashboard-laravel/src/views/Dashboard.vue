<template>
  <div class="dashboard-page">
    <div class="container-fluid py-4">
      <!-- Page Title -->
      <div class="row mb-4">
        <div class="col-12">
          <h4 class="mb-0">Dashboard Overview</h4>
        </div>
      </div>

      <!-- Statistics Row -->
      <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
          <stat-card title="Active Projects" value="16" icon="folder" icon_color="warning" />
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
          <stat-card title="Total Budget" value="₱58.5M" icon="paid" icon_color="primary" />
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
          <stat-card title="In Progress" value="4" icon="pending_actions" icon_color="danger" />
        </div>
        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
          <stat-card title="Alerts" value="1" icon="notifications" icon_color="danger" />
        </div>
      </div>

      <!-- Quick Navigation Row -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="quick-nav">
            <button class="nav-btn" @click="navigateTo('infrastructure-plans')">
              <i class="material-icons-round">apartment</i>
              <span>Infrastructure Plans</span>
            </button>
            <button class="nav-btn" @click="navigateTo('contract-management')">
              <i class="material-icons-round">description</i>
              <span>Contract Management</span>
            </button>
            <button class="nav-btn" @click="navigateTo('cashflow')">
              <i class="material-icons-round">trending_up</i>
              <span>Cashflow Management</span>
            </button>
            <button class="nav-btn" @click="navigateTo('engineering-plans')">
              <i class="material-icons-round">architecture</i>
              <span>Engineering Plans</span>
            </button>
            <button class="nav-btn" @click="navigateTo('variation-orders')">
              <i class="material-icons-round">edit_document</i>
              <span>Variation Orders</span>
            </button>
            <button class="nav-btn" @click="navigateTo('accomplishments')">
              <i class="material-icons-round">check_circle</i>
              <span>Accomplishments</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="row">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Recent Project Updates</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Project</th>
                      <th>Status</th>
                      <th>Progress</th>
                      <th>Last Updated</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in recentUpdates" :key="item.id">
                      <td>{{ item.project }}</td>
                      <td><status-badge :status="item.status" /></td>
                      <td>{{ item.progress }}%</td>
                      <td>{{ item.lastUpdated }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Notifications Panel -->
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Pending Actions</h6>
            </div>
            <div class="card-body">
              <div class="alert-list">
                <div v-for="alert in pendingAlerts" :key="alert.id" class="alert-item">
                  <i :class="['material-icons-round', alert.icon_class]">{{ alert.icon }}</i>
                  <div>
                    <p class="alert-title">{{ alert.title }}</p>
                    <span class="alert-time">{{ alert.time }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import StatCard from "@/components/StatCard.vue";
import StatusBadge from "@/components/StatusBadge.vue";

export default {
  name: "Dashboard",
  components: {
    StatCard,
    StatusBadge,
  },
  data() {
    return {
      recentUpdates: [
        { id: 1, project: "Road Widening Project", status: "on_track", progress: 65, lastUpdated: "2 hours ago" },
        { id: 2, project: "Fire Station Construction", status: "pending", progress: 45, lastUpdated: "1 day ago" },
        { id: 3, project: "Renovation - Santiago City", status: "delayed", progress: 30, lastUpdated: "3 days ago" },
        { id: 4, project: "Regional Office Development", status: "in_progress", progress: 78, lastUpdated: "5 hours ago" },
      ],
      pendingAlerts: [
        { id: 1, icon: "warning", icon_class: "text-danger", title: "Project Delayed", time: "Road Widening Project delayed by 5 days" },
        { id: 2, icon: "edit_document", icon_class: "text-warning", title: "Pending Approval", time: "2 variation orders awaiting approval" },
        { id: 3, icon: "trending_down", icon_class: "text-warning", title: "Budget Variance", time: "Contract #REG-II-001 exceeding budget" },
      ],
    };
  },
  methods: {
    navigateTo(route) {
      this.$router.push({ name: route });
    },
  },
};
</script>

<style scoped>
.dashboard-page {
  background: #f7fafc;
  min-height: 100vh;
}

.quick-nav {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 1rem;
}

.nav-btn {
  padding: 1rem;
  background: white;
  border: 2px solid #e0e5ee;
  border-radius: 1rem;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  color: #1f2633;
  font-weight: 500;
  font-size: 0.875rem;
}

.nav-btn:hover {
  border-color: #c82a3e;
  color: #c82a3e;
  transform: translateY(-2px);
}

.nav-btn i {
  font-size: 1.5rem;
}

.card {
  border: none;
  border-radius: 1rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.1);
}

.card-header {
  background: transparent;
  border-bottom: 1px solid #e0e5ee;
  padding: 1.5rem;
}

.card-header h6 {
  color: #1f2633;
  font-weight: 700;
  margin: 0;
}

.card-body {
  padding: 1.5rem;
}

.table {
  color: #495057;
  font-size: 0.875rem;
}

.table thead th {
  color: #5a6270;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  border: none;
}

.alert-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.alert-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f7fafc;
  border-radius: 0.75rem;
  align-items: flex-start;
}

.alert-item i {
  font-size: 1.25rem;
  min-width: 24px;
}

.alert-title {
  font-weight: 600;
  color: #1f2633;
  margin: 0;
}

.alert-time {
  font-size: 0.75rem;
  color: #a0aec0;
}
</style>