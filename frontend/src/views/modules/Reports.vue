<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Reports Center</h4>
          <p class="text-secondary small">Generate, view, and export comprehensive project management records.</p>
        </div>
        <div class="col-lg-4 d-flex justify-content-end gap-2">
          <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" @click="showPrintModal = true">
            <i class="bi bi-printer"></i> Print
          </button>
          <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" @click="showExcelModal = true">
            <i class="bi bi-file-earmark-excel"></i> Excel
          </button>
          <button class="btn btn-danger btn-sm d-flex align-items-center gap-1" @click="showPdfModal = true">
            <i class="bi bi-file-earmark-pdf"></i> PDF Export
          </button>
        </div>
      </div>

      <div class="row g-4">
        <!-- Left Panel -->
        <div class="col-lg-4">
          <!-- Step 1: Select Report Type -->
          <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
              <h6 class="fw-semibold mb-3">
                <i class="bi bi-list-check me-1 text-primary"></i> 1. Select Report Type
              </h6>
              <div class="d-flex flex-column gap-2">
                <label
                  v-for="report in reportTypes"
                  :key="report.value"
                  class="report-type-option"
                  :class="{ active: selectedReport === report.value }"
                  @click="selectedReport = report.value"
                >
                  <input
                    type="radio"
                    :value="report.value"
                    v-model="selectedReport"
                    class="me-2"
                  />
                  <div>
                    <div class="fw-semibold small">{{ report.label }}</div>
                    <div class="text-secondary" style="font-size: 0.78rem;">{{ report.description }}</div>
                  </div>
                </label>
              </div>
            </div>
          </div>

          <!-- Step 2: Refine Results -->
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <h6 class="fw-semibold mb-3">
                <i class="bi bi-funnel me-1 text-primary"></i> 2. Refine Results
              </h6>

              <div class="mb-3">
                <label class="form-label small fw-semibold">Date Range</label>
                <div class="d-flex gap-2">
                  <input type="date" class="form-control form-control-sm" v-model="dateFrom" />
                  <input type="date" class="form-control form-control-sm" v-model="dateTo" />
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold">Project Portfolio</label>
                <select class="form-select form-select-sm" v-model="selectedPortfolio">
                  <option value="all">All active projects (Region II)</option>
                  <option value="region1">All active projects (Region I)</option>
                  <option value="completed">Completed projects</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label small fw-semibold">Lead Contractor</label>
                <select class="form-select form-select-sm" v-model="selectedContractor">
                  <option value="all">All registered contractors</option>
                  <option value="metrobuild">MetroBuild Inc.</option>
                  <option value="northluzon">North Luzon Infra</option>
                  <option value="buildstrong">BuildStrong Corp.</option>
                  <option value="luzonpaving">Luzon Paving Co.</option>
                </select>
              </div>

              <button class="btn btn-primary w-100 btn-sm" @click="refreshPreview">
                Refresh Data Preview
              </button>
            </div>
          </div>
        </div>

        <!-- Right Panel: Live Report Preview -->
        <div class="col-lg-8">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">
                  <i class="bi bi-eye me-1 text-primary"></i> Live Report Preview
                </h6>
                <div class="d-flex gap-2">
                  <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-zoom-in"></i></button>
                  <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-fullscreen"></i></button>
                </div>
              </div>

              <!-- Report Document -->
              <div class="report-document p-4">
                <!-- Report Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                  <div>
                    <h5 class="fw-bold mb-1">PROJECT STATUS REPORT</h5>
                    <div class="text-secondary small">Report ID: #BFP-R2-2024-001</div>
                  </div>
                  <div class="text-end">
                    <div class="fw-semibold small">BFP Region II</div>
                    <div class="text-secondary small">Date Generated: Oct 24, 2023</div>
                  </div>
                </div>
                <hr class="my-2" />

                <!-- Summary Stats -->
                <div class="row g-2 mb-4">
                  <div class="col-3">
                    <div class="stat-box">
                      <div class="stat-label">Projects</div>
                      <div class="stat-value">12</div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="stat-box">
                      <div class="stat-label">Total Budget</div>
                      <div class="stat-value">₱42.5M</div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="stat-box">
                      <div class="stat-label">Disbursed</div>
                      <div class="stat-value">₱18.2M</div>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="stat-box">
                      <div class="stat-label">Avg Completion</div>
                      <div class="stat-value">48%</div>
                    </div>
                  </div>
                </div>

                <!-- Projects Table -->
                <table class="table table-sm report-table mb-4">
                  <thead>
                    <tr>
                      <th>Project Name</th>
                      <th>Contractor</th>
                      <th>Status</th>
                      <th class="text-end">Completion</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="project in projects" :key="project.name">
                      <td>{{ project.name }}</td>
                      <td>{{ project.contractor }}</td>
                      <td>
                        <span class="badge" :class="statusBadgeClass(project.status)">
                          {{ project.status }}
                        </span>
                      </td>
                      <td class="text-end fw-semibold">{{ project.completion }}</td>
                    </tr>
                  </tbody>
                </table>

                <!-- Monthly Progress Trend -->
                <div class="mb-3">
                  <div class="small fw-semibold text-secondary mb-2">MONTHLY PROGRESS TREND</div>
                  <div class="chart-area d-flex align-items-end gap-1 px-2 pb-1" style="height: 80px;">
                    <div
                      v-for="(bar, index) in chartBars"
                      :key="index"
                      class="chart-bar flex-fill"
                      :style="{ height: bar.height + '%', backgroundColor: bar.color }"
                      :title="bar.month"
                    ></div>
                  </div>
                  <div class="d-flex justify-content-between px-2 mt-1">
                    <span v-for="(bar, index) in chartBars" :key="index" class="chart-label">{{ bar.month }}</span>
                  </div>
                </div>

                <hr class="my-3" />

                <!-- Footer -->
                <div class="d-flex justify-content-between align-items-end">
                  <div>
                    <div class="small text-secondary">Certified By:</div>
                    <div style="border-top: 1px solid #333; width: 180px; margin-top: 24px;"></div>
                    <div class="small text-secondary mt-1">Region II Administrative Officer</div>
                  </div>
                  <div>
                    <span class="badge bg-dark text-white small">OFFICIAL REPORT</span>
                  </div>
                </div>
              </div>
              <!-- End Report Document -->

            </div>
          </div>
        </div>
      </div>
    </div>

    <BfpModal
      :show="showPrintModal"
      title="Print Report"
      stripe="REPORT PRINT SETUP"
      confirm-text="Print Report"
      confirm-icon="print"
      @close="showPrintModal = false"
      @confirm="showPrintModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">print</i> Print Options</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Paper Size</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">article</i>
              <select class="bfp-input bfp-select">
                <option>A4</option>
                <option>Letter</option>
                <option>Legal</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Orientation</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">crop_rotate</i>
              <select class="bfp-input bfp-select">
                <option>Portrait</option>
                <option>Landscape</option>
              </select>
            </div>
          </div>
          <label class="bfp-check-option bfp-field-full"><input type="checkbox" checked /> Include certification footer</label>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showExcelModal"
      title="Export Report to Excel"
      stripe="SPREADSHEET EXPORT"
      confirm-text="Export Excel"
      confirm-icon="grid_on"
      @close="showExcelModal = false"
      @confirm="showExcelModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">table_view</i> Workbook Content</div>
        <div class="bfp-filter-grid">
          <label class="bfp-check-option"><input type="checkbox" checked /> Summary dashboard</label>
          <label class="bfp-check-option"><input type="checkbox" checked /> Project table</label>
          <label class="bfp-check-option"><input type="checkbox" /> Chart data</label>
          <label class="bfp-check-option"><input type="checkbox" /> Filter metadata</label>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showPdfModal"
      title="PDF Export"
      stripe="OFFICIAL REPORT PACKAGE"
      confirm-text="Generate PDF"
      confirm-icon="picture_as_pdf"
      @close="showPdfModal = false"
      @confirm="showPdfModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">picture_as_pdf</i> PDF Settings</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Report Type</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">summarize</i>
              <select class="bfp-input bfp-select" v-model="selectedReport">
                <option v-for="report in reportTypes" :key="report.value" :value="report.value">{{ report.label }}</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Classification</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">verified</i>
              <select class="bfp-input bfp-select">
                <option>Official Report</option>
                <option>Draft Preview</option>
                <option>Internal Copy</option>
              </select>
            </div>
          </div>
          <label class="bfp-check-option bfp-field-full"><input type="checkbox" checked /> Attach generated date and report ID</label>
        </div>
      </div>
    </BfpModal>
  </div>
