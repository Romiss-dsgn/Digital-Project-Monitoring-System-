<template>
  <div class="dashboard-page">
    <div class="container-fluid py-4">
      <!-- Page Header -->
      <div class="row mb-4 align-items-center">
        <div class="col">
          <h4 class="mb-1">Regional Dashboard</h4>
          <p class="text-muted mb-0">
            Welcome back. Here is the overview for BFP Region II Contract
            Progress.
          </p>
        </div>
        <div class="col-auto d-flex gap-2 align-items-center">
          <button class="fiscal-year-btn" @click="showFiscalYearModal = true">
            <i class="material-icons-round">calendar_today</i>
            <span>Fiscal Year {{ selectedFiscalYear }}</span>
            <i class="material-icons-round chevron-icon">expand_more</i>
          </button>
          <button class="btn-export" @click="showExportModal = true">
            <i class="material-icons-round">download</i>
            Export Summary
          </button>
        </div>
      </div>

      <!-- Fiscal Year Modal -->
      <transition name="modal-fade">
        <div
          v-if="showFiscalYearModal"
          class="modal-overlay"
          @click.self="showFiscalYearModal = false"
        >
          <div class="modal-box modal-box-sm">
            <div class="modal-header-strip">
              <div class="modal-header-left">
                <div class="modal-icon-wrap">
                  <i class="material-icons-round">calendar_today</i>
                </div>
                <div>
                  <h6 class="modal-title">Select Fiscal Year</h6>
                  <p class="modal-subtitle">
                    Filter dashboard data by fiscal year
                  </p>
                </div>
              </div>
              <button
                class="modal-close-btn"
                @click="showFiscalYearModal = false"
              >
                <i class="material-icons-round">close</i>
              </button>
            </div>
            <div class="modal-body">
              <div class="fy-options">
                <button
                  v-for="fy in fiscalYears"
                  :key="fy.value"
                  :class="[
                    'fy-option',
                    { 'fy-option-active': selectedFiscalYear === fy.value },
                  ]"
                  @click="selectFiscalYear(fy.value)"
                >
                  <div class="fy-option-left">
                    <i class="material-icons-round">event_note</i>
                    <div>
                      <p class="fy-label">Fiscal Year {{ fy.value }}</p>
                      <span class="fy-range">{{ fy.range }}</span>
                    </div>
                  </div>
                  <div class="fy-option-right">
                    <span v-if="fy.tag" :class="['fy-tag', fy.tagClass]">{{
                      fy.tag
                    }}</span>
                    <i
                      v-if="selectedFiscalYear === fy.value"
                      class="material-icons-round fy-check"
                      >check_circle</i
                    >
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>

      <!-- Export Summary Modal -->
      <transition name="modal-fade">
        <div
          v-if="showExportModal"
          class="modal-overlay"
          @click.self="showExportModal = false"
        >
          <div class="modal-box">
            <div class="modal-header-strip">
              <div class="modal-header-left">
                <div class="modal-icon-wrap modal-icon-red">
                  <i class="material-icons-round">download</i>
                </div>
                <div>
                  <h6 class="modal-title">Export Summary</h6>
                  <p class="modal-subtitle">
                    FY{{ selectedFiscalYear }} — BFP Region II
                  </p>
                </div>
              </div>
              <button class="modal-close-btn" @click="showExportModal = false">
                <i class="material-icons-round">close</i>
              </button>
            </div>
            <div class="modal-body">
              <p class="modal-section-label">Export Format</p>
              <div class="export-format-grid">
                <button
                  v-for="fmt in exportFormats"
                  :key="fmt.value"
                  :class="[
                    'export-format-btn',
                    { 'export-format-active': exportForm.format === fmt.value },
                  ]"
                  @click="exportForm.format = fmt.value"
                >
                  <i class="material-icons-round">{{ fmt.icon }}</i>
                  <span>{{ fmt.label }}</span>
                </button>
              </div>
              <p class="modal-section-label mt-3">Report Scope</p>
              <div class="export-scope-list">
                <label
                  v-for="scope in exportScopes"
                  :key="scope.value"
                  class="scope-item"
                >
                  <input
                    type="checkbox"
                    v-model="exportForm.scopes"
                    :value="scope.value"
                    class="scope-checkbox"
                  />
                  <div class="scope-icon-wrap">
                    <i class="material-icons-round">{{ scope.icon }}</i>
                  </div>
                  <div>
                    <p class="scope-label">{{ scope.label }}</p>
                    <span class="scope-desc">{{ scope.desc }}</span>
                  </div>
                </label>
              </div>
              <p class="modal-section-label mt-3">Date Range</p>
              <div class="export-date-row">
                <div class="export-date-field">
                  <label class="date-label">From</label>
                  <input
                    type="date"
                    v-model="exportForm.dateFrom"
                    class="date-input"
                  />
                </div>
                <div class="export-date-sep">—</div>
                <div class="export-date-field">
                  <label class="date-label">To</label>
                  <input
                    type="date"
                    v-model="exportForm.dateTo"
                    class="date-input"
                  />
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button class="modal-btn-cancel" @click="showExportModal = false">
                Cancel
              </button>
              <button
                class="modal-btn-export"
                @click="confirmExport"
                :disabled="exportForm.scopes.length === 0"
              >
                <i class="material-icons-round">download</i>
                Export {{ exportForm.format.toUpperCase() }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Stats Row 1 -->
      <div class="row mb-3">
        <div class="col-lg-4 col-md-6 mb-3">
          <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-blue">
              <i class="material-icons-round">assignment</i>
            </div>
            <div class="stat-info">
              <div class="stat-badge text-info-badge">+2 this month</div>
              <p class="stat-label">ACTIVE PROJECTS</p>
              <h3 class="stat-value">24</h3>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
          <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-yellow">
              <i class="material-icons-round">build</i>
            </div>
            <div class="stat-info">
              <div class="stat-badge text-success-badge">85% On Track</div>
              <p class="stat-label">ONGOING PROJECTS</p>
              <h3 class="stat-value">18</h3>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-3">
          <div class="stat-card stat-card-wide">
            <div class="stat-icon-wrap stat-icon-teal">
              <i class="material-icons-round">payments</i>
            </div>
            <div class="stat-info">
              <div class="stat-badge text-muted-badge">
                Disbursement Rate: 64.2%
              </div>
              <p class="stat-label">TOTAL PROJECT BUDGET (FY24)</p>
              <h3 class="stat-value stat-value-lg">₱142,850,000.00</h3>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Row 2 -->
      <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-green">
              <i class="material-icons-round">check_circle</i>
            </div>
            <div class="stat-info">
              <div class="stat-badge text-success-badge">ANNUAL GOAL: 92%</div>
              <p class="stat-label">COMPLETED PROJECTS</p>
              <h3 class="stat-value">12</h3>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-orange">
              <i class="material-icons-round">edit_note</i>
            </div>
            <div class="stat-info">
              <div class="stat-badge text-warning-badge">Needs Review</div>
              <p class="stat-label">PENDING VOS</p>
              <h3 class="stat-value">05</h3>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="stat-card stat-card-danger">
            <div class="stat-icon-wrap stat-icon-red">
              <i class="material-icons-round">warning</i>
            </div>
            <div class="stat-info">
              <p class="stat-label">OVERDUE DOCUMENTS</p>
              <h3 class="stat-value stat-value-danger">03</h3>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-gray">
              <i class="material-icons-round">notifications_none</i>
            </div>
            <div class="stat-info">
              <div class="stat-badge text-muted-badge">All Systems Nominal</div>
              <p class="stat-label">SYSTEM ALERTS</p>
              <h3 class="stat-value">0</h3>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="row mb-4">
        <!-- Project Status Donut -->
        <div class="col-lg-5 mb-4">
          <div class="card h-100">
            <div
              class="card-header d-flex justify-content-between align-items-center"
            >
              <h6>Project Status</h6>
              <button class="btn-icon">
                <i class="material-icons-round">more_vert</i>
              </button>
            </div>
            <div
              class="card-body d-flex flex-column align-items-center justify-content-center"
            >
              <div class="donut-wrapper">
                <canvas ref="donutChart" width="220" height="220"></canvas>
                <div class="donut-center">
                  <span class="donut-total">54</span>
                  <span class="donut-label">TOTAL</span>
                </div>
              </div>
              <div class="donut-legend">
                <div class="legend-item">
                  <span class="legend-dot" style="background: #4a90d9"></span>
                  <span>Planning (12)</span>
                </div>
                <div class="legend-item">
                  <span class="legend-dot" style="background: #7b6b3d"></span>
                  <span>Ongoing (24)</span>
                </div>
                <div class="legend-item">
                  <span class="legend-dot" style="background: #3ebd7f"></span>
                  <span>Completed (14)</span>
                </div>
                <div class="legend-item">
                  <span class="legend-dot" style="background: #e05c5c"></span>
                  <span>Delayed (4)</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Budget vs Expenditure Bar Chart -->
        <div class="col-lg-7 mb-4">
          <div class="card h-100">
            <div
              class="card-header d-flex justify-content-between align-items-center"
            >
              <div>
                <h6>Budget vs Expenditure</h6>
                <p class="card-subtitle">
                  Comparison of allocated funds vs actual disbursements per
                  quarter.
                </p>
              </div>
              <div class="chart-legend">
                <span class="legend-pill" style="background: #4a90d9"
                  >Budget</span
                >
                <span class="legend-pill" style="background: #7b6b3d"
                  >Expenditure</span
                >
              </div>
            </div>
            <div class="card-body">
              <canvas ref="barChart" height="200"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Updates + Upcoming Deadlines -->
      <div class="row mb-4">
        <!-- Recent Contract Updates -->
        <div class="col-lg-6 mb-4">
          <div class="card h-100">
            <div class="card-header">
              <h6>Recent Contract Updates</h6>
            </div>
            <div class="card-body p-0">
              <div
                v-for="item in recentUpdates"
                :key="item.id"
                class="update-item"
              >
                <div :class="['update-icon-wrap', item.iconBg]">
                  <i class="material-icons-round">{{ item.icon }}</i>
                </div>
                <div class="update-content">
                  <div class="d-flex justify-content-between">
                    <p class="update-title">{{ item.project }}</p>
                    <span class="update-time">{{ item.time }}</span>
                  </div>
                  <p class="update-desc">{{ item.description }}</p>
                </div>
              </div>
            </div>
            <div class="card-footer text-center">
              <button class="btn-link">View All Updates</button>
            </div>
          </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="col-lg-6 mb-4">
          <div class="card h-100">
            <div class="card-header">
              <h6>Upcoming Deadlines</h6>
            </div>
            <div class="card-body p-0">
              <div
                v-for="deadline in upcomingDeadlines"
                :key="deadline.id"
                class="deadline-item"
              >
                <div :class="['deadline-date', deadline.dateColor]">
                  <span class="deadline-month">{{ deadline.month }}</span>
                  <span class="deadline-day">{{ deadline.day }}</span>
                </div>
                <div class="deadline-content">
                  <div class="d-flex justify-content-between align-items-start">
                    <p class="deadline-title">{{ deadline.title }}</p>
                    <span v-if="deadline.urgent" class="badge-urgent"
                      >URGENT</span
                    >
                  </div>
                  <p class="deadline-desc">{{ deadline.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Administrative Quick Actions -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="quick-actions-header">
            <h6>Administrative Quick Actions</h6>
            <span class="text-muted">Manage your core tasks efficiently</span>
          </div>
          <div class="quick-actions-grid">
            <button
              v-for="action in quickActions"
              :key="action.id"
              class="quick-action-btn"
              @click="handleQuickAction(action.route)"
            >
              <i class="material-icons-round">{{ action.icon }}</i>
              <span>{{ action.label }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="dashboard-footer">
        <span
          >© 2024 Bureau of Fire Protection - Region II. ConTrackPro v4.2.0. All
          Rights Reserved.</span
        >
      </div>
    </div>
  </div>
</template>

<script>
import Chart from "chart.js/auto";

export default {
  name: "Dashboard",
  components: {},
  data() {
    return {
      selectedFiscalYear: "2024",
      showFiscalYearModal: false,
      showExportModal: false,
      donutChartInstance: null,
      barChartInstance: null,

      fiscalYears: [
        {
          value: "2024",
          range: "Jan 2024 – Dec 2024",
          tag: "Current",
          tagClass: "fy-tag-green",
        },
        {
          value: "2023",
          range: "Jan 2023 – Dec 2023",
          tag: "Closed",
          tagClass: "fy-tag-gray",
        },
        {
          value: "2022",
          range: "Jan 2022 – Dec 2022",
          tag: "Closed",
          tagClass: "fy-tag-gray",
        },
        {
          value: "2021",
          range: "Jan 2021 – Dec 2021",
          tag: "Closed",
          tagClass: "fy-tag-gray",
        },
      ],

      exportFormats: [
        { value: "pdf", label: "PDF", icon: "picture_as_pdf" },
        { value: "excel", label: "Excel", icon: "table_chart" },
        { value: "csv", label: "CSV", icon: "grid_on" },
      ],

      exportScopes: [
        {
          value: "contracts",
          label: "Contract Summary",
          desc: "All contracts and statuses",
          icon: "description",
        },
        {
          value: "cashflow",
          label: "Cashflow Report",
          desc: "Budget vs actual disbursements",
          icon: "trending_up",
        },
        {
          value: "variation_orders",
          label: "Variation Orders",
          desc: "Approved and pending VOs",
          icon: "edit_document",
        },
        {
          value: "accomplishments",
          label: "Project Accomplishments",
          desc: "Completion rates and milestones",
          icon: "check_circle",
        },
        {
          value: "audit",
          label: "Audit Logs",
          desc: "System activity and changes",
          icon: "manage_search",
        },
      ],

      exportForm: {
        format: "pdf",
        scopes: ["contracts"],
        dateFrom: "2024-01-01",
        dateTo: "2024-12-31",
      },

      recentUpdates: [
        {
          id: 1,
          icon: "description",
          iconBg: "icon-bg-red",
          project: "Tuguegarao Fire Station Expansion",
          time: "2h ago",
          description: "Variation Order #3 approved by Regional Director.",
        },
        {
          id: 2,
          icon: "payments",
          iconBg: "icon-bg-blue",
          project: "Cauayan City Equipment Supply",
          time: "5h ago",
          description:
            "Progress payment of ₱2.4M released to ABC Construction.",
        },
        {
          id: 3,
          icon: "history",
          iconBg: "icon-bg-yellow",
          project: "Santiago Sub-Station Repair",
          time: "Yesterday",
          description:
            "New milestone added: Foundation structural works completed.",
        },
      ],

      upcomingDeadlines: [
        {
          id: 1,
          month: "OCT",
          day: "28",
          dateColor: "date-red",
          title: "Submit Quarterly Audit Report",
          description: "Submission to Central Office BFP.",
          urgent: true,
        },
        {
          id: 2,
          month: "NOV",
          day: "02",
          dateColor: "date-blue",
          title: "Ilagan Station Completion Date",
          description: "Final inspection and turnover ceremony.",
          urgent: false,
        },
        {
          id: 3,
          month: "NOV",
          day: "05",
          dateColor: "date-blue",
          title: "Contract Renewal - Logistics",
          description: "Bidding docs for vehicle maintenance.",
          urgent: false,
        },
      ],

      quickActions: [
        {
          id: 1,
          icon: "add_circle_outline",
          label: "Add Project",
          route: "add-project",
        },
        {
          id: 2,
          icon: "upload_file",
          label: "Upload Document",
          route: "upload-document",
        },
        {
          id: 3,
          icon: "note_add",
          label: "Add Contract",
          route: "add-contract",
        },
        {
          id: 4,
          icon: "add_photo_alternate",
          label: "Create VO",
          route: "create-vo",
        },
        {
          id: 5,
          icon: "bar_chart",
          label: "Generate Report",
          route: "generate-report",
        },
      ],
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.initDonutChart();
      this.initBarChart();
    });
  },
  beforeUnmount() {
    if (this.donutChartInstance) this.donutChartInstance.destroy();
    if (this.barChartInstance) this.barChartInstance.destroy();
  },
  methods: {
    initDonutChart() {
      if (!this.$refs.donutChart) return;
      this.donutChartInstance = new Chart(this.$refs.donutChart, {
        type: "doughnut",
        data: {
          labels: ["Planning", "Ongoing", "Completed", "Delayed"],
          datasets: [
            {
              data: [12, 24, 14, 4],
              backgroundColor: ["#4A90D9", "#7B6B3D", "#3EBD7F", "#E05C5C"],
              borderWidth: 3,
              borderColor: "#ffffff",
            },
          ],
        },
        options: {
          cutout: "70%",
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: (ctx) => ` ${ctx.label}: ${ctx.parsed}`,
              },
            },
          },
          animation: { animateRotate: true },
        },
      });
    },

    initBarChart() {
      if (!this.$refs.barChart) return;
      this.barChartInstance = new Chart(this.$refs.barChart, {
        type: "bar",
        data: {
          labels: ["Q1", "Q2", "Q3", "Q4 (Proj)"],
          datasets: [
            {
              label: "Budget",
              data: [35000000, 40000000, 38000000, 29850000],
              backgroundColor: "#4A90D9",
              borderRadius: 4,
              barPercentage: 0.5,
            },
            {
              label: "Expenditure",
              data: [28000000, 33000000, 25000000, 5700000],
              backgroundColor: "#7B6B3D",
              borderRadius: 4,
              barPercentage: 0.5,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: (ctx) =>
                  ` ${ctx.dataset.label}: ₱${(ctx.parsed.y / 1000000).toFixed(
                    1,
                  )}M`,
              },
            },
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { color: "#9ca3af", font: { size: 12 } },
            },
            y: {
              grid: { color: "#f0f0f0" },
              ticks: {
                color: "#9ca3af",
                font: { size: 12 },
                callback: (val) => `₱${(val / 1000000).toFixed(0)}M`,
              },
            },
          },
        },
      });
    },

    selectFiscalYear(value) {
      this.selectedFiscalYear = value;
      this.showFiscalYearModal = false;
    },

    confirmExport() {
      if (this.exportForm.scopes.length === 0) return;
      this.showExportModal = false;
    },

    handleQuickAction(route) {
      this.$router.push({ name: route }).catch(() => {});
    },
  },
};
</script>

