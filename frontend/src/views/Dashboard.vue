<template>
  <div class="dashboard-page">
    <div class="container-fluid py-4">
      <div v-if="loadError" class="alert alert-danger py-2 mb-4">
        {{ loadError }}
      </div>
      <div
        v-if="isLoading"
        class="alert alert-info py-2 mb-4 d-flex align-items-center gap-2"
      >
        <span class="spinner-border spinner-border-sm"></span>
        Loading dashboard data...
      </div>

      <div class="row mb-4 align-items-center gy-3">
        <div class="col">
          <h4 class="mb-1">Municipal Dashboard</h4>
          <p class="text-muted mb-0">{{ dashboardSubtitle }}</p>
        </div>
        <div class="col-12 col-lg-auto d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center justify-content-lg-end">
          <button class="fiscal-year-btn" @click="showFiscalYearModal = true">
            <i class="material-icons-round">calendar_today</i>
            <span>Fiscal Year {{ fiscalYearLabel || "Loading" }}</span>
            <i class="material-icons-round chevron-icon">expand_more</i>
          </button>
          <button
            class="btn-export"
            :disabled="isLoading || !canExport"
            @click="showExportModal = true"
          >
            <i class="material-icons-round">download</i>
            Export Summary
          </button>
        </div>
      </div>

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
                  <p class="modal-subtitle">Filter dashboard data by fiscal year</p>
                </div>
              </div>
              <button class="modal-close-btn" @click="showFiscalYearModal = false">
                <i class="material-icons-round">close</i>
              </button>
            </div>
            <div class="modal-body">
              <div class="fy-options">
                <button
                  v-for="fy in fiscalYears"
                  :key="fy.value"
                  :class="['fy-option', { 'fy-option-active': fiscalYearLabel === fy.value }]"
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
                    <span v-if="fy.tag" :class="['fy-tag', fy.tagClass]">{{ fy.tag }}</span>
                    <i
                      v-if="fiscalYearLabel === fy.value"
                      class="material-icons-round fy-check"
                    >check_circle</i>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>

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
                    FY{{ fiscalYearLabel }} - {{ organizationLabel }}
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
                  :class="['export-format-btn', { 'export-format-active': exportForm.format === fmt.value }]"
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
                    v-model="exportForm.scopes"
                    type="checkbox"
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
                    v-model="exportForm.dateFrom"
                    type="date"
                    class="date-input"
                  />
                </div>
                <div class="export-date-sep">-</div>
                <div class="export-date-field">
                  <label class="date-label">To</label>
                  <input
                    v-model="exportForm.dateTo"
                    type="date"
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
                :disabled="isExporting || exportForm.scopes.length === 0"
                @click="confirmExport"
              >
                <i class="material-icons-round">download</i>
                {{ isExporting ? "Exporting..." : `Export ${exportForm.format.toUpperCase()}` }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <div class="row mb-3">
        <div
          v-for="card in topStats"
          :key="card.key"
          class="col-lg-4 col-md-6 mb-3"
        >
          <div :class="['stat-card', card.cardClass]">
            <div :class="['stat-icon-wrap', card.iconClass]">
              <i class="material-icons-round">{{ card.icon }}</i>
            </div>
            <div class="stat-info">
              <div class="stat-badge" :class="card.badgeClass">{{ card.badge }}</div>
              <p class="stat-label">{{ card.label }}</p>
              <h3 :class="['stat-value', card.valueClass]">{{ card.value }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div
          v-for="card in bottomStats"
          :key="card.key"
          class="col-lg-3 col-md-6 mb-3"
        >
          <div :class="['stat-card', card.cardClass]">
            <div :class="['stat-icon-wrap', card.iconClass]">
              <i class="material-icons-round">{{ card.icon }}</i>
            </div>
            <div class="stat-info">
              <div class="stat-badge" :class="card.badgeClass">{{ card.badge }}</div>
              <p class="stat-label">{{ card.label }}</p>
              <h3 :class="['stat-value', card.valueClass]">{{ card.value }}</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-lg-5 mb-4">
          <div class="card h-100">
            <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
              <h6>Project Status</h6>
              <button class="btn-icon">
                <i class="material-icons-round">more_vert</i>
              </button>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
              <div class="donut-wrapper">
                <canvas ref="donutChart" width="220" height="220"></canvas>
                <div class="donut-center">
                  <span class="donut-total">{{ projectStatusTotal }}</span>
                  <span class="donut-label">TOTAL</span>
                </div>
              </div>
              <div class="donut-legend">
                <div
                  v-for="item in projectStatusLegend"
                  :key="item.label"
                  class="legend-item"
                >
                  <span class="legend-dot" :style="{ background: item.color }"></span>
                  <span>{{ item.label }} ({{ item.value }})</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-7 mb-4">
          <div class="card h-100">
            <div class="card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
              <div>
                <h6>Budget vs Expenditure</h6>
                <p class="card-subtitle">{{ budgetChartSubtitle }}</p>
              </div>
              <div class="chart-legend">
                <span class="legend-pill legend-pill-budget">Budget</span>
                <span class="legend-pill legend-pill-expenditure">Expenditure</span>
              </div>
            </div>
            <div class="card-body">
              <canvas ref="barChart" height="200"></canvas>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-lg-6 mb-4">
          <div class="card h-100">
            <div class="card-header">
              <h6>Recent Contract Updates</h6>
            </div>
            <div class="card-body p-0">
              <div v-if="recentUpdates.length === 0" class="p-4 text-center text-secondary">
                No recent activity for the selected fiscal year.
              </div>
              <div
                v-for="item in recentUpdates"
                :key="item.id"
                class="update-item"
              >
                <div :class="['update-icon-wrap', item.iconBg]">
                  <i class="material-icons-round">{{ item.icon }}</i>
                </div>
                <div class="update-content">
                  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-1">
                    <p class="update-title">{{ item.project }}</p>
                    <span class="update-time">{{ item.time }}</span>
                  </div>
                  <p class="update-desc">{{ item.description }}</p>
                </div>
              </div>
            </div>
            <div class="card-footer text-center">
              <button class="btn-link" @click="handleQuickAction('Reports')">
                View All Updates
              </button>
            </div>
          </div>
        </div>

        <div class="col-lg-6 mb-4">
          <div class="card h-100">
            <div class="card-header">
              <h6>Upcoming Deadlines</h6>
            </div>
            <div class="card-body p-0">
              <div v-if="upcomingDeadlines.length === 0" class="p-4 text-center text-secondary">
                No upcoming deadlines found in the selected fiscal year.
              </div>
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
                  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-1">
                    <p class="deadline-title">{{ deadline.title }}</p>
                    <span v-if="deadline.urgent" class="badge-urgent">URGENT</span>
                  </div>
                  <p class="deadline-desc">{{ deadline.description }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-12">
          <div class="quick-actions-header d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-1">
            <h6>Administrative Quick Actions</h6>
            <span class="text-muted">Manage your core tasks efficiently</span>
          </div>
          <div class="quick-actions-grid">
            <button
              v-for="action in visibleQuickActions"
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

      <div class="dashboard-footer">
        <span>
          &copy; {{ footerYear }} {{ organizationLabel }} - {{ organizationRegion }}. ConTrackPro {{ footerVersion }}. All Rights Reserved.
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import Chart from "chart.js/auto";
import DashboardService from "@/services/dashboard.service";

const DEFAULT_EXPORT_FORMATS = [
  { value: "pdf", label: "PDF", icon: "picture_as_pdf" },
  { value: "excel", label: "Excel", icon: "table_chart" },
  { value: "csv", label: "CSV", icon: "grid_on" },
];

export default {
  name: "Dashboard",
  data() {
    return {
      dashboard: null,
      selectedFiscalYear: "",
      showFiscalYearModal: false,
      showExportModal: false,
      isLoading: false,
      isExporting: false,
      loadError: null,
      donutChartInstance: null,
      barChartInstance: null,
      dashboardAbortController: null,
      dashboardRequestId: 0,
      exportForm: {
        format: "pdf",
        scopes: [],
        dateFrom: "",
        dateTo: "",
      },
      quickActions: [
        { id: 1, icon: "add_circle_outline", label: "Add Project", route: "infrastructure-plans" },
        { id: 2, icon: "upload_file", label: "Upload Document", route: "engineering-plans" },
        { id: 3, icon: "note_add", label: "Add Contract", route: "contract-management" },
        { id: 4, icon: "add_photo_alternate", label: "Create VO", route: "variation-orders" },
        { id: 5, icon: "bar_chart", label: "Generate Report", route: "Reports" },
      ],
    };
  },
  computed: {
    fiscalYearLabel() {
      return this.selectedFiscalYear || this.dashboard?.fiscal_year || "";
    },
    fiscalYears() {
      return this.dashboard?.fiscal_years || [];
    },
    exportFormats() {
      return this.dashboard?.export?.formats || DEFAULT_EXPORT_FORMATS;
    },
    exportScopes() {
      return this.dashboard?.export?.scopes || [];
    },
    footerYear() {
      return this.dashboard?.footer_year || new Date().getFullYear();
    },
    organizationLabel() {
      const organization = this.dashboard?.organization || {};
      return organization.office_unit || organization.name || organization.region || "LGU Tuao";
    },
    organizationRegion() {
      return this.dashboard?.organization?.region || "Municipality of Tuao";
    },
    footerVersion() {
      return this.dashboard?.footer_version || "v4.2.0";
    },
    dashboardSubtitle() {
      return `Welcome back. Here is the overview for ${this.organizationLabel} Contract Progress.`;
    },
    canExport() {
      return Boolean(this.dashboard) && this.dashboard?.permissions?.can_export !== false;
    },
    visibleQuickActions() {
      const actions = this.dashboard?.quick_actions?.length ? this.dashboard.quick_actions : this.quickActions;
      return actions.filter((action) => action.allowed !== false);
    },
    topStats() {
      return (this.dashboard?.stats || []).slice(0, 3);
    },
    bottomStats() {
      return (this.dashboard?.stats || []).slice(3);
    },
    projectStatusTotal() {
      return this.dashboard?.charts?.project_status?.total || 0;
    },
    projectStatusLegend() {
      const chart = this.dashboard?.charts?.project_status;
      if (!chart) return [];

      return (chart.labels || []).map((label, index) => ({
        label,
        value: chart.data?.[index] || 0,
        color: chart.colors?.[index] || "#9ca3af",
      }));
    },
    budgetChartSubtitle() {
      return (
        this.dashboard?.charts?.budget_vs_expenditure?.subtitle ||
        "Comparison of allocated funds vs actual disbursements per quarter."
      );
    },
    budgetChartData() {
      return this.dashboard?.charts?.budget_vs_expenditure || {
        labels: [],
        budget: [],
        expenditure: [],
      };
    },
    recentUpdates() {
      return this.dashboard?.recent_updates || [];
    },
    upcomingDeadlines() {
      return this.dashboard?.upcoming_deadlines || [];
    },
  },
  mounted() {
    this.loadDashboard();
  },
  beforeUnmount() {
    if (this.dashboardAbortController) {
      this.dashboardAbortController.abort();
      this.dashboardAbortController = null;
    }
    this.dashboardRequestId += 1;
    this.destroyCharts();
  },
  methods: {
    async loadDashboard() {
      const requestId = ++this.dashboardRequestId;
      if (this.dashboardAbortController) {
        this.dashboardAbortController.abort();
      }
      this.dashboardAbortController = typeof AbortController !== "undefined" ? new AbortController() : null;
      this.isLoading = true;
      this.loadError = null;

      try {
        const response = await DashboardService.getSummary({
          fiscal_year: this.selectedFiscalYear || undefined,
        }, this.dashboardAbortController ? { signal: this.dashboardAbortController.signal } : {});
        if (requestId !== this.dashboardRequestId) {
          return;
        }
        const payload = response.data.data || {};
        this.dashboard = payload;
        this.selectedFiscalYear = String(payload.fiscal_year || this.selectedFiscalYear || "");

        const defaults = payload.export?.defaults || {};
        if (!this.exportForm.scopes.length) {
          this.exportForm.scopes = defaults.scopes ? [...defaults.scopes] : [];
        }
        this.exportForm.format = this.exportForm.format || defaults.format || "pdf";
        this.exportForm.dateFrom = defaults.date_from || this.exportForm.dateFrom;
        this.exportForm.dateTo = defaults.date_to || this.exportForm.dateTo;

        await this.$nextTick();
        this.renderCharts();
      } catch (error) {
        if (error?.code === "ERR_CANCELED" || error?.name === "CanceledError" || requestId !== this.dashboardRequestId) {
          return;
        }
        this.loadError = error.response?.data?.message || "Unable to load dashboard data.";
      } finally {
        if (requestId === this.dashboardRequestId) {
          this.isLoading = false;
          this.dashboardAbortController = null;
        }
      }
    },

    renderCharts() {
      this.destroyCharts();
      this.renderDonutChart();
      this.renderBarChart();
    },

    destroyCharts() {
      if (this.donutChartInstance) {
        this.donutChartInstance.destroy();
        this.donutChartInstance = null;
      }
      if (this.barChartInstance) {
        this.barChartInstance.destroy();
        this.barChartInstance = null;
      }
    },

    renderDonutChart() {
      const canvas = this.$refs.donutChart;
      const chart = this.dashboard?.charts?.project_status;
      if (!canvas || !chart) return;

      this.donutChartInstance = new Chart(canvas, {
        type: "doughnut",
        data: {
          labels: chart.labels || [],
          datasets: [
            {
              data: chart.data || [],
              backgroundColor: chart.colors || [],
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

    renderBarChart() {
      const canvas = this.$refs.barChart;
      const chart = this.budgetChartData;
      if (!canvas || !chart) return;

      this.barChartInstance = new Chart(canvas, {
        type: "bar",
        data: {
          labels: chart.labels || [],
          datasets: [
            {
              label: "Budget",
              data: chart.budget || [],
              backgroundColor: "#4A90D9",
              borderRadius: 4,
              barPercentage: 0.5,
            },
            {
              label: "Expenditure",
              data: chart.expenditure || [],
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
                label: (ctx) => ` ${ctx.dataset.label}: ${this.formatMoney(ctx.parsed.y)}`,
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
                callback: (value) => this.formatMoneyShort(value),
              },
            },
          },
        },
      });
    },

    formatMoney(value) {
      return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        maximumFractionDigits: 2,
      }).format(Number(value || 0));
    },

    formatMoneyShort(value) {
      const amount = Number(value || 0);
      if (amount >= 1000000) {
        return `${this.formatMoney(amount / 1000000).replace(/\.00$/, "")}M`;
      }
      return this.formatMoney(amount);
    },

    selectFiscalYear(value) {
      const nextValue = String(value);
      if (nextValue === this.selectedFiscalYear) {
        this.showFiscalYearModal = false;
        return;
      }

      this.selectedFiscalYear = nextValue;
      this.showFiscalYearModal = false;
      this.loadDashboard();
    },

    async confirmExport() {
      if (!this.exportForm.scopes.length || this.isExporting) {
        return;
      }

      this.isExporting = true;
      this.loadError = null;

      try {
        const response = await DashboardService.exportSummary({
          fiscal_year: this.fiscalYearLabel || undefined,
          format: this.exportForm.format,
          scopes: this.exportForm.scopes,
          date_from: this.exportForm.dateFrom || undefined,
          date_to: this.exportForm.dateTo || undefined,
        });

        const ext = this.exportForm.format === "excel" ? "xlsx" : this.exportForm.format;
        const blob = new Blob([response.data], {
          type: response.headers["content-type"] || "application/octet-stream",
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.download = `dashboard_summary_fy${this.fiscalYearLabel}.${ext}`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        this.showExportModal = false;
      } catch (error) {
        this.loadError = error.response?.data?.message || "Unable to export the dashboard summary.";
      } finally {
        this.isExporting = false;
      }
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

h4 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a2e;
}

.text-muted {
  color: #6b7280;
  font-size: 0.875rem;
}

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
  border-color: #1565C0;
  box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.08);
}

.fiscal-year-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.fiscal-year-btn i {
  font-size: 1rem;
  color: #6b7280;
}

.chevron-icon {
  font-size: 1.1rem !important;
  color: #9ca3af !important;
}

.btn-export {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #1565C0;
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

.btn-export:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-export i {
  font-size: 1.1rem;
}

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
  min-width: 0;
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
  font-size: clamp(1rem, 1.6vw, 1.75rem);
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
  line-height: 1.1;
  overflow-wrap: break-word;
  word-break: break-word;
}

.stat-value-lg {
  font-size: clamp(0.8rem, 1.2vw, 1.35rem);
}

.stat-value-danger {
  color: #dc2626;
}

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
  color: #1565C0;
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

.legend-pill-budget {
  background: #4a90d9;
}

.legend-pill-expenditure {
  background: #7b6b3d;
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

.date-red .deadline-month,
.date-red .deadline-day {
  color: #dc2626;
}

.date-blue {
  background: #dbeafe;
}

.date-blue .deadline-month,
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
  border-color: #1565C0;
  color: #1565C0;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(21, 101, 192, 0.12);
}

.quick-action-btn i {
  font-size: 1.6rem;
}

.dashboard-footer {
  display: block;
  text-align: center;
  padding: 1.5rem 0 0.5rem;
  font-size: 0.75rem;
  color: #9ca3af;
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

/* ===== MODAL: fixed overflow so header/footer never get pushed off-screen ===== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1.5rem;
}

.modal-box {
  background: white;
  border-radius: 1rem;
  width: 100%;
  max-width: 520px;
  max-height: calc(100vh - 3rem);
  box-shadow: 0 20px 60px rgba(15, 23, 42, 0.18);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-box-sm {
  max-width: 400px;
}

.modal-header-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e0e5ee;
  flex-shrink: 0;
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
  color: #1565C0;
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

.modal-body {
  padding: 1.25rem 1.5rem;
  overflow-y: auto;
  flex: 1 1 auto;
  min-height: 0;
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

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #e0e5ee;
  background: #f8fafc;
  flex-shrink: 0;
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
  background: #1565C0;
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
  border-color: #1565C0;
  background: #fff5f5;
}

.fy-option-active {
  border-color: #1565C0 !important;
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
  color: #1565C0;
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
  color: #1565C0 !important;
}

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
  border-color: #1565C0;
  color: #1565C0;
}

.export-format-btn:hover i {
  color: #1565C0;
}

.export-format-active {
  border-color: #1565C0 !important;
  background: #fff5f5 !important;
  color: #1565C0 !important;
}

.export-format-active i {
  color: #1565C0 !important;
}

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
  accent-color: #1565C0;
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
  border-color: #1565C0;
}

.export-date-sep {
  font-size: 1rem;
  color: #9ca3af;
  padding-bottom: 0.5rem;
  flex-shrink: 0;
}

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

@media (max-width: 1199.98px) {
  .quick-actions-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .quick-actions-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .export-format-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .chart-legend {
    flex-wrap: wrap;
    justify-content: flex-start;
  }

  .donut-legend {
    grid-template-columns: 1fr;
    gap: 0.35rem;
  }

  .modal-header-strip,
  .modal-footer {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}

@media (max-width: 575.98px) {
  .fiscal-year-btn,
  .btn-export {
    width: 100%;
    justify-content: center;
  }

  .quick-actions-header {
    align-items: flex-start;
  }

  .quick-actions-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .stat-card {
    min-height: auto;
  }

  .card-header {
    padding: 0.875rem 1rem;
  }

  .card-body {
    padding: 1rem;
  }

  .donut-wrapper {
    width: 200px;
    height: 200px;
  }

  .donut-total {
    font-size: 1.75rem;
  }

  .update-item,
  .deadline-item {
    flex-direction: column;
  }

  .update-time {
    white-space: normal;
  }

  .modal-overlay {
    padding: 0.75rem;
  }

  .modal-box {
    max-height: calc(100vh - 1.5rem);
  }

  .modal-header-strip {
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .modal-body {
    padding: 1rem;
  }

  .modal-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .modal-btn-cancel,
  .modal-btn-export {
    width: 100%;
    justify-content: center;
  }

  .export-format-grid {
    grid-template-columns: 1fr;
  }

  .scope-item {
    align-items: flex-start;
  }

  .export-date-row {
    flex-direction: column;
    align-items: stretch;
  }

  .export-date-sep {
    display: none;
  }

  .fy-option {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .fy-option-right {
    align-self: flex-end;
  }
}
</style>
