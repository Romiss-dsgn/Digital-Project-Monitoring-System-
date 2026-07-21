<template>
  <div class="module-page">
    <div class="container-fluid py-4">

      <!-- ── Breadcrumb ──────────────────────────────────────────── -->
      <nav class="breadcrumb-nav mb-1">
        <span class="breadcrumb-parent">Administration</span>
        <i class="material-icons-round breadcrumb-sep">chevron_right</i>
        <span class="breadcrumb-current">User Management</span>
      </nav>

      <!-- ── Page Header ────────────────────────────────────────── -->
      <div class="row mb-4 align-items-center">
        <div class="col">
          <h4 class="page-title mb-1">User Management</h4>
          <p class="page-subtitle mb-0">
            Manage personnel access, define institutional roles, and monitor system activity.
          </p>
        </div>
        <div class="col-auto d-flex gap-2">
          <button class="btn btn-outline-dark btn-header" @click="showExportModal = true">
            <i class="material-icons-round">download</i> Export List
          </button>
          <button class="btn btn-danger btn-header" @click="openAddModal">
            <i class="material-icons-round">person_add</i> Add New User
          </button>
        </div>
      </div>

      <!-- ── Stat Cards ──────────────────────────────────────────── -->
      <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-card-body">
              <div class="stat-info">
                <p class="stat-label">Total Users</p>
                <h3 class="stat-value">{{ stats.total_users ?? '—' }}</h3>
                <p class="stat-sub text-success" v-if="stats.growth_pct !== undefined">
                  <i class="material-icons-round">trending_up</i>
                  {{ stats.growth_pct }}% increase vs last month
                </p>
              </div>
              <div class="stat-icon stat-icon-blue">
                <i class="material-icons-round">group</i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-card-body">
              <div class="stat-info">
                <p class="stat-label">Active Now</p>
                <h3 class="stat-value">{{ stats.active_now ?? '—' }}</h3>
                <p class="stat-sub text-secondary">Real-time system presence</p>
              </div>
              <div class="stat-icon stat-icon-indigo">
                <i class="material-icons-round">sensors</i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-card-body">
              <div class="stat-info">
                <p class="stat-label">Pending Requests</p>
                <h3 class="stat-value text-danger">{{ stats.pending_requests ?? '—' }}</h3>
                <p class="stat-sub text-danger" v-if="stats.pending_requests > 0">
                  <i class="material-icons-round">warning</i> Requires immediate review
                </p>
                <p class="stat-sub text-secondary" v-else>No pending items</p>
              </div>
              <div class="stat-icon stat-icon-red">
                <i class="material-icons-round">pending_actions</i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-6 col-lg-3">
          <div class="stat-card">
            <div class="stat-card-body">
              <div class="stat-info">
                <p class="stat-label">Roles Defined</p>
                <h3 class="stat-value">{{ stats.roles_defined ?? '—' }}</h3>
                <p class="stat-sub text-secondary">Standardized access levels</p>
              </div>
              <div class="stat-icon stat-icon-gold">
                <i class="material-icons-round">manage_accounts</i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Filters / Search Bar ────────────────────────────────── -->
      <div class="card filter-card mb-3">
        <div class="card-body py-2 px-3">
          <div class="row align-items-center g-2">

            <div class="col-md-5 col-lg-4">
              <div class="search-wrap">
                <i class="material-icons-round search-icon">search</i>
                <input
                  type="text"
                  class="form-control search-input"
                  placeholder="Search by name, email or unit..."
                  v-model="filters.search"
                  @input="debouncedFetch"
                />
              </div>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
              <div class="select-wrap">
                <select class="form-control filter-select" v-model="filters.role" @change="fetchUsers">
                  <option value="">All Roles</option>
                  <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                </select>
                <i class="material-icons-round select-arrow">expand_more</i>
              </div>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
              <div class="select-wrap">
                <select class="form-control filter-select" v-model="filters.status" @change="fetchUsers">
                  <option value="">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="pending">Pending</option>
                </select>
                <i class="material-icons-round select-arrow">expand_more</i>
              </div>
            </div>

            <div class="col-auto ms-auto">
              <button class="btn btn-sm btn-icon-only" title="Advanced Filters" @click="showAdvancedFilterModal = true">
                <i class="material-icons-round">tune</i>
              </button>
            </div>

          </div>
        </div>
      </div>

      <!-- ── Users Table ─────────────────────────────────────────── -->
      <div class="card table-card">
        <div class="card-body p-0" style="position: relative;">

          <!-- Loading overlay -->
          <div class="table-loading" v-if="loading">
            <div class="spinner-border text-secondary" role="status"></div>
          </div>

          <!-- Error state -->
          <div v-if="fetchError && !loading" class="text-center text-danger py-4 small">
            <i class="material-icons-round d-block mb-1" style="font-size:2rem;">error_outline</i>
            {{ fetchError }}
          </div>

          <div class="table-responsive">
            <table class="table users-table align-middle mb-0">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Role</th>
                  <th>Department/Unit</th>
                  <th>Status</th>
                  <th>Last Active</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="!loading && users.length === 0">
                  <td colspan="6" class="text-center py-5 text-secondary">
                    <i class="material-icons-round d-block mb-2" style="font-size:2rem;">search_off</i>
                    No users found matching your filters.
                  </td>
                </tr>
                <tr v-for="user in users" :key="user.id">
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <div class="user-avatar user-avatar-icon"><i class="material-icons-round">person</i></div>
                      <div>
                        <div class="user-name">{{ user.name }}</div>
                        <div class="user-email">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="role-badge" :class="roleBadgeClass(user.role)">
                      {{ user.role || 'N/A' }}
                    </span>
                  </td>
                  <td>
                    <span class="dept-text">{{ user.department || user.office_unit || '—' }}</span>
                  </td>
                  <td>
                    <span class="status-dot" :class="'status-' + user.status">
                      <i class="dot"></i>{{ statusLabel(user.status) }}
                    </span>
                  </td>
                  <td>
                    <span class="text-secondary small">{{ user.last_active }}</span>
                  </td>
                  <td class="text-center">
                    <div class="action-btns">

                      <!-- ── PENDING: Accept / Reject only ── -->
                      <template v-if="user.status === 'pending'">
                        <button
                          class="action-btn action-accept"
                          title="Accept user"
                          @click="confirmAccept(user)"
                        >
                          <i class="material-icons-round">check_circle</i>
                        </button>
                        <button
                          class="action-btn action-reject"
                          title="Reject & delete"
                          @click="confirmReject(user)"
                        >
                          <i class="material-icons-round">cancel</i>
                        </button>
                      </template>

                      <!-- ── ACTIVE / INACTIVE: Edit, Toggle, Delete ── -->
                      <template v-else>
                        <button class="action-btn action-edit" title="Edit user" @click="openEditModal(user)">
                          <i class="material-icons-round">edit</i>
                        </button>
                        <button
                          class="action-btn"
                          :class="user.status === 'active' ? 'action-deactivate' : 'action-activate'"
                          :title="user.status === 'active' ? 'Deactivate' : 'Activate'"
                          @click="toggleStatus(user)"
                        >
                          <i class="material-icons-round">{{ user.status === 'active' ? 'block' : 'check_circle' }}</i>
                        </button>
                        <button class="action-btn action-delete" title="Delete user" @click="confirmDelete(user)">
                          <i class="material-icons-round">delete</i>
                        </button>
                      </template>

                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- ── Pagination ──────────────────────────────────────── -->
          <div class="table-footer d-flex align-items-center justify-content-between px-3 py-2">
            <span class="text-secondary small">
              Showing {{ meta.from ?? 0 }} to {{ meta.to ?? 0 }} of {{ meta.total ?? 0 }} entries
            </span>
            <nav>
              <ul class="pagination mb-0 gap-1">
                <li class="page-item" :class="{ disabled: meta.current_page <= 1 }">
                  <button class="page-link" @click="changePage(meta.current_page - 1)">
                    <i class="material-icons-round" style="font-size:1rem;">chevron_left</i>
                  </button>
                </li>

                <template v-for="page in visiblePages" :key="page">
                  <li v-if="page === '...'" class="page-item disabled">
                    <span class="page-link">…</span>
                  </li>
                  <li v-else class="page-item" :class="{ active: page === meta.current_page }">
                    <button class="page-link" @click="changePage(page)">{{ page }}</button>
                  </li>
                </template>

                <li class="page-item" :class="{ disabled: meta.current_page >= meta.last_page }">
                  <button class="page-link" @click="changePage(meta.current_page + 1)">
                    <i class="material-icons-round" style="font-size:1rem;">chevron_right</i>
                  </button>
                </li>
              </ul>
            </nav>
          </div>

        </div>
      </div>

    </div><!-- /container-fluid -->

    <!-- ═══════════════════════════════════════════════════════════
         MODALS
    ═══════════════════════════════════════════════════════════ -->

    <!-- Add / Edit User Modal -->
    <BfpModal
      :show="showUserModal"
      :title="editingUser ? 'Edit System User' : 'Add System User'"
      :stripe="editingUser ? 'EDIT USER RECORD' : 'USER ACCESS REGISTRATION'"
      :confirm-text="editingUser ? 'Save Changes' : 'Create User'"
      :confirm-icon="editingUser ? 'save' : 'person_add'"
      width="700px"
      :loading="saving"
      @close="closeUserModal"
      @confirm="submitUserForm"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">person</i> User Identity</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Full Name <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">badge</i>
              <input class="bfp-input" type="text" v-model="form.name" placeholder="Complete name" />
            </div>
            <span class="bfp-error" v-if="errors.name">{{ errors.name[0] }}</span>
          </div>

          <div class="bfp-field-half">
            <label class="bfp-label">Username <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">alternate_email</i>
              <input class="bfp-input" type="text" v-model="form.username" placeholder="system.username" />
            </div>
            <span class="bfp-error" v-if="errors.username">{{ errors.username[0] }}</span>
          </div>

          <div class="bfp-field-full">
            <label class="bfp-label">Email Address <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">mail</i>
              <input class="bfp-input" type="email" v-model="form.email" placeholder="user@bfp.gov.ph" />
            </div>
            <span class="bfp-error" v-if="errors.email">{{ errors.email[0] }}</span>
          </div>

          <div class="bfp-field-half">
            <label class="bfp-label">Badge Number</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">fingerprint</i>
              <input class="bfp-input" type="text" v-model="form.badge_number" placeholder="BFP-2026-0000" />
            </div>
          </div>

          <div class="bfp-field-half">
            <label class="bfp-label">Contact Number</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">call</i>
              <input class="bfp-input" type="text" :value="form.contact_number" @input="handleContactNumberInput" maxlength="10" placeholder="9XXXXXXXXX (10 digits, no leading 0)" />
            </div>
            <span class="bfp-error" v-if="errors.contact_number">{{ errors.contact_number[0] }}</span>
          </div>
        </div>
      </div>

      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">apartment</i> Assignment</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Position / Designation</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">work</i>
              <input class="bfp-input" type="text" v-model="form.position" placeholder="e.g. Planning Engineer" />
            </div>
          </div>

          <div class="bfp-field-half">
            <label class="bfp-label">Office / Unit</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">business</i>
              <input class="bfp-input" type="text" v-model="form.office_unit" placeholder="e.g. BFP Region II - Engineering Planning Unit" />
            </div>
          </div>
        </div>
      </div>

      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">admin_panel_settings</i> Access Control</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Role</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">verified_user</i>
              <select class="bfp-input bfp-select" v-model="form.role_id">
                <option value="">Select Role</option>
                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
              </select>
            </div>
          </div>

          <!-- Status: pending notice for Add, dropdown for Edit -->
          <div class="bfp-field-half" v-if="!editingUser">
            <label class="bfp-label">Status</label>
            <div class="bfp-pending-notice">
              <i class="material-icons-round">hourglass_top</i>
              Account will be set as <strong>Pending</strong> until accepted by admin
            </div>
          </div>
          <div class="bfp-field-half" v-if="editingUser">
            <label class="bfp-label">Status</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">toggle_on</i>
              <select class="bfp-input bfp-select" v-model="form.status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="pending">Pending</option>
              </select>
            </div>
          </div>

          <template v-if="!editingUser">
            <div class="bfp-field-half">
              <label class="bfp-label">Password <span class="bfp-required">*</span></label>
              <div class="bfp-input-wrap">
                <i class="material-icons-round bfp-input-icon">lock</i>
                <input class="bfp-input" :type="showPw ? 'text' : 'password'" v-model="form.password" placeholder="Min. 8 characters" />
                <i class="material-icons-round bfp-input-suffix" style="cursor:pointer" @click="showPw = !showPw">
                  {{ showPw ? 'visibility_off' : 'visibility' }}
                </i>
              </div>
              <span class="bfp-error" v-if="errors.password">{{ errors.password[0] }}</span>
            </div>

            <div class="bfp-field-half">
              <label class="bfp-label">Confirm Password <span class="bfp-required">*</span></label>
              <div class="bfp-input-wrap">
                <i class="material-icons-round bfp-input-icon">lock_outline</i>
                <input class="bfp-input" :type="showPw ? 'text' : 'password'" v-model="form.password_confirmation" placeholder="Repeat password" />
              </div>
            </div>
          </template>
        </div>
      </div>
    </BfpModal>

    <!-- Delete Confirm Modal -->
    <BfpModal
      :show="showDeleteModal"
      title="Delete User"
      stripe="CONFIRM DELETION"
      confirm-text="Delete User"
      confirm-icon="delete"
      confirm-variant="danger"
      :loading="saving"
      @close="showDeleteModal = false"
      @confirm="deleteUser"
    >
      <div class="bfp-section">
        <div class="delete-confirm-body">
          <i class="material-icons-round delete-warn-icon">warning</i>
          <p>You are about to permanently delete the account of:</p>
          <div class="delete-user-card" v-if="deletingUser">
            <div class="user-avatar user-avatar-icon"><i class="material-icons-round">person</i></div>
            <div>
              <strong>{{ deletingUser.name }}</strong>
              <div class="text-secondary small">{{ deletingUser.email }}</div>
            </div>
          </div>
          <p class="text-danger mt-2 small">This action <strong>cannot</strong> be undone.</p>
        </div>
      </div>
    </BfpModal>

    <!-- ── Accept Confirm Modal ────────────────────────────────── -->
    <BfpModal
      :show="showAcceptModal"
      title="Accept User"
      stripe="CONFIRM ACCOUNT APPROVAL"
      confirm-text="Accept User"
      confirm-icon="check_circle"
      :loading="saving"
      @close="showAcceptModal = false"
      @confirm="acceptUser"
    >
      <div class="bfp-section">
        <div class="delete-confirm-body">
          <i class="material-icons-round" style="font-size:3rem;color:#16a34a;margin-bottom:.5rem;">verified_user</i>
          <p>You are about to <strong>accept</strong> the account registration of:</p>
          <div class="delete-user-card" v-if="actionTargetUser">
            <div class="user-avatar user-avatar-icon"><i class="material-icons-round">person</i></div>
            <div>
              <strong>{{ actionTargetUser.name }}</strong>
              <div class="text-secondary small">{{ actionTargetUser.email }}</div>
            </div>
          </div>
          <p class="text-secondary mt-2 small">
            Account status will remain <strong>Inactive</strong> until the user logs in for the first time.
          </p>
        </div>
      </div>
    </BfpModal>

    <!-- ── Reject Confirm Modal ────────────────────────────────── -->
    <BfpModal
      :show="showRejectModal"
      title="Reject User"
      stripe="CONFIRM ACCOUNT REJECTION"
      confirm-text="Reject & Delete"
      confirm-icon="cancel"
      confirm-variant="danger"
      :loading="saving"
      @close="showRejectModal = false"
      @confirm="rejectUser"
    >
      <div class="bfp-section">
        <div class="delete-confirm-body">
          <i class="material-icons-round delete-warn-icon" style="color:#c0392b;">block</i>
          <p>You are about to <strong>reject and permanently delete</strong> the account of:</p>
          <div class="delete-user-card" v-if="actionTargetUser">
            <div class="user-avatar user-avatar-icon"><i class="material-icons-round">person</i></div>
            <div>
              <strong>{{ actionTargetUser.name }}</strong>
              <div class="text-secondary small">{{ actionTargetUser.email }}</div>
            </div>
          </div>
          <p class="text-danger mt-2 small">This action <strong>cannot</strong> be undone.</p>
        </div>
      </div>
    </BfpModal>

    <!-- Advanced Filters Modal -->
    <BfpModal
      :show="showAdvancedFilterModal"
      title="Advanced Filters"
      stripe="USER SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showAdvancedFilterModal = false"
      @confirm="applyAdvancedFilters"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">tune</i> Filter Criteria</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Date Joined (From)</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" v-model="advFilters.date_from" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Date Joined (To)</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" v-model="advFilters.date_to" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Sort By</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">sort</i>
              <select class="bfp-input bfp-select" v-model="advFilters.sort_by">
                <option value="created_at">Date Joined</option>
                <option value="name">Name</option>
                <option value="last_active_at">Last Active</option>
                <option value="status">Status</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Direction</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">swap_vert</i>
              <select class="bfp-input bfp-select" v-model="advFilters.sort_dir">
                <option value="desc">Newest First</option>
                <option value="asc">Oldest First</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Rows Per Page</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">table_rows</i>
              <select class="bfp-input bfp-select" v-model="advFilters.per_page">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <!-- Export Modal -->
    <BfpModal
      :show="showExportModal"
      title="Export Users"
      stripe="USER LIST EXPORT"
      confirm-text="Export"
      confirm-icon="download"
      @close="showExportModal = false"
      @confirm="handleExport"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">ios_share</i> Export Options</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Format</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">file_download</i>
              <select class="bfp-input bfp-select" v-model="exportForm.format">
                <option>Excel</option>
                <option>CSV</option>
                <option>PDF</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Rows</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">table_rows</i>
              <select class="bfp-input bfp-select" v-model="exportForm.rows">
                <option>Filtered users</option>
                <option>Current page</option>
                <option>All users</option>
              </select>
            </div>
          </div>
          <label class="bfp-check-option bfp-field-full">
            <input type="checkbox" v-model="exportForm.include_extras" checked />
            Include role, unit, joined date, and last active columns
          </label>
        </div>
      </div>
    </BfpModal>

  </div>
