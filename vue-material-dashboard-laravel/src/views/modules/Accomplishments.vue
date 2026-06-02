<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Accomplishments Monitoring</h4>
          <p class="text-secondary small">Track milestones and project completion status</p>
        </div>
        <div class="col-lg-4 text-end">
          <button class="btn btn-light btn-sm me-2" @click="showFilterModal = true">
            <i class="material-icons-round">filter_list</i> Filters
          </button>
          <button class="btn btn-primary btn-sm" @click="showUploadReportModal = true">
            <i class="material-icons-round">upload</i> Upload Report
          </button>
        </div>
      </div>

      <!-- Summary Cards Row -->
      <div class="row mb-4 align-items-stretch">
        <!-- Overall Progress -->
        <div class="col-md-3 mb-3">
          <div class="card summary-card h-100">
            <div class="card-body d-flex flex-column justify-content-center">
              <div class="d-flex align-items-center mb-1">
                <span class="summary-label">OVERALL PROGRESS</span>
                <i class="material-icons-round ms-2 summary-icon-chart">insert_chart</i>
              </div>
              <div class="summary-value">78.4%</div>
              <div class="progress summary-progress mt-2">
                <div class="progress-fill" style="width: 78.4%"></div>
              </div>
              <div class="summary-sub mt-1">+4.2% from last month</div>
            </div>
          </div>
        </div>

        <!-- Milestones Met -->
        <div class="col-md-3 mb-3">
          <div class="card summary-card h-100">
            <div class="card-body d-flex flex-column justify-content-center">
              <div class="d-flex align-items-center mb-1">
                <span class="summary-label">MILESTONES MET</span>
                <i class="material-icons-round ms-2 summary-icon-check">check_circle</i>
              </div>
              <div class="summary-value">12 / 16</div>
              <div class="summary-sub mt-1">4 milestones currently active</div>
            </div>
          </div>
        </div>

        <!-- Delayed Tasks -->
        <div class="col-md-3 mb-3">
          <div class="card summary-card h-100">
            <div class="card-body d-flex flex-column justify-content-center">
              <div class="d-flex align-items-center mb-1">
                <span class="summary-label">DELAYED TASKS</span>
                <i class="material-icons-round ms-2 summary-icon-warn">warning</i>
              </div>
              <div class="summary-value text-danger">02</div>
              <div class="summary-sub mt-1">Requires immediate attention</div>
            </div>
          </div>
        </div>

        <!-- Featured Project -->
        <div class="col-md-3 mb-3">
          <div class="card featured-project-card h-100">
            <div class="card-body d-flex flex-column justify-content-center py-3">
              <div class="featured-project-title">Region II HQ Retrofitting</div>
              <div class="featured-project-sub mb-2">Contract ID: BFP-R2-2023-08</div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="featured-phase">Phase 3: Structural Integrity</span>
                <span class="featured-pct">92%</span>
              </div>
              <div class="progress featured-progress">
                <div class="featured-progress-fill" style="width: 92%"></div>
              </div>
              <div class="featured-next mt-2">
                <i class="material-icons-round" style="font-size:0.85rem;vertical-align:middle;">schedule</i>
                Next Milestone: Oct 24, 2024
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Project Milestones Table -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0 d-flex align-items-center justify-content-between">
              <h6>Project Milestones &amp; Accomplishments</h6>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-icon btn-light text-secondary" @click="showExportModal = true">
                  <i class="material-icons-round">download</i>
                </button>
                <button class="btn btn-sm btn-icon btn-light text-secondary" @click="showPrintModal = true">
                  <i class="material-icons-round">print</i>
                </button>
                <button class="btn btn-sm btn-icon btn-light text-secondary">
                  <i class="material-icons-round">more_vert</i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Project</th>
                      <th>Milestone Phase</th>
                      <th>Target Completion</th>
                      <th>Progress %</th>
                      <th>Status</th>
                      <th>Reports</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="milestone in accomplishments" :key="milestone.id">
                      <td>
                        <div class="fw-semibold">{{ milestone.project }}</div>
                        <div class="text-secondary small">{{ milestone.location }}</div>
                      </td>
                      <td>{{ milestone.milestone }}</td>
                      <td>{{ milestone.target_date }}</td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="progress-bar-wrapper">
                            <div
                              class="progress-bar"
                              :class="getProgressClass(milestone.status)"
                              :style="{ width: milestone.completion + '%' }"
                            ></div>
                          </div>
                          <span class="progress-text-outside">{{ milestone.completion }}%</span>
                        </div>
                      </td>
                      <td><status-badge :status="milestone.status" /></td>
                      <td>
                        <span v-if="milestone.report_type === 'pdf'">
                          <a href="#" class="report-link">
                            <i class="material-icons-round report-icon-pdf">picture_as_pdf</i>
                            {{ milestone.report_label }}
                          </a>
                        </span>
                        <span v-else-if="milestone.report_type === 'update'">
                          <a href="#" class="report-link">
                            <i class="material-icons-round report-icon-upload">upload</i>
                            {{ milestone.report_label }}
                          </a>
                        </span>
                        <span v-else-if="milestone.report_type === 'issue'">
                          <a href="#" class="report-link text-warning">
                            <i class="material-icons-round report-icon-warn">warning</i>
                            {{ milestone.report_label }}
                          </a>
                        </span>
                        <span v-else class="text-secondary small">{{ milestone.report_label }}</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Pagination -->
              <div class="d-flex justify-content-between align-items-center mt-3 px-1">
                <span class="text-secondary small">Showing 1 to 4 of 24 projects</span>
                <div class="d-flex align-items-center gap-1">
                  <button class="btn btn-sm btn-icon btn-light text-secondary">
                    <i class="material-icons-round">chevron_left</i>
                  </button>
                  <button class="btn btn-sm btn-pagination active">1</button>
                  <button class="btn btn-sm btn-pagination">2</button>
                  <button class="btn btn-sm btn-pagination">3</button>
                  <button class="btn btn-sm btn-icon btn-light text-secondary">
                    <i class="material-icons-round">chevron_right</i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Accomplishment Timeline -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Recent Accomplishment Timeline</h6>
            </div>
            <div class="card-body">
              <div class="timeline">
                <!-- Timeline Item 1 -->
                <div class="timeline-item">
                  <div class="timeline-icon timeline-icon-blue">
                    <i class="material-icons-round">description</i>
                  </div>
                  <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <div class="timeline-title">Weekly Accomplishment Report Submitted</div>
                        <div class="timeline-sub">Project: HQ Retrofitting Phase II</div>
                        <a href="#" class="timeline-link mt-1 d-inline-flex align-items-center gap-1">
                          <i class="material-icons-round" style="font-size:0.85rem;">picture_as_pdf</i>
                          View PDF Report
                        </a>
                      </div>
                      <span class="timeline-time">2 hours ago</span>
                    </div>
                  </div>
                </div>
                <!-- Timeline Item 2 -->
                <div class="timeline-item">
                  <div class="timeline-icon timeline-icon-olive">
                    <i class="material-icons-round">flag</i>
                  </div>
                  <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <div class="timeline-title">Milestone Reached: 100% Electrical Wiring</div>
                        <div class="timeline-sub">Project: Region II HQ Retrofitting</div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                          <div class="timeline-thumb">
                            <img src="https://via.placeholder.com/60x45/555/999?text=📷" alt="milestone photo" />
                          </div>
                          <span class="text-secondary small">Verified by Engr. Santos</span>
                        </div>
                      </div>
                      <span class="timeline-time">Yesterday</span>
                    </div>
                  </div>
                </div>
                <!-- Timeline Item 3 -->
                <div class="timeline-item timeline-item-last">
                  <div class="timeline-icon timeline-icon-red">
                    <i class="material-icons-round">access_time</i>
                  </div>
                  <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <div class="timeline-title">Delay Alert Logged</div>
                        <div class="timeline-sub">Project: Dormitory Construction</div>
                        <div class="timeline-quote mt-1">"Supply chain disruption for roofing materials causing 2-week delay in finishing phase."</div>
                      </div>
                      <span class="timeline-time">Oct 20, 2023</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <BfpModal
      :show="showUploadReportModal"
      title="Upload Accomplishment Report"
      stripe="MILESTONE REPORT UPLOAD"
      confirm-text="Upload Report"
      confirm-icon="upload"
      @close="showUploadReportModal = false"
      @confirm="showUploadReportModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">cloud_upload</i> Report Attachment</div>
        <div class="bfp-upload-panel">
          <i class="material-icons-round">upload_file</i>
          <p class="bfp-upload-title">Attach accomplishment report</p>
          <p class="bfp-upload-sub">Accepted formats: PDF, DOCX, JPG, PNG.</p>
          <input type="file" class="form-control form-control-sm" />
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">flag</i> Milestone Details</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Project <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">business</i>
              <input class="bfp-input" type="text" placeholder="Project name" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Milestone Phase</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">flag</i>
              <input class="bfp-input" type="text" placeholder="e.g. Electrical Installation" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Completion %</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">percent</i>
              <input class="bfp-input" type="number" min="0" max="100" placeholder="0" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Status</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">fact_check</i>
              <select class="bfp-input bfp-select">
                <option>In Progress</option>
                <option>Completed</option>
                <option>Delayed</option>
                <option>Not Started</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showFilterModal"
      title="Accomplishment Filters"
      stripe="MILESTONE SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showFilterModal = false"
      @confirm="showFilterModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">tune</i> Filter Criteria</div>
        <div class="bfp-filter-grid">
          <label class="bfp-check-option"><input type="checkbox" /> Completed</label>
          <label class="bfp-check-option"><input type="checkbox" /> In Progress</label>
          <label class="bfp-check-option"><input type="checkbox" /> Delayed</label>
          <label class="bfp-check-option"><input type="checkbox" /> Not Started</label>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">date_range</i> Target Date</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">From</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">To</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event_available</i>
              <input class="bfp-input" type="date" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showPrintModal"
      title="Print Accomplishment Summary"
      stripe="MILESTONE PRINT SETUP"
      confirm-text="Print"
      confirm-icon="print"
      @close="showPrintModal = false"
      @confirm="showPrintModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">print</i> Print Scope</div>
        <div class="bfp-filter-grid">
          <label class="bfp-check-option"><input type="checkbox" checked /> Summary cards</label>
          <label class="bfp-check-option"><input type="checkbox" checked /> Milestone table</label>
          <label class="bfp-check-option"><input type="checkbox" /> Recent timeline</label>
          <label class="bfp-check-option"><input type="checkbox" /> Photo references</label>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showExportModal"
      title="Export Accomplishments"
      stripe="MILESTONE EXPORT"
      confirm-text="Export"
      confirm-icon="download"
      @close="showExportModal = false"
      @confirm="showExportModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">ios_share</i> Export Options</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Format</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">file_download</i>
              <select class="bfp-input bfp-select">
                <option>PDF</option>
                <option>Excel</option>
                <option>CSV</option>
              </select>
            </div>
          </div>
          <label class="bfp-check-option bfp-field-full"><input type="checkbox" checked /> Include accomplishment report links</label>
        </div>
      </div>
    </BfpModal>
  </div>