</template>

<script>
import BfpModal from "@/components/BfpModal.vue";

export default {
  name: "Reports",
  components: { BfpModal },
  data() {
    return {
      showPrintModal: false,
      showExcelModal: false,
      showPdfModal: false,
      selectedReport: "project-status",
      dateFrom: "",
      dateTo: "",
      selectedPortfolio: "all",
      selectedContractor: "all",
      reportTypes: [
        {
          value: "project-status",
          label: "Project Status Report",
          description: "Milestones, completion %, and delays.",
        },
        {
          value: "contract-summary",
          label: "Contract Summary",
          description: "Legal terms, parties, and amendments.",
        },
        {
          value: "cashflow-analysis",
          label: "Cashflow Analysis",
          description: "Disbursements, remaining budget, and forecasts.",
        },
        {
          value: "variation-orders",
          label: "Variation Orders Audit",
          description: "History of scope changes and costs.",
        },
      ],
      projects: [
        { name: "Station Refurbishment RII", contractor: "MetroBuild Inc.", status: "ON TRACK", completion: "75%" },
        { name: "Cauayan Fire Hub Phase 1", contractor: "North Luzon Infra", status: "PENDING VO", completion: "32%" },
        { name: "Logistics Center Expansion", contractor: "BuildStrong Corp.", status: "DELAYED", completion: "12%" },
        { name: "District 3 Service Road", contractor: "Luzon Paving Co.", status: "COMPLETED", completion: "100%" },
      ],
      chartBars: [
        { month: "JAN", height: 40, color: "#c0a0a0" },
        { month: "MAR", height: 55, color: "#c0a0a0" },
        { month: "MAY", height: 75, color: "#8b1a1a" },
        { month: "JUL", height: 90, color: "#8b1a1a" },
        { month: "SEP", height: 60, color: "#c0a0a0" },
        { month: "NOV", height: 45, color: "#c0a0a0" },
      ],
    };
  },
  methods: {
    statusBadgeClass(status) {
      switch (status) {
        case "ON TRACK": return "bg-success text-white";
        case "PENDING VO": return "bg-warning text-dark";
        case "DELAYED": return "bg-danger text-white";
        case "COMPLETED": return "bg-primary text-white";
        default: return "bg-secondary text-white";
      }
    },
    refreshPreview() {
      // Placeholder for refresh logic
    },
  },
};
</script>