</template>

<script>
import BfpModal from "@/components/BfpModal.vue";
import UserService from "@/services/user.service";

const PH_COUNTRY_CODE = "63";
const CONTACT_NUMBER_MAX_LENGTH = 10;

export default {
  name: "UserManagement",
  components: { BfpModal },

  data() {
    return {
      // ── State ─────────────────────────────────────────────
      users: [],
      roles: [],
      stats: {},
      loading: false,
      saving: false,
      fetchError: null,

      // ── Pagination meta ───────────────────────────────────
      meta: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0,
      },

      // ── Filters ───────────────────────────────────────────
      filters: {
        search: "",
        role: "",
        status: "",
      },

      // ── Advanced filters ──────────────────────────────────
      advFilters: {
        date_from: "",
        date_to: "",
        sort_by: "created_at",
        sort_dir: "desc",
        per_page: 10,
      },

      // ── Modal visibility ──────────────────────────────────
      showUserModal: false,
      showDeleteModal: false,
      showAdvancedFilterModal: false,
      showExportModal: false,
      showAcceptModal: false,   // NEW
      showRejectModal: false,   // NEW

      // ── Form ──────────────────────────────────────────────
      editingUser: null,
      deletingUser: null,
      actionTargetUser: null,  // NEW — shared for accept/reject target
      showPw: false,
      errors: {},
      form: this.emptyForm(),

      // ── Export form ───────────────────────────────────────
      exportForm: {
        format: "Excel",
        rows: "Filtered users",
        include_extras: true,
      },

      // ── Debounce handle ───────────────────────────────────
      searchTimer: null,
    };
  },

  computed: {
    visiblePages() {
      const { current_page: cur, last_page: last } = this.meta;
      if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1);

      const pages = [];
      if (cur <= 4) {
        pages.push(1, 2, 3, 4, 5, "...", last);
      } else if (cur >= last - 3) {
        pages.push(1, "...", last - 4, last - 3, last - 2, last - 1, last);
      } else {
        pages.push(1, "...", cur - 1, cur, cur + 1, "...", last);
      }
      return pages;
    },
  },

  mounted() {
    this.fetchStats();
    this.fetchRoles();
    this.fetchUsers();
  },

  methods: {
    stripCountryCode(value) {
      let digits = String(value || "").replace(/\D/g, "");
      if (digits.startsWith(PH_COUNTRY_CODE)) {
        digits = digits.slice(PH_COUNTRY_CODE.length);
      } else if (digits.startsWith("0")) {
        digits = digits.slice(1);
      }
      return digits.slice(0, CONTACT_NUMBER_MAX_LENGTH);
    },
    handleContactNumberInput(event) {
      const digitsOnly = event.target.value.replace(/\D/g, "").slice(0, CONTACT_NUMBER_MAX_LENGTH);
      this.form.contact_number = digitsOnly;
      event.target.value = digitsOnly;
    },
    getErrorMessage(err, fallback) {
      return err.response?.data?.message || err.message || fallback;
    },

    // ── API Calls ──────────────────────────────────────────

    async fetchUsers(page = 1) {
      this.loading = true;
      this.fetchError = null;
      try {
        const res = await UserService.getUsers({
          page,
          per_page:  this.advFilters.per_page,
          search:    this.filters.search   || undefined,
          role:      this.filters.role     || undefined,
          status:    this.filters.status   || undefined,
          date_from: this.advFilters.date_from || undefined,
          date_to:   this.advFilters.date_to   || undefined,
          sort_by:   this.advFilters.sort_by,
          sort_dir:  this.advFilters.sort_dir,
        });
        this.users = res.data.data;
        this.meta  = res.data.meta;
      } catch (err) {
        this.fetchError = this.getErrorMessage(err, "Failed to load users. Check your API connection.");
      } finally {
        this.loading = false;
      }
    },

    async fetchStats() {
      try {
        const res = await UserService.getStats();
        this.stats = res.data;
      } catch (err) {
        this.fetchError = this.getErrorMessage(err, "Failed to load user statistics.");
      }
    },

    async fetchRoles() {
      try {
        const res = await UserService.getRoles();
        this.roles = res.data;
      } catch (err) {
        this.fetchError = this.getErrorMessage(err, "Failed to load roles.");
      }
    },

    // ── Debounced search ───────────────────────────────────

    debouncedFetch() {
      clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(() => this.fetchUsers(1), 400);
    },

    // ── Pagination ─────────────────────────────────────────

    changePage(page) {
      if (page < 1 || page > this.meta.last_page) return;
      this.fetchUsers(page);
    },

    // ── Add / Edit Modal ───────────────────────────────────

    openAddModal() {
      this.editingUser = null;
      this.form = this.emptyForm();
      this.errors = {};
      this.showPw = false;
      this.showUserModal = true;
    },

    openEditModal(user) {
      this.editingUser = user;
      this.form = {
        name:           user.name,
        username:       user.username,
        email:          user.email,
        badge_number:   user.badge_number || "",
        contact_number: this.stripCountryCode(user.contact_number || ""),
        position:       user.position || "",
        office_unit:    user.office_unit || "",
        role_id:        user.role_id || "",
        status:         user.status || "pending",
        send_invite:    false,
        password: "",
        password_confirmation: "",
      };
      this.errors = {};
      this.showPw = false;
      this.showUserModal = true;
    },

    closeUserModal() {
      this.showUserModal = false;
      this.editingUser = null;
      this.errors = {};
    },

    async submitUserForm() {
      this.saving = true;
      this.errors = {};
      const localDigits = this.stripCountryCode(this.form.contact_number);
      if (localDigits && localDigits.length !== CONTACT_NUMBER_MAX_LENGTH) {
        this.saving = false;
        this.errors = {
          contact_number: [`Contact number must be ${CONTACT_NUMBER_MAX_LENGTH} digits.`],
        };
        return;
      }
      const payload = {
        ...this.form,
        contact_number: localDigits ? `${PH_COUNTRY_CODE}${localDigits}` : "",
      };
      try {
        if (this.editingUser) {
          await UserService.updateUser(this.editingUser.id, payload);
        } else {
          await UserService.createUser(payload);
        }
        this.showUserModal = false;
        await this.fetchUsers(this.meta.current_page);
        await this.fetchStats();
      } catch (err) {
        if (err.response?.status === 422) {
          this.errors = err.response.data.errors || {};
        } else {
          this.fetchError = this.getErrorMessage(err, "Failed to save user.");
        }
      } finally {
        this.saving = false;
      }
    },

    // ── Delete ─────────────────────────────────────────────

    confirmDelete(user) {
      this.deletingUser = user;
      this.showDeleteModal = true;
    },

    async deleteUser() {
      if (!this.deletingUser) return;
      this.saving = true;
      try {
        await UserService.deleteUser(this.deletingUser.id);
        this.showDeleteModal = false;
        this.deletingUser = null;
        await this.fetchUsers(this.meta.current_page);
        await this.fetchStats();
      } catch (err) {
        this.fetchError = this.getErrorMessage(err, "Failed to delete user.");
      } finally {
        this.saving = false;
      }
    },

    // ── Accept ─────────────────────────────────────────────

    confirmAccept(user) {
      this.actionTargetUser = user;
      this.showAcceptModal = true;
    },

    async acceptUser() {
      if (!this.actionTargetUser) return;
      this.saving = true;
      try {
        await UserService.acceptUser(this.actionTargetUser.id);
        this.showAcceptModal = false;
        this.actionTargetUser = null;
        await this.fetchUsers(this.meta.current_page);
        await this.fetchStats();
      } catch (err) {
        this.fetchError = this.getErrorMessage(err, "Failed to accept user.");
      } finally {
        this.saving = false;
      }
    },

    // ── Reject ─────────────────────────────────────────────

    confirmReject(user) {
      this.actionTargetUser = user;
      this.showRejectModal = true;
    },

    async rejectUser() {
      if (!this.actionTargetUser) return;
      this.saving = true;
      try {
        await UserService.rejectUser(this.actionTargetUser.id);
        this.showRejectModal = false;
        this.actionTargetUser = null;
        await this.fetchUsers(this.meta.current_page);
        await this.fetchStats();
      } catch (err) {
        this.fetchError = this.getErrorMessage(err, "Failed to reject user.");
      } finally {
        this.saving = false;
      }
    },

    // ── Toggle Status (active ↔ inactive) ─────────────────

    async toggleStatus(user) {
      const goActive = user.status !== "active";
      try {
        await UserService.updateStatus(user.id, goActive);
        await this.fetchUsers(this.meta.current_page);
        await this.fetchStats();
      } catch (err) {
        this.fetchError = this.getErrorMessage(err, "Failed to update user status.");
      }
    },

    // ── Advanced Filters ───────────────────────────────────

    applyAdvancedFilters() {
      this.showAdvancedFilterModal = false;
      this.fetchUsers(1);
    },

    // ── Export ─────────────────────────────────────────────

    handleExport() {
      this.showExportModal = false;
      alert(`Export as ${this.exportForm.format} — wire to /api/users/export`);
    },

    // ── Helpers ────────────────────────────────────────────

    emptyForm() {
      return {
        name: "", username: "", email: "",
        badge_number: "", contact_number: "",
        position: "", office_unit: "",
        role_id: "", status: "pending",   // always pending on create
        password: "", password_confirmation: "",
        send_invite: true,
      };
    },

    statusLabel(status) {
      return {
        active:   "ACTIVE",
        inactive: "INACTIVE",
        pending:  "PENDING",
      }[status] ?? (status ? status.toUpperCase() : "");
    },

    roleBadgeClass(role) {
      const map = {
        Admin:             "role-admin",
        Finance:           "role-finance",
        Engineer:          "role-engineer",
        "Records Officer": "role-records",
        Moderator:         "role-moderator",
        User:              "role-user",
      };
      return map[role] ?? "role-default";
    },
  },
};
</script>

