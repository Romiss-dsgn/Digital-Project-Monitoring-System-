<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">User Management</h4>
          <p class="text-secondary small">Manage all users in one place. Control access, assign roles, and monitor activity across your platform.</p>
        </div>
      </div>

      <!-- Controls -->
      <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-2">
          <div class="input-group">
            <i class="material-icons-round input-icon">search</i>
            <input type="text" class="form-control" placeholder="Search" v-model="searchQuery" />
          </div>
        </div>
        <div class="col-md-3 col-lg-2 mb-2">
          <select class="form-control" v-model="selectedRole">
            <option value="">Role</option>
            <option value="Admin">Admin</option>
            <option value="User">User</option>
            <option value="Guest">Guest</option>
            <option value="Moderator">Moderator</option>
          </select>
        </div>
        <div class="col-md-3 col-lg-2 mb-2">
          <select class="form-control" v-model="selectedStatus">
            <option value="">Status</option>
            <option value="Dashboard">Dashboard</option>
            <option value="Infrastructure Plans">Infrastructure Plans</option>
            <option value="Contract Management">Contract Management</option>
            <option value="Cashflows Management">Cashflows Management</option>
            <option value="Engineering Plans">Engineering Plans</option>
            <option value="Variation Orders">Variation Orders</option>
            <option value="Project Accomplishments">Project Accomplishments</option>
            <option value="Reports">Reports</option>
            <option value="Audit Logs">Audit Logs</option>
            <option value="Settings">Settings</option>
          </select>
        </div>
        <div class="col-md-6 col-lg-2 mb-2">
          <input type="date" class="form-control" />
        </div>
        <div class="col-12 col-lg-auto mb-2 text-end">
          <button class="btn btn-outline-secondary btn-sm" @click="showAdvancedFilterModal = true">
            <i class="material-icons-round me-2">tune</i>Advanced Filters
          </button>
          <button class="btn btn-primary btn-sm" @click="showExportModal = true">
            <i class="material-icons-round me-2">download</i>Export
          </button>
          <button class="btn btn-success btn-sm ms-2" @click="showAddUserModal = true">
            <i class="material-icons-round me-2">add</i>Add User
          </button>
        </div>
      </div>

      <!-- Users Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Users List</h6>
              <p class="text-secondary text-sm">Rows per page: {{ rowsPerPage }} of {{ filteredUsers.length }} rows</p>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Full Name</th>
                      <th>Email</th>
                      <th>Username</th>
                      <th>Status</th>
                      <th>Role</th>
                      <th>Joined Date</th>
                      <th>Last Active</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="user in paginatedUsers" :key="user.id">
                      <td>
                        <div class="d-flex align-items-center">
                          <img :src="user.avatar" :alt="user.name" class="user-avatar me-2" />
                          <span>{{ user.name }}</span>
                        </div>
                      </td>
                      <td><small>{{ user.email }}</small></td>
                      <td><small>{{ user.username }}</small></td>
                      <td>
                        <status-badge :status="user.status" />
                      </td>
                      <td><span class="role-badge" :class="'role-' + user.role.toLowerCase()">{{ user.role }}</span></td>
                      <td><small>{{ user.joinedDate }}</small></td>
                      <td><small class="text-secondary">{{ user.lastActive }}</small></td>
                      <td>
                        <button class="btn btn-icon btn-sm btn-edit" title="Edit">
                          <i class="material-icons-round">edit</i>
                        </button>
                        <button class="btn btn-icon btn-sm btn-delete" title="Delete">
                          <i class="material-icons-round">delete</i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <!-- Pagination -->
            <div class="card-footer d-flex align-items-center justify-content-between">
              <p class="mb-0 text-secondary text-sm">Rows per page: {{ rowsPerPage }} of {{ filteredUsers.length }} rows</p>
              <nav aria-label="Page navigation example">
                <ul class="pagination mb-0">
                  <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <a class="page-link" href="#" @click.prevent="previousPage">Previous</a>
                  </li>
                  <li class="page-item active">
                    <a class="page-link" href="#">{{ currentPage }}</a>
                  </li>
                  <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <a class="page-link" href="#" @click.prevent="nextPage">Next</a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <BfpModal
      :show="showAdvancedFilterModal"
      title="User Advanced Filters"
      stripe="ACCESS SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showAdvancedFilterModal = false"
      @confirm="showAdvancedFilterModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">tune</i> Account Criteria</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Role</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">verified_user</i>
              <select class="bfp-input bfp-select" v-model="selectedRole">
                <option value="">All Roles</option>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
                <option value="Guest">Guest</option>
                <option value="Moderator">Moderator</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Default Module</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">apps</i>
              <select class="bfp-input bfp-select" v-model="selectedStatus">
                <option value="">All Modules</option>
                <option value="Dashboard">Dashboard</option>
                <option value="Infrastructure Plans">Infrastructure Plans</option>
                <option value="Contract Management">Contract Management</option>
                <option value="Cashflows Management">Cashflows Management</option>
                <option value="Engineering Plans">Engineering Plans</option>
                <option value="Variation Orders">Variation Orders</option>
                <option value="Project Accomplishments">Project Accomplishments</option>
                <option value="Reports">Reports</option>
                <option value="Audit Logs">Audit Logs</option>
                <option value="Settings">Settings</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Search User</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">search</i>
              <input class="bfp-input" type="text" v-model="searchQuery" placeholder="Name, email, or username" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showAddUserModal"
      title="Add System User"
      stripe="USER ACCESS REGISTRATION"
      confirm-text="Create User"
      confirm-icon="person_add"
      width="680px"
      @close="showAddUserModal = false"
      @confirm="showAddUserModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">person</i> User Identity</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Full Name <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">badge</i>
              <input class="bfp-input" type="text" placeholder="Complete name" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Username <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">alternate_email</i>
              <input class="bfp-input" type="text" placeholder="system username" />
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Email Address <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">mail</i>
              <input class="bfp-input" type="email" placeholder="user@bfp.gov.ph" />
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
              <select class="bfp-input bfp-select">
                <option>Admin</option>
                <option>User</option>
                <option>Moderator</option>
                <option>Guest</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Default Module</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">apps</i>
              <select class="bfp-input bfp-select">
                <option>Dashboard</option>
                <option>Infrastructure Plans</option>
                <option>Contract Management</option>
                <option>Reports</option>
                <option>Audit Logs</option>
              </select>
            </div>
          </div>
          <label class="bfp-check-option bfp-field-full"><input type="checkbox" checked /> Send account invitation by email</label>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showExportModal"
      title="Export Users"
      stripe="USER ACCESS EXPORT"
      confirm-text="Export Users"
      confirm-icon="download"
      @close="showExportModal = false"
      @confirm="showExportModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">ios_share</i> Export Scope</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Format</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">file_download</i>
              <select class="bfp-input bfp-select">
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
              <select class="bfp-input bfp-select">
                <option>Filtered users</option>
                <option>Current page</option>
                <option>All users</option>
              </select>
            </div>
          </div>
          <label class="bfp-check-option bfp-field-full"><input type="checkbox" checked /> Include roles, modules, joined date, and last active columns</label>
        </div>
      </div>
    </BfpModal>
  </div>