<style scoped>
.dashboard-page {
  background: #f7fafc;
  min-height: 100vh;
}

/* Header */
h4 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a2e;
}

.text-muted {
  color: #6b7280;
  font-size: 0.875rem;
}

.fiscal-year-select {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: 1px solid #e0e5ee;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: white;
  color: #1f2633;
  font-size: 0.875rem;
}

.fiscal-year-select select {
  border: none;
  outline: none;
  background: transparent;
  font-size: 0.875rem;
  color: #1f2633;
  cursor: pointer;
}

.fiscal-year-select i {
  font-size: 1.1rem;
  color: #6b7280;
}

.btn-export {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #c82a3e;
  color: white;
  border: none;
  border-radius: 0.5rem;
  padding: 0.55rem 1rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-export:hover {
  background: #a41f30;
}

.btn-export i {
  font-size: 1.1rem;
}

/* Stat Cards */
.stat-card {
  background: white;
  border: 1px solid #e0e5ee;
  border-radius: 0.875rem;
  padding: 1.25rem;
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  min-height: 110px;
}

.stat-card-danger {
  background: #fff5f5;
  border-color: #fecaca;
}

.stat-card-wide {
  flex: 1;
}

.stat-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 0.625rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon-wrap i {
  font-size: 1.4rem;
}

.stat-icon-blue {
  background: #dbeafe;
}
.stat-icon-blue i {
  color: #2563eb;
}
.stat-icon-yellow {
  background: #fef3c7;
}
.stat-icon-yellow i {
  color: #d97706;
}
.stat-icon-teal {
  background: #d1fae5;
}
.stat-icon-teal i {
  color: #059669;
}
.stat-icon-green {
  background: #dcfce7;
}
.stat-icon-green i {
  color: #16a34a;
}
.stat-icon-orange {
  background: #ffedd5;
}
.stat-icon-orange i {
  color: #ea580c;
}
.stat-icon-red {
  background: #fee2e2;
}
.stat-icon-red i {
  color: #dc2626;
}
.stat-icon-gray {
  background: #f1f5f9;
}
.stat-icon-gray i {
  color: #64748b;
}

.stat-info {
  flex: 1;
}

.stat-badge {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.15rem 0.45rem;
  border-radius: 0.25rem;
  display: inline-block;
  margin-bottom: 0.4rem;
}

.text-info-badge {
  background: #dbeafe;
  color: #1d4ed8;
}
.text-success-badge {
  background: #dcfce7;
  color: #15803d;
}
.text-warning-badge {
  background: #fff7ed;
  color: #c2410c;
}
.text-muted-badge {
  background: #f1f5f9;
  color: #64748b;
}

.stat-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: #9ca3af;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin: 0 0 0.2rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
  line-height: 1.1;
}

