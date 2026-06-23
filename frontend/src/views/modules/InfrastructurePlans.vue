<template>
  <div class="admin-page infra-page">
    <div class="container-fluid py-4">
      <div class="infra-hero card shadow-sm border-0 mb-4">
        <div class="infra-hero-copy">
          <p class="infra-eyebrow mb-2">Infrastructure Plans Management</p>
          <h4 class="mb-2">Infrastructure Plans Management</h4>
          <p class="infra-hero-subtitle mb-0">
            Monitor and manage institutional construction projects across Region II.
          </p>
        </div>
        <div class="infra-hero-actions">
          <button class="btn btn-outline-secondary btn-sm infra-ghost-btn" type="button" @click="showFilterModal = true">
            <i class="material-icons-round">tune</i>
            Apply Filters
          </button>
          <button
            v-if="canCreate"
            class="btn btn-primary btn-sm infra-primary-btn"
            type="button"
            @click="openCreateModal"
          >
            <i class="material-icons-round">add</i>
            Add New Project
          </button>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div v-for="card in summaryCards" :key="card.key" class="col-12 col-sm-6 col-xl-3">
          <div class="card stat-card h-100 shadow-sm border-0">
            <div class="card-body">
              <div class="stat-card-top">
                <div class="stat-icon" :class="`stat-icon-${card.tone}`">
                  <i class="material-icons-round">{{ card.icon }}</i>
                </div>
                <span class="stat-badge" :class="`stat-badge-${card.tone}`">{{ card.delta }}</span>
              </div>
              <p class="stat-label mb-1">{{ card.label }}</p>
              <div class="stat-value mb-1">{{ card.value }}</div>
              <p class="stat-note mb-0">{{ card.note }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="card inventory-card shadow-sm border-0 mb-4">
        <div class="inventory-header">
          <div>
            <p class="section-kicker mb-1">Project Inventory</p>
            <h5 class="mb-0">Project Inventory</h5>
          </div>
          <div class="inventory-toolbar">
            <div class="inventory-tabs">
              <button
                v-for="tab in statusTabs"
                :key="tab.value || 'all'"
                type="button"
                class="inventory-tab"
                :class="{ active: filters.status === tab.value }"
                @click="setFilterStatus(tab.value)"
              >
                {{ tab.label }}
              </button>
            </div>
            <button class="btn btn-outline-secondary btn-sm infra-toolbar-btn" type="button" @click="showFilterModal = true">
              <i class="material-icons-round">filter_alt</i>
              Filters
            </button>
            <button
              v-if="canCreate"
              class="btn btn-outline-secondary btn-sm infra-toolbar-btn"
              type="button"
              @click="openCreateModal"
            >
              <i class="material-icons-round">timeline</i>
              Timeline
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table align-items-center mb-0 inventory-table">
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
              <tr v-if="loading">
                <td colspan="8" class="align-middle text-center py-4">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                  Loading projects...
                </td>
              </tr>
              <tr v-else-if="error">
                <td colspan="8" class="align-middle text-center py-4 text-danger">
                  {{ error }}
                </td>
              </tr>
              <tr v-else-if="filteredProjects.length === 0">
                <td colspan="8" class="align-middle text-center py-4 text-secondary">
                  No projects found. Click "Add New Project" to create one.
                </td>
              </tr>
              <tr v-for="project in filteredProjects" :key="project.id">
                <td class="align-middle text-sm">
                  <span class="project-code">{{ project.code }}</span>
                </td>
                <td class="align-middle text-sm">
                  <div class="project-name">{{ project.name }}</div>
                </td>
                <td class="align-middle text-sm">
                  <div class="project-location">{{ project.location }}</div>
                </td>
                <td class="align-middle text-sm">{{ project.contractor }}</td>
                <td class="align-middle text-sm">
                  <div class="d-flex align-items-center gap-2 progress-group">
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
                      <li v-if="canEdit">
                        <a class="dropdown-item" href="#" @click.prevent="editProject(project)">
                          <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit</i>
                          Edit
                        </a>
                      </li>
                      <li v-if="canEdit && canDelete"><hr class="dropdown-divider" /></li>
                      <li v-if="canDelete">
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

        <div class="inventory-footer">
          <div class="inventory-summary">
            {{ tableRangeLabel }}
          </div>
          <nav v-if="meta.last_page > 1" aria-label="Project pagination">
            <ul class="pagination pagination-sm mb-0 infra-pagination">
              <li class="page-item" :class="{ disabled: meta.current_page <= 1 }">
                <button class="page-link" type="button" :disabled="meta.current_page <= 1" @click="goToPage(meta.current_page - 1)">Previous</button>
              </li>
              <li
                v-for="page in paginationPages"
                :key="page"
                class="page-item"
                :class="{ active: meta.current_page === page }"
              >
                <button class="page-link" type="button" :disabled="meta.current_page === page" @click="goToPage(page)">{{ page }}</button>
              </li>
              <li class="page-item" :class="{ disabled: meta.current_page >= meta.last_page }">
                <button class="page-link" type="button" :disabled="meta.current_page >= meta.last_page" @click="goToPage(meta.current_page + 1)">Next</button>
              </li>
            </ul>
          </nav>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-xl-8">
          <div class="card analytics-card shadow-sm border-0 h-100">
            <div class="analytics-header">
              <div>
                <p class="section-kicker mb-1">Regional Distribution</p>
                <h5 class="mb-0">Regional Distribution</h5>
                <p class="section-subtext mb-0">Projects mapped by geographical cluster</p>
              </div>
              <button class="analytics-link" type="button" @click="showFilterModal = true">
                View Filters
                <i class="material-icons-round">chevron_right</i>
              </button>
            </div>

            <div class="regional-grid">
              <div class="regional-bars">
                <div v-for="item in regionalDistribution" :key="item.location" class="regional-row">
                  <div class="regional-row-head">
                    <span>{{ item.location }}</span>
                    <strong>{{ item.percent }}%</strong>
                  </div>
                  <div class="progress progress-sm regional-progress">
                    <div class="progress-bar regional-progress-bar" :style="{ width: item.percent + '%' }"></div>
                  </div>
                </div>
              </div>

              <div class="regional-insights">
                <div class="regional-summary-card">
                  <div class="regional-summary-label">Top Region</div>
                  <div class="regional-summary-value">{{ topRegion.location }}</div>
                  <div class="regional-summary-meta">{{ topRegion.percent }}% share of the current register</div>
                </div>
                <div class="regional-summary-card muted">
                  <div class="regional-summary-label">Active Coverage</div>
                  <div class="regional-summary-value">{{ summaryCards[0].value }} projects</div>
                  <div class="regional-summary-meta">Across {{ locationCount }} provinces in Region II</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-4">
          <div class="card analytics-card shadow-sm border-0 h-100">
            <div class="analytics-header">
              <div>
                <p class="section-kicker mb-1">Recent Updates</p>
                <h5 class="mb-0">Recent Updates</h5>
                <p class="section-subtext mb-0">Latest system activity from the project register</p>
              </div>
            </div>

            <div class="update-feed">
              <div v-for="item in recentUpdates" :key="item.id" class="update-item">
                <div class="update-bullet" :class="`update-bullet-${statusTone(item.status)}`"></div>
                <div class="update-copy">
                  <div class="update-title">{{ item.name }}</div>
                  <div class="update-meta">
                    {{ item.location }}
                    <span class="update-dot">&middot;</span>
                    {{ item.relativeTime }}
                  </div>
                </div>
                <span class="update-status" :class="item.status">{{ statusLabel(item.status) }}</span>
              </div>
            </div>

            <button class="audit-trail-btn" type="button" @click="showFilterModal = true">
              View Full Audit Trail
            </button>
          </div>
        </div>
      </div>
    </div>

    <BfpModal
      :show="showModal"
      :title="modalTitle"
      stripe="PROJECT REGISTRATION FORM"
      note="Fields marked * are required."
      :confirm-text="modalConfirmText"
      confirm-icon="save"
      :loading="isSubmitting"
      @close="closeModal"
      @confirm="saveProject"
    >
      <div v-if="modalError" class="alert alert-danger infra-modal-alert" role="alert">
        {{ modalError }}
      </div>

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
                placeholder="e.g. BFP-2026-X001"
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
              Project Budget (PHP)
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
                placeholder="Optional - observations, special conditions, site concerns, etc."
                rows="3"
              ></textarea>
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

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
import projectService from "@/services/project.service";

export default {
  name: "InfrastructurePlans",
  components: { BfpModal },
  data() {
    return {
      bfpLogo,
      showModal: false,
      showFilterModal: false,
      loading: false,
      error: null,
      modalError: null,
      isSubmitting: false,
      permissions: {
        can_create: false,
        can_edit: false,
        can_delete: false,
      },
      projects: [],
      meta: {
        current_page: 1,
        last_page: 1,
        per_page: 6,
        total: 0,
      },
      stats: {
        total_projects: 0,
        delayed_works: 0,
        regional_efficiency: 0,
        total_budget: 0,
        last_updated_at: null,
        regional_distribution: [],
        recent_updates: [],
      },
      currentPage: 1,
      perPage: 6,
      filters: { name: "", code: "", location: "", status: "", phase: "" },
      locations: ["Cagayan", "Isabela", "Nueva Vizcaya", "Quirino"],
      phases: ["Planning", "Foundation", "Construction", "Finishing", "Post-Eval"],
      statusTabs: [
        { label: "All", value: "" },
        { label: "On Time", value: "on_time" },
        { label: "Delayed", value: "delayed" },
        { label: "Completed", value: "completed" },
        { label: "Ongoing", value: "ongoing" },
        { label: "Planning", value: "planning" },
        { label: "Suspended", value: "suspended" },
      ],
      statusOptions: {
        on_time: "On Time",
        delayed: "Delayed",
        ongoing: "Ongoing",
        planning: "Planning",
        completed: "Completed",
        suspended: "Suspended",
      },
      form: {
        id: null,
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
        notes: "",
      },
      errors: {},
    };
  },

  mounted() {
    this.fetchProjects(1);
  },

  computed: {
    filteredProjects() {
      return this.projects;
    },

    modalConfirmText() {
      return this.isSubmitting
        ? "Saving..."
        : this.form.id
          ? "Update Project"
          : "Register Project";
    },

    modalTitle() {
      return this.form.id ? "Edit Infrastructure Project" : "New Infrastructure Project";
    },

    canCreate() {
      return this.permissions.can_create;
    },

    canEdit() {
      return this.permissions.can_edit;
    },

    canDelete() {
      return this.permissions.can_delete;
    },

    paginationPages() {
      const pages = [];
      const lastPage = this.meta.last_page || 1;

      for (let page = 1; page <= lastPage; page += 1) {
        pages.push(page);
      }

      return pages;
    },

    tableRangeLabel() {
      const total = this.meta.total || 0;

      if (!total) {
        return "Showing 0 of 0 projects";
      }

      const perPage = this.meta.per_page || this.perPage;
      const start = ((this.meta.current_page || 1) - 1) * perPage + 1;
      const end = Math.min(start + this.projects.length - 1, total);
      return `Showing ${start}-${end} of ${total} projects`;
    },

    summaryCards() {
      const totalProjects = this.stats.total_projects || 0;
      const delayedWorks = this.stats.delayed_works || 0;
      const efficiency = Number(this.stats.regional_efficiency || 0);
      const totalBudget = Number(this.stats.total_budget || 0);

      return [
        {
          key: "active-projects",
          label: "Active Projects",
          value: this.formatInteger(totalProjects),
          note: "Current filtered register",
          delta: "Live",
          icon: "engineering",
          tone: "primary",
        },
        {
          key: "regional-efficiency",
          label: "Regional Efficiency",
          value: `${efficiency.toFixed(1)}%`,
          note: "On-time delivery average",
          delta: "Realtime",
          icon: "trending_up",
          tone: "success",
        },
        {
          key: "delayed-works",
          label: "Delayed Works",
          value: this.formatInteger(delayedWorks).padStart(2, "0"),
          note: "Requiring urgent intervention",
          delta: "Watch",
          icon: "schedule",
          tone: "warning",
        },
        {
          key: "total-budget",
          label: "Total Budget",
          value: this.formatBudget(totalBudget),
          note: "Current FY allocation",
          delta: "FY",
          icon: "payments",
          tone: "danger",
        },
      ];
    },

    regionalDistribution() {
      const rows = Array.isArray(this.stats.regional_distribution) ? this.stats.regional_distribution : [];
      const total = rows.reduce((sum, row) => sum + (Number(row.total) || 0), 0) || 1;

      return rows.map((row) => ({
        location: row.location,
        total: Number(row.total) || 0,
        percent: Math.round(((Number(row.total) || 0) / total) * 100),
      }));
    },

    topRegion() {
      return this.regionalDistribution[0] || { location: "N/A", percent: 0, total: 0 };
    },

    locationCount() {
      return this.regionalDistribution.length || this.locations.length;
    },

    recentUpdates() {
      return (Array.isArray(this.stats.recent_updates) ? this.stats.recent_updates : []).map((project) => ({
        id: project.id,
        name: `${project.code} - ${project.name}`,
        location: project.location,
        status: project.status,
        relativeTime: this.formatRelativeTime(project.updated_at || project.created_at),
      }));
    },
  },

  watch: {
    filters: {
      handler() {
        this.fetchProjects(1);
      },
      deep: true,
    },
  },

  methods: {
    async fetchProjects(page = 1) {
      this.currentPage = page;
      this.loading = true;
      this.error = null;

      try {
        const response = await projectService.getProjects({
          search: this.filters.name || undefined,
          code: this.filters.code || undefined,
          location: this.filters.location || undefined,
          status: this.filters.status || undefined,
          phase: this.filters.phase || undefined,
          page,
          per_page: this.perPage,
        });

        this.projects = response.data.data || [];
        this.meta = response.data.meta || this.meta;
        this.stats = response.data.stats || this.stats;
        this.permissions = response.data.meta?.permissions || this.permissions;
        this.currentPage = this.meta.current_page || page;
      } catch (e) {
        const message = e.response?.data?.message;
        if (e.response?.status === 403) {
          this.error = message || "You do not have permission to view projects.";
        } else if (e.response?.status === 401) {
          this.error = "Your session has expired. Please sign in again.";
        } else {
          this.error = message || "Failed to load projects. Please try again.";
        }
      } finally {
        this.loading = false;
      }
    },

    goToPage(page) {
      if (page < 1 || page > (this.meta.last_page || 1) || page === this.meta.current_page) {
        return;
      }

      this.fetchProjects(page);
    },

    resetFilters() {
      this.filters = { name: "", code: "", location: "", status: "", phase: "" };
    },

    setFilterStatus(status) {
      this.filters.status = status || "";
    },

    openCreateModal() {
      this.modalError = null;
      this.errors = {};
      this.form = {
        id: null,
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
        notes: "",
      };
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.modalError = null;
      this.errors = {};
      this.form = {
        id: null,
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
        notes: "",
      };
    },

    validateForm() {
      const e = {};
      if (!this.form.code.trim()) e.code = "Project code is required.";
      if (!this.form.name.trim()) e.name = "Project name is required.";
      if (!this.form.location) e.location = "Please select a province.";
      if (!this.form.contractor?.trim()) e.contractor = "Contractor / firm is required.";
      if (!this.form.phase) e.phase = "Please select a phase.";
      if (!this.form.status) e.status = "Please select a status.";
      this.errors = e;
      return Object.keys(e).length === 0;
    },

    parseBudget(value) {
      if (!value) {
        return null;
      }

      const normalized = String(value).replace(/,/g, "").trim();
      if (!normalized) {
        return null;
      }

      const amount = Number(normalized);
      return Number.isFinite(amount) ? amount : null;
    },

    async saveProject() {
      if (this.isSubmitting) {
        return;
      }

      if (!this.validateForm()) return;

      this.isSubmitting = true;
      this.modalError = null;
      this.errors = {};

      try {
        const payload = {
          code: this.form.code.trim(),
          name: this.form.name.trim(),
          location: this.form.location,
          contractor: this.form.contractor?.trim() || null,
          startDate: this.form.startDate || null,
          endDate: this.form.endDate || null,
          budget: this.parseBudget(this.form.budget),
          phase: this.form.phase,
          status: this.form.status,
          progress: Math.min(100, Math.max(0, Number(this.form.progress) || 0)),
          notes: this.form.notes || null,
        };

        if (this.form.id) {
          await projectService.updateProject(this.form.id, payload);
        } else {
          await projectService.createProject(payload);
        }

        this.closeModal();
        await this.fetchProjects(this.currentPage);
      } catch (e) {
        const response = e.response?.data || {};
        const validationErrors = response.errors || {};

        if (e.response?.status === 422 && Object.keys(validationErrors).length) {
          this.errors = Object.fromEntries(
            Object.entries(validationErrors).map(([field, messages]) => [
              field,
              Array.isArray(messages) ? messages[0] : String(messages),
            ])
          );
          this.modalError = response.message || "Please correct the highlighted fields.";
        } else if (e.response?.status === 403) {
          this.modalError = response.message || "You do not have permission to save this project.";
        } else {
          this.modalError = response.message || "Failed to save project.";
        }
      } finally {
        this.isSubmitting = false;
      }
    },

    viewProject(project) {
      alert(`View: ${project.name}`);
    },

    editProject(project) {
      if (!this.canEdit) {
        return;
      }

      this.modalError = null;
      this.errors = {};
      this.form = {
        id: project.id,
        code: project.code,
        name: project.name,
        location: project.location,
        contractor: project.contractor === "-" ? "" : project.contractor,
        startDate: project.start_date,
        endDate: project.end_date,
        budget: project.budget,
        phase: project.phase,
        status: project.status,
        progress: project.progress,
        notes: project.notes || "",
      };
      this.showModal = true;
    },

    statusLabel(status) {
      const labels = {
        on_time: "ON TIME",
        delayed: "DELAYED",
        completed: "COMPLETED",
        ongoing: "ONGOING",
        planning: "PLANNING",
        suspended: "SUSPENDED",
      };

      return labels[status] || status.toUpperCase();
    },

    progressBarClass(progress) {
      if (progress === 100) return "progress-bar-completed";
      if (progress >= 75) return "progress-bar-high";
      if (progress >= 40) return "progress-bar-mid";
      return "progress-bar-low";
    },

    progressTextClass(progress) {
      if (progress === 100) return "text-completed";
      if (progress >= 75) return "text-high";
      if (progress >= 40) return "text-mid";
      return "text-low";
    },

    progressColor(p) {
      if (p === 100) return "#15803d";
      if (p >= 75) return "#16a34a";
      if (p >= 40) return "#d97706";
      return "#ef4444";
    },

    sliderTrackStyle(p) {
      const color = this.progressColor(p);
      return `--slider-fill: ${color}; background: linear-gradient(to right, ${color} ${p}%, #e5e7eb ${p}%)`;
    },

    formatInteger(value) {
      return new Intl.NumberFormat("en-PH").format(Number(value) || 0);
    },

    formatBudget(value) {
      const amount = Number(value) || 0;
      const abs = Math.abs(amount);

      if (abs >= 1000000000) {
        return `PHP ${(amount / 1000000000).toFixed(abs >= 10000000000 ? 0 : 1)}B`;
      }

      if (abs >= 1000000) {
        return `PHP ${(amount / 1000000).toFixed(abs >= 10000000 ? 0 : 1)}M`;
      }

      if (abs >= 1000) {
        return `PHP ${(amount / 1000).toFixed(abs >= 10000 ? 0 : 1)}K`;
      }

      return `PHP ${new Intl.NumberFormat("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(amount)}`;
    },

    formatRelativeTime(value) {
      if (!value) {
        return "Just now";
      }

      const date = new Date(value);
      if (Number.isNaN(date.getTime())) {
        return "Recently";
      }

      const diffMinutes = Math.round((Date.now() - date.getTime()) / 60000);
      if (Math.abs(diffMinutes) < 1) return "Just now";
      if (Math.abs(diffMinutes) < 60) return `${Math.abs(diffMinutes)}m ago`;

      const diffHours = Math.round(diffMinutes / 60);
      if (Math.abs(diffHours) < 24) return `${Math.abs(diffHours)}h ago`;

      const diffDays = Math.round(diffHours / 24);
      return `${Math.abs(diffDays)}d ago`;
    },

    statusTone(status) {
      if (status === "completed") return "success";
      if (status === "delayed") return "danger";
      if (status === "ongoing" || status === "planning") return "warning";
      return "primary";
    },

    async deleteProject(project) {
      if (!this.canDelete) {
        return;
      }

      if (!confirm(`Archive "${project.name}"?`)) return;
      this.error = null;

      try {
        await projectService.deleteProject(project.id);
        await this.fetchProjects(this.currentPage);
      } catch (e) {
        this.error = e.response?.data?.message || "Failed to archive project.";
      }
    },
  },
};
</script>

<style scoped>
.admin-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top left, rgba(192, 57, 43, 0.08), transparent 26%),
    linear-gradient(180deg, #f7f8fc 0%, #f5f6fa 100%);
}

