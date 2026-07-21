<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <div v-if="apiError" class="alert alert-danger py-2 px-3 mb-3">{{ apiError }}</div>
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Audit Logs</h4>
          <p class="text-secondary small">Real-time system activity monitoring and accountability tracking.</p>
        </div>
        <div class="col-lg-4 d-flex justify-content-end gap-2">
          <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" @click="showAdvancedFilterModal = true">
            <i class="bi bi-sliders"></i> Advanced Filters
          </button>
          <button class="btn btn-danger btn-sm d-flex align-items-center gap-1" @click="openExportModal">
            <i class="bi bi-download"></i> Export Logs
          </button>
        </div>
      </div>

      <!-- Summary Stats -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="card shadow-sm border-0 stat-card stat-blue">
            <div class="card-body">
              <div class="stat-card-label">Total Events (24h)</div>
              <div class="stat-card-value">
                {{ statsLoading ? '...' : stats.total_events_24h.toLocaleString() }}
                <span class="stat-card-badge" :class="stats.percent_change >= 0 ? 'text-success' : 'text-danger'">
                  {{ stats.percent_change >= 0 ? '+' : '' }}{{ stats.percent_change }}%
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card shadow-sm border-0 stat-card stat-red">
            <div class="card-body">
              <div class="stat-card-label">Security Alerts</div>
              <div class="stat-card-value">
                {{ statsLoading ? '...' : stats.security_alerts }}
                <span class="stat-card-sub text-danger">Critical</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card shadow-sm border-0 stat-card stat-olive">
            <div class="card-body">
              <div class="stat-card-label">Modules Active</div>
              <div class="stat-card-value">
                {{ statsLoading ? '...' : stats.modules_active }}
                <span class="stat-card-sub text-secondary">System-wide</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card shadow-sm border-0 stat-card stat-dark-red">
            <div class="card-body">
              <div class="stat-card-label">Active Admin Users</div>
              <div class="stat-card-value">
                {{ statsLoading ? '...' : stats.active_admin_users }}
                <span class="stat-card-sub text-secondary">Online Now</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Activity Log Ledger -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
              <h6 class="d-flex align-items-center gap-2 mb-0">
                <i class="bi bi-bar-chart-steps text-primary"></i>
                ACTIVITY LOG LEDGER
              </h6>
              <span class="text-secondary small" v-if="!logsLoading">
                Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total.toLocaleString() }} entries
              </span>
              <span class="text-secondary small" v-else>Loading...</span>
            </div>
            <div class="card-body pt-2">

              <!-- Search Bar -->
              <div class="d-flex gap-2 mb-3">
                <div class="input-group input-group-sm" style="max-width: 320px;">
                  <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-secondary"></i>
                  </span>
                  <input
                    type="text"
                    class="form-control border-start-0"
                    placeholder="Search user, module, action..."
                    v-model="filters.search"
                    @input="debouncedFetch"
                  />
                </div>
                <button
                  v-if="hasActiveFilters"
                  class="btn btn-sm btn-outline-secondary"
                  @click="clearFilters"
                >
                  <i class="bi bi-x-circle"></i> Clear Filters
                </button>
              </div>

              <!-- Loading State -->
              <div v-if="logsLoading" class="text-center py-5">
                <div class="spinner-border text-danger" role="status"></div>
                <div class="text-secondary small mt-2">Fetching audit logs...</div>
              </div>

              <!-- Empty State -->
              <div v-else-if="logs.length === 0" class="text-center py-5">
                <i class="bi bi-journal-x text-secondary" style="font-size: 2rem;"></i>
                <div class="text-secondary mt-2">No audit logs found.</div>
              </div>

              <!-- Table -->
              <div v-else class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-primary">Date/Time</th>
                      <th class="text-primary">User</th>
                      <th class="text-primary">Role</th>
                      <th class="text-primary">Module</th>
                      <th class="text-primary">Action</th>
                      <th class="text-primary">Record Affected</th>
                      <th class="text-primary">Remarks</th>
                      <th class="text-primary">Det.</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="log in logs" :key="log.id">
                      <td>
                        <div class="fw-semibold small">{{ formatDate(log.performed_at) }}</div>
                        <div class="text-secondary" style="font-size:0.75rem;">{{ formatTime(log.performed_at) }}</div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="user-avatar" :style="{ background: avatarColor(log.user_name) }">
                            {{ initials(log.user_name) }}
                          </div>
                          <span class="small">{{ log.user_name || 'System' }}</span>
                        </div>
                      </td>
                      <td>
                        <span class="role-badge" :class="roleBadgeClass(log.role_name)">
                          {{ log.role_name || 'N/A' }}
                        </span>
                      </td>
                      <td><span class="small text-capitalize">{{ log.module }}</span></td>
                      <td>
                        <span class="action-badge" :class="'action-' + log.action.toLowerCase()">
                          <i class="bi" :class="actionIcon(log.action)"></i>
                          {{ log.action.toUpperCase() }}
                        </span>
                      </td>
                      <td><span class="fw-semibold small text-dark">{{ log.record_code || '-' }}</span></td>
                      <td><small class="text-secondary fst-italic">{{ log.remarks || '-' }}</small></td>
                      <td>
                        <button class="btn btn-sm btn-outline-secondary p-1 lh-1" @click="showDetail(log)">
                          <i class="bi bi-eye-fill"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div class="d-flex justify-content-between align-items-center mt-3" v-if="pagination.last_page > 1">
                <div class="text-secondary small">
                  Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </div>
                <div class="d-flex gap-1">
                  <button
                    class="btn btn-sm btn-outline-secondary"
                    :disabled="pagination.current_page === 1"
                    @click="changePage(pagination.current_page - 1)"
                  >
                    <i class="bi bi-chevron-left"></i>
                  </button>
                  <button
                    v-for="page in visiblePages"
                    :key="page"
                    class="btn btn-sm"
                    :class="page === pagination.current_page ? 'btn-danger' : 'btn-outline-secondary'"
                    @click="changePage(page)"
                  >
                    {{ page }}
                  </button>
                  <button
                    class="btn btn-sm btn-outline-secondary"
                    :disabled="pagination.current_page === pagination.last_page"
                    @click="changePage(pagination.current_page + 1)"
                  >
                    <i class="bi bi-chevron-right"></i>
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Advanced Filter Modal -->
    <BfpModal
      :show="showAdvancedFilterModal"
      title="Audit Log Advanced Filters"
      stripe="ACTIVITY LOG SEARCH"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      width="680px"
      @close="showAdvancedFilterModal = false"
      @confirm="applyAdvancedFilters"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">admin_panel_settings</i> Users & Modules</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Role</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">badge</i>
              <select class="bfp-input bfp-select" v-model="advFilters.role">
                <option value="">All Roles</option>
                <option v-for="r in roleOptions" :key="r.id" :value="r.name">{{ r.name }}</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Module</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">apps</i>
              <select class="bfp-input bfp-select" v-model="advFilters.module">
                <option value="">All Modules</option>
                <option v-for="m in moduleOptions" :key="m" :value="m">{{ m }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">manage_search</i> Actions & Date Range</div>
        <div class="bfp-filter-grid">
          <label class="bfp-check-option" v-for="action in actionOptions" :key="action">
            <input type="checkbox" :value="action" v-model="advFilters.actions" /> {{ action }}
          </label>
        </div>
        <div class="bfp-form-grid mt-3">
          <div class="bfp-field-half">
            <label class="bfp-label">Date From</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" v-model="advFilters.date_from" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Date To</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event_available</i>
              <input class="bfp-input" type="date" v-model="advFilters.date_to" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <!-- Export Modal -->
    <BfpModal
      :show="showExportModal"
      title="Export Audit Logs"
      stripe="ACCOUNTABILITY RECORD EXPORT"
      confirm-text="Export Logs"
      confirm-icon="download"
      @close="showExportModal = false"
      @confirm="doExport"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">ios_share</i> Export Package</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Format <span class="text-danger">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">file_download</i>
              <select class="bfp-input bfp-select" v-model="exportOptions.format">
                <option value="csv">CSV</option>
                <option value="excel">Excel</option>
                <option value="pdf">PDF</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Retention Label</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">label</i>
              <select class="bfp-input bfp-select" v-model="exportOptions.retention_label">
                <option value="Official Copy">Official Copy</option>
                <option value="Internal Review">Internal Review</option>
                <option value="Security Incident">Security Incident</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">date_range</i> Date Range (Optional)</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Date From</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" v-model="exportOptions.date_from" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Date To</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event_available</i>
              <input class="bfp-input" type="date" v-model="exportOptions.date_to" />
            </div>
          </div>
        </div>
      </div>
      <div class="text-danger small mt-2" v-if="exportError">{{ exportError }}</div>
    </BfpModal>

    <!-- Detail Modal -->
    <BfpModal
      :show="showDetailModal"
      title="Audit Log Detail"
      stripe="ACTIVITY RECORD DETAIL"
      confirm-text="Close"
      confirm-icon="close"
      @close="showDetailModal = false"
      @confirm="showDetailModal = false"
    >
      <div class="bfp-section" v-if="selectedLog">
        <div class="bfp-section-label"><i class="material-icons-round">info</i> Log Information</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Date/Time</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">schedule</i>
              <input class="bfp-input" readonly :value="formatDate(selectedLog.performed_at) + ' ' + formatTime(selectedLog.performed_at)" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">User</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">person</i>
              <input class="bfp-input" readonly :value="selectedLog.user_name || 'System'" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Role</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">badge</i>
              <input class="bfp-input" readonly :value="selectedLog.role_name || 'N/A'" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Module</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">apps</i>
              <input class="bfp-input" readonly :value="selectedLog.module" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Action</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">bolt</i>
              <input class="bfp-input" readonly :value="selectedLog.action.toUpperCase()" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Record Affected</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">folder</i>
              <input class="bfp-input" readonly :value="selectedLog.record_code || '-'" />
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Remarks</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">notes</i>
              <input class="bfp-input" readonly :value="selectedLog.remarks || '-'" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

  </div>
</template>

<script>
import BfpModal from "@/components/BfpModal.vue";
import AuditService from "@/services/audit.service";

const AVATAR_COLORS = [
  "#3b4fc4","#7c5cbf","#c0392b","#e67e22",
  "#27ae60","#0288d1","#d81b60","#00838f",
];

// The display timezone for all audit timestamps. The backend (Laravel/MySQL)
// commonly returns naive datetime strings (e.g. "2026-07-19 05:41:07") with
// no timezone marker. Browsers then parse that as *local* time, which is
// wrong if the stored value is actually UTC — this is what was causing the
// displayed time to lag behind the real time. We normalize below.
const DISPLAY_TIMEZONE = "Asia/Manila";

export default {
  name: "AuditTrail",
  components: { BfpModal },
  data() {
    return {
      // State
      logs: [],
      logsLoading: false,
      statsLoading: false,
      stats: {
        total_events_24h: 0,
        percent_change: 0,
        security_alerts: 0,
        modules_active: 0,
        active_admin_users: 0,
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
      },

      // Filters
      filters: {
        search: "",
        role: "",
        module: "",
        actions: [],
        date_from: "",
        date_to: "",
        per_page: 15,
      },

      // Advanced filter modal temp state
      advFilters: {
        role: "",
        module: "",
        actions: [],
        date_from: "",
        date_to: "",
      },

      // Options for dropdowns
      roleOptions: [],
      moduleOptions: [],
      actionOptions: ["created", "updated", "deleted", "accessed", "uploaded", "login", "approval"],

      // Modals
      showAdvancedFilterModal: false,
      showExportModal: false,
      showDetailModal: false,
      selectedLog: null,

      // Export
      exportOptions: {
        format: "csv",
        retention_label: "Official Copy",
        date_from: "",
        date_to: "",
        include_ip: false,
        include_user_agent: false,
      },
      exportError: "",
      exportLoading: false,
      apiError: "",

      // Debounce timer
      searchTimer: null,
    };
  },

  computed: {
    hasActiveFilters() {
      return (
        this.filters.search ||
        this.filters.role ||
        this.filters.module ||
        this.filters.actions.length > 0 ||
        this.filters.date_from ||
        this.filters.date_to
      );
    },
    visiblePages() {
      const current = this.pagination.current_page;
      const last = this.pagination.last_page;
      const pages = [];
      const delta = 2;
      for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
        pages.push(i);
      }
      return pages;
    },
  },

  mounted() {
    this.fetchLogs();
    this.fetchStats();
    this.fetchModules();
    this.fetchRoles();
  },

  methods: {
    setApiError(error, fallback) {
      this.apiError = error?.response?.data?.message || error.message || fallback;
    },

    async fetchLogs(page = 1) {
      this.logsLoading = true;
      try {
        const params = {
          page,
          per_page: this.filters.per_page,
        };
        if (this.filters.search)   params.search   = this.filters.search;
        if (this.filters.role)     params.role     = this.filters.role;
        if (this.filters.module)   params.module   = this.filters.module;
        if (this.filters.date_from) params.date_from = this.filters.date_from;
        if (this.filters.date_to)   params.date_to   = this.filters.date_to;
        if (this.filters.actions.length > 0) {
          params.action = this.filters.actions.join(",");
        }

        const res = await AuditService.getLogs(params);
        const data = res.data;
        this.logs = data.logs.data;
        this.pagination = {
          current_page: data.logs.current_page,
          last_page:    data.logs.last_page,
          from:         data.logs.from || 0,
          to:           data.logs.to || 0,
          total:        data.logs.total,
        };
        if (data.stats) this.stats = data.stats;
        this.apiError = "";
      } catch (e) {
        this.setApiError(e, "Failed to fetch audit logs.");
      } finally {
        this.logsLoading = false;
      }
    },

    async fetchStats() {
      this.statsLoading = true;
      try {
        const res = await AuditService.getStats();
        this.stats = res.data;
        this.apiError = "";
      } catch (e) {
        this.setApiError(e, "Failed to fetch audit stats.");
      } finally {
        this.statsLoading = false;
      }
    },

    async fetchModules() {
      try {
        const res = await AuditService.getModules();
        this.moduleOptions = res.data;
      } catch (e) {
        this.setApiError(e, "Failed to fetch audit modules.");
      }
    },

    async fetchRoles() {
      try {
        const res = await AuditService.getRoles();
        this.roleOptions = res.data;
      } catch (e) {
        this.setApiError(e, "Failed to fetch audit roles.");
      }
    },

    debouncedFetch() {
      clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(() => this.fetchLogs(1), 400);
    },

    changePage(page) {
      if (page < 1 || page > this.pagination.last_page) return;
      this.fetchLogs(page);
    },

    applyAdvancedFilters() {
      this.filters.role     = this.advFilters.role;
      this.filters.module   = this.advFilters.module;
      this.filters.actions  = [...this.advFilters.actions];
      this.filters.date_from = this.advFilters.date_from;
      this.filters.date_to   = this.advFilters.date_to;
      this.showAdvancedFilterModal = false;
      this.fetchLogs(1);
    },

    clearFilters() {
      this.filters = {
        search: "",
        role: "",
        module: "",
        actions: [],
        date_from: "",
        date_to: "",
        per_page: 15,
      };
      this.advFilters = {
        role: "",
        module: "",
        actions: [],
        date_from: "",
        date_to: "",
      };
      this.fetchLogs(1);
    },

    openExportModal() {
      this.exportError = "";
      this.showExportModal = true;
    },

    async doExport() {
      this.exportError = "";
      if (!this.exportOptions.format) {
        this.exportError = "Please select a format.";
        return;
      }
      this.exportLoading = true;
      try {
        const payload = {
          format:          this.exportOptions.format,
          retention_label: this.exportOptions.retention_label,
        };
        if (this.exportOptions.date_from) payload.date_from = this.exportOptions.date_from;
        if (this.exportOptions.date_to)   payload.date_to   = this.exportOptions.date_to;
        if (this.exportOptions.include_ip) payload.include_ip = true;
        if (this.exportOptions.include_user_agent) payload.include_user_agent = true;
        if (this.filters.module)  payload.module = this.filters.module;
        if (this.filters.actions.length > 0) payload.action = this.filters.actions.join(",");

        const res = await AuditService.exportLogs(payload);

        const extMap = { csv: 'csv', excel: 'xlsx', pdf: 'pdf' };
        const ext    = extMap[this.exportOptions.format] || this.exportOptions.format;
        const url    = window.URL.createObjectURL(new Blob([res.data]));
        const link   = document.createElement('a');
        link.href    = url;
        link.setAttribute('download', `audit_logs_${Date.now()}.${ext}`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        this.showExportModal = false;
      } catch (e) {
        this.exportError = e?.response?.data?.message || "Export failed. Please try again.";
        this.apiError = this.exportError;
      } finally {
        this.exportLoading = false;
      }
    },

    showDetail(log) {
      this.selectedLog = log;
      this.showDetailModal = true;
    },

    // Helpers

    // Normalizes a timestamp string coming from the API into something the
    // Date constructor will reliably interpret as UTC. If the string already
    // carries a timezone marker ("Z" or a +/-HH:MM offset) it's left as-is.
    // Otherwise (e.g. "2026-07-19 05:41:07" from Laravel/MySQL) we treat it
    // as UTC and append "Z" so it isn't misread as local time.
    toUtcDate(dt) {
      if (!dt) return null;
      if (dt instanceof Date) return dt;
      const hasTimezone = /Z$|[+-]\d{2}:?\d{2}$/.test(dt);
      const isoLike = dt.includes("T") ? dt : dt.replace(" ", "T");
      return new Date(hasTimezone ? isoLike : `${isoLike}Z`);
    },
    formatDate(dt) {
      const d = this.toUtcDate(dt);
      if (!d) return "-";
      return d.toLocaleDateString("en-US", {
        month: "short", day: "2-digit", year: "numeric", timeZone: DISPLAY_TIMEZONE,
      });
    },
    formatTime(dt) {
      const d = this.toUtcDate(dt);
      if (!d) return "";
      return d.toLocaleTimeString("en-US", {
        hour: "2-digit", minute: "2-digit", second: "2-digit", timeZone: DISPLAY_TIMEZONE,
      });
    },
    initials(name) {
      if (!name) return "?";
      return name.split(" ").map(n => n[0]).slice(0, 2).join("").toUpperCase();
    },
    avatarColor(name) {
      if (!name) return "#aaa";
      let hash = 0;
      for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
      return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
    },
    roleBadgeClass(role) {
      if (!role) return "role-viewer";
      const map = {
        "System Administrator": "role-administrator",
        "Administrator":        "role-administrator",
        "Editor":               "role-editor",
        "Superuser":            "role-superuser",
        "Viewer":               "role-viewer",
      };
      return map[role] || "role-viewer";
    },
    actionIcon(action) {
      const map = {
        created:  "bi-plus-circle-fill",
        updated:  "bi-pencil-fill",
        deleted:  "bi-trash-fill",
        accessed: "bi-eye-fill",
        uploaded: "bi-upload",
        login:    "bi-box-arrow-in-right",
        approval: "bi-check-circle-fill",
      };
      return map[action?.toLowerCase()] || "bi-circle-fill";
    },
  },
};
</script>

<style scoped>
.module-page {
  background: #f7fafc;
  min-height: 100vh;
}
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
.table { font-size: 0.85rem; }
.action-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: white;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}
.action-deleted  { background: #d32f2f; }
.action-created  { background: #2e7d32; }
.action-updated  { background: #1565c0; }
.action-accessed { background: #6a1b9a; }
.action-uploaded { background: #0277bd; }
.action-login    { background: #2c5aa0; }
.action-approval { background: #4caf50; }
.stat-card { border-radius: 0.75rem; border-top: 3px solid transparent !important; }
.stat-blue     { border-top-color: #3b4fc4 !important; }
.stat-red      { border-top-color: #c0392b !important; }
.stat-olive    { border-top-color: #7d8b00 !important; }
.stat-dark-red { border-top-color: #8b1a1a !important; }
.stat-card-label { font-size: 0.75rem; color: #888; margin-bottom: 0.25rem; }
.stat-card-value {
  font-size: 1.5rem; font-weight: 700; color: #1f2633;
  display: flex; align-items: baseline; gap: 0.4rem;
}
.stat-card-badge { font-size: 0.75rem; font-weight: 600; }
.stat-card-sub   { font-size: 0.75rem; font-weight: 400; }
.user-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  color: white; font-size: 0.7rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.role-badge {
  padding: 0.25rem 0.6rem; border-radius: 0.4rem;
  font-size: 0.7rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.04em;
}
.role-administrator { background: #e3e8ff; color: #3b4fc4; }
.role-editor        { background: #ede8f9; color: #7c5cbf; }
.role-superuser     { background: #fde8e8; color: #c0392b; }
.role-viewer        { background: #e8f5e9; color: #2e7d32; }
pre { white-space: pre-wrap; word-break: break-all; }
</style>