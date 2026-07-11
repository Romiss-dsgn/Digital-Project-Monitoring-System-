<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <div class="engineering-hero card shadow-sm border-0 mb-4">
        <div class="engineering-hero-copy">
          <p class="text-secondary small mb-1">
            <span>Infrastructure Plans</span>
            <i class="material-icons-round engineering-breadcrumb-icon">chevron_right</i>
            <span>Engineering Plans Management</span>
          </p>
          <h4 class="mb-1">Engineering Plans</h4>
          <p class="engineering-hero-subtitle mb-0">
            Upload, review, and track technical drawings and engineering documents linked to existing projects.
          </p>
        </div>
        <div class="engineering-hero-actions">
          <button
            v-if="canCreate"
            class="btn btn-primary btn-sm engineering-primary-btn"
            type="button"
            :disabled="isLoadingProjects || selectableProjects.length === 0"
            :title="selectableProjects.length === 0 ? 'Create a project first in Infrastructure Plans' : 'Upload a new plan'"
            @click="showUploadPlanModal = true"
          >
            <i class="material-icons-round">cloud_upload</i>
            Upload New Plan
          </button>
        </div>
      </div>

      <div v-if="canCreate && !isLoadingProjects && selectableProjects.length === 0" class="alert alert-warning py-2 px-3 mb-4">
        Create a project first in Infrastructure Plans before uploading engineering plans.
      </div>

      <div class="row g-3 mb-4">
        <div v-for="card in summaryCards" :key="card.key" class="col-12 col-sm-6 col-xl-3">
          <div class="card engineering-stat-card h-100 shadow-sm border-0">
            <div class="card-body">
              <div class="engineering-stat-body">
                <div>
                  <p class="engineering-stat-label mb-1">{{ card.label }}</p>
                  <div class="engineering-stat-value">{{ card.value }}</div>
                </div>
                <div class="engineering-stat-icon" :class="`engineering-stat-icon-${card.tone}`">
                  <i class="material-icons-round">{{ card.icon }}</i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card engineering-register-card shadow-sm border-0 mb-4">
        <div class="engineering-register-header">
          <div>
            <p class="engineering-section-kicker mb-1">Document Register</p>
            <h5 class="mb-0">Engineering Plan Register</h5>
          </div>
          <div class="engineering-register-toolbar">
            <div class="engineering-tabs">
              <button
                v-for="tab in tabs"
                :key="tab"
                type="button"
                class="engineering-tab"
                :class="{ active: activeTab === tab }"
                @click="setActiveTab(tab)"
              >
                {{ tab }}
              </button>
            </div>
            <button class="btn btn-outline-secondary btn-sm engineering-toolbar-btn" type="button" @click="showFilterModal = true">
              <i class="material-icons-round">filter_list</i>
              Filter
            </button>
          </div>
        </div>

        <div class="engineering-table-wrap">
          <table class="table align-items-center mb-0 engineering-table">
            <thead>
              <tr>
                <th>Document</th>
                <th>Project</th>
                <th>Type</th>
                <th>Uploaded</th>
                <th>Version</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoadingPlans">
                <td colspan="7" class="text-center py-4">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                  Loading engineering plans...
                </td>
              </tr>
              <tr v-else-if="engineeringPlanError">
                <td colspan="7" class="text-center py-4 text-danger">
                  {{ engineeringPlanError }}
                </td>
              </tr>
              <tr v-else-if="filteredDocuments.length === 0">
                <td colspan="7" class="text-center py-4 text-secondary">
                  No engineering plans found. Create a project first in Infrastructure Plans, then upload a plan here.
                </td>
              </tr>
              <tr v-for="doc in filteredDocuments" :key="doc.id">
                <td class="engineering-document-cell">
                  <div class="d-flex align-items-center gap-2">
                    <i class="material-icons-round engineering-file-icon" :style="{ color: doc.iconColor }">{{ doc.icon }}</i>
                    <div class="engineering-document-copy">
                      <div class="engineering-document-name">{{ doc.filename }}</div>
                      <div class="engineering-document-meta">{{ doc.file_type || "File" }}</div>
                    </div>
                  </div>
                </td>
                <td class="engineering-project-cell">
                  <div class="engineering-project-name">{{ doc.project }}</div>
                  <div class="engineering-document-meta">{{ doc.project_code || "Linked project" }}</div>
                </td>
                <td>
                  <span class="engineering-type-badge">{{ doc.type }}</span>
                </td>
                <td>
                  <div class="engineering-uploaded-by">{{ doc.uploaded_by }}</div>
                  <div class="engineering-document-meta">{{ doc.date_uploaded }}</div>
                </td>
                <td>{{ doc.version }}</td>
                <td><status-badge :status="doc.status" /></td>
                <td class="align-middle text-end engineering-actions-cell">
                  <div class="engineering-actions-menu">
                    <button
                      class="btn btn-sm btn-icon btn-light text-secondary engineering-action-btn"
                      type="button"
                      :disabled="busyPlanId === doc.id"
                      :aria-expanded="openActionMenuId === doc.id"
                      title="Engineering plan actions"
                      @click.stop="toggleActionMenu(doc.id, $event)"
                    >
                      <i class="material-icons-round">{{ busyPlanId === doc.id ? "hourglass_empty" : "more_vert" }}</i>
                    </button>
                    <div
                      v-if="openActionMenuId === doc.id"
                      class="engineering-action-menu"
                      :style="{
                        top: `${actionMenuPosition.top}px`,
                        left: `${actionMenuPosition.left}px`,
                      }"
                      role="menu"
                      @click.stop
                    >
                      <button
                        class="engineering-action-menu-item"
                        type="button"
                        role="menuitem"
                        @click="handleDownloadDocument(doc)"
                      >
                          <i class="material-icons-round align-middle me-2 dropdown-icon view-icon">download</i>
                          Download
                      </button>
                      <button
                        v-if="canApprove && doc.status !== 'approved'"
                        class="engineering-action-menu-item"
                        type="button"
                        role="menuitem"
                        @click="handleUpdateDocumentStatus(doc, 'approved')"
                      >
                          <i class="material-icons-round align-middle me-2 dropdown-icon approve-icon">check_circle</i>
                          Mark Approved
                      </button>
                      <button
                        v-if="canApprove && doc.status !== 'revision'"
                        class="engineering-action-menu-item"
                        type="button"
                        role="menuitem"
                        @click="handleUpdateDocumentStatus(doc, 'revision')"
                      >
                          <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit_document</i>
                          Require Revision
                      </button>
                      <button
                        v-if="canApprove && doc.status !== 'for_review'"
                        class="engineering-action-menu-item"
                        type="button"
                        role="menuitem"
                        @click="handleUpdateDocumentStatus(doc, 'for_review')"
                      >
                          <i class="material-icons-round align-middle me-2 dropdown-icon review-icon">pending_actions</i>
                          Send to Review
                      </button>
                      <button
                        v-if="canDelete"
                        class="engineering-action-menu-item danger"
                        type="button"
                        role="menuitem"
                        @click="handleArchiveDocument(doc)"
                      >
                          <i class="material-icons-round align-middle me-2 dropdown-icon">delete</i>
                          Archive
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="engineering-register-footer">
          <div class="engineering-summary">{{ tableRangeLabel }}</div>
          <div v-if="planMeta.last_page > 1" class="engineering-pagination" role="navigation" aria-label="Engineering plan pagination">
            <button
              class="engineering-page-btn"
              type="button"
              :disabled="planMeta.current_page <= 1"
              aria-label="Previous page"
              @click="goToPage(planMeta.current_page - 1)"
            >
              <i class="material-icons-round">chevron_left</i>
            </button>
            <button
              v-for="page in paginationPages"
              :key="page"
              class="engineering-page-btn engineering-page-number"
              :class="{ active: planMeta.current_page === page }"
              type="button"
              :disabled="planMeta.current_page === page"
              @click="goToPage(page)"
            >
              {{ page }}
            </button>
            <button
              class="engineering-page-btn"
              type="button"
              :disabled="planMeta.current_page >= planMeta.last_page"
              aria-label="Next page"
              @click="goToPage(planMeta.current_page + 1)"
            >
              <i class="material-icons-round">chevron_right</i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <BfpModal
      v-if="canCreate"
      :show="showUploadPlanModal"
      title="Upload New Engineering Plan"
      stripe="PLAN DOCUMENT UPLOAD"
      :confirm-text="isSavingPlan ? 'Uploading...' : 'Upload Plan'"
      confirm-icon="cloud_upload"
      :loading="isSavingPlan"
      @close="closeUploadModal"
      @confirm="submitEngineeringPlan"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">cloud_upload</i> Plan File</div>
        <div
          class="bfp-upload-panel"
          :class="{ 'drag-over': planUploadDragOver }"
          @dragover.prevent="planUploadDragOver = true"
          @dragleave.prevent="planUploadDragOver = false"
          @drop.prevent="handlePlanFileDrop"
        >
          <i class="material-icons-round">cloud_upload</i>
          <p class="bfp-upload-title">Drop plan files here</p>
          <p class="bfp-upload-sub">Accepted formats: PDF, DWG, PNG, DOCX up to 25MB.</p>
          <input
            type="file"
            class="form-control form-control-sm"
            multiple
            accept=".pdf,.dwg,.png,.docx,application/pdf,image/png,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            @change="handlePlanFileSelect"
          />
        </div>
        <div v-if="selectedPlanFiles.length > 0" class="selected-plan-files">
          <div v-for="(file, index) in selectedPlanFiles" :key="`${file.name}-${index}`" class="selected-plan-file">
            <i class="material-icons-round">description</i>
            <span class="selected-plan-file-name">{{ file.name }}</span>
            <span class="selected-plan-file-size">{{ formatFileSize(file.size) }}</span>
            <button type="button" class="selected-plan-file-remove" @click="removePlanFile(index)">
              <i class="material-icons-round">close</i>
            </button>
          </div>
          <p v-if="selectedPlanFiles.length > 1" class="text-secondary" style="font-size:0.75rem;margin-top:4px;">
            Only the first file ({{ selectedPlanFiles[0].name }}) will be attached to this plan record.
          </p>
        </div>
      </div>

      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">assignment</i> Document Details</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-full">
            <label class="bfp-label">Plan Title <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">title</i>
              <input class="bfp-input" type="text" v-model="engineeringPlanForm.plan_title" placeholder="e.g. Main Building Structural Plan" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Project <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">business</i>
              <select class="bfp-input bfp-select" v-model="engineeringPlanForm.project_id">
                <option value="">{{ isLoadingProjects ? "Loading projects..." : "Select a project" }}</option>
                <option v-for="p in selectableProjects" :key="p.id" :value="p.id">
                  {{ projectOptionLabel(p) }}
                </option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Plan Type <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">category</i>
              <select class="bfp-input bfp-select" v-model="engineeringPlanForm.plan_type">
                <option v-for="type in planTypes" :key="type">{{ type }}</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Version</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">history</i>
              <input class="bfp-input" type="text" v-model="engineeringPlanForm.version" placeholder="e.g. v2.1" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Review Status</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">fact_check</i>
              <select class="bfp-input bfp-select" v-model="engineeringPlanForm.status">
                <option value="for_review">For Review</option>
                <option value="approved">Approved</option>
                <option value="revision">Revision Required</option>
                <option value="uploaded">Uploaded</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Remarks</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">notes</i>
              <input class="bfp-input" type="text" v-model="engineeringPlanForm.remarks" placeholder="Optional notes" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showFilterModal"
      title="Engineering Plan Filters"
      stripe="DOCUMENT SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showFilterModal = false"
      @confirm="applyFilters"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">tune</i> Filter Criteria</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Document Type</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">description</i>
              <select class="bfp-input bfp-select" v-model="filterForm.type">
                <option value="">All Documents</option>
                <option v-for="type in planTypes" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Status</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">flag</i>
              <select class="bfp-input bfp-select" v-model="filterForm.status">
                <option value="">Any Status</option>
                <option value="approved">Approved</option>
                <option value="for_review">For Review</option>
                <option value="revision">Revision Required</option>
                <option value="uploaded">Uploaded</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Project / Filename</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">search</i>
              <input class="bfp-input" type="text" v-model="filterForm.search" placeholder="Search plan title, project, or file name" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

  </div>
