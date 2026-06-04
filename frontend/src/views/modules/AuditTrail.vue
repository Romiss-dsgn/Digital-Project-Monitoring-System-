<template>
  <div class="module-page">
    <div class="container-fluid py-4">
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
          <button class="btn btn-danger btn-sm d-flex align-items-center gap-1" @click="openExportModal('All Formats')">
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
              <div class="stat-card-value">1,248 <span class="stat-card-badge text-success">+12%</span></div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card shadow-sm border-0 stat-card stat-red">
            <div class="card-body">
              <div class="stat-card-label">Security Alerts</div>
              <div class="stat-card-value">3 <span class="stat-card-sub text-danger">Critical</span></div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card shadow-sm border-0 stat-card stat-olive">
            <div class="card-body">
              <div class="stat-card-label">Modules Active</div>
              <div class="stat-card-value">14 <span class="stat-card-sub text-secondary">System-wide</span></div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3">
          <div class="card shadow-sm border-0 stat-card stat-dark-red">
            <div class="card-body">
              <div class="stat-card-label">Active Admin Users</div>
              <div class="stat-card-value">24 <span class="stat-card-sub text-secondary">Online Now</span></div>
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
              <span class="text-secondary small">Showing 1-15 of 12,482 entries</span>
            </div>
            <div class="card-body pt-2">
              <div class="table-responsive">
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
                        <div class="fw-semibold small">{{ log.date }}</div>
                        <div class="text-secondary" style="font-size:0.75rem;">{{ log.time }}</div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="user-avatar" :style="{ background: log.avatarColor }">{{ log.initials }}</div>
                          <span class="small">{{ log.user }}</span>
                        </div>
                      </td>
                      <td>
                        <span class="role-badge" :class="'role-' + log.role.toLowerCase()">{{ log.role }}</span>
                      </td>
                      <td><span class="small">{{ log.module }}</span></td>
                      <td>
                        <span class="action-badge" :class="'action-' + log.action.toLowerCase()">
                          <i class="bi" :class="actionIcon(log.action)"></i>
                          {{ log.action }}
                        </span>
                      </td>
                      <td><span class="fw-semibold small text-dark">{{ log.record }}</span></td>
                      <td><small class="text-secondary fst-italic">{{ log.remarks }}</small></td>
                      <td>
                        <button class="btn btn-sm btn-outline-secondary p-1 lh-1">
                          <i class="bi bi-info-circle"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Export Options -->
      <div class="row mt-4">
        <div class="col-12">
          <button class="btn btn-secondary btn-sm me-2" @click="openExportModal('Excel')">
            <i class="bi bi-file-earmark-excel me-1"></i> Export to Excel
          </button>
          <button class="btn btn-secondary btn-sm" @click="openExportModal('PDF')">
            <i class="bi bi-file-earmark-pdf me-1"></i> Export to PDF
          </button>
        </div>
      </div>
    </div>

    <BfpModal
      :show="showAdvancedFilterModal"
      title="Audit Log Advanced Filters"
      stripe="ACTIVITY LOG SEARCH"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      width="680px"
      @close="showAdvancedFilterModal = false"
      @confirm="showAdvancedFilterModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">admin_panel_settings</i> Users & Modules</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Role</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">badge</i>
              <select class="bfp-input bfp-select">
                <option>All Roles</option>
                <option>Administrator</option>
                <option>Editor</option>
                <option>Superuser</option>
                <option>Viewer</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Module</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">apps</i>
              <select class="bfp-input bfp-select">
                <option>All Modules</option>
                <option>Contract Management</option>
                <option>Reports</option>
                <option>Engineering Plans</option>
                <option>Payments</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">manage_search</i> Actions & Date Range</div>
        <div class="bfp-filter-grid">
          <label class="bfp-check-option"><input type="checkbox" /> Created</label>
          <label class="bfp-check-option"><input type="checkbox" /> Updated</label>
          <label class="bfp-check-option"><input type="checkbox" /> Deleted</label>
          <label class="bfp-check-option"><input type="checkbox" /> Accessed</label>
        </div>
        <div class="bfp-form-grid mt-3">
          <div class="bfp-field-half">
            <label class="bfp-label">Date From</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Date To</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event_available</i>
              <input class="bfp-input" type="date" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showExportModal"
      title="Export Audit Logs"
      stripe="ACCOUNTABILITY RECORD EXPORT"
      confirm-text="Export Logs"
      confirm-icon="download"
      @close="showExportModal = false"
      @confirm="showExportModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">ios_share</i> Export Package</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Format</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">file_download</i>
              <select class="bfp-input bfp-select" v-model="exportFormat">
                <option>All Formats</option>
                <option>Excel</option>
                <option>PDF</option>
                <option>CSV</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Retention Label</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">label</i>
              <select class="bfp-input bfp-select">
                <option>Official Copy</option>
                <option>Internal Review</option>
                <option>Security Incident</option>
              </select>
            </div>
          </div>
          <label class="bfp-check-option bfp-field-full"><input type="checkbox" checked /> Include user, role, module, and remarks columns</label>
        </div>
      </div>
    </BfpModal>
  </div>