</template>

<script>
import StatusBadge from "@/components/StatusBadge.vue";
import BfpModal from "@/components/BfpModal.vue";

export default {
  name: "Accomplishments",
  components: {
    StatusBadge,
    BfpModal
  },
  data() {
    return {
      showUploadReportModal: false,
      showFilterModal: false,
      showPrintModal: false,
      showExportModal: false,
      accomplishments: [
        {
          id: 1,
          project: "HQ Retrofitting Phase II",
          location: "Isabela Province",
          milestone: "Electrical Installation",
          completion: 100,
          target_date: "Oct 15, 2023",
          actual_date: "Oct 15, 2023",
          status: "completed",
          report_type: "pdf",
          report_label: "final_report.pdf"
        },
        {
          id: 2,
          project: "New Tuguegarao Fire Station",
          location: "Tuguegarao City",
          milestone: "Foundation Piling",
          completion: 65,
          target_date: "Nov 20, 2023",
          actual_date: "-",
          status: "in_progress",
          report_type: "update",
          report_label: "Update"
        },
        {
          id: 3,
          project: "Dormitory Construction",
          location: "Cagayan Field Office",
          milestone: "Roofing & Finishing",
          completion: 82,
          target_date: "Oct 01, 2023",
          actual_date: "-",
          status: "delayed",
          report_type: "issue",
          report_label: "Issue Log"
        },
        {
          id: 4,
          project: "Regional Training Center",
          location: "Santiago City",
          milestone: "Internal Partitioning",
          completion: 0,
          target_date: "Dec 12, 2023",
          actual_date: "-",
          status: "not_started",
          report_type: "pending",
          report_label: "Pending"
        }
      ]
    };
  },
  methods: {
    getProgressClass(status) {
      if (status === "completed") return "progress-bar-completed";
      if (status === "delayed") return "progress-bar-delayed";
      if (status === "not_started") return "progress-bar-empty";
      return "";
    }
  }
};
</script>