.stat-value-lg {
  font-size: 1.35rem;
}

.stat-value-danger {
  color: #dc2626;
}

/* Card */
.card {
  border: 1px solid #e0e5ee;
  border-radius: 0.875rem;
  background: white;
  overflow: hidden;
}

.card-header {
  background: transparent;
  border-bottom: 1px solid #e0e5ee;
  padding: 1rem 1.25rem;
}

.card-header h6 {
  color: #1f2633;
  font-weight: 700;
  margin: 0;
  font-size: 0.9rem;
}

.card-subtitle {
  font-size: 0.75rem;
  color: #9ca3af;
  margin: 0.2rem 0 0;
}

.card-body {
  padding: 1.25rem;
}

.card-body.p-0 {
  padding: 0;
}

.card-footer {
  border-top: 1px solid #e0e5ee;
  padding: 0.75rem;
  background: transparent;
}

.btn-link {
  background: none;
  border: none;
  color: #c82a3e;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
}

.btn-link:hover {
  text-decoration: underline;
}

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  color: #9ca3af;
  padding: 0.25rem;
  border-radius: 0.25rem;
  display: flex;
  align-items: center;
}

.btn-icon:hover {
  color: #1f2633;
}

/* Donut Chart */
.donut-wrapper {
  position: relative;
  width: 220px;
  height: 220px;
  margin: 0 auto 1.25rem;
}