<style scoped>
/* ── Page chrome ────────────────────────────────────────────── */
.module-page {
  background: #f5f6fa;
  min-height: 100vh;
}

.breadcrumb-nav {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.78rem;
  color: #8b92ab;
}
.breadcrumb-parent { color: #8b92ab; }
.breadcrumb-sep { font-size: 0.9rem; color: #c2c8d4; }
.breadcrumb-current { color: #c0392b; font-weight: 600; }

.page-title {
  font-size: 1.35rem;
  font-weight: 700;
  color: #1a202c;
}
.page-subtitle {
  font-size: 0.85rem;
  color: #718096;
}

/* ── Header buttons ─────────────────────────────────────────── */
.btn-header {
  font-size: 0.82rem;
  font-weight: 600;
  padding: 0.45rem 1rem;
  border-radius: 0.6rem;
  display: flex;
  align-items: center;
  gap: 6px;
}
.btn-header .material-icons-round { font-size: 1.1rem; }
.btn-danger { background: #c0392b; border-color: #c0392b; }
.btn-danger:hover { background: #a93226; border-color: #a93226; }

/* ── Stat cards ─────────────────────────────────────────────── */
.stat-card {
  background: #fff;
  border: 1px solid #e8ecf1;
  border-radius: 1rem;
  box-shadow: 0 2px 12px rgba(0,0,0,.05);
  transition: box-shadow 0.2s;
}
.stat-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.09); }
.stat-card-body {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.1rem 1.25rem;
}
.stat-info { flex: 1; }
.stat-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: #8b92ab;
  text-transform: uppercase;
  letter-spacing: .04em;
  margin-bottom: 4px;
}
.stat-value {
  font-size: 1.9rem;
  font-weight: 800;
  color: #1a202c;
  margin-bottom: 4px;
  line-height: 1;
}
.stat-sub {
  font-size: 0.72rem;
  display: flex;
  align-items: center;
  gap: 3px;
  margin: 0;
}
.stat-sub .material-icons-round { font-size: 0.9rem; }

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-icon .material-icons-round { font-size: 1.4rem; color: #fff; }
.stat-icon-blue   { background: linear-gradient(135deg, #1a56db, #3b82f6); }
.stat-icon-indigo { background: linear-gradient(135deg, #5145cd, #818cf8); }
.stat-icon-red    { background: linear-gradient(135deg, #c0392b, #e74c3c); }
.stat-icon-gold   { background: linear-gradient(135deg, #d97706, #fbbf24); }

/* ── Filter card ────────────────────────────────────────────── */
.filter-card {
  border: 1px solid #e8ecf1;
  border-radius: 0.75rem;
  box-shadow: none;
}

.search-wrap, .select-wrap { position: relative; }
.search-icon {
  position: absolute; left: 10px; top: 50%;
  transform: translateY(-50%);
  color: #a0aec0; font-size: 1.1rem; pointer-events: none; z-index: 2;
}
.search-input, .filter-select {
  padding-left: 34px;
  font-size: 0.82rem;
  border: 1px solid #dde1ea;
  border-radius: 0.6rem;
  height: 36px;
  background: #f8fafc;
}
.filter-select { padding-left: 10px; padding-right: 28px; appearance: none; }
.select-arrow {
  position: absolute; right: 8px; top: 50%;
  transform: translateY(-50%);
  font-size: 1rem; color: #a0aec0; pointer-events: none;
}
.search-input:focus, .filter-select:focus {
  border-color: #c0392b;
  box-shadow: 0 0 0 2px rgba(192,57,43,.12);
  background: #fff;
}

.btn-icon-only {
  width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid #dde1ea; border-radius: 0.6rem;
  background: #f8fafc; color: #718096;
  padding: 0;
}
.btn-icon-only:hover { background: #edf2f7; color: #1a202c; }

/* ── Table card ─────────────────────────────────────────────── */
.table-card {
  border: 1px solid #e8ecf1;
  border-radius: 1rem;
  box-shadow: 0 2px 12px rgba(0,0,0,.05);
  overflow: hidden;
}

.table-loading {
  position: absolute; inset: 0;
  background: rgba(255,255,255,.75);
  display: flex; align-items: center; justify-content: center;
  z-index: 10;
  border-radius: 1rem;
}

.users-table thead th {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .05em;
  color: #718096;
  background: #f8fafc;
  border-bottom: 1px solid #e8ecf1;
  padding: 0.85rem 1rem;
  white-space: nowrap;
}

.users-table tbody td {
  padding: 0.8rem 1rem;
  border-bottom: 1px solid #f0f3f8;
  vertical-align: middle;
}

.users-table tbody tr:last-child td { border-bottom: none; }
.users-table tbody tr:hover { background: #fafbfd; }

.user-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e8ecf1;
  flex-shrink: 0;
}
.user-avatar-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e8ecf1;
  color: #8b92ab;
}
.user-avatar-icon .material-icons-round {
  font-size: 1.2rem;
}
.user-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: #1a202c;
  white-space: nowrap;
}
.user-email {
  font-size: 0.75rem;
  color: #718096;
  white-space: nowrap;
}

/* Role badges */
.role-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .04em;
  white-space: nowrap;
}
.role-admin    { background: #fdecea; color: #c0392b; }
.role-finance  { background: #e8f4fd; color: #1a56db; }
.role-engineer { background: #e6f9f1; color: #16a34a; }
.role-records  { background: #eef2ff; color: #6366f1; }
.role-moderator{ background: #fff7ed; color: #ea580c; }
.role-user     { background: #f0f4f8; color: #4a5568; }
.role-default  { background: #f0f4f8; color: #718096; }

/* Status indicators */
.status-dot {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.73rem;
  font-weight: 700;
  letter-spacing: .05em;
}
.dot {
  display: inline-block;
  width: 7px; height: 7px;
  border-radius: 50%;
}
.status-active   { color: #16a34a; }
.status-active   .dot { background: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.15); }
.status-inactive { color: #718096; }
.status-inactive .dot { background: #a0aec0; }
.status-pending  { color: #d97706; }
.status-pending  .dot { background: #d97706; box-shadow: 0 0 0 3px rgba(217,119,6,.15); }

.dept-text { font-size: 0.8rem; color: #4a5568; }

/* Action buttons */
.action-btns { display: flex; align-items: center; justify-content: center; gap: 5px; }
.action-btn {
  width: 30px; height: 30px;
  display: inline-flex; align-items: center; justify-content: center;
  border: 1px solid #e2e8f0;
  border-radius: 0.45rem;
  background: transparent;
  cursor: pointer;
  transition: all 0.18s;
  padding: 0;
}
.action-btn .material-icons-round { font-size: 0.95rem; }
.action-edit           { color: #1a56db; }
.action-edit:hover     { background: #ebf0fb; border-color: #1a56db; }
.action-activate       { color: #16a34a; }
.action-activate:hover { background: #e6f9f1; border-color: #16a34a; }
.action-deactivate       { color: #718096; }
.action-deactivate:hover { background: #f0f4f8; border-color: #718096; }
.action-delete       { color: #c0392b; }
.action-delete:hover { background: #fdecea; border-color: #c0392b; }

/* NEW: Accept / Reject buttons */
.action-accept       { color: #16a34a; }
.action-accept:hover { background: #e6f9f1; border-color: #16a34a; }
.action-reject       { color: #c0392b; }
.action-reject:hover { background: #fdecea; border-color: #c0392b; }

/* Table footer / pagination */
.table-footer {
  border-top: 1px solid #f0f3f8;
  background: #fafbfd;
}
.pagination { gap: 4px; }
.page-link {
  width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.8rem;
  color: #4a5568;
  background: #fff;
  padding: 0;
  cursor: pointer;
}
.page-link:hover { background: #f0f4f8; }
.page-item.active .page-link {
  background: #c0392b;
  border-color: #c0392b;
  color: #fff;
  font-weight: 700;
}
.page-item.disabled .page-link { opacity: .4; pointer-events: none; }

/* Delete / confirm body */
.delete-confirm-body {
  text-align: center;
  padding: 1rem 0 0.5rem;
}
.delete-warn-icon {
  font-size: 3rem;
  color: #e74c3c;
  margin-bottom: 0.5rem;
  display: block;
}
.delete-user-card {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  background: #fafbfd;
  border: 1px solid #e8ecf1;
  border-radius: 0.75rem;
  padding: 0.6rem 1.2rem;
  margin: 0.5rem auto;
  text-align: left;
}

/* BFP modal form helpers */
.bfp-input-suffix {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #a0aec0;
  font-size: 1rem;
}
.bfp-error {
  display: block;
  color: #c0392b;
  font-size: 0.72rem;
  margin-top: 3px;
}

/* NEW: Pending notice inside modal */
.bfp-pending-notice {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff8e1;
  border: 1px solid #fbbf24;
  border-radius: 0.6rem;
  padding: 0.55rem 0.85rem;
  font-size: 0.78rem;
  color: #92400e;
  margin-top: 4px;
  min-height: 38px;
}
.bfp-pending-notice .material-icons-round {
  font-size: 1rem;
  color: #d97706;
  flex-shrink: 0;
}
</style>
