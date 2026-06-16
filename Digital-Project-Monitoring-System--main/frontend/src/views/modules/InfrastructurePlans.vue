<template>
  <div class="admin-page">
    <div class="container-fluid py-4">

      <!-- Page Header -->
      <div class="row align-items-center mb-4">
        <div class="col-lg-8">
          <h4 class="mb-1">Infrastructure Plans Management</h4>
          <p class="text-secondary small mb-0">Monitor and manage institutional construction projects across Region II.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
          <button class="btn btn-primary btn-sm" @click="showModal = true">
            <i class="material-icons-round">add</i>
            Add New Project
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="row g-2 mb-4 filters-row">
        <div class="col-sm-6 col-md-4 col-lg-2">
          <input v-model="filters.name" type="text" class="form-control form-control-sm" placeholder="Project Name" />
        </div>
        <div class="col-sm-6 col-md-4 col-lg-2">
          <input v-model="filters.code" type="text" class="form-control form-control-sm" placeholder="Project Code" />
        </div>
        <div class="col-sm-6 col-md-4 col-lg-2">
          <select v-model="filters.location" class="form-control form-control-sm">
            <option value="">All Provinces</option>
            <option v-for="location in locations" :key="location" :value="location">{{ location }}</option>
          </select>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-2">
          <select v-model="filters.phase" class="form-control form-control-sm">
            <option value="">All Phases</option>
            <option v-for="phase in phases" :key="phase" :value="phase">{{ phase }}</option>
          </select>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-2 d-grid">
          <button class="btn btn-outline-primary btn-sm" type="button" @click="showFilterModal = true">
            Apply Filters
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="row">
        <div class="col-12">
          <div class="card shadow-sm border-0">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Project Code</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Project Name</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Location</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contractor</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Progress %</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Phase</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="project in filteredProjects" :key="project.id">
                      <td class="align-middle text-sm">
                        <span class="project-code">{{ project.code }}</span>
                      </td>
                      <td class="align-middle text-sm">{{ project.name }}</td>
                      <td class="align-middle text-sm">{{ project.location }}</td>
                      <td class="align-middle text-sm">{{ project.contractor }}</td>
                      <td class="align-middle text-sm">
                        <div class="d-flex align-items-center gap-2">
                          <div class="progress progress-sm flex-grow-1">
                            <div
                              class="progress-bar"
                              :class="progressBarClass(project.progress)"
                              :style="{ width: project.progress + '%' }"
                            ></div>
                          </div>
                          <span class="progress-label" :class="progressTextClass(project.progress)">
                            {{ project.progress }}%
                          </span>
                        </div>
                      </td>
                      <td class="align-middle text-sm">{{ project.phase }}</td>
                      <td class="align-middle text-sm">
                        <span class="status-pill" :class="project.status">
                          <span class="status-dot"></span>
                          {{ statusLabel(project.status) }}
                        </span>
                      </td>
                      <td class="align-middle text-end">
                        <div class="dropdown">
                          <button
                            class="btn btn-sm btn-icon btn-light text-secondary"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                          >
                            <i class="material-icons-round">more_vert</i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="viewProject(project)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon view-icon">visibility</i>
                                View
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="editProject(project)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit</i>
                                Edit
                              </a>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                              <a class="dropdown-item text-danger" href="#" @click.prevent="deleteProject(project)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon">delete</i>
                                Delete
                              </a>
                            </li>
                          </ul>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Cards -->
      <div class="row mt-4">
        <div class="col-lg-6 mb-3">
          <div class="card card-body border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-secondary text-uppercase text-xxs mb-1">Last Update</p>
                <h6 class="mb-0">{{ lastUpdate }}</h6>
              </div>
              <span class="badge bg-soft-primary text-primary py-2 px-3 rounded-pill">
                <i class="material-icons-round">update</i>
              </span>
            </div>
          </div>
        </div>
        <div class="col-lg-6 mb-3">
          <div class="card card-body border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-secondary text-uppercase text-xxs mb-1">Regional Efficiency</p>
                <h6 class="mb-0">{{ efficiency }} On-Time</h6>
              </div>
              <span class="badge bg-soft-success text-success py-2 px-3 rounded-pill">
                <i class="material-icons-round">trending_up</i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════
         ADD NEW PROJECT MODAL
    ═══════════════════════════════════════════════ -->
    <Teleport to="body">
      <Transition name="modal-fade">
        <div v-if="showModal" class="bfp-modal-overlay" @click.self="closeModal">
          <div class="bfp-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">

            <!-- Modal Header -->
            <div class="bfp-modal-header">
              <div class="bfp-modal-header-left">
                <div class="bfp-modal-emblem">
                  <img :src="bfpLogo" alt="BFP Logo" class="bfp-logo-img" />
                </div>
                <div>
                  <p class="bfp-modal-agency">Bureau of Fire Protection</p>
                  <h5 class="bfp-modal-title" id="modal-title">New Infrastructure Project</h5>
                </div>
              </div>
              <button class="bfp-modal-close" @click="closeModal" aria-label="Close modal">
                <i class="material-icons-round">close</i>
              </button>
            </div>

            <!-- Red stripe accent -->
            <div class="bfp-modal-stripe">
              <span>REGION II — CAGAYAN VALLEY</span>
              <span>PROJECT REGISTRATION FORM</span>
            </div>

            <!-- Modal Body -->
            <div class="bfp-modal-body">

              <!-- Section: Project Identification -->
              <div class="bfp-section">
                <div class="bfp-section-label">
                  <i class="material-icons-round">folder_open</i>
                  Project Identification
                </div>
                <div class="bfp-form-grid">
                  <div class="bfp-field bfp-field-half">
                    <label class="bfp-label" for="m-code">
                      Project Code <span class="bfp-required">*</span>
                    </label>
                    <div class="bfp-input-wrap">
                      <i class="material-icons-round bfp-input-icon">tag</i>
                      <input
                        id="m-code"
                        v-model="form.code"
                        type="text"
                        class="bfp-input"
                        :class="{ 'bfp-input-error': errors.code }"
                        placeholder="e.g. BFP-2024-C001"
                      />
                    </div>
                    <span v-if="errors.code" class="bfp-error-msg">{{ errors.code }}</span>
                  </div>

                  <div class="bfp-field bfp-field-half">
                    <label class="bfp-label" for="m-location">
                      Province / Location <span class="bfp-required">*</span>
                    </label>
                    <div class="bfp-input-wrap">
                      <i class="material-icons-round bfp-input-icon">location_on</i>
                      <select
                        id="m-location"
                        v-model="form.location"
                        class="bfp-input bfp-select"
                        :class="{ 'bfp-input-error': errors.location }"
                      >
                        <option value="">Select province</option>
                        <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
                      </select>
                    </div>
                    <span v-if="errors.location" class="bfp-error-msg">{{ errors.location }}</span>
                  </div>

                  <div class="bfp-field bfp-field-full">
                    <label class="bfp-label" for="m-name">
                      Project Name <span class="bfp-required">*</span>
                    </label>
                    <div class="bfp-input-wrap">
                      <i class="material-icons-round bfp-input-icon">business</i>
                      <input
                        id="m-name"
                        v-model="form.name"
                        type="text"
                        class="bfp-input"
                        :class="{ 'bfp-input-error': errors.name }"
                        placeholder="Full official project name"
                      />
                    </div>
                    <span v-if="errors.name" class="bfp-error-msg">{{ errors.name }}</span>
                  </div>
                </div>
              </div>

              <!-- Section: Contractor & Schedule -->
              <div class="bfp-section">
                <div class="bfp-section-label">
                  <i class="material-icons-round">engineering</i>
                  Contractor & Schedule
                </div>
                <div class="bfp-form-grid">
                  <div class="bfp-field bfp-field-full">
                    <label class="bfp-label" for="m-contractor">
                      Contractor / Firm <span class="bfp-required">*</span>
                    </label>
                    <div class="bfp-input-wrap">
                      <i class="material-icons-round bfp-input-icon">groups</i>
                      <input
                        id="m-contractor"
                        v-model="form.contractor"
                        type="text"
                        class="bfp-input"
                        :class="{ 'bfp-input-error': errors.contractor }"
                        placeholder="Accredited contractor or company name"
                      />
                    </div>
                    <span v-if="errors.contractor" class="bfp-error-msg">{{ errors.contractor }}</span>
                  </div>

                  <div class="bfp-field bfp-field-half">
                    <label class="bfp-label" for="m-start">Start Date</label>
                    <div class="bfp-input-wrap">
                      <i class="material-icons-round bfp-input-icon">event</i>
                      <input id="m-start" v-model="form.startDate" type="date" class="bfp-input" />
                    </div>
                  </div>

                  <div class="bfp-field bfp-field-half">
                    <label class="bfp-label" for="m-end">Target Completion</label>
                    <div class="bfp-input-wrap">
                      <i class="material-icons-round bfp-input-icon">event_available</i>
                      <input id="m-end" v-model="form.endDate" type="date" class="bfp-input" />
                    </div>
                  </div>

                  <div class="bfp-field bfp-field-full">
                    <label class="bfp-label" for="m-budget">
                      Project Budget (₱)
                    </label>
                    <div class="bfp-input-wrap">
                      <i class="material-icons-round bfp-input-icon">payments</i>
                      <input
                        id="m-budget"
                        v-model="form.budget"
                        type="text"
                        class="bfp-input"
                        placeholder="e.g. 5,000,000.00"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section: Status & Progress -->
              <div class="bfp-section">
                <div class="bfp-section-label">
                  <i class="material-icons-round">insights</i>
                  Status & Progress
                </div>
                <div class="bfp-form-grid">
                  <div class="bfp-field bfp-field-half">
                    <label class="bfp-label" for="m-phase">
                      Current Phase <span class="bfp-required">*</span>
                    </label>
                    <div class="bfp-input-wrap">
                      <i class="material-icons-round bfp-input-icon">layers</i>
                      <select
                        id="m-phase"
                        v-model="form.phase"
                        class="bfp-input bfp-select"
                        :class="{ 'bfp-input-error': errors.phase }"
                      >
                        <option value="">Select phase</option>
                        <option v-for="ph in phases" :key="ph" :value="ph">{{ ph }}</option>
                      </select>
                    </div>
                    <span v-if="errors.phase" class="bfp-error-msg">{{ errors.phase }}</span>
                  </div>

                  <div class="bfp-field bfp-field-half">
                    <label class="bfp-label" for="m-status">
                      Project Status <span class="bfp-required">*</span>
                    </label>
                    <div class="bfp-input-wrap">
                      <i class="material-icons-round bfp-input-icon">flag</i>
                      <select
                        id="m-status"
                        v-model="form.status"
                        class="bfp-input bfp-select"
                        :class="{ 'bfp-input-error': errors.status }"
                      >
                        <option value="">Select status</option>
                        <option value="on_time">On Time</option>
                        <option value="delayed">Delayed</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="planning">Planning</option>
                        <option value="completed">Completed</option>
                        <option value="suspended">Suspended</option>
                      </select>
                    </div>
                    <span v-if="errors.status" class="bfp-error-msg">{{ errors.status }}</span>
                  </div>

                  <!-- Progress Slider -->
                  <div class="bfp-field bfp-field-full">
                    <label class="bfp-label">
                      Completion Progress
                      <span class="bfp-progress-pct" :style="{ color: progressColor(form.progress) }">
                        {{ form.progress }}%
                      </span>
                    </label>
                    <div class="bfp-slider-wrap">
                      <input
                        v-model.number="form.progress"
                        type="range"
                        min="0"
                        max="100"
                        step="1"
                        class="bfp-slider"
                        :style="sliderTrackStyle(form.progress)"
                      />
                      <div class="bfp-slider-labels">
                        <span>0%</span>
                        <span>25%</span>
                        <span>50%</span>
                        <span>75%</span>
                        <span>100%</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Section: Remarks -->
              <div class="bfp-section">
                <div class="bfp-section-label">
                  <i class="material-icons-round">notes</i>
                  Additional Remarks
                </div>
                <div class="bfp-form-grid">
                  <div class="bfp-field bfp-field-full">
                    <div class="bfp-input-wrap">
                      <textarea
                        v-model="form.notes"
                        class="bfp-input bfp-textarea"
                        placeholder="Optional — observations, special conditions, site concerns, etc."
                        rows="3"
                      ></textarea>
                    </div>
                  </div>
                </div>
              </div>

            </div><!-- /modal-body -->

            <!-- Modal Footer -->
            <div class="bfp-modal-footer">
              <div class="bfp-footer-note">
                <i class="material-icons-round" style="font-size:14px;vertical-align:-2px">info</i>
                Fields marked <span style="color:#c0392b;font-weight:700">*</span> are required.
              </div>
              <div class="bfp-footer-actions">
                <button class="bfp-btn-cancel" @click="closeModal">Cancel</button>
                <button class="bfp-btn-save" @click="saveProject">
                  <i class="material-icons-round">save</i>
                  Register Project
                </button>
              </div>
            </div>

          </div>
        </div>
      </Transition>
    </Teleport>

    <BfpModal
      :show="showFilterModal"
      title="Infrastructure Plan Filters"
      stripe="PROJECT SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showFilterModal = false"
      @confirm="showFilterModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">tune</i> Project Criteria</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Project Name</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">business</i>
              <input v-model="filters.name" class="bfp-input" type="text" placeholder="Search project name" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Project Code</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">tag</i>
              <input v-model="filters.code" class="bfp-input" type="text" placeholder="Search project code" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Province</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">location_on</i>
              <select v-model="filters.location" class="bfp-input bfp-select">
                <option value="">All Provinces</option>
                <option v-for="location in locations" :key="location" :value="location">{{ location }}</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Phase</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">layers</i>
              <select v-model="filters.phase" class="bfp-input bfp-select">
                <option value="">All Phases</option>
                <option v-for="phase in phases" :key="phase" :value="phase">{{ phase }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">flag</i> Status</div>
        <div class="bfp-filter-grid">
          <label class="bfp-check-option">
            <input type="radio" value="" v-model="filters.status" />
            All Status
          </label>
          <label v-for="(label, key) in statusOptions" :key="key" class="bfp-check-option">
            <input type="radio" :value="key" v-model="filters.status" />
            {{ label }}
          </label>
        </div>
      </div>
    </BfpModal>

  </div>
</template>

<script>
import bfpLogo from "@/assets/img/BFP 11.png";
import BfpModal from "@/components/BfpModal.vue";

export default {
  name: "InfrastructurePlans",
  components: { BfpModal },
  data() {
    return {
      bfpLogo,
      showModal: false,
      showFilterModal: false,
      filters: { name: "", code: "", location: "", status: "", phase: "" },
      locations: ["Cagayan", "Isabela", "Nueva Vizcaya", "Quirino"],
      phases: ["Planning", "Foundation", "Construction", "Finishing", "Post-Eval"],
      statusOptions: {
        on_time: "On Time",
        delayed: "Delayed",
        ongoing: "Ongoing",
        planning: "Planning",
        completed: "Completed",
        suspended: "Suspended"
      },
      lastUpdate: "Oct 24, 2024 · 14:30",
      efficiency: "94.2%",

      /* ── New Project Form ── */
      form: {
        code: "",
        name: "",
        location: "",
        contractor: "",
        startDate: "",
        endDate: "",
        budget: "",
        phase: "",
        status: "",
        progress: 0,
        notes: ""
      },
      errors: {},

      projects: [
        { id: 1, code: "BFP-2024-C001", name: "Tuguegarao Central Fire Station - Phase II",     location: "Cagayan",       contractor: "V.G. Construction Services",   progress: 45,  phase: "Construction", status: "on_time"  },
        { id: 2, code: "BFP-2024-1012", name: "Ilagan City Fire Sub-Station Annex",             location: "Isabela",       contractor: "BuildRight Partners Corp.",    progress: 12,  phase: "Foundation",   status: "delayed"  },
        { id: 3, code: "BFP-2023-N005", name: "Bayombong Regional Logistics Hub",               location: "Nueva Vizcaya", contractor: "NorthEdge Engineering",        progress: 85,  phase: "Finishing",    status: "ongoing"  },
        { id: 4, code: "BFP-2024-P002", name: "Region II Headquarters Renovation",              location: "Cagayan",       contractor: "PrimeBuilders Inc.",           progress: 5,   phase: "Planning",     status: "planning" },
        { id: 5, code: "BFP-2023-Q008", name: "Quirino Provincial Fire Office - Solar Project", location: "Quirino",       contractor: "EcoPower Solutions",           progress: 100, phase: "Post-Eval",    status: "completed"}
      ]
    };
  },

  computed: {
    filteredProjects() {
      return this.projects.filter((p) => {
        return (
          (!this.filters.name     || p.name.toLowerCase().includes(this.filters.name.toLowerCase())) &&
          (!this.filters.code     || p.code.toLowerCase().includes(this.filters.code.toLowerCase())) &&
          (!this.filters.location || p.location === this.filters.location) &&
          (!this.filters.status   || p.status   === this.filters.status)   &&
          (!this.filters.phase    || p.phase     === this.filters.phase)
        );
      });
    }
  },

  methods: {
    resetFilters() {
      // filters are applied reactively via computed — this button is a no-op
      // kept for UX affordance; to clear filters, set all values to empty
    },

      setFilterStatus(status) {
        this.filters.status = status;
      },

    closeModal() {
      this.showModal = false;
      this.errors = {};
      this.form = { code: "", name: "", location: "", contractor: "", startDate: "", endDate: "", budget: "", phase: "", status: "", progress: 0, notes: "" };
    },

    validateForm() {
      const e = {};
      if (!this.form.code.trim())       e.code       = "Project code is required.";
      if (!this.form.name.trim())       e.name       = "Project name is required.";
      if (!this.form.location)          e.location   = "Please select a province.";
      if (!this.form.contractor.trim()) e.contractor = "Contractor name is required.";
      if (!this.form.phase)             e.phase      = "Please select a phase.";
      if (!this.form.status)            e.status     = "Please select a status.";
      this.errors = e;
      return Object.keys(e).length === 0;
    },

    saveProject() {
      if (!this.validateForm()) return;
      this.projects.push({
        id:         Date.now(),
        code:       this.form.code.trim(),
        name:       this.form.name.trim(),
        location:   this.form.location,
        contractor: this.form.contractor.trim(),
        progress:   Math.min(100, Math.max(0, Number(this.form.progress) || 0)),
        phase:      this.form.phase,
        status:     this.form.status
      });
      this.closeModal();
    },

    statusLabel(status) {
      const labels = { on_time: "ON TIME", delayed: "DELAYED", completed: "COMPLETED", ongoing: "ONGOING", planning: "PLANNING", suspended: "SUSPENDED" };
      return labels[status] || status.toUpperCase();
    },

    progressBarClass(progress) {
      if (progress === 100) return "progress-bar-completed";
      if (progress >= 75)   return "progress-bar-high";
      if (progress >= 40)   return "progress-bar-mid";
      return "progress-bar-low";
    },

    progressTextClass(progress) {
      if (progress === 100) return "text-completed";
      if (progress >= 75)   return "text-high";
      if (progress >= 40)   return "text-mid";
      return "text-low";
    },

    progressColor(p) {
      if (p === 100) return "#15803d";
      if (p >= 75)   return "#16a34a";
      if (p >= 40)   return "#d97706";
      return "#ef4444";
    },

    sliderTrackStyle(p) {
      const color = this.progressColor(p);
      return `--slider-fill: ${color}; background: linear-gradient(to right, ${color} ${p}%, #e5e7eb ${p}%)`;
    },

    viewProject(project)   { alert(`View: ${project.name}`);   },
    editProject(project)   { alert(`Edit: ${project.name}`);   },
    deleteProject(project) {
      if (confirm(`Delete "${project.name}"?`)) {
        this.projects = this.projects.filter(p => p.id !== project.id);
      }
    }
  }
};
</script>

<style scoped>

/* ════════════════════════════════════════════
   PAGE BASE
════════════════════════════════════════════ */
.admin-page {
  min-height: 100vh;
  background: #f8f9fc;
}

/* ── Filters ── */
.filters-row .form-control,
.filters-row .btn {
  border-radius: 0.85rem;
  min-height: 44px;
}

/* ── Table ── */
.table thead th { border-bottom: none; background: #fff; }
.table tbody tr { border-bottom: 1px solid rgba(0,0,0,.05); }
.table thead th, .table tbody td { vertical-align: middle; }

.project-code {
  font-weight: 700;
  color: #2563eb;
  font-size: 0.8rem;
  background: #eff6ff;
  padding: 3px 8px;
  border-radius: 5px;
  white-space: nowrap;
}

.progress { background: rgba(0,0,0,.07); height: 6px; border-radius: 99px; min-width: 100px; }
.progress-bar-low       { background-color: #ef4444; }
.progress-bar-mid       { background-color: #f59e0b; }
.progress-bar-high      { background-color: #22c55e; }
.progress-bar-completed { background-color: #16a34a; }

.progress-label { font-size: 0.75rem; font-weight: 600; white-space: nowrap; }
.text-low       { color: #ef4444; }
.text-mid       { color: #d97706; }
.text-high      { color: #16a34a; }
.text-completed { color: #15803d; }

.status-pill { display: inline-flex; align-items: center; gap: 5px; padding: .35rem .8rem; border-radius: 999px; font-size: .7rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; white-space: nowrap; }
.status-dot  { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.status-pill.on_time   { background: rgba(88,207,151,.15); color: #1d7a3f; } .status-pill.on_time   .status-dot { background: #1d7a3f; }
.status-pill.delayed   { background: rgba(239,68,68,.12);  color: #c62828; } .status-pill.delayed   .status-dot { background: #c62828; }
.status-pill.completed { background: rgba(22,163,74,.12);  color: #15803d; border: 1px solid rgba(22,163,74,.3); } .status-pill.completed .status-dot { background: #15803d; }
.status-pill.ongoing   { background: rgba(245,158,11,.15); color: #b45309; } .status-pill.ongoing   .status-dot { background: #d97706; }
.status-pill.planning  { background: rgba(59,130,246,.12); color: #1e40af; } .status-pill.planning  .status-dot { background: #2563eb; }
.status-pill.suspended { background: rgba(139,92,246,.12); color: #6d28d9; } .status-pill.suspended .status-dot { background: #7c3aed; }

.btn-icon { width: 2.2rem; height: 2.2rem; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
.dropdown-menu { border: 1px solid rgba(0,0,0,.08); border-radius: .75rem; font-size: .85rem; min-width: 140px; padding: .3rem; }
.dropdown-item { border-radius: .5rem; padding: .45rem .75rem; display: flex; align-items: center; }
.dropdown-item:hover { background: #f3f4f6; }
.dropdown-item.text-danger:hover { background: #fef2f2; }
.dropdown-icon { font-size: 1rem; }
.view-icon { color: #2563eb; }
.edit-icon { color: #d97706; }
.card { border: none; border-radius: 1rem; }
.card-body { padding: 1.25rem 1.5rem; }
.bg-soft-primary { background: rgba(13,110,253,.12) !important; }
.bg-soft-success { background: rgba(25,135,84,.12)  !important; }


/* ════════════════════════════════════════════
   MODAL OVERLAY
════════════════════════════════════════════ */
.bfp-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(10, 10, 20, 0.60);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1055;
  padding: 1rem;
}

/* ════════════════════════════════════════════
   MODAL BOX
════════════════════════════════════════════ */
.bfp-modal {
  background: #ffffff;
  border-radius: 16px;
  width: 620px;
  max-width: 100%;
  max-height: 92vh;
  overflow-y: auto;
  overflow-x: hidden;
  box-shadow: 0 24px 60px rgba(0,0,0,0.25);
  display: flex;
  flex-direction: column;
}

/* ── Modal Header ── */
.bfp-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 22px 14px;
  background: #1a1a2e;
  border-radius: 16px 16px 0 0;
}

.bfp-modal-header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.bfp-modal-emblem {
  width: 52px;
  height: 52px;
  background: rgba(255,255,255,0.08);
  border: 1.5px solid rgba(255,255,255,0.15);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}

.bfp-logo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 11px;
}

.bfp-modal-agency {
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.55);
  margin: 0 0 3px;
}

.bfp-modal-title {
  font-size: 17px;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
  letter-spacing: -0.01em;
}

.bfp-modal-close {
  width: 34px;
  height: 34px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: rgba(255,255,255,0.7);
  transition: all 0.15s;
  flex-shrink: 0;
}
.bfp-modal-close:hover {
  background: rgba(192,57,43,0.6);
  color: #fff;
  border-color: transparent;
}
.bfp-modal-close .material-icons-round { font-size: 18px; }

/* ── Red Stripe ── */
.bfp-modal-stripe {
  background: linear-gradient(90deg, #c0392b 0%, #922b21 100%);
  padding: 7px 22px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.bfp-modal-stripe span {
  font-size: 9.5px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: rgba(255,255,255,0.85);
}

/* ── Modal Body ── */
.bfp-modal-body {
  padding: 20px 22px;
  flex: 1;
}

/* ── Section ── */
.bfp-section {
  margin-bottom: 20px;
}
.bfp-section:last-child {
  margin-bottom: 0;
}

.bfp-section-label {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #7f1d1d;
  background: #fef2f2;
  border-left: 3px solid #c0392b;
  padding: 7px 12px;
  border-radius: 0 8px 8px 0;
  margin-bottom: 14px;
}
.bfp-section-label .material-icons-round {
  font-size: 15px;
  color: #c0392b;
}

/* ── Form Grid ── */
.bfp-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.bfp-field-half { grid-column: span 1; }
.bfp-field-full { grid-column: 1 / -1; }

/* ── Label ── */
.bfp-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 11.5px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 5px;
  letter-spacing: 0.01em;
}
.bfp-required { color: #c0392b; font-size: 13px; }

/* ── Input Wrap ── */
.bfp-input-wrap { position: relative; }
.bfp-input-icon {
  position: absolute;
  left: 11px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 16px;
  color: #9ca3af;
  pointer-events: none;
}

/* ── Inputs ── */
.bfp-input {
  width: 100%;
  padding: 9px 12px 9px 36px;
  border: 1.5px solid #e5e7eb;
  border-radius: 9px;
  font-size: 13px;
  color: #111827;
  background: #fafafa;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
  -webkit-appearance: none;
  appearance: none;
}
.bfp-input:focus {
  border-color: #c0392b;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(192,57,43,0.10);
}
.bfp-input-error {
  border-color: #ef4444 !important;
  background: #fff5f5 !important;
}
.bfp-select {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  padding-right: 32px;
}
.bfp-textarea {
  padding: 10px 12px;
  resize: vertical;
  min-height: 72px;
  line-height: 1.5;
}

/* ── Error message ── */
.bfp-error-msg {
  display: block;
  font-size: 11px;
  color: #dc2626;
  margin-top: 4px;
  padding-left: 2px;
}

/* ── Progress Slider ── */
.bfp-progress-pct {
  font-size: 15px;
  font-weight: 700;
  transition: color 0.2s;
}

.bfp-slider-wrap { margin-top: 4px; }

.bfp-slider {
  -webkit-appearance: none;
  appearance: none;
  width: 100%;
  height: 6px;
  border-radius: 99px;
  outline: none;
  cursor: pointer;
  transition: background 0.1s;
}
.bfp-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #ffffff;
  border: 2.5px solid var(--slider-fill, #c0392b);
  box-shadow: 0 1px 4px rgba(0,0,0,0.18);
  cursor: pointer;
  transition: transform 0.12s;
}
.bfp-slider::-webkit-slider-thumb:hover { transform: scale(1.15); }
.bfp-slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #ffffff;
  border: 2.5px solid var(--slider-fill, #c0392b);
  box-shadow: 0 1px 4px rgba(0,0,0,0.18);
  cursor: pointer;
}

.bfp-slider-labels {
  display: flex;
  justify-content: space-between;
  margin-top: 5px;
}
.bfp-slider-labels span {
  font-size: 10.5px;
  color: #9ca3af;
  font-weight: 500;
}

/* ── Modal Footer ── */
.bfp-modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 22px;
  background: #f9fafb;
  border-top: 1px solid #f0f0f0;
  border-radius: 0 0 16px 16px;
}

.bfp-footer-note {
  font-size: 11.5px;
  color: #6b7280;
}

.bfp-footer-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.bfp-btn-cancel {
  padding: 9px 18px;
  border: 1.5px solid #e5e7eb;
  border-radius: 9px;
  background: transparent;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.15s;
}
.bfp-btn-cancel:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
  color: #374151;
}

.bfp-btn-save {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 9px 22px;
  background: #c0392b;
  border: none;
  border-radius: 9px;
  font-size: 13px;
  font-weight: 600;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.15s;
  letter-spacing: 0.01em;
}
.bfp-btn-save:hover {
  background: #a93226;
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(192,57,43,0.35);
}
.bfp-btn-save:active {
  transform: translateY(0);
}
.bfp-btn-save .material-icons-round { font-size: 17px; }

/* ════════════════════════════════════════════
   MODAL TRANSITION
════════════════════════════════════════════ */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-active .bfp-modal,
.modal-fade-leave-active .bfp-modal {
  transition: transform 0.22s cubic-bezier(0.34, 1.2, 0.64, 1), opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-from .bfp-modal {
  transform: translateY(24px) scale(0.97);
  opacity: 0;
}
.modal-fade-leave-to .bfp-modal {
  transform: translateY(12px) scale(0.98);
  opacity: 0;
}

/* ── Scrollbar styling inside modal ── */
.bfp-modal::-webkit-scrollbar { width: 5px; }
.bfp-modal::-webkit-scrollbar-track { background: transparent; }
.bfp-modal::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 99px; }
.bfp-modal::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

/* ── Responsive ── */
@media (max-width: 576px) {
  .bfp-form-grid { grid-template-columns: 1fr; }
  .bfp-field-half { grid-column: span 1; }
  .bfp-modal-footer { flex-direction: column; gap: 10px; align-items: stretch; }
  .bfp-footer-actions { justify-content: flex-end; }
}
</style>
