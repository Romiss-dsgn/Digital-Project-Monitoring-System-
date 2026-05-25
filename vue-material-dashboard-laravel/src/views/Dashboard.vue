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
    StatusBadge
  },
  data() {
    return {
      recentUpdates: [
        { id: 1, project: "Road Widening Project", status: "on_track", progress: 65, lastUpdated: "2 hours ago" },
        { id: 2, project: "Fire Station Construction", status: "pending", progress: 45, lastUpdated: "1 day ago" },
        { id: 3, project: "Renovation - Santiago City", status: "delayed", progress: 30, lastUpdated: "3 days ago" },
        { id: 4, project: "Regional Office Development", status: "in_progress", progress: 78, lastUpdated: "5 hours ago" }
      ],
      pendingAlerts: [
        { id: 1, icon: "warning", icon_class: "text-danger", title: "Project Delayed", time: "Road Widening Project delayed by 5 days" },
        { id: 2, icon: "edit_document", icon_class: "text-warning", title: "Pending Approval", time: "2 variation orders awaiting approval" },
        { id: 3, icon: "trending_down", icon_class: "text-warning", title: "Budget Variance", time: "Contract #REG-II-001 exceeding budget" }
      ]
    };
  },
  methods: {
    navigateTo(route) {
      this.$router.push({ name: route });
    }
  }
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

        <div class="row">
          <div class="col-lg-3 col-md-6 col-sm-6">
            <mini-statistics-card
              :title="{ text: 'Today\'s Money', value: '$53k' }"
              detail="<span class='text-success text-sm font-weight-bolder'>+55%</span> than last week"
              :icon="{
                name: 'weekend',
                color: 'text-white',
                background: 'dark',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-sm-6 mt-lg-0 mt-4">
            <mini-statistics-card
              :title="{ text: 'Today\'s Users', value: '2,300' }"
              detail="<span class='text-success text-sm font-weight-bolder'>+3%</span> than last month"
              :icon="{
                name: 'leaderboard',
                color: 'text-white',
                background: 'primary',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-sm-6 mt-lg-0 mt-4">
            <mini-statistics-card
              :title="{ text: 'New Clients', value: '3,462' }"
              detail="<span class='text-danger text-sm font-weight-bolder'>-2%</span> than yesterday"
              :icon="{
                name: 'person',
                color: 'text-white',
                background: 'success',
              }"
            />
          </div>
          <div class="col-lg-3 col-md-6 col-sm-6 mt-lg-0 mt-4">
            <mini-statistics-card
              :title="{ text: 'Sales', value: '$103,430' }"
              detail="<span class='text-success text-sm font-weight-bolder'>+5%</span> Just updated"
              :icon="{
                name: 'weekend',
                color: 'text-white',
                background: 'info',
              }"
            />
          </div>
        </div>
        <div class="row mt-4">
          <div class="col-lg-4 col-md-6 mt-4">
            <chart-holder-card
              title="Website Views"
              subtitle="Last Campaign Performance"
              update="campaign sent 2 days ago"
            >
              <reports-bar-chart
                :chart="{
                  labels: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
                  datasets: {
                    label: 'Sales',
                    data: [50, 20, 10, 22, 50, 10, 40],
                  },
                }"
              />
            </chart-holder-card>
          </div>
          <div class="col-lg-4 col-md-6 mt-4">
            <chart-holder-card
              title="Daily Sales"
              subtitle="(<span class='font-weight-bolder'>+15%</span>) increase in today sales."
              update="updated 4 min ago"
              color="success"
            >
              <reports-line-chart
                :chart="{
                  labels: [
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec',
                  ],
                  datasets: {
                    label: 'Mobile apps',
                    data: [50, 40, 300, 320, 500, 350, 200, 230, 500],
                  },
                }"
              />
            </chart-holder-card>
          </div>
          <div class="col-lg-4 mt-4">
            <chart-holder-card
              title="Completed Tasks"
              subtitle="Last Campaign Performance"
              update="just updated"
              color="dark"
            >
              <reports-line-chart
                id="tasks-chart"
                :chart="{
                  labels: [
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec',
                  ],
                  datasets: {
                    label: 'Mobile apps',
                    data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
                  },
                }"
              />
            </chart-holder-card>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
        <project-card
          title="Projects"
          description="<i class='fa fa-check text-info' aria-hidden='true'></i> <span class='font-weight-bold ms-1'>30 done</span> this month"
          :headers="['Companies', 'Members', 'Budget', 'Progress']"
          :projects="[
            {
              logo: logoXD,
              title: 'Material XD Material XD Version',
              members: [team1, team2, team3, team4],
              budget: '$14,000',
              progress: { percentage: 60, color: 'info' },
            },
            {
              logo: logoAtlassian,
              title: 'Add Progress Track',
              members: [team2, team4],
              budget: '$3,000',
              progress: { percentage: 10, color: 'info' },
            },
            {
              logo: logoSlack,
              title: 'Fix Platform Errors',
              members: [team3, team1],
              budget: 'Not set',
              progress: { percentage: 100, color: 'success' },
            },
            {
              logo: logoSpotify,
              title: 'Launch our Mobile App',
              members: [team4, team3, team4, team1],
              budget: '$20,500',
              progress: { percentage: 100, color: 'success' },
            },
            {
              logo: logoJira,
              title: 'Add the New Pricing Page',
              members: [team4],
              budget: '$500',
              progress: { percentage: 25, color: 'info' },
            },
            {
              logo: logoJira,
              title: 'Redesign New Online Shop',
              members: [team1, team4],
              budget: '$2,000',
              progress: { percentage: 40, color: 'info' },
            },
          ]"
        />
      </div>
      <div class="col-lg-4 col-md-6">
        <timeline-list
          class="h-100"
          title="Orders overview"
          description="<i class='fa fa-arrow-up text-success' aria-hidden='true'></i>
        <span class='font-weight-bold'>24%</span> this month"
        >
          <timeline-item
            :icon="{
              component: 'notifications',
              class: 'text-success',
            }"
            title="$2400 Design changes"
            date-time="22 DEC 7:20 PM"
          />
          <TimelineItem
            :icon="{
              component: 'code',
              class: 'text-danger',
            }"
            title="New order #1832412"
            date-time="21 DEC 11 PM"
          />
          <TimelineItem
            :icon="{
              component: 'shopping_cart',
              class: 'text-info',
            }"
            title="Server payments for April"
            date-time="21 DEC 9:34 PM"
          />
          <TimelineItem
            :icon="{
              component: 'credit_card',
              class: 'text-warning',
            }"
            title="New card added for order #4395133"
            date-time="20 DEC 2:20 AM"
          />
          <TimelineItem
            :icon="{
              component: 'vpn_key',
              class: 'text-primary',
            }"
            title="Unlock packages for development"
            date-time="18 DEC 4:54 AM"
            class="pb-1"
          />
        </timeline-list>
      </div>
    </div>
  </div>