.donut-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  pointer-events: none;
}

.donut-total {
  display: block;
  font-size: 2rem;
  font-weight: 700;
  color: #1a1a2e;
  line-height: 1;
}

.donut-label {
  display: block;
  font-size: 0.7rem;
  font-weight: 600;
  color: #9ca3af;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin-top: 0.2rem;
}

.donut-legend {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem 1.5rem;
  width: 100%;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: #495057;
}

.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

/* Chart legend pills */
.chart-legend {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.legend-pill {
  font-size: 0.7rem;
  color: white;
  padding: 0.15rem 0.6rem;
  border-radius: 1rem;
  font-weight: 600;
}

/* Recent Updates */
.update-item {
  display: flex;
  gap: 0.875rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  align-items: flex-start;
}

.update-item:last-child {
  border-bottom: none;
}

.update-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.update-icon-wrap i {
  font-size: 1.1rem;
}

.icon-bg-red {
  background: #fee2e2;
}
.icon-bg-red i {
  color: #dc2626;
}
.icon-bg-blue {
  background: #dbeafe;
}
.icon-bg-blue i {
  color: #2563eb;
}
.icon-bg-yellow {
  background: #fef3c7;
}
.icon-bg-yellow i {
  color: #d97706;
}

.update-content {
  flex: 1;
  min-width: 0;
}

.update-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2633;
  margin: 0 0 0.2rem;
}