.infra-hero {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 1.5rem;
  padding: 1.6rem 1.75rem;
  border-radius: 1.25rem;
  background: linear-gradient(135deg, #ffffff 0%, #fff7f5 100%);
}

.infra-eyebrow {
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: #ef476f;
}

.infra-hero-copy h4 {
  font-size: 2rem;
  font-weight: 800;
  color: #1f2937;
  line-height: 1.05;
}

.infra-hero-subtitle {
  max-width: 56rem;
  color: #6b7280;
  font-size: 0.98rem;
}

.infra-hero-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.infra-ghost-btn,
.infra-primary-btn,
.infra-toolbar-btn {
  min-height: 42px;
  border-radius: 0.95rem;
  padding-inline: 1rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.infra-primary-btn {
  box-shadow: 0 12px 20px rgba(239, 71, 111, 0.18);
}

.stat-card {
  border-radius: 1.25rem;
  background: #fff;
}

.stat-card .card-body {
  padding: 1.25rem 1.35rem 1.15rem;
}

.stat-card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.85rem;
}

.stat-icon {
  width: 2.7rem;
  height: 2.7rem;
  border-radius: 0.9rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.stat-icon .material-icons-round {
  font-size: 1.35rem;
}

.stat-icon-primary {
  background: rgba(37, 99, 235, 0.12);
  color: #2563eb;
}

.stat-icon-success {
  background: rgba(16, 185, 129, 0.12);
  color: #059669;
}

.stat-icon-warning {
  background: rgba(245, 158, 11, 0.12);
  color: #d97706;
}

.stat-icon-danger {
  background: rgba(239, 68, 68, 0.12);
  color: #dc2626;
}

.stat-badge {
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  padding: 0.28rem 0.55rem;
  border-radius: 999px;
}

.stat-badge-primary {
  background: rgba(37, 99, 235, 0.1);
  color: #2563eb;
}

.stat-badge-success {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.stat-badge-warning {
  background: rgba(245, 158, 11, 0.12);
  color: #d97706;
}

.stat-badge-danger {
  background: rgba(239, 68, 68, 0.12);
  color: #dc2626;
}

.stat-label,
.section-kicker,
.section-subtext {
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.73rem;
  font-weight: 800;
  color: #6b7280;
}

.stat-value {
  font-size: 2rem;
  font-weight: 800;
  line-height: 1;
  color: #111827;
}

.stat-note {
  color: #6b7280;
  font-size: 0.9rem;
}

.inventory-card,
.analytics-card {
  border-radius: 1.25rem;
  overflow: hidden;
}

.inventory-header,
.analytics-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.35rem 1.45rem;
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}

.inventory-toolbar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.inventory-tabs {
  display: inline-flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  padding: 0.3rem;
  background: #f3f4f8;
  border-radius: 999px;
}

.inventory-tab {
  border: 0;
  background: transparent;
  color: #6b7280;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 0.5rem 0.8rem;
  border-radius: 999px;
}

.inventory-tab.active {
  background: #fff;
  color: #ef476f;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
}

.inventory-table thead th {
  background: #fff;
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
  padding: 0.95rem 1.1rem;
}

.inventory-table tbody td {
  padding: 1.02rem 1.1rem;
  border-color: rgba(15, 23, 42, 0.07);
}

.project-code {
  font-weight: 800;
  color: #ef476f;
  background: rgba(239, 71, 111, 0.08);
  padding: 0.25rem 0.55rem;
  border-radius: 0.5rem;
  white-space: nowrap;
}

.project-name {
  font-weight: 700;
  color: #1f2937;
}

.project-location {
  color: #6b7280;
}

.progress-group {
  min-width: 160px;
}

.progress {
  background: rgba(15, 23, 42, 0.06);
  height: 6px;
  border-radius: 999px;
}

.progress-bar-low {
  background-color: #ef4444;
}

.progress-bar-mid {
  background-color: #f59e0b;
}

.progress-bar-high {
  background-color: #22c55e;
}

.progress-bar-completed {
  background-color: #16a34a;
}

.progress-label {
  font-size: 0.75rem;
  font-weight: 700;
  white-space: nowrap;
}

.text-low {
  color: #ef4444;
}

.text-mid {
  color: #d97706;
}

.text-high {
  color: #16a34a;
}

.text-completed {
  color: #15803d;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 0.35rem 0.8rem;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  white-space: nowrap;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.status-pill.on_time {
  background: rgba(88, 207, 151, 0.16);
  color: #1d7a3f;
}

.status-pill.on_time .status-dot {
  background: #1d7a3f;
}

.status-pill.delayed {
  background: rgba(239, 68, 68, 0.12);
  color: #c62828;
}

.status-pill.delayed .status-dot {
  background: #c62828;
}

.status-pill.completed {
  background: rgba(22, 163, 74, 0.12);
  color: #15803d;
  border: 1px solid rgba(22, 163, 74, 0.3);
}

.status-pill.completed .status-dot {
  background: #15803d;
}

.status-pill.ongoing {
  background: rgba(245, 158, 11, 0.15);
  color: #b45309;
}

.status-pill.ongoing .status-dot {
  background: #d97706;
}

.status-pill.planning {
  background: rgba(59, 130, 246, 0.12);
  color: #1e40af;
}

.status-pill.planning .status-dot {
  background: #2563eb;
}

.status-pill.suspended {
  background: rgba(139, 92, 246, 0.12);
  color: #6d28d9;
}

.status-pill.suspended .status-dot {
  background: #7c3aed;
}

.btn-icon {
  width: 2.2rem;
  height: 2.2rem;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

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

.dropdown-item:hover {
  background: #f3f4f6;
}

.dropdown-item.text-danger:hover {
  background: #fef2f2;
}

.dropdown-icon {
  font-size: 1rem;
}

.view-icon {
  color: #2563eb;
}

.edit-icon {
  color: #d97706;
}

.inventory-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.45rem 1.15rem;
  border-top: 1px solid rgba(15, 23, 42, 0.08);
}

.inventory-summary {
  color: #6b7280;
  font-size: 0.9rem;
  font-weight: 600;
}

.infra-pagination .page-link {
  border-radius: 0.8rem;
  margin: 0 0.14rem;
  border-color: rgba(15, 23, 42, 0.08);
  color: #374151;
  font-weight: 700;
}

.infra-pagination .page-item.active .page-link {
  background: #ef476f;
  border-color: #ef476f;
  color: #fff;
}

.analytics-card {
  min-height: 100%;
}

.analytics-link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  border: 0;
  background: transparent;
  color: #ef476f;
  font-weight: 800;
  font-size: 0.9rem;
}

.analytics-link .material-icons-round {
  font-size: 1rem;
}

.section-subtext {
  color: #7c869a;
  font-size: 0.82rem;
  letter-spacing: 0;
  text-transform: none;
  font-weight: 500;
}

.regional-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.65fr) minmax(280px, 0.95fr);
  gap: 1rem;
  padding: 1.35rem 1.45rem 1.45rem;
}

