<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Project Accomplishments</h4>
          <p class="text-secondary small">Track milestones and project completion status</p>
        </div>
        <div class="col-lg-4 text-end">
          <button class="btn btn-primary btn-sm">
            <i class="material-icons-round">add</i> Record Accomplishment
          </button>
        </div>
      </div>

      <!-- Project Filter -->
      <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-2">
          <select class="form-control">
            <option>All Projects</option>
            <option>Road Widening Project</option>
            <option>Fire Station Construction</option>
          </select>
        </div>
      </div>

      <!-- Accomplishments Timeline -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Milestone Records</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Project</th>
                      <th>Milestone</th>
                      <th>Completion %</th>
                      <th>Target Date</th>
                      <th>Actual Date</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="milestone in accomplishments" :key="milestone.id">
                      <td>{{ milestone.project }}</td>
                      <td>{{ milestone.milestone }}</td>
                      <td>
                        <div class="progress-bar-wrapper">
                          <div class="progress-bar" :style="{ width: milestone.completion + '%' }"></div>
                          <span class="progress-text">{{ milestone.completion }}%</span>
                        </div>
                      </td>
                      <td>{{ milestone.target_date }}</td>
                      <td>{{ milestone.actual_date }}</td>
                      <td><status-badge :status="milestone.status" /></td>
                      <td>
                        <div class="dropdown">
                          <button
                            class="btn btn-sm btn-icon btn-light text-secondary dropdown-toggle"
                            type="button"
                            :id="`milestoneActionDropdown-${milestone.id}`"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                          >
                            <i class="material-icons-round">more_vert</i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end" :aria-labelledby="`milestoneActionDropdown-${milestone.id}`">
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="viewMilestone(milestone)">
                                <i class="material-icons-round align-middle me-2">visibility</i>
                                View
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="editMilestone(milestone)">
                                <i class="material-icons-round align-middle me-2">edit</i>
                                Edit
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item text-danger" href="#" @click.prevent="deleteMilestone(milestone)">
                                <i class="material-icons-round align-middle me-2">delete</i>
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
  name: "Accomplishments",
  components: {
    StatusBadge
  },
  data() {
    return {
      accomplishments: [
        { id: 1, project: "Road Widening Project", milestone: "Site Mobilization", completion: 100, target_date: "01/30/2023", actual_date: "02/01/2023", status: "completed" },
        { id: 2, project: "Road Widening Project", milestone: "Foundation & Subgrade", completion: 80, target_date: "03/15/2023", actual_date: "-", status: "in_progress" },
        { id: 3, project: "Fire Station Construction", milestone: "Structural Framework", completion: 60, target_date: "06/30/2023", actual_date: "-", status: "in_progress" },
        { id: 4, project: "Road Widening Project", milestone: "Asphalt Pavement", completion: 45, target_date: "05/31/2023", actual_date: "-", status: "pending" }
      ]
    };
  },
  methods: {
    viewMilestone(milestone) {
      alert(`View milestone ${milestone.milestone}`);
    },
    editMilestone(milestone) {
      alert(`Edit milestone ${milestone.milestone}`);
    },
    deleteMilestone(milestone) {
      alert(`Delete milestone ${milestone.milestone}`);
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

.table {
  font-size: 0.875rem;
}

.progress-bar-wrapper {
  position: relative;
  height: 20px;
  background: #e0e5ee;
  border-radius: 0.5rem;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #2c5aa0, #0288d1);
  transition: width 0.3s ease;
}

.progress-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 0.75rem;
  font-weight: 600;
  color: #1f2633;
}

.form-control {
  border-radius: 0.75rem;
  border: 1px solid #dfe4ed;
}
</style>