.update-time {
  font-size: 0.75rem;
  color: #9ca3af;
  white-space: nowrap;
  flex-shrink: 0;
}

.update-desc {
  font-size: 0.8rem;
  color: #6b7280;
  margin: 0;
}

/* Upcoming Deadlines */
.deadline-item {
  display: flex;
  gap: 0.875rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #f1f5f9;
  align-items: flex-start;
}

.deadline-item:last-child {
  border-bottom: none;
}

.deadline-date {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 44px;
  padding: 0.35rem 0.5rem;
  border-radius: 0.5rem;
}

.date-red {
  background: #fee2e2;
}
.date-red .deadline-month {
  color: #dc2626;
}
.date-red .deadline-day {
  color: #dc2626;
}
.date-blue {
  background: #dbeafe;
}
.date-blue .deadline-month {
  color: #1d4ed8;
}
.date-blue .deadline-day {
  color: #1d4ed8;
}

.deadline-month {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.deadline-day {
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1;
}

.deadline-content {
  flex: 1;
}

.deadline-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2633;
  margin: 0 0 0.2rem;
}

.deadline-desc {
  font-size: 0.8rem;
  color: #6b7280;
  margin: 0;
}

.badge-urgent {
  background: #dc2626;
  color: white;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 0.25rem;
  letter-spacing: 0.03em;
  white-space: nowrap;
}

