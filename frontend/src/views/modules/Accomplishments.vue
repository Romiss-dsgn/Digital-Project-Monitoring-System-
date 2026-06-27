<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <div v-if="apiError" class="alert alert-danger py-2 px-3 mb-3">{{ apiError }}</div>
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Accomplishments Monitoring</h4>
          <p class="text-secondary small">
            Record milestone progress, completion evidence, validation status, and accomplishment reports for existing projects.
          </p>
        </div>
        <div class="col-lg-4 text-end">
          <button class="btn btn-light btn-sm me-2" @click="showFilterModal = true">
            <i class="material-icons-round">filter_list</i> Filters
          </button>
          <button
            v-if="permissions.create"
            class="btn btn-primary btn-sm"
            :disabled="isLoading || projects.length === 0"
            :title="projects.length === 0 ? 'All projects already have an accomplishment report' : ''"
            @click="openCreateModal"
          >
            <i class="material-icons-round">upload</i> Upload Report
          </button>
        </div>
      </div>

      <div v-if="!isLoading && allProjectsCount === 0" class="alert alert-warning py-2 px-3 mb-4">
        Create a project first in Infrastructure Plans before adding accomplishment reports.
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
              <div class="summary-value">{{ summary.overall_progress }}%</div>
              <div class="progress summary-progress mt-2">
                <div class="progress-fill" :style="{ width: summary.overall_progress + '%' }"></div>
              </div>
              <div class="summary-sub mt-1">Average of all recorded milestones</div>
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
              <div class="summary-value">{{ summary.milestones_completed }} / {{ summary.milestones_total }}</div>
              <div class="summary-sub mt-1">{{ summary.active_milestones }} milestones currently active</div>
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
              <div class="summary-value text-danger">{{ summary.delayed_tasks }}</div>
              <div class="summary-sub mt-1">Requires immediate attention</div>
            </div>
          </div>
        </div>

        <!-- Featured Project -->
        <div class="col-md-3 mb-3">
          <div class="card featured-project-card h-100">
            <div class="card-body d-flex flex-column justify-content-center py-3">
              <template v-if="summary.featured">
              <div class="featured-project-title">{{ summary.featured.project_name }}</div>
              <div class="featured-project-sub mb-2">Contract ID: {{ summary.featured.contract_number || 'Not assigned' }}</div>
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="featured-phase">{{ summary.featured.milestone_title }}</span>
                <span class="featured-pct">{{ summary.featured.percent_complete }}%</span>
              </div>
              <div class="progress featured-progress">
                <div class="featured-progress-fill" :style="{ width: summary.featured.percent_complete + '%' }"></div>
              </div>
              <div class="featured-next mt-2">
                <i class="material-icons-round" style="font-size:0.85rem;vertical-align:middle;">schedule</i>
                Target: {{ formatDate(summary.featured.target_date) }}
              </div>
              </template>
              <div v-else class="featured-project-title">No active milestone</div>
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
                <button v-if="permissions.export" class="btn btn-sm btn-icon btn-light text-secondary" @click="showExportModal = true">
                  <i class="material-icons-round">download</i>
                </button>
                <button class="btn btn-sm btn-icon btn-light text-secondary" @click="showPrintModal = true">
                  <i class="material-icons-round">print</i>
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
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="isLoading"><td colspan="7" class="text-center py-4">Loading accomplishments...</td></tr>
                    <tr v-else-if="filteredAccomplishments.length === 0"><td colspan="7" class="text-center py-4 text-secondary">No milestone records found.</td></tr>
                    <tr v-for="milestone in displayedAccomplishments" :key="milestone.id">
                      <td>
                        <div class="fw-semibold">{{ milestone.project_name }}</div>
                        <div class="text-secondary small">{{ milestone.project_location }}</div>
                      </td>
                      <td>{{ milestone.milestone_title }}</td>
                      <td>{{ formatDate(milestone.target_date) }}</td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="progress-bar-wrapper">
                            <div
                              class="progress-bar"
                              :class="getProgressClass(milestone.status)"
                              :style="{ width: milestone.percent_complete + '%' }"
                            ></div>
                          </div>
                          <span class="progress-text-outside">{{ milestone.percent_complete }}%</span>
                        </div>
                      </td>
                      <td><status-badge :status="milestone.status" /></td>
                      <td>
                        <span v-if="milestone.documents?.length">
                          <a href="#" class="report-link" @click.prevent="downloadReport(milestone.documents[0])">
                            <i class="material-icons-round report-icon-pdf">picture_as_pdf</i>
                            {{ milestone.documents[0].file_name }}
                          </a>
                        </span>
                        <span v-else class="text-secondary small">No report</span>
                      </td>
                      <td>
                        <div class="d-flex gap-1">
                          <button v-if="permissions.edit" class="btn btn-sm btn-light" title="Edit" @click="editAccomplishment(milestone)">
                            <i class="material-icons-round">edit</i>
                          </button>
                          <button v-if="permissions.approve && !milestone.validated_at" class="btn btn-sm btn-light" title="Validate" @click="validateAccomplishment(milestone)">
                            <i class="material-icons-round">verified</i>
                          </button>
                          <button v-if="permissions.delete" class="btn btn-sm btn-light text-danger" title="Archive" @click="archiveAccomplishment(milestone)">
                            <i class="material-icons-round">archive</i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <!-- Pagination -->
              <div class="d-flex justify-content-between align-items-center mt-3 px-1">
                <span class="text-secondary small">Showing {{ paginationFrom }} to {{ paginationTo }} of {{ filteredAccomplishments.length }} milestones</span>
                <div class="d-flex align-items-center gap-1">
                  <button class="btn btn-sm btn-icon btn-light text-secondary" :disabled="currentPage === 1" @click="currentPage--">
                    <i class="material-icons-round">chevron_left</i>
                  </button>
                  <button
                    v-for="page in totalPages"
                    :key="page"
                    class="btn btn-sm btn-pagination"
                    :class="{ active: page === currentPage }"
                    @click="currentPage = page"
                  >{{ page }}</button>
                  <button class="btn btn-sm btn-icon btn-light text-secondary" :disabled="currentPage === totalPages" @click="currentPage++">
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
                <p v-if="!summary.recent_activity.length" class="text-secondary small mb-0">No recent milestone activity.</p>
                <div
                  v-for="(activity, index) in summary.recent_activity"
                  :key="activity.id"
                  class="timeline-item"
                  :class="{ 'timeline-item-last': index === summary.recent_activity.length - 1 }"
                >
                  <div class="timeline-icon" :class="activity.status === 'Delayed' ? 'timeline-icon-red' : 'timeline-icon-blue'">
                    <i class="material-icons-round">flag</i>
                  </div>
                  <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <div class="timeline-title">{{ activity.title }} · {{ activity.percent_complete }}%</div>
                        <div class="timeline-sub">Project: {{ activity.project_name }} · {{ activity.status }}</div>
                      </div>
                      <span class="timeline-time">{{ formatDateTime(activity.updated_at) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Upload / Edit Modal ───────────────────────────────────────────────── -->
    <BfpModal
      :show="showUploadReportModal"
      :title="accomplishmentForm.id ? 'Edit Accomplishment' : 'Add Accomplishment Report'"
      stripe="MILESTONE REPORT UPLOAD"
      :confirm-text="isSaving ? 'Saving...' : accomplishmentForm.id ? 'Update Record' : 'Save Report'"
      confirm-icon="upload"
      @close="showUploadReportModal = false"
      @confirm="saveAccomplishment"
    >
      <!-- File attachment -->
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">cloud_upload</i> Report Attachment</div>
        <div class="bfp-upload-panel">
          <i class="material-icons-round">upload_file</i>
          <p class="bfp-upload-title">Attach accomplishment report</p>
          <p class="bfp-upload-sub">Accepted formats: PDF, DOCX, JPG, PNG.</p>
          <input type="file" class="form-control form-control-sm" accept=".pdf,.docx,.jpg,.jpeg,.png" @change="handleReportFile" />
        </div>
      </div>

      <!-- Milestone details -->
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">flag</i> Milestone Details</div>
        <div class="bfp-form-grid">

          <!-- Project -->
          <div class="bfp-field-half">
            <label class="bfp-label">Project <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">business</i>
              <!--
                Only projects without an existing active accomplishment
                report are selectable. When editing, the project already
                tied to this record is included too (fetched separately via
                include_project_id) so the field doesn't show blank.
              -->
              <select v-model="accomplishmentForm.project_id" class="bfp-input bfp-select" :disabled="isLoadingModalProjects">
                <option value="">{{ isLoadingModalProjects ? 'Loading projects...' : 'Select project' }}</option>
                <option v-for="project in modalProjects" :key="project.id" :value="project.id">
                  {{ project.project_code }} · {{ project.project_name }}
                </option>
              </select>
            </div>
            <p v-if="!isLoadingModalProjects && modalProjects.length === 0 && !accomplishmentForm.id" class="text-secondary small mt-1 mb-0">
              All active projects already have an accomplishment report.
            </p>
          </div>

          <!-- Milestone Phase -->
          <div class="bfp-field-half">
            <label class="bfp-label">Milestone Phase</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">flag</i>
              <input v-model="accomplishmentForm.milestone_title" class="bfp-input" type="text" placeholder="e.g. Electrical Installation" />
            </div>
          </div>

          <!-- Completion % -->
          <div class="bfp-field-half">
            <label class="bfp-label">Completion %</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">percent</i>
              <input v-model.number="accomplishmentForm.percent_complete" class="bfp-input" type="number" min="0" max="100" placeholder="0" />
            </div>
          </div>

          <!-- Status -->
          <div class="bfp-field-half">
            <label class="bfp-label">Status</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">fact_check</i>
              <select v-model="accomplishmentForm.status" class="bfp-input bfp-select">
                <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
              </select>
            </div>
          </div>

          <!-- Target Date -->
          <div class="bfp-field-half">
            <label class="bfp-label">Target Date <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input v-model="accomplishmentForm.target_date" class="bfp-input" type="date" />
            </div>
          </div>

          <!-- Completion Date -->
          <div class="bfp-field-half">
            <label class="bfp-label">Completion Date</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event_available</i>
              <input v-model="accomplishmentForm.completion_date" class="bfp-input" type="date" />
            </div>
          </div>

          <!-- Description -->
          <div class="bfp-field-full">
            <label class="bfp-label">Description</label>
            <textarea v-model="accomplishmentForm.description" class="bfp-input" rows="2" placeholder="Milestone scope and work completed"></textarea>
          </div>

          <!-- Remarks -->
          <div class="bfp-field-full">
            <label class="bfp-label">Remarks</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">notes</i>
              <input
                v-model="accomplishmentForm.remarks"
                class="bfp-input"
                type="text"
                placeholder="Optional notes or validation remarks"
              />
            </div>
          </div>

        </div>
      </div>
    </BfpModal>

    <!-- ── Filter Modal ──────────────────────────────────────────────────────── -->
    <BfpModal
      :show="showFilterModal"
      title="Accomplishment Filters"
      stripe="MILESTONE SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showFilterModal = false"
      @confirm="applyFilters"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">tune</i> Filter Criteria</div>
        <div class="bfp-filter-grid">
          <label v-for="status in statusOptions" :key="status" class="bfp-check-option">
            <input v-model="filters.statuses" type="checkbox" :value="status" /> {{ status }}
          </label>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">date_range</i> Target Date</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">From</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input v-model="filters.target_from" class="bfp-input" type="date" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">To</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event_available</i>
              <input v-model="filters.target_to" class="bfp-input" type="date" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <!-- ── Print Modal ───────────────────────────────────────────────────────── -->
    <BfpModal
      :show="showPrintModal"
      title="Print Accomplishment Summary"
      stripe="MILESTONE PRINT SETUP"
      confirm-text="Print"
      confirm-icon="print"
      @close="showPrintModal = false"
      @confirm="printSummary"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">print</i> Print Summary</div>
        <p class="text-secondary small mb-0">The browser print dialog will print the current database-backed summary, filters, and milestone table.</p>
      </div>
    </BfpModal>

    <!-- ── Export Modal ──────────────────────────────────────────────────────── -->
    <BfpModal
      :show="showExportModal"
      title="Export Accomplishments"
      stripe="MILESTONE EXPORT"
      confirm-text="Export"
      confirm-icon="download"
      @close="showExportModal = false"
      @confirm="exportCsv"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">ios_share</i> Export Options</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Format</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">file_download</i>
              <select class="bfp-input bfp-select">
                <option>CSV</option>
              </select>
            </div>
          </div>
          <p class="text-secondary small bfp-field-full mb-0">CSV exports include the currently filtered milestone records.</p>
        </div>
      </div>
    </BfpModal>
  </div>
</template>

<script>
import StatusBadge from "@/components/StatusBadge.vue";
import BfpModal from "@/components/BfpModal.vue";
import accomplishmentService from "@/services/accomplishment.service";

const emptyAccomplishmentForm = () => ({
  id: null,
  project_id: "",
  milestone_title: "",
  description: "",
  target_date: "",
  completion_date: "",
  percent_complete: 0,
  status: "Not Started",
  remarks: "",        // ← always initialised so it is never sent as null
  attachment: null,
});

export default {
  name: "Accomplishments",
  components: {
    StatusBadge,
    BfpModal,
  },

  data() {
    return {
      showUploadReportModal: false,
      showFilterModal: false,
      showPrintModal: false,
      showExportModal: false,
      isLoading: false,
      isSaving: false,
      isLoadingModalProjects: false,
      apiError: "",
      currentPage: 1,
      rowsPerPage: 10,
      accomplishments: [],
      // Projects without an active accomplishment report yet — used as the
      // dropdown source when creating a *new* report, and to gate the
      // "Upload Report" button / empty-state messaging.
      projects: [],
      // Total active project count (including ones that already have a
      // report), used only to distinguish "no projects exist at all" from
      // "all projects already have a report" in the empty-state messaging.
      allProjectsCount: 0,
      // Projects shown specifically inside the open Add/Edit modal. For
      // "create" this mirrors `projects`. For "edit" it's fetched with
      // include_project_id so the record's current project still appears
      // as a selectable (and pre-selected) option.
      modalProjects: [],
      statusOptions: ["Not Started", "In Progress", "Delayed", "Completed"],
      accomplishmentForm: emptyAccomplishmentForm(),
      filters: {
        statuses: [],
        target_from: "",
        target_to: "",
      },
      summary: {
        overall_progress: 0,
        milestones_completed: 0,
        milestones_total: 0,
        active_milestones: 0,
        delayed_tasks: 0,
        featured: null,
        recent_activity: [],
      },
      permissions: {
        view: false,
        create: false,
        edit: false,
        delete: false,
        approve: false,
        export: false,
      },
    };
  },

  computed: {
    filteredAccomplishments() {
      return this.accomplishments.filter((item) => {
        if (this.filters.statuses.length && !this.filters.statuses.includes(item.status)) return false;
        if (this.filters.target_from && item.target_date < this.filters.target_from) return false;
        if (this.filters.target_to && item.target_date > this.filters.target_to) return false;
        return true;
      });
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.filteredAccomplishments.length / this.rowsPerPage));
    },
    displayedAccomplishments() {
      const start = (this.currentPage - 1) * this.rowsPerPage;
      return this.filteredAccomplishments.slice(start, start + this.rowsPerPage);
    },
    paginationFrom() {
      return this.filteredAccomplishments.length ? (this.currentPage - 1) * this.rowsPerPage + 1 : 0;
    },
    paginationTo() {
      return Math.min(this.currentPage * this.rowsPerPage, this.filteredAccomplishments.length);
    },
  },

  async mounted() {
    await this.loadAccomplishments();
  },

  methods: {
    async openCreateModal() {
      if (this.allProjectsCount === 0) {
        alert("Create a project first in Infrastructure Plans before adding accomplishment reports.");
        return;
      }
      if (!this.projects.length) {
        alert("Every active project already has an accomplishment report. Edit an existing record instead.");
        return;
      }
      this.accomplishmentForm = emptyAccomplishmentForm();
      this.modalProjects = this.projects;
      this.showUploadReportModal = true;
    },

    async loadAccomplishments() {
      this.isLoading = true;
      this.apiError = "";

      try {
        const [records, summary, options] = await Promise.all([
          accomplishmentService.getAccomplishments(),
          accomplishmentService.getSummary(),
          accomplishmentService.getOptions(),
        ]);

        this.accomplishments = records.data || [];
        this.summary = summary;
        // `options.projects` already excludes projects that have an active
        // accomplishment report (filtered server-side).
        this.projects = options.projects || [];
        this.allProjectsCount = typeof options.all_projects_count === "number"
          ? options.all_projects_count
          : this.projects.length;
        this.statusOptions = options.statuses || this.statusOptions;
        this.permissions = options.permissions || summary.permissions || this.permissions;
        this.currentPage = 1;
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to load accomplishment records.");
      } finally {
        this.isLoading = false;
      }
    },

    async saveAccomplishment() {
      if (this.isSaving) return;

      if (!this.accomplishmentForm.project_id) {
        this.apiError = "Please select an existing project before saving an accomplishment report.";
        return;
      }

      this.isSaving = true;
      this.apiError = "";

      try {
        if (this.accomplishmentForm.id) {
          const { attachment, ...payload } = this.accomplishmentForm;
          await accomplishmentService.updateAccomplishment(payload.id, payload);
          if (attachment) await accomplishmentService.uploadDocument(payload.id, attachment);
        } else {
          await accomplishmentService.createAccomplishment(this.accomplishmentForm);
        }

        this.showUploadReportModal = false;
        this.accomplishmentForm = emptyAccomplishmentForm();
        await this.loadAccomplishments();
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to save accomplishment.");
      } finally {
        this.isSaving = false;
      }
    },

    async editAccomplishment(item) {
      this.accomplishmentForm = {
        id: item.id,
        project_id: item.project_id,
        milestone_title: item.milestone_title,
        description: item.description || "",
        target_date: item.target_date || "",
        completion_date: item.completion_date || "",
        percent_complete: item.percent_complete,
        status: item.status,
        remarks: item.remarks || "",
        attachment: null,
      };

      this.showUploadReportModal = true;
      this.isLoadingModalProjects = true;

      try {
        // Re-fetch with include_project_id so this record's own project
        // still shows up as a selectable option, even though it already
        // "has" a report (this one).
        const options = await accomplishmentService.getOptions({ include_project_id: item.project_id });
        this.modalProjects = options.projects || [];
      } catch (error) {
        // Fall back to whatever we already have rather than blocking edit.
        this.modalProjects = this.projects;
      } finally {
        this.isLoadingModalProjects = false;
      }
    },

    async validateAccomplishment(item) {
      try {
        await accomplishmentService.validateAccomplishment(item.id);
        await this.loadAccomplishments();
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to validate accomplishment.");
      }
    },

    async archiveAccomplishment(item) {
      if (!confirm(`Archive ${item.milestone_title}?`)) return;

      try {
        await accomplishmentService.archiveAccomplishment(item.id);
        await this.loadAccomplishments();
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to archive accomplishment.");
      }
    },

    handleReportFile(event) {
      this.accomplishmentForm.attachment = event.target.files?.[0] || null;
    },

    applyFilters() {
      this.currentPage = 1;
      this.showFilterModal = false;
    },

    getProgressClass(status) {
      if (status === "Completed") return "progress-bar-completed";
      if (status === "Delayed") return "progress-bar-delayed";
      if (status === "Not Started") return "progress-bar-empty";
      return "";
    },

    formatDate(value) {
      if (!value) return "-";
      return new Intl.DateTimeFormat("en-PH", { dateStyle: "medium" }).format(new Date(`${value}T00:00:00`));
    },

    formatDateTime(value) {
      if (!value) return "-";
      return new Intl.DateTimeFormat("en-PH", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
    },

    async downloadReport(document) {
      try {
        await accomplishmentService.downloadDocument(document);
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to download report.");
      }
    },

    printSummary() {
      this.showPrintModal = false;
      window.print();
    },

    exportCsv() {
      const headers = ["Project", "Milestone", "Target Date", "Progress", "Status", "Remarks"];
      const rows = this.filteredAccomplishments.map((item) => [
        item.project_name,
        item.milestone_title,
        item.target_date,
        item.percent_complete,
        item.status,
        item.remarks || "",
      ]);
      const escape = (value) => `"${String(value ?? "").replace(/"/g, '""')}"`;
      const csv = [headers, ...rows].map((row) => row.map(escape).join(",")).join("\n");
      const url = URL.createObjectURL(new Blob([csv], { type: "text/csv;charset=utf-8" }));
      const link = window.document.createElement("a");
      link.href = url;
      link.download = "project-accomplishments.csv";
      link.click();
      URL.revokeObjectURL(url);
      this.showExportModal = false;
    },

    errorMessage(error, fallback) {
      const errors = error?.response?.data?.errors;
      if (errors && typeof errors === "object" && !Array.isArray(errors)) {
        return Object.values(errors).flat().join(" ");
      }
      return error?.response?.data?.message || fallback;
    },
  },
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