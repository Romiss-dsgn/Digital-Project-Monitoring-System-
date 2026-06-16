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
                <div class="d-flex gap-2 align-items-center">
  <!-- Zoom controls -->
  <button class="btn btn-outline-secondary btn-sm" @click="zoomOut" title="Zoom Out">
    <i class="material-icons-round" style="font-size:1rem;vertical-align:middle;">zoom_out</i>
  </button>
  <span class="zoom-label">{{ Math.round(zoomLevel * 100) }}%</span>
  <button class="btn btn-outline-secondary btn-sm" @click="zoomIn" title="Zoom In">
    <i class="material-icons-round" style="font-size:1rem;vertical-align:middle;">zoom_in</i>
  </button>
  <!-- Fullscreen -->
  <button class="btn btn-outline-secondary btn-sm" @click="toggleFullscreen" title="Fullscreen">
    <i class="material-icons-round" style="font-size:1rem;vertical-align:middle;">
      {{ isFullscreen ? 'fullscreen_exit' : 'fullscreen' }}
    </i>
  </button>
          </div>
              </div>

              <!-- Scrollable preview area -->
              <div class="preview-scroll-area" ref="previewArea">
                <!-- Report Document -->
                <div
                  class="report-document p-4"
                  :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top center' }"
                  ref="reportDoc"
                >
                  <!-- Report Header -->
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <div class="report-agency-tag">Bureau of Fire Protection — Region II</div>
                      <h5 class="fw-bold mb-1 mt-1">PROJECT STATUS REPORT</h5>
                      <div class="text-secondary small">Report ID: <strong>#BFP-R2-2024-001</strong></div>
                    </div>
                    <div class="text-end">
                      <div class="report-official-badge">OFFICIAL</div>
                      <div class="text-secondary small mt-1">Date Generated:</div>
                      <div class="fw-semibold small">Oct 24, 2023</div>
                    </div>
                  </div>
                  <div class="report-red-divider mb-4"></div>

                  <!-- Summary Stats -->
                  <div class="row g-2 mb-4">
                    <div class="col-3">
                      <div class="stat-box">
                        <div class="stat-label">Total Projects</div>
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
                  <div class="small fw-semibold text-secondary mb-2 report-section-title">PROJECT SUMMARY TABLE</div>
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
                        <td class="fw-semibold">{{ project.name }}</td>
                        <td class="text-secondary">{{ project.contractor }}</td>
                        <td>
                          <span class="badge" :class="statusBadgeClass(project.status)">
                            {{ project.status }}
                          </span>
                        </td>
                        <td class="text-end">
                          <div class="d-flex align-items-center justify-content-end gap-2">
                            <div class="completion-bar-bg">
                              <div
                                class="completion-bar-fill"
                                :style="{
                                  width: project.completion,
                                  background: completionColor(project.completion)
                                }"
                              ></div>
                            </div>
                            <span class="fw-semibold" style="font-size:0.8rem;min-width:36px;">{{ project.completion }}</span>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  <!-- Monthly Progress Trend -->
                  <div class="small fw-semibold text-secondary mb-2 report-section-title">MONTHLY PROGRESS TREND</div>
                  <div class="chart-area d-flex align-items-end gap-1 px-2 pb-1" style="height: 90px;">
                    <div
                      v-for="(bar, index) in chartBars"
                      :key="index"
                      class="chart-bar flex-fill"
                      :style="{ height: bar.height + '%', backgroundColor: bar.color }"
                      :title="bar.month"
                    ></div>
                  </div>
                  <div class="d-flex justify-content-between px-2 mt-1 mb-4">
                    <span v-for="(bar, index) in chartBars" :key="index" class="chart-label">{{ bar.month }}</span>
                  </div>

                  <div class="report-red-divider mb-4"></div>

                  <!-- Footer -->
                  <div class="d-flex justify-content-between align-items-end">
                    <div>
                      <div class="small text-secondary mb-1">Certified By:</div>
                      <div style="border-top: 1.5px solid #333; width: 200px; margin-top: 28px;"></div>
                      <div class="small text-secondary mt-1">Region II Administrative Officer</div>
                    </div>
                    <div class="text-end">
                      <div class="small text-secondary">Printed by ConTrackPro v4.2.0</div>
                      <div class="small text-secondary">BFP Region II · FY 2024</div>
                    </div>
                  </div>

                </div>
                <!-- End Report Document -->
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fullscreen Overlay -->
    <transition name="fs-fade">
      <div v-if="isFullscreen" class="fullscreen-overlay" @keydown.esc="isFullscreen = false" tabindex="0" ref="fsOverlay">
        <div class="fullscreen-toolbar">
          <span class="fs-title"><i class="bi bi-eye me-2"></i>Live Report Preview</span>
          <div class="d-flex gap-2 align-items-center">
          <button class="btn btn-sm btn-outline-light" @click="zoomOut">
          <i class="material-icons-round" style="font-size:1rem;vertical-align:middle;">zoom_out</i>
          </button>
          <span class="zoom-label-fs">{{ Math.round(zoomLevel * 100) }}%</span>
          <button class="btn btn-sm btn-outline-light" @click="zoomIn">
          <i class="material-icons-round" style="font-size:1rem;vertical-align:middle;">zoom_in</i>
          </button>
          <button class="btn btn-sm btn-outline-light ms-2" @click="isFullscreen = false">
          <i class="material-icons-round" style="font-size:1rem;vertical-align:middle;">fullscreen_exit</i> Exit
          </button>
          </div>
        </div>
        <div class="fullscreen-body">
          <div
            class="report-document p-4 fs-report-doc"
            :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top center' }"
          >
            <!-- Report Header -->
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <div class="report-agency-tag">Bureau of Fire Protection — Region II</div>
                <h5 class="fw-bold mb-1 mt-1">PROJECT STATUS REPORT</h5>
                <div class="text-secondary small">Report ID: <strong>#BFP-R2-2024-001</strong></div>
              </div>
              <div class="text-end">
                <div class="report-official-badge">OFFICIAL</div>
                <div class="text-secondary small mt-1">Date Generated:</div>
                <div class="fw-semibold small">Oct 24, 2023</div>
              </div>
            </div>
            <div class="report-red-divider mb-4"></div>

            <!-- Summary Stats -->
            <div class="row g-2 mb-4">
              <div class="col-3"><div class="stat-box"><div class="stat-label">Total Projects</div><div class="stat-value">12</div></div></div>
              <div class="col-3"><div class="stat-box"><div class="stat-label">Total Budget</div><div class="stat-value">₱42.5M</div></div></div>
              <div class="col-3"><div class="stat-box"><div class="stat-label">Disbursed</div><div class="stat-value">₱18.2M</div></div></div>
              <div class="col-3"><div class="stat-box"><div class="stat-label">Avg Completion</div><div class="stat-value">48%</div></div></div>
            </div>

            <!-- Projects Table -->
            <div class="small fw-semibold text-secondary mb-2 report-section-title">PROJECT SUMMARY TABLE</div>
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
                <tr v-for="project in projects" :key="'fs-' + project.name">
                  <td class="fw-semibold">{{ project.name }}</td>
                  <td class="text-secondary">{{ project.contractor }}</td>
                  <td><span class="badge" :class="statusBadgeClass(project.status)">{{ project.status }}</span></td>
                  <td class="text-end">
                    <div class="d-flex align-items-center justify-content-end gap-2">
                      <div class="completion-bar-bg">
                        <div class="completion-bar-fill" :style="{ width: project.completion, background: completionColor(project.completion) }"></div>
                      </div>
                      <span class="fw-semibold" style="font-size:0.8rem;min-width:36px;">{{ project.completion }}</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Monthly Progress Trend -->
            <div class="small fw-semibold text-secondary mb-2 report-section-title">MONTHLY PROGRESS TREND</div>
            <div class="chart-area d-flex align-items-end gap-1 px-2 pb-1" style="height:90px;">
              <div v-for="(bar, i) in chartBars" :key="'fsb'+i" class="chart-bar flex-fill" :style="{ height: bar.height+'%', backgroundColor: bar.color }"></div>
            </div>
            <div class="d-flex justify-content-between px-2 mt-1 mb-4">
              <span v-for="(bar, i) in chartBars" :key="'fsl'+i" class="chart-label">{{ bar.month }}</span>
            </div>

            <div class="report-red-divider mb-4"></div>

            <!-- Footer -->
            <div class="d-flex justify-content-between align-items-end">
              <div>
                <div class="small text-secondary mb-1">Certified By:</div>
                <div style="border-top:1.5px solid #333;width:200px;margin-top:28px;"></div>
                <div class="small text-secondary mt-1">Region II Administrative Officer</div>
              </div>
              <div class="text-end">
                <div class="small text-secondary">Printed by ConTrackPro v4.2.0</div>
                <div class="small text-secondary">BFP Region II · FY 2024</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Print Modal -->
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
          <label class="bfp-check-option bfp-field-full">
            <input type="checkbox" checked /> Include certification footer
          </label>
        </div>
      </div>
    </BfpModal>

    <!-- Excel Modal -->
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

    <!-- PDF Modal -->
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
                <option v-for="report in reportTypes" :key="report.value" :value="report.value">
                  {{ report.label }}
                </option>
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
          <label class="bfp-check-option bfp-field-full">
            <input type="checkbox" checked /> Attach generated date and report ID
          </label>
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
      zoomLevel: 1,
      isFullscreen: false,
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
    zoomIn() {
      if (this.zoomLevel < 2) this.zoomLevel = Math.round((this.zoomLevel + 0.1) * 10) / 10;
    },
    zoomOut() {
      if (this.zoomLevel > 0.5) this.zoomLevel = Math.round((this.zoomLevel - 0.1) * 10) / 10;
    },
    toggleFullscreen() {
      this.isFullscreen = !this.isFullscreen;
      if (this.isFullscreen) {
        this.$nextTick(() => {
          if (this.$refs.fsOverlay) this.$refs.fsOverlay.focus();
        });
      }
    },
    statusBadgeClass(status) {
      switch (status) {
        case "ON TRACK":   return "bg-success text-white";
        case "PENDING VO": return "bg-warning text-dark";
        case "DELAYED":    return "bg-danger text-white";
        case "COMPLETED":  return "bg-primary text-white";
        default:           return "bg-secondary text-white";
      }
    },
    completionColor(completion) {
      const val = parseInt(completion);
      if (val >= 75) return "#16a34a";
      if (val >= 40) return "#d97706";
      return "#dc2626";
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

/* ── Report type selector ── */
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

/* ── Report document shell ── */
.report-document {
  border: 1px solid #e0e0e0;
  border-radius: 0.5rem;
  background: #fff;
}

/* ── Agency tag & official badge ── */
.report-agency-tag {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #7b1113;
  text-transform: uppercase;
}

.report-official-badge {
  display: inline-block;
  background: #7b1113;
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  padding: 0.2rem 0.65rem;
  border-radius: 0.25rem;
}

/* ── Red divider ── */
.report-red-divider {
  height: 3px;
  background: linear-gradient(90deg, #7b1113 0%, #c0392b 50%, #e0e0e0 100%);
  border-radius: 2px;
}

/* ── Section title ── */
.report-section-title {
  letter-spacing: 0.06em;
  border-left: 3px solid #c0392b;
  padding-left: 0.5rem;
}

/* ── Stat boxes ── */
.stat-box {
  background: #f5f5f5;
  border-radius: 0.4rem;
  padding: 0.6rem 0.75rem;
  border-left: 3px solid #c0392b;
}
.stat-label {
  font-size: 0.72rem;
  color: #888;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.stat-value {
  font-size: 1.15rem;
  font-weight: 700;
  color: #1a1a2e;
}

/* ── Table ── */
.report-table thead th {
  font-size: 0.8rem;
  color: #333;
  font-weight: 600;
  border-bottom: 2px solid #dee2e6;
  background: #fafafa;
}
.report-table tbody td {
  font-size: 0.82rem;
  vertical-align: middle;
}
.report-table tbody tr:hover {
  background: #fef9f9;
}

/* ── Completion bar ── */
.completion-bar-bg {
  width: 64px;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}
.completion-bar-fill {
  height: 6px;
  border-radius: 3px;
  transition: width 0.4s ease;
}

/* ── Chart ── */
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

/* ── Zoom label ── */
.zoom-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: #495057;
  min-width: 38px;
  text-align: center;
}

/* ── Preview scroll area ── */
.preview-scroll-area {
  overflow: auto;
  max-height: 680px;
  border-radius: 0.5rem;
  background: #f0f0f0;
  padding: 1rem;
}

/* ── Fullscreen overlay ── */
.fullscreen-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: #1a1a2e;
  display: flex;
  flex-direction: column;
  outline: none;
}

.fullscreen-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: #111827;
  border-bottom: 2px solid #7b1113;
  flex-shrink: 0;
}

.fs-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #fff;
}

.zoom-label-fs {
  font-size: 0.82rem;
  font-weight: 600;
  color: #fff;
  min-width: 42px;
  text-align: center;
}

.fullscreen-body {
  flex: 1;
  overflow: auto;
  padding: 2rem;
  display: flex;
  justify-content: center;
}

.fs-report-doc {
  width: 100%;
  max-width: 860px;
  height: fit-content;
}

/* ── Fullscreen transition ── */
.fs-fade-enter-active,
.fs-fade-leave-active {
  transition: opacity 0.2s ease;
}
.fs-fade-enter-from,
.fs-fade-leave-to {
  opacity: 0;
}
</style>