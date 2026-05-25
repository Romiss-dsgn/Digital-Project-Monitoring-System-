<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Infrastructure Plans</h4>
          <p class="text-secondary small">Track all active projects and their phases</p>
        </div>
        <div class="col-lg-4 text-end">
          <button class="btn btn-primary btn-sm">
            <i class="material-icons-round">add</i> New Project
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="tabs-navigation">
            <button v-for="tab in tabs" :key="tab.id" :class="['tab-btn', { active: activeTab === tab.id }]" @click="activeTab = tab.id">
              {{ tab.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Projects Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Project Code</th>
                      <th>Project Name</th>
                      <th>Phase</th>
                      <th>Start Date</th>
                      <th>End Date</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="project in projects" :key="project.id">
                      <td><strong>{{ project.code }}</strong></td>
                      <td>{{ project.name }}</td>
                      <td>
                        <span class="phase-badge" :class="'phase-' + project.phase.toLowerCase()">{{ project.phase }}</span>
                      </td>
                      <td>{{ project.start_date }}</td>
                      <td>{{ project.end_date }}</td>
                      <td><status-badge :status="project.status" /></td>
                      <td>
                        <button class="btn btn-sm btn-link text-primary">
                          <i class="material-icons-round">visibility</i>
                        </button>
                        <button class="btn btn-sm btn-link text-warning">
                          <i class="material-icons-round">edit</i>
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
  name: "InfrastructurePlans",
  components: {
    StatusBadge
  },
  data() {
    return {
      activeTab: "all",
      tabs: [
        { id: "all", label: "All Projects" },
        { id: "construction", label: "Construction" },
        { id: "mobilization", label: "Mobilization" },
        { id: "execution", label: "Execution" },
        { id: "inspection", label: "Inspection & Testing" },
        { id: "closeout", label: "Project Closeout" }
      ],
      projects: [
        { id: 1, code: "PRO-II-2023-001", name: "Construction of New Fire Station - Tuguegarao City", phase: "Construction", start_date: "02/01/2023", end_date: "06/30/2023", status: "in_progress" },
        { id: 2, code: "PRO-II-2023-002", name: "Renovation of Fire Station - Santiago City", phase: "Construction", start_date: "05/15/2023", end_date: "09/15/2023", status: "pending" },
        { id: 3, code: "PRO-II-2023-003", name: "Construction of Fire Station - Cauayan City", phase: "Construction", start_date: "03/02/2023", end_date: "07/30/2023", status: "on_track" },
        { id: 4, code: "PRO-II-2023-004", name: "Redstone Development, of BFP Regional Office II", phase: "Planning", start_date: "04/01/2023", end_date: "05/15/2023", status: "pending" }
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

.tabs-navigation {
  display: flex;
  gap: 1rem;
  border-bottom: 2px solid #e0e5ee;
  overflow-x: auto;
  margin-bottom: -2px;
}

.tab-btn {
  padding: 1rem 1.5rem;
  background: transparent;
  border: none;
  color: #5a6270;
  font-weight: 500;
  font-size: 0.95rem;
  cursor: pointer;
  position: relative;
  white-space: nowrap;
}

.tab-btn.active {
  color: #c82a3e;
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: #c82a3e;
}

.card {
  border: none;
  border-radius: 1rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.1);
}

.table {
  font-size: 0.875rem;
}

.phase-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.phase-construction {
  background: #fff3cd;
  color: #856404;
}

.phase-mobilization {
  background: #d1ecf1;
  color: #0c5460;
}

.phase-execution {
  background: #d4edda;
  color: #155724;
}

.phase-inspection {
  background: #cce5ff;
  color: #004085;
}

.phase-planning {
  background: #f8d7da;
  color: #721c24;
}
</style>