</template>

<script>
import StatusBadge from "@/components/StatusBadge.vue";
import BfpModal from "@/components/BfpModal.vue";

export default {
  name: "UserManagement",
  components: {
    StatusBadge,
    BfpModal
  },
  data() {
    return {
      showAddUserModal: false,
      showExportModal: false,
      showAdvancedFilterModal: false,
      searchQuery: "",
      selectedRole: "",
      selectedStatus: "",
      currentPage: 1,
      rowsPerPage: 10,
      users: [
        {
          id: 1,
          name: "John Smith",
          email: "john.smith@gmail.com",
          username: "jonny77",
          status: "Dashboard",
          role: "Admin",
          joinedDate: "March 12, 2023",
          lastActive: "1 minute ago",
          avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop"
        },
        {
          id: 2,
          name: "Olivia Bennett",
          email: "ollyben@gmail.com",
          username: "olly659",
          status: "Infrastructure Plans",
          role: "User",
          joinedDate: "June 27, 2022",
          lastActive: "1 month ago",
          avatar: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=40&h=40&fit=crop"
        },
        {
          id: 3,
          name: "Daniel Warren",
          email: "dwarren3@gmail.com",
          username: "dwarren3",
          status: "Contract Management",
          role: "User",
          joinedDate: "January 8, 2024",
          lastActive: "4 days ago",
          avatar: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=40&h=40&fit=crop"
        },
        {
          id: 4,
          name: "Chloe Hayes",
          email: "chloehye@gmail.com",
          username: "chloeh",
          status: "Cashflows Management",
          role: "Guest",
          joinedDate: "October 5, 2021",
          lastActive: "10 days ago",
          avatar: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=40&h=40&fit=crop"
        },
        {
          id: 5,
          name: "Marcus Reed",
          email: "reeds777@gmail.com",
          username: "reeds7",
          status: "Engineering Plans",
          role: "User",
          joinedDate: "February 19, 2023",
          lastActive: "3 months ago",
          avatar: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=40&h=40&fit=crop"
        },
        {
          id: 6,
          name: "Isabelle Clark",
          email: "belleclark@gmail.com",
          username: "bellecl",
          status: "Variation Orders",
          role: "Moderator",
          joinedDate: "August 30, 2022",
          lastActive: "1 week ago",
          avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop"
        },
        {
          id: 7,
          name: "Lucas Mitchell",
          email: "lucamich@gmail.com",
          username: "lucamich",
          status: "Project Accomplishments",
          role: "Guest",
          joinedDate: "April 23, 2024",
          lastActive: "4 hours ago",
          avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop"
        },
        {
          id: 8,
          name: "Mark Wilburg",
          email: "markwil32@gmail.com",
          username: "markwil32",
          status: "Reports",
          role: "User",
          joinedDate: "November 14, 2020",
          lastActive: "2 months ago",
          avatar: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=40&h=40&fit=crop"
        },
        {
          id: 9,
          name: "Nicholas Agern",
          email: "nicolass009@gmail.com",
          username: "nicolass009",
          status: "Audit Logs",
          role: "User",
          joinedDate: "July 6, 2023",
          lastActive: "3 hours ago",
          avatar: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop"
        },
        {
          id: 10,
          name: "Mia Nadim",
          email: "mianadim@gmail.com",
          username: "mianadim",
          status: "Settings",
          role: "Guest",
          joinedDate: "December 31, 2021",
          lastActive: "4 months ago",
          avatar: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=40&h=40&fit=crop"
        },
        {
          id: 11,
          name: "Noemi Villan",
          email: "noemivil99@gmail.com",
          username: "noemi",
          status: "Dashboard",
          role: "Admin",
          joinedDate: "August 10, 2024",
          lastActive: "15 minutes ago",
          avatar: "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=40&h=40&fit=crop"
        }
      ]
    };
  },
  computed: {
    filteredUsers() {
      return this.users.filter(user => {
        const matchSearch = user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                           user.email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                           user.username.toLowerCase().includes(this.searchQuery.toLowerCase());
        const matchRole = !this.selectedRole || user.role === this.selectedRole;
        const matchStatus = !this.selectedStatus || user.status === this.selectedStatus;
        return matchSearch && matchRole && matchStatus;
      });
    },
    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.rowsPerPage);
    },
    paginatedUsers() {
      const startIndex = (this.currentPage - 1) * this.rowsPerPage;
      const endIndex = startIndex + this.rowsPerPage;
      return this.filteredUsers.slice(startIndex, endIndex);
    }
  },
  methods: {
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    }
  }
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