.regional-bars {
  display: grid;
  gap: 1rem;
}

.regional-row-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.45rem;
  font-size: 0.95rem;
  font-weight: 700;
  color: #374151;
}

.regional-progress {
  background: rgba(15, 23, 42, 0.06);
}

.regional-progress-bar {
  background: linear-gradient(90deg, #ef476f 0%, #f66f8d 100%);
}

.regional-insights {
  display: grid;
  gap: 0.9rem;
}

.regional-summary-card {
  border-radius: 1rem;
  padding: 1.05rem 1rem;
  border: 1px solid rgba(15, 23, 42, 0.08);
  background: #fff;
}

.regional-summary-card.muted {
  background: #fbfcfe;
}

.regional-summary-label {
  color: #6b7280;
  font-size: 0.74rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 800;
  margin-bottom: 0.35rem;
}

.regional-summary-value {
  font-size: 1.2rem;
  font-weight: 800;
  color: #111827;
  margin-bottom: 0.25rem;
}

.regional-summary-meta {
  color: #6b7280;
  font-size: 0.88rem;
}

.update-feed {
  padding: 1.05rem 1.45rem 1.2rem;
  display: grid;
  gap: 0.9rem;
}

.update-item {
  display: grid;
  grid-template-columns: 12px minmax(0, 1fr) auto;
  gap: 0.75rem;
  align-items: center;
}

.update-bullet {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.update-bullet-primary {
  background: #2563eb;
}

.update-bullet-success {
  background: #059669;
}

.update-bullet-warning {
  background: #d97706;
}

.update-bullet-danger {
  background: #dc2626;
}

.update-title {
  font-weight: 800;
  color: #1f2937;
  font-size: 0.95rem;
  line-height: 1.25;
}

.update-meta {
  color: #6b7280;
  font-size: 0.82rem;
  display: flex;
  align-items: center;
  gap: 0.35rem;
  flex-wrap: wrap;
}

.update-dot {
  opacity: 0.65;
}

.update-status {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 0.33rem 0.65rem;
  border-radius: 999px;
  white-space: nowrap;
}

.update-status.on_time {
  background: rgba(88, 207, 151, 0.16);
  color: #1d7a3f;
}

.update-status.delayed {
  background: rgba(239, 68, 68, 0.12);
  color: #c62828;
}

.update-status.completed {
  background: rgba(22, 163, 74, 0.12);
  color: #15803d;
}

.update-status.ongoing,
.update-status.planning {
  background: rgba(245, 158, 11, 0.12);
  color: #b45309;
}

.update-status.suspended {
  background: rgba(139, 92, 246, 0.12);
  color: #6d28d9;
}

.audit-trail-btn {
  width: calc(100% - 2.9rem);
  margin: 0 1.45rem 1.45rem;
  border-radius: 0.95rem;
  border: 1px solid rgba(15, 23, 42, 0.08);
  background: #fff;
  min-height: 44px;
  font-weight: 700;
  color: #1f2937;
}

.bfp-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.bfp-field-half {
  grid-column: span 1;
}

.bfp-field-full {
  grid-column: 1 / -1;
}

.bfp-progress-pct {
  font-weight: 800;
}

.bfp-slider-wrap {
  display: grid;
  gap: 0.45rem;
}

.bfp-slider {
  width: 100%;
  accent-color: #c0392b;
}

.bfp-slider-labels {
  display: flex;
  justify-content: space-between;
  color: #9ca3af;
  font-size: 0.78rem;
}

.bfp-check-option {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 9px 11px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  color: #374151;
  font-size: 13px;
  cursor: pointer;
  background: #fafafa;
}

.bfp-check-option input {
  accent-color: #c0392b;
}

.bfp-filter-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.infra-modal-alert {
  margin-bottom: 1rem;
  border-radius: 0.9rem;
  font-size: 0.92rem;
  font-weight: 600;
}

@media (max-width: 1200px) {
  .regional-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 992px) {
  .infra-hero {
    flex-direction: column;
    align-items: flex-start;
  }

  .inventory-header,
  .analytics-header,
  .inventory-footer {
    flex-direction: column;
    align-items: flex-start;
  }

  .inventory-toolbar {
    justify-content: flex-start;
  }
}

@media (max-width: 576px) {
  .bfp-form-grid,
  .bfp-filter-grid {
    grid-template-columns: 1fr;
  }

  .bfp-field-half {
    grid-column: span 1;
  }

  .infra-hero-copy h4 {
    font-size: 1.6rem;
  }

  .inventory-tab {
    font-size: 0.72rem;
    padding: 0.45rem 0.7rem;
  }
}
</style>