/* Quick Actions */
.quick-actions-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.quick-actions-header h6 {
  color: #1f2633;
  font-weight: 700;
  font-size: 0.9rem;
  margin: 0;
}

.quick-actions-header span {
  font-size: 0.8rem;
  color: #9ca3af;
}

.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1rem;
}

.quick-action-btn {
  background: white;
  border: 1px solid #e0e5ee;
  border-radius: 0.875rem;
  padding: 1.5rem 1rem;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.625rem;
  transition: all 0.2s ease;
  color: #1f2633;
  font-size: 0.8rem;
  font-weight: 500;
}

.quick-action-btn:hover {
  border-color: #c82a3e;
  color: #c82a3e;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(200, 42, 62, 0.12);
}

.quick-action-btn i {
  font-size: 1.6rem;
}

/* Dashboard Footer */
.dashboard-footer {
  display: none;
  text-align: center;
  padding: 1.5rem 0 0.5rem;
  font-size: 0.75rem;
  color: #9ca3af;
}

/* Responsive */
@media (max-width: 768px) {
  .quick-actions-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 480px) {
  .quick-actions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* ── Fiscal Year Button ── */
.fiscal-year-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  border: 1px solid #e0e5ee;
  border-radius: 0.5rem;
  padding: 0.5rem 0.875rem;
  background: white;
  color: #1f2633;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.fiscal-year-btn:hover {
  border-color: #c82a3e;
  box-shadow: 0 0 0 3px rgba(200, 42, 62, 0.08);
}