</template>

<script>
import StatusBadge from "@/components/StatusBadge.vue";
import BfpModal from "@/components/BfpModal.vue";
import EngineeringPlanService from "@/services/engineering-plan.service";

const FILE_TYPE_ICON = {
  PDF: { icon: "picture_as_pdf", color: "#2563eb" },
  DWG: { icon: "description", color: "#ea580c" },
  PNG: { icon: "image", color: "#dc2626" },
  DOCX: { icon: "article", color: "#2563eb" },
};

const DEFAULT_FILE_ICON = { icon: "description", color: "#6b7280" };

function normalisePlanType(raw) {
  return (raw ?? "").trim();
}

export default {
  name: "EngineeringPlans",
  components: { StatusBadge, BfpModal },

  data() {
    return {
      activeTab: "All Documents",
      showUploadPlanModal: false,
      showFilterModal: false,
      planUploadDragOver: false,
      selectedPlanFiles: [],
      busyPlanId: null,
      openActionMenuId: null,
      actionMenuPosition: {
        top: 0,
        left: 0,
      },
      maxPlanFileSizeMb: 25,
      allowedPlanExtensions: [".pdf", ".dwg", ".png", ".docx"],
      allowedPlanTypes: [
        "application/pdf",
        "image/png",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/acad",
        "application/autocad_dwg",
        "application/dwg",
        "application/x-autocad",
        "image/vnd.dwg",
        "image/x-dwg",
      ],
      planTypes: ["Architectural", "Structural", "Electrical", "Mechanical", "Plumbing & Sanitary"],
      tabs: ["All Documents", "Architectural", "Structural", "Electrical", "Mechanical", "Plumbing & Sanitary"],
      projects: [],
      documents: [],
      isLoadingProjects: false,
      isLoadingPlans: false,
      engineeringPlanError: "",
      isSavingPlan: false,
      engineeringPlanForm: {
        project_id: "",
        plan_title: "",
        plan_type: "Architectural",
        version: "",
        status: "for_review",
        remarks: "",
      },
      filterForm: {
        search: "",
        status: "",
        type: "",
      },
      stats: {
        total_documents: 0,
        pending_review: 0,
        approved_plans: 0,
        revisions_required: 0,
      },
      planMeta: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
      },
    };
  },

  computed: {
    // ── RBAC: reads module_permissions.engineering_plans from the logged-in user ──
    // ADJUST THIS PATH to match where your Vuex/Pinia store keeps the logged-in
    // user returned by GET /api/v2/me (see MeController@readProfile).
    // Expected shape: { can_view, can_create, can_edit, can_delete, can_approve, can_export }

    planPermissions() {
      const profile = this.$store.getters["profile/getUserProfile"];
      const fullAccessRoles = ["System Administrator", "Engineer - Monitoring"];

      if (fullAccessRoles.includes(profile?.role)) {
        return { can_view: true, can_create: true, can_edit: true, can_delete: true, can_approve: true, can_export: true };
      }

      return profile?.module_permissions?.engineering_plans || {};
    },
    canCreate() {
      return !!this.planPermissions.can_create;
    },
    canApprove() {
      return !!this.planPermissions.can_approve;
    },
    canDelete() {
      return !!this.planPermissions.can_delete;
    },
    filteredDocuments() {
      if (this.activeTab === "All Documents") return this.documents;
      return this.documents.filter((item) => normalisePlanType(item.type) === normalisePlanType(this.activeTab));
    },

    selectableProjects() {
      return this.projects.filter((project) => this.projectOptionLabel(project) !== "");
    },

    summaryCards() {
      return [
        {
          key: "total",
          label: "Total Documents",
          value: this.formatNumber(this.stats.total_documents),
          icon: "description",
          tone: "purple",
        },
        {
          key: "pending",
          label: "Pending Review",
          value: this.formatNumber(this.stats.pending_review),
          icon: "pending_actions",
          tone: "yellow",
        },
        {
          key: "approved",
          label: "Approved Plans",
          value: this.formatNumber(this.stats.approved_plans),
          icon: "check_circle",
          tone: "green",
        },
        {
          key: "revision",
          label: "Revisions Required",
          value: this.formatNumber(this.stats.revisions_required),
          icon: "edit_document",
          tone: "red",
        },
      ];
    },

    tableRangeLabel() {
      if (this.isLoadingPlans) return "Loading engineering plans...";
      if (this.planMeta.total === 0) return "Showing 0 engineering plans";

      const start = ((this.planMeta.current_page - 1) * this.planMeta.per_page) + 1;
      const end = Math.min(this.planMeta.current_page * this.planMeta.per_page, this.planMeta.total);

      return `Showing ${start}-${end} of ${this.planMeta.total} engineering plans`;
    },

    paginationPages() {
      const total = this.planMeta.last_page || 1;
      const current = this.planMeta.current_page || 1;
      const start = Math.max(1, current - 2);
      const end = Math.min(total, start + 4);

      return Array.from({ length: end - start + 1 }, (_, index) => start + index);
    },
  },

  mounted() {
    this.fetchProjects();
    this.fetchEngineeringPlans();
    document.addEventListener("click", this.closeActionMenu);
    window.addEventListener("resize", this.closeActionMenu);
    window.addEventListener("scroll", this.closeActionMenu, true);
  },

  beforeUnmount() {
    document.removeEventListener("click", this.closeActionMenu);
    window.removeEventListener("resize", this.closeActionMenu);
    window.removeEventListener("scroll", this.closeActionMenu, true);
  },

  methods: {
    toggleActionMenu(planId, event) {
      if (this.busyPlanId === planId) {
        return;
      }

      if (this.openActionMenuId === planId) {
        this.closeActionMenu();
        return;
      }

      this.positionActionMenu(event.currentTarget);
      this.openActionMenuId = planId;
    },

    positionActionMenu(trigger) {
      const rect = trigger.getBoundingClientRect();
      const menuWidth = 190;
      const menuHeight = 220;
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

    handleDownloadDocument(doc) {
      this.closeActionMenu();
      this.downloadDocument(doc);
    },

    handleUpdateDocumentStatus(doc, status) {
      this.closeActionMenu();
      this.updateDocumentStatus(doc, status);
    },

    handleArchiveDocument(doc) {
      this.closeActionMenu();
      this.archiveDocument(doc);
    },

    async fetchProjects() {
      this.isLoadingProjects = true;
      try {
        const { data } = await EngineeringPlanService.getProjects();
        this.projects = data.data ?? [];
      } catch (error) {
        this.projects = [];
      } finally {
        this.isLoadingProjects = false;
      }
    },

    async fetchEngineeringPlans(page = 1) {
      this.closeActionMenu();
      this.isLoadingPlans = true;
      this.engineeringPlanError = "";

      try {
        const params = {
          page,
          per_page: this.planMeta.per_page,
          search: this.filterForm.search || undefined,
          status: this.filterForm.status || undefined,
          type: this.filterForm.type || undefined,
        };

        const { data } = await EngineeringPlanService.getAll(params);
        this.documents = (data.data ?? []).map((plan) => this.mapPlanFromApi(plan));
        this.stats = {
          total_documents: data.stats?.total_documents ?? this.documents.length,
          pending_review: data.stats?.pending_review ?? 0,
          approved_plans: data.stats?.approved_plans ?? 0,
          revisions_required: data.stats?.revisions_required ?? 0,
        };
        this.planMeta = {
          current_page: data.meta?.current_page ?? page,
          last_page: data.meta?.last_page ?? 1,
          per_page: data.meta?.per_page ?? this.planMeta.per_page,
          total: data.meta?.total ?? this.documents.length,
        };
      } catch (error) {
        this.engineeringPlanError = "Unable to load engineering plans from the database.";
      } finally {
        this.isLoadingPlans = false;
      }
    },

    async submitEngineeringPlan() {
      if (this.isSavingPlan) return;

      if (!this.canCreate) {
        alert("You do not have permission to upload engineering plans.");
        return;
      }

      if (!this.engineeringPlanForm.project_id) {
        alert("Create/select a project first in Infrastructure Plans.");
        return;
      }

      if (!this.engineeringPlanForm.plan_title.trim()) {
        alert("Plan title is required.");
        return;
      }

      if (this.selectedPlanFiles.length === 0) {
        alert("Please attach a plan file.");
        return;
      }

      this.isSavingPlan = true;

      try {
        const formData = new FormData();
        Object.entries(this.engineeringPlanForm).forEach(([key, value]) => {
          formData.append(key, value ?? "");
        });
        formData.append("file", this.selectedPlanFiles[0]);

        await EngineeringPlanService.create(formData);
        // Reload from MySQL after saving so the table always shows persisted DB data.
        await this.fetchEngineeringPlans(1);

        this.showUploadPlanModal = false;
        this.resetEngineeringPlanForm();
      } catch (error) {
        const status = error.response?.status;

        if (status === 403) {
          alert(error.response?.data?.message || "You do not have permission to perform this action.");
        } else if (status === 413) {
          alert("The selected file is too large for the server upload limit. Use a file below 25MB or rebuild the Docker backend with the updated PHP upload settings.");
        } else if (status === 422) {
          const errors = error.response.data.errors ?? {};
          alert(Object.values(errors).flat().join("\n") || "Validation failed.");
        } else {
          alert(error.response?.data?.message || "Failed to upload engineering plan. Please try again.");
        }
      } finally {
        this.isSavingPlan = false;
      }
    },

    setActiveTab(tab) {
      this.activeTab = tab;
      this.filterForm.type = tab === "All Documents" ? "" : tab;
      this.fetchEngineeringPlans(1);
    },

    applyFilters() {
      this.activeTab = this.filterForm.type || "All Documents";
      this.showFilterModal = false;
      this.fetchEngineeringPlans(1);
    },

    goToPage(page) {
      if (page < 1 || page > this.planMeta.last_page || page === this.planMeta.current_page) return;
      this.fetchEngineeringPlans(page);
    },

    mapPlanFromApi(plan) {
      const fileType = (plan.file_type || "").toUpperCase();
      const display = FILE_TYPE_ICON[fileType] ?? DEFAULT_FILE_ICON;

      return {
        id: plan.id,
        project_id: plan.project_id,
        project_code: plan.project_code,
        filename: plan.file_name || plan.plan_title || "Untitled plan",
        icon: display.icon,
        iconColor: display.color,
        type: normalisePlanType(plan.plan_type) || "Uncategorized",
        file_type: fileType || "File",
        project: plan.project_name || "Unassigned project",
        uploaded_by: plan.uploaded_by || "System",
        date_uploaded: this.formatDateTime(plan.uploaded_at || plan.created_at),
        version: plan.version || "-",
        status: plan.status || "uploaded",
        remarks: plan.remarks || "",
      };
    },

    closeUploadModal() {
      if (this.isSavingPlan) return;
      this.showUploadPlanModal = false;
      this.resetEngineeringPlanForm();
    },

    resetEngineeringPlanForm() {
      this.engineeringPlanForm = {
        project_id: "",
        plan_title: "",
        plan_type: "Architectural",
        version: "",
        status: "for_review",
        remarks: "",
      };
      this.selectedPlanFiles = [];
    },

    projectDisplayName(project) {
      return (project?.project_name || project?.name || "-").trim();
    },

    projectOptionLabel(project) {
      // Supports both admin dropdown rows and the shared paginated project API shape.
      const code = (project?.project_code || project?.code || "").trim();
      const name = (project?.project_name || project?.name || "").trim();

      return [code, name].filter(Boolean).join(" - ");
    },

    getFileExtension(file) {
      const dotIndex = file.name.lastIndexOf(".");
      return dotIndex >= 0 ? file.name.slice(dotIndex).toLowerCase() : "";
    },

    isAllowedPlanFile(file) {
      const extension = this.getFileExtension(file);
      return this.allowedPlanExtensions.includes(extension) || this.allowedPlanTypes.includes(file.type);
    },

    isAllowedPlanFileSize(file) {
      return file.size <= this.maxPlanFileSizeMb * 1024 * 1024;
    },

    splitPlanFiles(files) {
      return files.reduce((groups, file) => {
        if (!this.isAllowedPlanFile(file)) {
          groups.invalidType.push(file);
        } else if (!this.isAllowedPlanFileSize(file)) {
          groups.invalidSize.push(file);
        } else {
          groups.valid.push(file);
        }
        return groups;
      }, { valid: [], invalidType: [], invalidSize: [] });
    },

    addPlanFiles(files) {
      const { valid, invalidType, invalidSize } = this.splitPlanFiles(Array.from(files));
      const messages = [];
      if (invalidType.length > 0) {
        messages.push(`Only PDF, DWG, PNG, and DOCX files are allowed.\nRejected: ${invalidType.map((file) => file.name).join(", ")}`);
      }
      if (invalidSize.length > 0) {
        messages.push(`Maximum file size is ${this.maxPlanFileSizeMb}MB.\nToo large: ${invalidSize.map((file) => file.name).join(", ")}`);
      }
      if (messages.length > 0) alert(messages.join("\n\n"));
      if (valid.length > 0) {
        this.selectedPlanFiles = [...this.selectedPlanFiles, ...valid];
      }
    },

    handlePlanFileDrop(event) {
      this.planUploadDragOver = false;
      this.addPlanFiles(event.dataTransfer.files);
    },

    handlePlanFileSelect(event) {
      this.addPlanFiles(event.target.files);
      event.target.value = "";
    },

    removePlanFile(index) {
      this.selectedPlanFiles.splice(index, 1);
    },

    formatFileSize(size) {
      if (size >= 1024 * 1024) return `${(size / (1024 * 1024)).toFixed(1)} MB`;
      return `${(size / 1024).toFixed(1)} KB`;
    },

    formatNumber(value) {
      return Number(value || 0).toLocaleString();
    },

    formatDateTime(value) {
      if (!value) return "-";
      return new Date(value).toLocaleString([], {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
    },

    async downloadDocument(doc) {
      if (this.busyPlanId) return;

      this.busyPlanId = doc.id;
      try {
        const response = await EngineeringPlanService.download(doc.id);
        const blob = new Blob([response.data], {
          type: response.headers["content-type"] || "application/octet-stream",
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");

        link.href = url;
        link.download = doc.filename || "engineering-plan";
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      } catch (error) {
        alert(error.response?.status === 404
          ? "The stored file was not found. Seeded demo rows may not have a physical file yet."
          : "Failed to download engineering plan.");
      } finally {
        this.busyPlanId = null;
      }
    },

    async updateDocumentStatus(doc, status) {
      if (this.busyPlanId) return;

      if (!this.canApprove) {
        alert("You do not have permission to review engineering plans.");
        return;
      }

      const labels = {
        approved: "mark this plan as approved",
        revision: "require revision for this plan",
        for_review: "send this plan back to review",
      };

      if (!confirm(`Are you sure you want to ${labels[status] || "update this plan"}?`)) {
        return;
      }

      const remarks = status === "revision"
        ? prompt("Revision remarks", doc.remarks || "")
        : doc.remarks || "";

      if (status === "revision" && remarks === null) {
        return;
      }

      this.busyPlanId = doc.id;
      try {
        await EngineeringPlanService.updateStatus(doc.id, {
          status,
          remarks,
        });
        await this.fetchEngineeringPlans(this.planMeta.current_page);
      } catch (error) {
        alert(error.response?.data?.message || "Failed to update engineering plan status.");
      } finally {
        this.busyPlanId = null;
      }
    },

    async archiveDocument(doc) {
      if (this.busyPlanId) return;

      if (!this.canDelete) {
        alert("You do not have permission to archive engineering plans.");
        return;
      }

      if (!confirm(`Archive "${doc.filename}"?`)) return;

      this.busyPlanId = doc.id;
      try {
        await EngineeringPlanService.remove(doc.id);
        await this.fetchEngineeringPlans(this.planMeta.current_page);
      } catch (error) {
        alert(error.response?.data?.message || "Failed to archive engineering plan.");
      } finally {
        this.busyPlanId = null;
      }
    },
  },
};
</script>

<style scoped>
.module-page {
  background: #f7fafc;
  min-height: 100vh;
}

.engineering-hero {
  align-items: center;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  gap: 24px;
  padding: 24px;
}

.engineering-hero h4,
.engineering-register-header h5 {
  color: #243b5a;
  font-weight: 800;
}

.engineering-breadcrumb-icon {
  font-size: 0.9rem;
  margin: 0 2px;
  vertical-align: middle;
}

.engineering-hero-subtitle {
  color: #72769a;
  font-size: 0.95rem;
}

.engineering-hero-actions,
.engineering-register-toolbar {
  align-items: center;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: flex-end;
}

.engineering-primary-btn,
.engineering-toolbar-btn {
  align-items: center;
  border-radius: 8px;
  display: inline-flex;
  font-size: 0.78rem;
  font-weight: 800;
  gap: 6px;
  letter-spacing: 0;
  padding: 10px 14px;
  text-transform: uppercase;
}

.engineering-primary-btn {
  background: #c0392b;
  border-color: #c0392b;
}

.engineering-toolbar-btn i {
  font-size: 1rem;
}

.engineering-stat-card,
.engineering-register-card {
  border-radius: 12px;
}

.engineering-stat-body {
  align-items: center;
  display: flex;
  justify-content: space-between;
  min-height: 64px;
}

.engineering-stat-label,
.engineering-section-kicker {
  color: #7b82a1;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.engineering-stat-value {
  color: #0f172a;
  font-size: 1.6rem;
  font-weight: 900;
  line-height: 1;
}

.engineering-stat-icon {
  align-items: center;
  border-radius: 14px;
  display: inline-flex;
  height: 44px;
  justify-content: center;
  width: 44px;
}

.engineering-stat-icon-purple { background: #ede9fe; color: #7c3aed; }
.engineering-stat-icon-yellow { background: #fef3c7; color: #d97706; }
.engineering-stat-icon-green { background: #dcfce7; color: #16a34a; }
.engineering-stat-icon-red { background: #fee2e2; color: #dc2626; }

.engineering-register-header {
  align-items: center;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  justify-content: space-between;
  padding: 22px 24px;
}

.engineering-tabs {
  align-items: center;
  background: #f1f3f7;
  border-radius: 999px;
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  padding: 4px;
}

.engineering-tab {
  background: transparent;
  border: 0;
  border-radius: 999px;
  color: #636b83;
  font-size: 0.78rem;
  font-weight: 800;
  padding: 8px 12px;
}

.engineering-tab.active {
  background: #fff;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
  color: #e91e63;
}

.engineering-table-wrap {
  overflow-x: auto;
}

.engineering-table {
  table-layout: fixed;
  width: 100%;
}

.engineering-table th {
  border-bottom: 1px solid #e5e7eb;
  color: #8a91aa;
  font-size: 0.7rem;
  font-weight: 900;
  letter-spacing: 0.04em;
  padding: 14px 18px;
  text-transform: uppercase;
}

.engineering-table td {
  border-bottom: 1px solid #edf0f5;
  color: #38415f;
  font-size: 0.86rem;
  padding: 14px 18px;
  vertical-align: middle;
}

.engineering-table th:nth-child(1) { width: 31%; }
.engineering-table th:nth-child(2) { width: 24%; }
.engineering-table th:nth-child(3) { width: 12%; }
.engineering-table th:nth-child(4) { width: 15%; }
.engineering-table th:nth-child(5) { width: 7%; }
.engineering-table th:nth-child(6) { width: 9%; }
.engineering-table th:nth-child(7) { width: 6%; }

.engineering-actions-cell {
  overflow: visible;
}

.engineering-actions-menu {
  display: inline-flex;
  justify-content: flex-end;
}

.engineering-action-btn {
  position: relative;
  z-index: 1;
}

.engineering-action-menu {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
  display: flex;
  flex-direction: column;
  min-width: 190px;
  padding: 6px;
  position: fixed;
  z-index: 10060;
}

.engineering-action-menu-item {
  align-items: center;
  background: transparent;
  border: 0;
  border-radius: 8px;
  color: #374151;
  display: flex;
  font-size: 0.84rem;
  font-weight: 700;
  gap: 8px;
  padding: 9px 10px;
  text-align: left;
  width: 100%;
}

.engineering-action-menu-item:hover {
  background: #f3f4f6;
}

.engineering-action-menu-item.danger {
  color: #dc2626;
}

.engineering-action-menu-item.danger:hover {
  background: #fef2f2;
}

.engineering-file-icon {
  flex: 0 0 auto;
  font-size: 1.4rem;
}

.engineering-document-copy,
.engineering-project-cell {
  min-width: 0;
}

.engineering-document-name,
.engineering-project-name,
.engineering-uploaded-by {
  color: #1f2a44;
  font-weight: 700;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.engineering-document-meta {
  color: #7b82a1;
  font-size: 0.74rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.engineering-type-badge {
  background: #f1f5f9;
  border-radius: 8px;
  color: #334155;
  display: inline-flex;
  font-size: 0.72rem;
  font-weight: 800;
  padding: 6px 10px;
  text-transform: uppercase;
}

.engineering-register-footer {
  align-items: center;
  border-top: 1px solid #e5e7eb;
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: space-between;
  padding: 14px 24px;
}

.engineering-summary {
  color: #697089;
  font-size: 0.86rem;
  font-weight: 700;
}

.engineering-pagination {
  align-items: center;
  display: inline-flex;
  flex-wrap: nowrap;
  gap: 0.35rem;
}

.engineering-page-btn {
  align-items: center;
  background: #fff;
  border: 1px solid #dce3ee;
  border-radius: 999px;
  color: #475569;
  display: inline-flex;
  font-size: 0.82rem;
  font-weight: 800;
  height: 34px;
  justify-content: center;
  transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, color 0.15s ease;
  width: 34px;
}

.engineering-page-btn .material-icons-round {
  font-size: 1.05rem;
}

.engineering-page-btn:hover:not(:disabled),
.engineering-page-number.active {
  background: #ef476f;
  border-color: #ef476f;
  box-shadow: 0 8px 18px rgba(239, 71, 111, 0.22);
  color: #fff;
}

.engineering-page-btn:disabled {
  cursor: not-allowed;
  opacity: 0.48;
}

.engineering-page-number.active:disabled {
  cursor: default;
  opacity: 1;
}

.dropdown-menu {
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 0.75rem;
  font-size: 0.85rem;
  min-width: 140px;
  padding: 0.3rem;
}

.dropdown-item {
  align-items: center;
  border-radius: 0.5rem;
  display: flex;
  padding: 0.45rem 0.75rem;
}

.dropdown-item:hover { background: #f3f4f6; }
.dropdown-item.text-danger:hover { background: #fef2f2; }
.dropdown-icon { font-size: 1rem; }
.view-icon { color: #2563eb; }
.edit-icon { color: #d97706; }

.bfp-upload-panel.drag-over {
  background: rgba(192, 57, 43, 0.04);
  border-color: #c0392b;
}

.selected-plan-files {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 12px;
}

.selected-plan-file {
  align-items: center;
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  display: flex;
  gap: 10px;
  padding: 10px 12px;
}

.selected-plan-file .material-icons-round {
  color: #9ca3af;
  font-size: 20px;
}

.selected-plan-file-name {
  color: #374151;
  flex: 1;
  font-size: 13px;
  font-weight: 500;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.selected-plan-file-size {
  color: #9ca3af;
  font-size: 11px;
  white-space: nowrap;
}

.selected-plan-file-remove {
  align-items: center;
  background: transparent;
  border: 0;
  color: #9ca3af;
  cursor: pointer;
  display: inline-flex;
  justify-content: center;
  padding: 0;
}

.selected-plan-file-remove:hover,
.selected-plan-file-remove:hover .material-icons-round {
  color: #c0392b;
}

@media (max-width: 992px) {
  .engineering-hero,
  .engineering-register-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .engineering-register-toolbar,
  .engineering-hero-actions {
    justify-content: flex-start;
    width: 100%;
  }

  .engineering-table {
    min-width: 980px;
  }
}
</style>