</template>
<script>
import ChartHolderCard from "./components/ChartHolderCard.vue";
import ReportsBarChart from "@/examples/Charts/ReportsBarChart.vue";
import ReportsLineChart from "@/examples/Charts/ReportsLineChart.vue";
import MiniStatisticsCard from "./components/MiniStatisticsCard.vue";
import ProjectCard from "./components/ProjectCard.vue";
import TimelineList from "@/examples/Cards/TimelineList.vue";
import TimelineItem from "@/examples/Cards/TimelineItem.vue";
import logoXD from "@/assets/img/small-logos/logo-xd.svg";
import logoAtlassian from "@/assets/img/small-logos/logo-atlassian.svg";
import logoSlack from "@/assets/img/small-logos/logo-slack.svg";
import logoSpotify from "@/assets/img/small-logos/logo-spotify.svg";
import logoJira from "@/assets/img/small-logos/logo-jira.svg";
import logoInvision from "@/assets/img/small-logos/logo-invision.svg";
import team1 from "@/assets/img/team-1.jpg";
import team2 from "@/assets/img/team-2.jpg";
import team3 from "@/assets/img/team-3.jpg";
import team4 from "@/assets/img/team-4.jpg";
export default {
  name: "dashboard-default",
  data() {
    return {
      logoXD,
      team1,
      team2,
      team3,
      team4,
      logoAtlassian,
      logoSlack,
      logoSpotify,
      logoJira,
      logoInvision,
    };
  },
  components: {
    ChartHolderCard,
    ReportsBarChart,
    ReportsLineChart,
    MiniStatisticsCard,
    ProjectCard,
    TimelineList,
    TimelineItem,
  },
};
</script>