.fiscal-year-btn i {
  font-size: 1rem;
  color: #6b7280;
}

.chevron-icon {
  font-size: 1.1rem !important;
  color: #9ca3af !important;
}

/* ── Modal Overlay ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.modal-box {
  background: white;
  border-radius: 1rem;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18);
  overflow: hidden;
}

.modal-box-sm {
  max-width: 400px;
}

/* Modal Header */
.modal-header-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e0e5ee;
}

.modal-header-left {
  display: flex;
  align-items: center;
  gap: 0.875rem;
}

.modal-icon-wrap {
  width: 42px;
  height: 42px;
  border-radius: 0.625rem;
  background: #dbeafe;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.modal-icon-wrap i {
  font-size: 1.25rem;
  color: #2563eb;
}

.modal-icon-red {
  background: #fee2e2;
}

.modal-icon-red i {
  color: #c82a3e;
}

.modal-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
}

.modal-subtitle {
  font-size: 0.775rem;
  color: #9ca3af;
  margin: 0.1rem 0 0;
}

.modal-close-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: #9ca3af;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.375rem;
  transition: background 0.15s, color 0.15s;
  flex-shrink: 0;
}

.modal-close-btn:hover {
  background: #f1f5f9;
  color: #1f2633;
}

.modal-close-btn i {
  font-size: 1.25rem;
}

/* Modal Body */
.modal-body {
  padding: 1.25rem 1.5rem;
}

.modal-section-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0 0 0.75rem;
}

.mt-3 {
  margin-top: 1.25rem !important;
}