</template>

<script>
import BfpModal from "@/components/BfpModal.vue";

export default {
  name: "AuditTrail",
  components: { BfpModal },
  data() {
    return {
      showAdvancedFilterModal: false,
      showExportModal: false,
      exportFormat: "All Formats",
      logs: [
        {
          id: 1,
          date: "Oct 24, 2023",
          time: "14:32:11 PM",
          user: "Supt. Juan Dela Cruz",
          initials: "JD",
          avatarColor: "#3b4fc4",
          role: "ADMINISTRATOR",
          module: "Contract Management",
          action: "CREATED",
          record: "CNTR-2023-0882",
          remarks: "New infrastructure plan for Tuguegarao Station.",
        },
        {
          id: 2,
          date: "Oct 24, 2023",
          time: "13:15:04 PM",
          user: "Maria Ressa",
          initials: "MR",
          avatarColor: "#7c5cbf",
          role: "EDITOR",
          module: "Reports",
          action: "UPDATED",
          record: "REP-ANNUAL-2023",
          remarks: "Modified financial summary in annual accomplishment.",
        },
        {
          id: 3,
          date: "Oct 24, 2023",
          time: "11:02:45 AM",
          user: "SysAdmin",
          initials: "SA",
          avatarColor: "#c0392b",
          role: "SUPERUSER",
          module: "Settings",
          action: "DELETED",
          record: "USR-9921",
          remarks: "Permanent deletion of inactive staff account.",
        },
        {
          id: 4,
          date: "Oct 24, 2023",
          time: "09:44:52 AM",
          user: "Ricardo Lindo",
          initials: "RL",
          avatarColor: "#e67e22",
          role: "VIEWER",
          module: "Engineering Plans",
          action: "ACCESSED",
          record: "PLN-TUG-S3",
          remarks: "Viewed blueprint for structural reinforcement.",
        },
        {
          id: 5,
          date: "Oct 24, 2023",
          time: "08:30:10 AM",
          user: "Anna Mendoza",
          initials: "AM",
          avatarColor: "#27ae60",
          role: "EDITOR",
          module: "Payments",
          action: "UPLOADED",
          record: "TRANS-0041",
          remarks: "Attached receipt for material procurement.",
        },
      ],
    };
  },
  methods: {
    openExportModal(format) {
      this.exportFormat = format;
      this.showExportModal = true;
    },
    actionIcon(action) {
      const map = {
        CREATED: "bi-plus-circle-fill",
        UPDATED: "bi-pencil-fill",
        DELETED: "bi-trash-fill",
        ACCESSED: "bi-eye-fill",
        UPLOADED: "bi-upload",
        LOGIN: "bi-box-arrow-in-right",
        APPROVAL: "bi-check-circle-fill",
      };
      return map[action] || "bi-circle-fill";
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

.table {
  font-size: 0.85rem;
}

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

.action-upload {
  background: #0288d1;
}

.action-approval {
  background: #4caf50;
}

.action-status_change {
  background: #fb8500;
}

.action-login {
  background: #2c5aa0;
}

.action-delete, .action-deleted {
  background: #d32f2f;
}

.action-created {
  background: #2e7d32;
}

.action-updated {
  background: #1565c0;
}

.action-accessed {
  background: #6a1b9a;
}

.action-uploaded {
  background: #0277bd;
}

.form-control {
  border-radius: 0.75rem;
  border: 1px solid #dfe4ed;
}

.btn-block {
  width: 100%;
}

/* Stat cards */
.stat-card {
  border-radius: 0.75rem;
  border-top: 3px solid transparent !important;
}
.stat-blue { border-top-color: #3b4fc4 !important; }
.stat-red { border-top-color: #c0392b !important; }
.stat-olive { border-top-color: #7d8b00 !important; }
.stat-dark-red { border-top-color: #8b1a1a !important; }

.stat-card-label {
  font-size: 0.75rem;
  color: #888;
  margin-bottom: 0.25rem;
}
.stat-card-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2633;
  display: flex;
  align-items: baseline;
  gap: 0.4rem;
}
.stat-card-badge {
  font-size: 0.75rem;
  font-weight: 600;
}
.stat-card-sub {
  font-size: 0.75rem;
  font-weight: 400;
}

/* User avatar */
.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: white;
  font-size: 0.7rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* Role badges */
.role-badge {
  padding: 0.25rem 0.6rem;
  border-radius: 0.4rem;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.role-administrator {
  background: #e3e8ff;
  color: #3b4fc4;
}
.role-editor {
  background: #ede8f9;
  color: #7c5cbf;
}
.role-superuser {
  background: #fde8e8;
  color: #c0392b;
}
.role-viewer {
  background: #e8f5e9;
  color: #2e7d32;
}
</style>