<style scoped>
.module-page {
  min-height: 100vh;
  background: #f8f9fc;
}
.card {
  border-radius: 1rem;
}

/* Report type selector */
.report-type-option {
  display: flex;
  align-items: flex-start;
  padding: 0.65rem 0.75rem;
  border: 1px solid #e0e0e0;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
}
.report-type-option:hover {
  background: #f5f5f5;
}
.report-type-option.active {
  background: #fef2f2;
  border-color: #c0392b;
  color: #c0392b;
}
.report-type-option input[type="radio"] {
  margin-top: 2px;
  accent-color: #c0392b;
}

/* Report document */
.report-document {
  border: 1px solid #e0e0e0;
  border-radius: 0.5rem;
  background: #fff;
}

/* Stat boxes */
.stat-box {
  background: #f5f5f5;
  border-radius: 0.4rem;
  padding: 0.5rem 0.75rem;
}
.stat-label {
  font-size: 0.72rem;
  color: #888;
}
.stat-value {
  font-size: 1.1rem;
  font-weight: 700;
}

/* Table */
.report-table thead th {
  font-size: 0.8rem;
  color: #333;
  font-weight: 600;
  border-bottom: 2px solid #dee2e6;
}
.report-table tbody td {
  font-size: 0.82rem;
  vertical-align: middle;
}

/* Chart */
.chart-area {
  background: #fafafa;
  border-radius: 0.4rem;
  border: 1px solid #eee;
}
.chart-bar {
  border-radius: 3px 3px 0 0;
  min-width: 0;
  transition: opacity 0.2s;
}
.chart-bar:hover {
  opacity: 0.75;
}
.chart-label {
  font-size: 0.65rem;
  color: #999;
  flex: 1;
  text-align: center;
}
</style>
