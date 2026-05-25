<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Engineering Plans</h4>
          <p class="text-secondary small">Centralized repository of technical drawings and documents</p>
        </div>
        <div class="col-lg-4 text-end">
          <button class="btn btn-primary btn-sm">
            <i class="material-icons-round">cloud_upload</i> Upload Plans
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-2">
          <input type="text" class="form-control" placeholder="Search by file name...">
        </div>
        <div class="col-md-6 col-lg-4 mb-2">
          <select class="form-control">
            <option>All Projects</option>
            <option>Road Widening</option>
            <option>Fire Station</option>
          </select>
        </div>
        <div class="col-md-6 col-lg-4 mb-2">
          <select class="form-control">
            <option>All Types</option>
            <option>Architectural</option>
            <option>Structural</option>
            <option>Electrical</option>
          </select>
        </div>
      </div>

      <!-- Documents Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Engineering Documents</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>File Name</th>
                      <th>Type</th>
                      <th>Project</th>
                      <th>Uploaded By</th>
                      <th>Date Uploaded</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="doc in documents" :key="doc.id">
                      <td><i class="material-icons-round">description</i> {{ doc.filename }}</td>
                      <td>{{ doc.type }}</td>
                      <td>{{ doc.project }}</td>
                      <td>{{ doc.uploaded_by }}</td>
                      <td>{{ doc.date_uploaded }}</td>
                      <td><status-badge :status="doc.status" /></td>
                      <td>
                        <button class="btn btn-sm btn-link text-primary">
                          <i class="material-icons-round">visibility</i>
                        </button>
                        <button class="btn btn-sm btn-link text-info">
                          <i class="material-icons-round">file_download</i>
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
    </div>
  </div>
</template>

<script>
import StatusBadge from "@/components/StatusBadge.vue";

export default {
  name: "EngineeringPlans",
  components: {
    StatusBadge
  },
  data() {
    return {
      documents: [
        { id: 1, filename: "Architectural Plans", type: "Architectural", project: "Road Widening Project - PRO-II-2023-001", uploaded_by: "Engr. Dela Cruz", date_uploaded: "04/01/2023", status: "completed" },
        { id: 2, filename: "Structural Plans", type: "Structural", project: "Road Widening Project - PRO-II-2023-001", uploaded_by: "Engr. Dela Cruz", date_uploaded: "04/01/2023", status: "completed" },
        { id: 3, filename: "Electrical Plans", type: "Electrical", project: "Road Widening Project - PRO-II-2023-001", uploaded_by: "Engr. Dela Cruz", date_uploaded: "04/01/2023", status: "completed" }
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
  font-size: 0.875rem;
}

.form-control {
  border-radius: 0.75rem;
  border: 1px solid #dfe4ed;
}
</style>
