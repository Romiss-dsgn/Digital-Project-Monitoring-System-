<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Audit Trail & System Logs</h4>
          <p class="text-secondary small">Complete audit log of all system activities and changes</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-2">
          <select class="form-control">
            <option>All Actions</option>
            <option>Login</option>
            <option>File Upload</option>
            <option>Status Change</option>
            <option>Approval</option>
            <option>Delete</option>
          </select>
        </div>
        <div class="col-md-6 col-lg-3 mb-2">
          <select class="form-control">
            <option>All Users</option>
            <option>Engr. Dela Cruz</option>
            <option>Admin User</option>
          </select>
        </div>
        <div class="col-md-6 col-lg-3 mb-2">
          <input type="date" class="form-control" />
        </div>
        <div class="col-md-6 col-lg-3 mb-2">
          <button class="btn btn-primary btn-block">
            <i class="material-icons-round">search</i> Search
          </button>
        </div>
      </div>

      <!-- Logs Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>System Activity Logs</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Timestamp</th>
                      <th>User</th>
                      <th>Action</th>
                      <th>Module</th>
                      <th>Record</th>
                      <th>Changes</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="log in logs" :key="log.id">
                      <td><small>{{ log.timestamp }}</small></td>
                      <td>{{ log.user }}</td>
                      <td>
                        <span class="action-badge" :class="'action-' + log.action.toLowerCase()">{{ log.action }}</span>
                      </td>
                      <td>{{ log.module }}</td>
                      <td>{{ log.record }}</td>
                      <td><small class="text-secondary">{{ log.changes }}</small></td>
                      <td><status-badge :status="log.status" /></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Download/Export Options -->
      <div class="row mt-4">
        <div class="col-12">
          <button class="btn btn-secondary btn-sm me-2">
            <i class="material-icons-round">file_download</i> Export to Excel
          </button>
          <button class="btn btn-secondary btn-sm">
            <i class="material-icons-round">file_download</i> Export to PDF
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import StatusBadge from "@/components/StatusBadge.vue";

export default {
  name: "AuditTrail",
  components: {
    StatusBadge
  },
  data() {
    return {
      logs: [
        { id: 1, timestamp: "2023-06-15 14:32:21", user: "Engr. Dela Cruz", action: "Upload", module: "Engineering Plans", record: "Architectural_Plans_PRO-II-2023-001.pdf", changes: "File uploaded successfully", status: "completed" },
        { id: 2, timestamp: "2023-06-15 13:45:10", user: "Admin User", action: "Approval", module: "Variation Orders", record: "VO-2023-001", changes: "Status changed to Approved", status: "completed" },
        { id: 3, timestamp: "2023-06-15 12:20:33", user: "Engr. Dela Cruz", action: "Status Change", module: "Infrastructure Plans", record: "PRO-II-2023-001", changes: "Phase changed from Planning to Execution", status: "completed" },
        { id: 4, timestamp: "2023-06-15 10:15:45", user: "Admin User", action: "Login", module: "System", record: "User Login", changes: "Successful login from IP 192.168.1.100", status: "completed" },
        { id: 5, timestamp: "2023-06-14 16:50:22", user: "Engr. Dela Cruz", action: "Upload", module: "Contract Management", record: "Supplemental_Agreement_REG-II-001.pdf", changes: "Contract document uploaded", status: "completed" }
      ]
    };
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

.action-delete {
  background: #d32f2f;
}

.form-control {
  border-radius: 0.75rem;
  border: 1px solid #dfe4ed;
}

.btn-block {
  width: 100%;
}
</style>