.card-footer {
  border-top: 1px solid #e0e5ee;
  padding: 1.5rem;
  background: transparent;
}

.input-group {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #8b92ab;
  font-size: 1.25rem;
  pointer-events: none;
  z-index: 1;
}

.form-control {
  padding-left: 40px;
  border-radius: 0.75rem;
  border: 1px solid #dfe4ed;
  font-size: 0.875rem;
}

.form-control:focus {
  border-color: #1f2633;
  box-shadow: 0 0 0 0.2rem rgba(31, 38, 51, 0.1);
}

.table {
  font-size: 0.875rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
}

.role-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
  color: white;
}

.role-admin {
  background: #2c5aa0;
}

.role-user {
  background: #0288d1;
}

.role-moderator {
  background: #fb8500;
}

.role-guest {
  background: #858585;
}

.btn-icon {
  width: 32px;
  height: 32px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #dfe4ed;
  border-radius: 0.5rem;
  background: transparent;
  transition: all 0.3s ease;
}

.btn-icon:hover {
  background: #f0f2f5;
}

.btn-edit {
  color: #0288d1;
}

.btn-edit:hover {
  color: #0276c1;
}

.btn-delete {
  color: #d32f2f;
}

.btn-delete:hover {
  color: #b71c1c;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.75rem;
}

.btn-success {
  background: #2cdc71;
  border-color: #2cdc71;
  color: white;
}

.btn-success:hover {
  background: #22a850;
  border-color: #22a850;
}

.pagination {
  gap: 0.5rem;
}

.page-link {
  border-radius: 0.5rem;
  border: 1px solid #dfe4ed;
  color: #1f2633;
  padding: 0.5rem 0.75rem;
}

.page-link:hover {
  background: #f0f2f5;
  color: #1f2633;
}

.page-item.active .page-link {
  background: #1f2633;
  border-color: #1f2633;
}

.page-item.disabled .page-link {
  opacity: 0.5;
  pointer-events: none;
}
</style>
