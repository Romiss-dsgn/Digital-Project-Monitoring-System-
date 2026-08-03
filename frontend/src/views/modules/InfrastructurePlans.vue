<template>
  <div class="admin-page infra-page">
    <div class="container-fluid py-4">
      <div class="row mb-4 align-items-center infra-page-header">
        <div class="col">
          <h4 class="mb-1">Infrastructure Plans Management</h4>
          <p class="infra-page-subtitle mb-0">
            Register project baselines, budgets, contractors, timelines, and overall status across LGU Tuao.
          </p>
        </div>
        <div class="col-auto infra-header-actions">
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
            <div class="infra-search-group">
              <div class="infra-search-wrap">
                <i class="material-icons-round infra-search-icon">search</i>
                <input
                  v-model="filters.name"
                  class="infra-search-input"
                  type="search"
                  placeholder="Search project name"
                  @keyup.enter="searchProjects"
                />
              </div>
              <button class="btn btn-outline-secondary btn-sm infra-toolbar-btn" type="button" @click="searchProjects">
                <i class="material-icons-round">search</i>
                Search
              </button>
            </div>
            <button class="btn btn-outline-secondary btn-sm infra-toolbar-btn" type="button" @click="showFilterModal = true">
              <i class="material-icons-round">filter_list</i>
              Filter
            </button>
          </div>
        </div>

        <div class="table-responsive inventory-table-shell">
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
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 inventory-actions-head">Actions</th>
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
                  <div class="project-name" :title="project.name">{{ project.name }}</div>
                </td>
                <td class="align-middle text-sm">
                  <div class="project-location" :title="project.location">{{ project.location }}</div>
                </td>
                <td class="align-middle text-sm">
                  <div class="project-contractor" :title="project.contractor">{{ project.contractor }}</div>
                </td>
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
                <td class="align-middle inventory-actions-cell">
                  <div class="inventory-actions-menu">
                    <button
                      class="btn btn-sm btn-icon btn-light text-secondary inventory-action-btn"
                      type="button"
                      :disabled="!canEdit && !canDelete"
                      :aria-expanded="openActionMenuId === project.id"
                      :title="canEdit || canDelete ? 'Project actions' : 'No actions available'"
                      @click.stop="toggleActionMenu(project.id, $event)"
                    >
                      <i class="material-icons-round">more_vert</i>
                    </button>
                    <div
                      v-if="openActionMenuId === project.id"
                      class="inventory-action-menu"
                      :style="{
                        top: `${actionMenuPosition.top}px`,
                        left: `${actionMenuPosition.left}px`,
                      }"
                      role="menu"
                      @click.stop
                    >
                      <button
                        v-if="canEdit"
                        class="inventory-action-menu-item"
                        type="button"
                        role="menuitem"
                        @click="handleEditProject(project)"
                      >
                        <i class="material-icons-round dropdown-icon edit-icon">edit</i>
                        Edit
                      </button>
                      <button
                        v-if="canDelete"
                        class="inventory-action-menu-item danger"
                        type="button"
                        role="menuitem"
                        @click="handleDeleteProject(project)"
                      >
                        <i class="material-icons-round dropdown-icon">delete</i>
                        Delete
                      </button>
                    </div>
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
          <div v-if="meta.last_page > 1" class="infra-pagination" role="navigation" aria-label="Project pagination">
            <button
              class="infra-page-btn"
              type="button"
              :disabled="meta.current_page <= 1"
              aria-label="Previous page"
              @click="goToPage(meta.current_page - 1)"
            >
              <i class="material-icons-round">chevron_left</i>
            </button>
            <button
              v-for="page in paginationPages"
              :key="page"
              class="infra-page-btn infra-page-number"
              :class="{ active: meta.current_page === page }"
              type="button"
              :disabled="meta.current_page === page"
              @click="goToPage(page)"
            >
              {{ page }}
            </button>
            <button
              class="infra-page-btn"
              type="button"
              :disabled="meta.current_page >= meta.last_page"
              aria-label="Next page"
              @click="goToPage(meta.current_page + 1)"
            >
              <i class="material-icons-round">chevron_right</i>
            </button>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-xl-8">
          <div class="card analytics-card shadow-sm border-0 h-100">
            <div class="analytics-header">
              <div>
                <p class="section-kicker mb-1">Location Distribution</p>
                <h5 class="mb-0">Location Distribution</h5>
                <p class="section-subtext mb-0">Projects mapped by municipal location</p>
              </div>
            </div>

            <div class="location-grid">
              <div v-if="locationDistribution.length" class="location-bars">
                <div v-for="item in locationDistribution" :key="item.location" class="location-row">
                  <div class="location-row-head">
                    <span>{{ item.location }}</span>
                    <strong>{{ item.percent }}%</strong>
                  </div>
                  <div class="progress progress-sm location-progress">
                    <div class="progress-bar location-progress-bar" :style="{ width: item.percent + '%' }"></div>
                  </div>
                </div>
              </div>
              <div v-else class="location-empty">
                Create a project first in Infrastructure Plans to populate location distribution.
              </div>

              <div class="location-insights">
                <div class="location-summary-card">
                  <div class="location-summary-label">Top Location</div>
                  <div class="location-summary-value">{{ topLocation.location }}</div>
                  <div class="location-summary-meta">{{ topLocation.percent }}% share of the current register</div>
                </div>
                <div class="location-summary-card muted">
                  <div class="location-summary-label">Active Coverage</div>
                  <div class="location-summary-value">{{ summaryCards[0].value }} projects</div>
                  <div class="location-summary-meta">Across {{ locationCount }} project locations in LGU Tuao</div>
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
              <div v-if="!recentUpdates.length" class="update-empty">
                Recent project updates will appear after creating or editing project records.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <TuaoModal
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

      <div class="tuao-section">
        <div class="tuao-section-label">
          <i class="material-icons-round">folder_open</i>
          Project Identification
        </div>
        <div class="tuao-form-grid">
          <div class="tuao-field tuao-field-half">
            <label class="tuao-label" for="m-code">
              Project Code <span class="tuao-required">*</span>
            </label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">tag</i>
              <input
                id="m-code"
                v-model="form.code"
                type="text"
                class="tuao-input"
                :class="{ 'tuao-input-error': errors.code }"
                placeholder="e.g. LGU-TUAO-PROJ-001"
              />
            </div>
            <span v-if="errors.code" class="tuao-error-msg">{{ errors.code }}</span>
          </div>

          <div class="tuao-field tuao-field-half">
            <label class="tuao-label" for="m-location">
              Project Location <span class="tuao-required">*</span>
            </label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">location_on</i>
              <select
                id="m-location"
                v-model="form.location"
                class="tuao-input tuao-select"
                :class="{ 'tuao-input-error': errors.location }"
              >
                <option value="">Select location</option>
                <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
              </select>
            </div>
            <span v-if="errors.location" class="tuao-error-msg">{{ errors.location }}</span>
          </div>

          <div class="tuao-field tuao-field-full">
            <label class="tuao-label" for="m-name">
              Project Name <span class="tuao-required">*</span>
            </label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">business</i>
              <input
                id="m-name"
                v-model="form.name"
                type="text"
                class="tuao-input"
                :class="{ 'tuao-input-error': errors.name }"
                placeholder="Full official project name"
              />
            </div>
            <span v-if="errors.name" class="tuao-error-msg">{{ errors.name }}</span>
          </div>
        </div>
      </div>

      <div class="tuao-section">
        <div class="tuao-section-label">
          <i class="material-icons-round">engineering</i>
          Contractor & Schedule
        </div>
        <div class="tuao-form-grid">
          <div class="tuao-field tuao-field-full">
            <label class="tuao-label" for="m-contractor">
              Contractor / Firm <span class="tuao-required">*</span>
            </label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">groups</i>
              <select
                id="m-contractor"
                v-model="form.contractor_id"
                class="tuao-input tuao-select"
                :class="{ 'tuao-input-error': errors.contractor_id }"
              >
                <option value="">Select contractor</option>
                <option
                  v-for="contractor in contractors"
                  :key="contractor.id"
                  :value="contractor.id"
                >
                  {{ contractor.company_name }}
                </option>
                <option value="__new">+ Add new contractor</option>
              </select>
            </div>
            <span v-if="errors.contractor_id" class="tuao-error-msg">{{ errors.contractor_id }}</span>
          </div>

          <div v-if="form.contractor_id === '__new'" class="tuao-field tuao-field-full">
            <label class="tuao-label" for="m-new-contractor">
              New Contractor Name <span class="tuao-required">*</span>
            </label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">business</i>
              <input
                id="m-new-contractor"
                v-model="form.new_contractor_name"
                type="text"
                class="tuao-input"
                :class="{ 'tuao-input-error': errors.new_contractor_name }"
                placeholder="Accredited contractor or company name"
              />
            </div>
            <span v-if="errors.new_contractor_name" class="tuao-error-msg">
              {{ errors.new_contractor_name }}
            </span>
          </div>

          <div class="tuao-field tuao-field-half">
            <label class="tuao-label" for="m-start">Start Date</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">event</i>
              <input id="m-start" v-model="form.startDate" type="date" class="tuao-input" />
            </div>
          </div>

          <div class="tuao-field tuao-field-half">
            <label class="tuao-label" for="m-end">Target Completion</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">event_available</i>
              <input id="m-end" v-model="form.endDate" type="date" class="tuao-input" />
            </div>
          </div>

          <div class="tuao-field tuao-field-full">
            <label class="tuao-label" for="m-budget">
              Project Budget (PHP)
            </label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">payments</i>
              <input
                id="m-budget"
                v-model="form.budget"
                type="text"
                class="tuao-input"
                placeholder="e.g. 5,000,000.00"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="tuao-section">
        <div class="tuao-section-label">
          <i class="material-icons-round">insights</i>
          Status & Progress
        </div>
        <div class="tuao-form-grid">
          <div class="tuao-field tuao-field-half">
            <label class="tuao-label" for="m-phase">
              Current Phase <span class="tuao-required">*</span>
            </label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">layers</i>
              <select
                id="m-phase"
                v-model="form.phase"
                class="tuao-input tuao-select"
                :class="{ 'tuao-input-error': errors.phase }"
              >
                <option value="">Select phase</option>
                <option v-for="ph in phases" :key="ph" :value="ph">{{ ph }}</option>
              </select>
            </div>
            <span v-if="errors.phase" class="tuao-error-msg">{{ errors.phase }}</span>
          </div>

          <div class="tuao-field tuao-field-half">
            <label class="tuao-label" for="m-status">
              Project Status <span class="tuao-required">*</span>
            </label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">flag</i>
              <select
                id="m-status"
                v-model="form.status"
                class="tuao-input tuao-select"
                :class="{ 'tuao-input-error': errors.status }"
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
            <span v-if="errors.status" class="tuao-error-msg">{{ errors.status }}</span>
          </div>

          <div class="tuao-field tuao-field-full">
            <label class="tuao-label">
              Completion Progress
              <span class="tuao-progress-pct" :style="{ color: progressColor(form.progress) }">
                {{ form.progress }}%
              </span>
            </label>
            <div class="tuao-slider-wrap">
              <input
                v-model.number="form.progress"
                type="range"
                min="0"
                max="100"
                step="1"
                class="tuao-slider"
                :style="sliderTrackStyle(form.progress)"
              />
              <div class="tuao-slider-labels">
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

      <div class="tuao-section">
        <div class="tuao-section-label">
          <i class="material-icons-round">notes</i>
          Additional Remarks
        </div>
        <div class="tuao-form-grid">
          <div class="tuao-field tuao-field-full">
            <div class="tuao-input-wrap">
              <textarea
                v-model="form.notes"
                class="tuao-input tuao-textarea"
                placeholder="Optional - observations, special conditions, site concerns, etc."
                rows="3"
              ></textarea>
            </div>
          </div>
        </div>
      </div>
    </TuaoModal>

    <TuaoModal
      :show="showFilterModal"
      title="Infrastructure Plan Filters"
      stripe="PROJECT SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showFilterModal = false"
      @confirm="applyFilters"
    >
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">tune</i> Project Criteria</div>
        <div class="tuao-form-grid">
          <div class="tuao-field-half">
            <label class="tuao-label">Project Name</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">business</i>
              <input v-model="filters.name" class="tuao-input" type="text" placeholder="Search project name" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Project Code</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">tag</i>
              <input v-model="filters.code" class="tuao-input" type="text" placeholder="Search project code" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Location</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">location_on</i>
              <select v-model="filters.location" class="tuao-input tuao-select">
                <option value="">All Locations</option>
                <option v-for="location in locations" :key="location" :value="location">{{ location }}</option>
              </select>
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Phase</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">layers</i>
              <select v-model="filters.phase" class="tuao-input tuao-select">
                <option value="">All Phases</option>
                <option v-for="phase in phases" :key="phase" :value="phase">{{ phase }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">flag</i> Status</div>
        <div class="tuao-filter-grid">
          <label class="tuao-check-option">
            <input type="radio" value="" v-model="filters.status" />
            All Status
          </label>
          <label v-for="(label, key) in statusOptions" :key="key" class="tuao-check-option">
            <input type="radio" :value="key" v-model="filters.status" />
            {{ label }}
          </label>
        </div>
      </div>
    </TuaoModal>
  </div>
</template>

<script>
import tuaoLogo from "@/assets/img/LGU TUAO logo.jpeg";
import TuaoModal from "@/components/TuaoModal.vue";
import projectService from "@/services/project.service";

export default {
  name: "InfrastructurePlans",
  components: { TuaoModal },
  data() {
    return {
      tuaoLogo,
      showModal: false,
      showFilterModal: false,
      loading: false,
      error: null,
      modalError: null,
      isSubmitting: false,
      openActionMenuId: null,
      actionMenuPosition: {
        top: 0,
        left: 0,
      },
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
        municipal_efficiency: 0,
        total_budget: 0,
        last_updated_at: null,
        location_distribution: [],
        recent_updates: [],
      },
      currentPage: 1,
      perPage: 6,
      contractors: [],
      filters: { name: "", code: "", location: "", status: "", phase: "" },
      locations: ["Municipal Hall Compound", "Public Market Area", "Rural Health Unit Compound", "Tuao Municipal Roads"],
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
        contractor_id: "",
        new_contractor_name: "",
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
    this.fetchProjectOptions();
    this.fetchProjects(1);
    document.addEventListener("click", this.closeActionMenu);
    window.addEventListener("resize", this.closeActionMenu);
    window.addEventListener("scroll", this.closeActionMenu, true);
  },

  beforeUnmount() {
    document.removeEventListener("click", this.closeActionMenu);
    window.removeEventListener("resize", this.closeActionMenu);
    window.removeEventListener("scroll", this.closeActionMenu, true);
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
      const efficiency = Number(this.stats.municipal_efficiency || 0);
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
          key: "municipal-efficiency",
          label: "Municipal Efficiency",
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

    locationDistribution() {
      const rows = Array.isArray(this.stats.location_distribution) ? this.stats.location_distribution : [];
      const total = rows.reduce((sum, row) => sum + (Number(row.total) || 0), 0) || 1;

      return rows.map((row) => ({
        location: row.location,
        total: Number(row.total) || 0,
        percent: Math.round(((Number(row.total) || 0) / total) * 100),
      }));
    },

    topLocation() {
      return this.locationDistribution[0] || { location: "N/A", percent: 0, total: 0 };
    },

    locationCount() {
      return this.locationDistribution.length || this.locations.length;
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

  methods: {
    emptyForm() {
      return {
        id: null,
        code: "",
        name: "",
        location: "",
        contractor_id: "",
        new_contractor_name: "",
        startDate: "",
        endDate: "",
        budget: "",
        phase: "",
        status: "",
        progress: 0,
        notes: "",
      };
    },

    async fetchProjectOptions() {
      try {
        const response = await projectService.getProjectOptions();
        this.contractors = response.data.contractors || [];
      } catch (e) {
        // The project list can still render if options fail; save validation will catch it.
        this.contractors = [];
      }
    },

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

    searchProjects() {
      this.showFilterModal = false;
      this.fetchProjects(1);
    },

    applyFilters() {
      this.showFilterModal = false;
      this.fetchProjects(1);
    },

    setFilterStatus(status) {
      this.filters.status = status || "";
      this.fetchProjects(1);
    },

    toggleActionMenu(projectId, event) {
      if (!this.canEdit && !this.canDelete) {
        return;
      }

      if (this.openActionMenuId === projectId) {
        this.closeActionMenu();
        return;
      }

      this.positionActionMenu(event.currentTarget);
      this.openActionMenuId = projectId;
    },

    positionActionMenu(trigger) {
      const rect = trigger.getBoundingClientRect();
      const menuWidth = 132;
      const menuHeight = 92;
      const margin = 8;
      const viewportPadding = 8;
      const left = Math.max(
        viewportPadding,
        Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - viewportPadding)
      );
      const opensUp = rect.bottom + menuHeight + margin > window.innerHeight;

      this.actionMenuPosition = {
        top: opensUp ? Math.max(viewportPadding, rect.top - menuHeight - margin) : rect.bottom + margin,
        left,
      };
    },

    closeActionMenu() {
      this.openActionMenuId = null;
    },

    handleEditProject(project) {
      this.closeActionMenu();
      this.editProject(project);
    },

    handleDeleteProject(project) {
      this.closeActionMenu();
      this.deleteProject(project);
    },

    openCreateModal() {
      this.modalError = null;
      this.errors = {};
      this.form = this.emptyForm();
      this.showModal = true;
    },

    closeModal() {
      this.showModal = false;
      this.modalError = null;
      this.errors = {};
      this.form = this.emptyForm();
    },

    validateForm() {
      const e = {};
      if (!this.form.code.trim()) e.code = "Project code is required.";
      if (!this.form.name.trim()) e.name = "Project name is required.";
      if (!this.form.location) e.location = "Please select a project location.";
      if (!this.form.contractor_id) e.contractor_id = "Please select a contractor.";
      if (this.form.contractor_id === "__new" && !this.form.new_contractor_name.trim()) {
        e.new_contractor_name = "New contractor name is required.";
      }
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
        const isNewContractor = this.form.contractor_id === "__new";
        const payload = {
          code: this.form.code.trim(),
          name: this.form.name.trim(),
          location: this.form.location,
          contractor_id: isNewContractor ? null : this.form.contractor_id,
          new_contractor_name: isNewContractor ? this.form.new_contractor_name.trim() : null,
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
        await this.fetchProjectOptions();
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

    editProject(project) {
      if (!this.canEdit) {
        return;
      }

      this.modalError = null;
      this.errors = {};
      const existingContractorId = project.contractor_id || this.contractorIdByName(project.contractor);
      const contractorName = project.contractor === "-" ? "" : project.contractor;
      this.form = {
        id: project.id,
        code: project.code,
        name: project.name,
        location: project.location,
        contractor_id: existingContractorId || (contractorName ? "__new" : ""),
        new_contractor_name: existingContractorId ? "" : contractorName,
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

    contractorIdByName(name) {
      if (!name || name === "-") {
        return "";
      }

      const contractor = this.contractors.find(
        (item) => String(item.company_name).toLowerCase() === String(name).toLowerCase()
      );

      return contractor ? contractor.id : "";
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

.infra-page {
  --infra-content-pad-x: 1.35rem;
  --infra-card-radius: 1rem;
  font-size: 0.92rem;
}

.infra-page :deep(.container-fluid) {
  padding-left: var(--infra-content-pad-x) !important;
  padding-right: var(--infra-content-pad-x) !important;
}

.infra-page-header {
  min-height: 46px;
  margin-bottom: 1.25rem !important;
}

.infra-page-header h4 {
  font-size: 1.35rem;
  font-weight: 800;
  color: #111827;
  line-height: 1.2;
}

.infra-page-subtitle {
  color: #6b7280;
  font-size: 0.95rem;
}

.infra-header-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  justify-content: flex-end;
  align-items: center;
  flex: 0 0 auto;
}

.infra-ghost-btn,
.infra-primary-btn,
.infra-toolbar-btn {
  height: 36px;
  min-height: 36px;
  border-radius: 0.85rem;
  padding-inline: 0.8rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
  line-height: 1;
  box-sizing: border-box;
}

.infra-search-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: nowrap;
}

.infra-search-wrap {
  position: relative;
  width: 210px;
  flex: 0 1 210px;
  height: 36px;
  min-height: 36px;
}

.infra-search-icon {
  position: absolute;
  top: 50%;
  left: 0.75rem;
  transform: translateY(-50%);
  font-size: 0.95rem;
  color: #9ca3af;
  pointer-events: none;
}

.infra-search-input {
  width: 100%;
  height: 36px;
  min-height: 36px;
  border: 1px solid #d8dee9;
  border-radius: 0.85rem;
  padding: 0.35rem 0.8rem 0.35rem 2.15rem;
  font-size: 0.84rem;
  color: #111827;
  background: #fff;
  outline: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
  box-sizing: border-box;
}

.infra-search-input::placeholder {
  color: #9ca3af;
}

.infra-search-input:focus {
  border-color: #1565C0;
  box-shadow: 0 0 0 0.18rem rgba(21, 101, 192, 0.12);
}

.infra-primary-btn {
  box-shadow: 0 12px 20px rgba(21, 101, 192, 0.18);
}

.stat-card {
  border-radius: var(--infra-card-radius);
  background: #fff;
}

.stat-card .card-body {
  padding: 1rem 1.15rem;
}

.stat-card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.7rem;
}

.stat-icon {
  width: 2.45rem;
  height: 2.45rem;
  border-radius: 0.8rem;
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
  font-size: 1.75rem;
  font-weight: 800;
  line-height: 1;
  color: #111827;
}

.stat-note {
  color: #6b7280;
  font-size: 0.84rem;
}

.inventory-card {
  border-radius: 8px;
  overflow: visible;
}

.analytics-card {
  border-radius: 8px;
  overflow: hidden;
}

.inventory-header,
.analytics-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.1rem 1.25rem;
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}

.inventory-header {
  align-items: center;
  flex-wrap: wrap;
  padding-block: 1rem;
}

.inventory-toolbar {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: nowrap;
  justify-content: flex-end;
  margin-left: auto;
  min-height: 36px;
}

.inventory-table-shell {
  overflow-x: auto;
  overflow-y: visible;
  scrollbar-gutter: stable;
}

.inventory-table {
  min-width: 1180px;
  table-layout: fixed;
}

.inventory-table th:nth-child(1),
.inventory-table td:nth-child(1) {
  width: 150px;
}

.inventory-table th:nth-child(2),
.inventory-table td:nth-child(2) {
  width: 265px;
}

.inventory-table th:nth-child(3),
.inventory-table td:nth-child(3) {
  width: 145px;
}

.inventory-table th:nth-child(4),
.inventory-table td:nth-child(4) {
  width: 220px;
}

.inventory-table th:nth-child(5),
.inventory-table td:nth-child(5) {
  width: 180px;
}

.inventory-table th:nth-child(6),
.inventory-table td:nth-child(6) {
  width: 130px;
}

.inventory-table th:nth-child(7),
.inventory-table td:nth-child(7) {
  width: 150px;
}

.inventory-table th:nth-child(8),
.inventory-table td:nth-child(8) {
  width: 78px;
}

.inventory-tabs {
  display: inline-flex;
  align-items: center;
  flex-wrap: nowrap;
  gap: 0.2rem;
  min-height: 36px;
  padding: 0.2rem 0.25rem;
  background: #f3f4f8;
  border-radius: 999px;
  box-sizing: border-box;
}

.inventory-tab {
  border: 0;
  background: transparent;
  color: #6b7280;
  font-size: 0.76rem;
  font-weight: 700;
  height: 32px;
  min-height: 32px;
  padding: 0 0.8rem;
  border-radius: 999px;
  line-height: 1;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  box-sizing: border-box;
}

.inventory-tab.active {
  background: #fff;
  color: #1565C0;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
}

.inventory-table thead th {
  background: #fff;
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
  padding: 0.78rem 0.9rem;
  vertical-align: middle;
}

.inventory-table tbody td {
  padding: 0.82rem 0.9rem;
  border-color: rgba(15, 23, 42, 0.07);
  vertical-align: middle;
}

.inventory-actions-head,
.inventory-actions-cell {
  text-align: center;
}

.inventory-actions-cell {
  padding-left: 0.45rem;
  padding-right: 0.45rem;
}

.project-code {
  font-weight: 800;
  color: #1565C0;
  background: rgba(21, 101, 192, 0.08);
  padding: 0.25rem 0.55rem;
  border-radius: 0.5rem;
  white-space: nowrap;
}

.project-name {
  font-weight: 700;
  color: #1f2937;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.project-location,
.project-contractor {
  color: #6b7280;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.progress-group {
  min-width: 130px;
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
  padding: 0.3rem 0.65rem;
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
  width: 2rem;
  height: 2rem;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.inventory-actions-head,
.inventory-actions-cell {
  text-align: center !important;
}

.inventory-actions-cell {
  padding-left: 0.45rem !important;
  padding-right: 0.45rem !important;
}

.inventory-actions-menu {
  display: flex;
  justify-content: center;
  position: relative;
}

.inventory-action-btn {
  margin: 0 auto;
  color: #64748b !important;
  background: #f5f7fb !important;
  border: 1px solid transparent !important;
}

.inventory-action-btn .material-icons-round {
  font-size: 1.05rem;
}

.inventory-action-btn {
  width: 36px;
  height: 36px;
  padding: 0;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.inventory-action-btn:hover:not(:disabled),
.inventory-action-btn:focus-visible {
  color: #8b0000 !important;
  background: #fff !important;
  border-color: rgba(139, 0, 0, 0.16) !important;
  box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
}

.inventory-action-menu {
  position: fixed;
  min-width: 132px;
  padding: 0.35rem;
  background: #fff;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 0.75rem;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.16);
  z-index: 1060;
}

.inventory-action-menu-item {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  width: 100%;
  border: 0;
  background: transparent;
  color: #374151;
  font-size: 0.85rem;
  font-weight: 700;
  text-align: left;
  border-radius: 0.5rem;
  padding: 0.45rem 0.75rem;
  cursor: pointer;
}

.inventory-action-menu-item:hover {
  background: #f3f4f6;
}

.inventory-action-menu-item.danger {
  color: #c62828;
}

.inventory-action-menu-item.danger:hover {
  background: #fef2f2;
}

.dropdown-icon {
  font-size: 1rem;
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

.infra-pagination {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  flex-wrap: nowrap;
}

.infra-page-btn {
  width: 34px;
  height: 34px;
  border: 1px solid rgba(15, 23, 42, 0.1);
  border-radius: 999px;
  background: #fff;
  color: #475569;
  font-size: 0.82rem;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
}

.infra-page-btn .material-icons-round {
  font-size: 1.05rem;
}

.infra-page-btn:hover:not(:disabled),
.infra-page-number.active {
  background: #1565C0;
  border-color: #1565C0;
  color: #fff;
  box-shadow: 0 8px 18px rgba(21, 101, 192, 0.22);
}

.infra-page-btn:disabled {
  opacity: 0.48;
  cursor: not-allowed;
}

.infra-page-number.active:disabled {
  opacity: 1;
  cursor: default;
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
  color: #1565C0;
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

.location-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.65fr) minmax(280px, 0.95fr);
  gap: 1rem;
  padding: 1.35rem 1.45rem 1.45rem;
}

.location-bars {
  display: grid;
  gap: 1rem;
}

.location-empty,
.update-empty {
  border: 1px dashed rgba(15, 23, 42, 0.16);
  border-radius: 8px;
  color: #7c869a;
  font-size: 0.9rem;
  font-weight: 600;
  padding: 1rem;
  background: #fbfcfe;
}

.location-row-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.45rem;
  font-size: 0.95rem;
  font-weight: 700;
  color: #374151;
}

.location-progress {
  background: rgba(15, 23, 42, 0.06);
}

.location-progress-bar {
  background: linear-gradient(90deg, #1565C0 0%, #42a5f5 100%);
}

.location-insights {
  display: grid;
  gap: 0.9rem;
}

.location-summary-card {
  border-radius: 1rem;
  padding: 1.05rem 1rem;
  border: 1px solid rgba(15, 23, 42, 0.08);
  background: #fff;
}

.location-summary-card.muted {
  background: #fbfcfe;
}

.location-summary-label {
  color: #6b7280;
  font-size: 0.74rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 800;
  margin-bottom: 0.35rem;
}

.location-summary-value {
  font-size: 1.2rem;
  font-weight: 800;
  color: #111827;
  margin-bottom: 0.25rem;
}

.location-summary-meta {
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

.tuao-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.tuao-field-half {
  grid-column: span 1;
}

.tuao-field-full {
  grid-column: 1 / -1;
}

.tuao-progress-pct {
  font-weight: 800;
}

.tuao-slider-wrap {
  display: grid;
  gap: 0.45rem;
}

.tuao-slider {
  width: 100%;
  accent-color: #1565C0;
}

.tuao-slider-labels {
  display: flex;
  justify-content: space-between;
  color: #9ca3af;
  font-size: 0.78rem;
}

.tuao-check-option {
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

.tuao-check-option input {
  accent-color: #1565C0;
}

.tuao-filter-grid {
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
  .location-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 992px) {
  .infra-page-header {
    row-gap: 1rem;
  }

  .infra-header-actions {
    justify-content: flex-start;
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

  .inventory-footer {
    align-items: stretch;
  }

  .infra-pagination {
    align-self: flex-end;
  }
}

@media (max-width: 576px) {
  .tuao-form-grid,
  .tuao-filter-grid {
    grid-template-columns: 1fr;
  }

  .tuao-field-half {
    grid-column: span 1;
  }

  .infra-page-header h4 {
    font-size: 1.35rem;
  }

  .inventory-tab {
    font-size: 0.72rem;
    padding: 0.45rem 0.7rem;
  }

  .infra-header-actions,
  .infra-ghost-btn,
  .infra-primary-btn {
    width: 100%;
  }

  .infra-ghost-btn,
  .infra-primary-btn {
    justify-content: center;
  }
}
</style>