/* Modal Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e0e5ee;
  background: #f8fafc;
}

.modal-btn-cancel {
  background: white;
  border: 1px solid #e0e5ee;
  border-radius: 0.5rem;
  padding: 0.55rem 1.25rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #4b5563;
  cursor: pointer;
  transition: background 0.15s;
}

.modal-btn-cancel:hover {
  background: #f1f5f9;
}

.modal-btn-export {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  background: #c82a3e;
  color: white;
  border: none;
  border-radius: 0.5rem;
  padding: 0.55rem 1.25rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.modal-btn-export:hover:not(:disabled) {
  background: #a41f30;
}

.modal-btn-export:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.modal-btn-export i {
  font-size: 1rem;
}

/* ── Fiscal Year Options ── */
.fy-options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.fy-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.875rem 1rem;
  border: 1.5px solid #e0e5ee;
  border-radius: 0.75rem;
  background: white;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  text-align: left;
  width: 100%;
}

.fy-option:hover {
  border-color: #c82a3e;
  background: #fff5f5;
}

.fy-option-active {
  border-color: #c82a3e !important;
  background: #fff5f5 !important;
}

.fy-option-left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.fy-option-left > i {
  font-size: 1.25rem;
  color: #6b7280;
}

.fy-option-active .fy-option-left > i {
  color: #c82a3e;
}

.fy-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1f2633;
  margin: 0;
}

.fy-range {
  font-size: 0.75rem;
  color: #9ca3af;
}

.fy-option-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.fy-tag {
  font-size: 0.65rem;
  font-weight: 700;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  letter-spacing: 0.04em;
}

.fy-tag-green {
  background: #dcfce7;
  color: #15803d;
}

.fy-tag-gray {
  background: #f1f5f9;
  color: #64748b;
}

.fy-check {
  font-size: 1.1rem !important;
  color: #c82a3e !important;
}

/* ── Export Format Grid ── */
.export-format-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.625rem;
}

.export-format-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
  padding: 0.875rem 0.5rem;
  border: 1.5px solid #e0e5ee;
  border-radius: 0.75rem;
  background: white;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 600;
  color: #4b5563;
  transition: border-color 0.2s, background 0.2s, color 0.2s;
}

.export-format-btn i {
  font-size: 1.5rem;
  color: #9ca3af;
  transition: color 0.2s;
}

.export-format-btn:hover {
  border-color: #c82a3e;
  color: #c82a3e;
}

.export-format-btn:hover i {
  color: #c82a3e;
}

.export-format-active {
  border-color: #c82a3e !important;
  background: #fff5f5 !important;
  color: #c82a3e !important;
}

.export-format-active i {
  color: #c82a3e !important;
}

/* ── Export Scope List ── */
.export-scope-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.scope-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border: 1px solid #e0e5ee;
  border-radius: 0.625rem;
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
}

.scope-item:hover {
  background: #f8fafc;
  border-color: #c0cadc;
}

.scope-checkbox {
  width: 16px;
  height: 16px;
  accent-color: #c82a3e;
  flex-shrink: 0;
  cursor: pointer;
}

.scope-icon-wrap {
  width: 32px;
  height: 32px;
  border-radius: 0.375rem;
  background: #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.scope-icon-wrap i {
  font-size: 1rem;
  color: #6b7280;
}

.scope-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1f2633;
  margin: 0;
}

.scope-desc {
  font-size: 0.75rem;
  color: #9ca3af;
}

/* ── Export Date Row ── */
.export-date-row {
  display: flex;
  align-items: flex-end;
  gap: 0.75rem;
}

.export-date-field {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.date-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #6b7280;
}

.date-input {
  border: 1px solid #e0e5ee;
  border-radius: 0.5rem;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  color: #1f2633;
  width: 100%;
  outline: none;
  transition: border-color 0.2s;
}

.date-input:focus {
  border-color: #c82a3e;
}

.export-date-sep {
  font-size: 1rem;
  color: #9ca3af;
  padding-bottom: 0.5rem;
  flex-shrink: 0;
}

/* ── Modal Transition ── */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-active .modal-box,
.modal-fade-leave-active .modal-box {
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .modal-box,
.modal-fade-leave-to .modal-box {
  transform: translateY(-12px);
  opacity: 0;
}
</style>