<style scoped>
.module-page {
  background: #f7fafc;
  min-height: 100vh;
}

/* ── Dropdown menu ── */
.dropdown-menu {
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 0.75rem;
  font-size: 0.85rem;
  min-width: 140px;
  padding: 0.3rem;
}
.dropdown-item {
  border-radius: 0.5rem;
  padding: 0.45rem 0.75rem;
  display: flex;
  align-items: center;
}
.dropdown-item:hover { background: #f3f4f6; }
.dropdown-item.text-danger:hover { background: #fef2f2; }

.dropdown-icon { font-size: 1rem; }
.view-icon { color: #2563eb; }
.edit-icon { color: #d97706; }

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

.table {
  font-size: 0.875rem;
}

.progress-bar-wrapper {
  position: relative;
  height: 20px;
  background: #e0e5ee;
  border-radius: 0.5rem;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #2c5aa0, #0288d1);
  transition: width 0.3s ease;
}

.progress-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 0.75rem;
  font-weight: 600;
  color: #1f2633;
}

.form-control {
  border-radius: 0.75rem;
  border: 1px solid #dfe4ed;
}

/* ── Summary Cards ── */
.summary-card {
  box-shadow: 0 4px 15px rgba(15, 23, 42, 0.08) !important;
}

.summary-label {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #64748b;
  text-transform: uppercase;
}

.summary-icon-chart { color: #f59e0b; font-size: 1.1rem; }
.summary-icon-check { color: #2563eb; font-size: 1.1rem; }
.summary-icon-warn  { color: #ef4444; font-size: 1.1rem; }

.summary-value {
  font-size: 2rem;
  font-weight: 800;
  color: #1f2633;
  line-height: 1.1;
  margin-top: 0.25rem;
}

.summary-progress {
  height: 6px;
  background: #e0e5ee;
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #f59e0b, #fbbf24);
  border-radius: 3px;
}

.summary-sub {
  font-size: 0.75rem;
  color: #94a3b8;
}

/* ── Featured Project Card ── */
.featured-project-card {
  background: linear-gradient(135deg, #1e3a6e, #2563eb) !important;
  color: #fff !important;
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.35) !important;
}

.featured-project-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: #fff;
}

.featured-project-sub {
  font-size: 0.72rem;
  color: rgba(255,255,255,0.65);
}

.featured-phase {
  font-size: 0.78rem;
  color: rgba(255,255,255,0.9);
  font-weight: 600;
}

.featured-pct {
  font-size: 0.85rem;
  font-weight: 800;
  color: #fff;
}

.featured-progress {
  height: 7px;
  background: rgba(255,255,255,0.25);
  border-radius: 4px;
  overflow: hidden;
}

.featured-progress-fill {
  height: 100%;
  background: #facc15;
  border-radius: 4px;
}

.featured-next {
  font-size: 0.72rem;
  color: rgba(255,255,255,0.75);
}

/* ── Progress bar variants ── */
.progress-bar-wrapper {
  width: 120px;
  min-width: 80px;
}

.progress-bar-completed {
  background: linear-gradient(90deg, #2563eb, #3b82f6) !important;
}

.progress-bar-delayed {
  background: linear-gradient(90deg, #ef4444, #f87171) !important;
}

.progress-bar-empty {
  background: #d1d5db !important;
}

.progress-text-outside {
  font-size: 0.8rem;
  font-weight: 600;
  color: #374151;
  white-space: nowrap;
}

/* ── Report Links ── */
.report-link {
  font-size: 0.8rem;
  color: #64748b;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}
.report-link:hover { color: #1f2633; }
.report-icon-pdf   { font-size: 0.95rem; color: #ef4444; }
.report-icon-upload{ font-size: 0.95rem; color: #2563eb; }
.report-icon-warn  { font-size: 0.95rem; color: #f59e0b; }

/* ── Pagination ── */
.btn-pagination {
  width: 32px;
  height: 32px;
  padding: 0;
  border-radius: 0.5rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  background: transparent;
  border: none;
}
.btn-pagination.active {
  background: #2563eb;
  color: #fff;
}
.btn-pagination:hover:not(.active) { background: #f1f5f9; }

/* ── Timeline ── */
.timeline {
  position: relative;
  padding-left: 0;
}

.timeline-item {
  display: flex;
  gap: 1rem;
  position: relative;
  padding-bottom: 1.5rem;
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: 19px;
  top: 40px;
  bottom: 0;
  width: 2px;
  background: #e0e5ee;
}

.timeline-item-last::before { display: none; }

.timeline-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  z-index: 1;
}

.timeline-icon i { font-size: 1.1rem; color: #fff; }

.timeline-icon-blue  { background: #2563eb; }
.timeline-icon-olive { background: #6b7c2e; }
.timeline-icon-red   { background: #ef4444; }

.timeline-content {
  flex: 1;
  background: #f8fafc;
  border: 1px solid #e0e5ee;
  border-radius: 0.75rem;
  padding: 0.85rem 1rem;
}

.timeline-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #1f2633;
}

.timeline-sub {
  font-size: 0.78rem;
  color: #64748b;
  margin-top: 0.1rem;
}

.timeline-link {
  font-size: 0.78rem;
  color: #ef4444;
  text-decoration: none;
}
.timeline-link:hover { text-decoration: underline; }

.timeline-time {
  font-size: 0.75rem;
  color: #94a3b8;
  white-space: nowrap;
  margin-left: 0.5rem;
}

.timeline-thumb img {
  width: 60px;
  height: 45px;
  object-fit: cover;
  border-radius: 0.4rem;
}

.timeline-quote {
  font-size: 0.78rem;
  color: #64748b;
  font-style: italic;
}
</style>
