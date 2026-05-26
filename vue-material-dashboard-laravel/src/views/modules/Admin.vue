<template>
  <div class="admin-page">
    <div class="container-fluid py-4">
      <div class="row align-items-center mb-4">
        <div class="col-lg-8">
          <h4 class="mb-1">Infrastructure Plans Management</h4>
          <p class="text-secondary small mb-0">Monitor and manage institutional construction projects across Region II.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
          <button class="btn btn-primary btn-sm">
            <i class="material-icons-round">add</i>
            Add New Project
          </button>
        </div>
      </div>

      <div class="row g-2 mb-4 filters-row">
        <div class="col-sm-6 col-md-4 col-lg-2">
          <input v-model="filters.name" type="text" class="form-control form-control-sm" placeholder="Project Name" />
        </div>
        <div class="col-sm-6 col-md-4 col-lg-2">
          <input v-model="filters.code" type="text" class="form-control form-control-sm" placeholder="Project Code" />
        </div>
        <div class="col-sm-6 col-md-4 col-lg-2">
          <select v-model="filters.location" class="form-control form-control-sm">
            <option value="">All Provinces</option>
            <option v-for="location in locations" :key="location" :value="location">{{ location }}</option>
          </select>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-2">
          <select v-model="filters.status" class="form-control form-control-sm">
            <option value="">All Status</option>
            <option value="on_time">On Time</option>
            <option value="delayed">Delayed</option>
            <option value="completed">Completed</option>
            <option value="ongoing">Ongoing</option>
            <option value="planning">Planning</option>
          </select>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-2">
          <select v-model="filters.phase" class="form-control form-control-sm">
            <option value="">All Phases</option>
            <option v-for="phase in phases" :key="phase" :value="phase">{{ phase }}</option>
          </select>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-2 d-grid">
          <button class="btn btn-outline-primary btn-sm" @click="resetFilters">Apply Filters</button>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card shadow-sm border-0">
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Project Code</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Project Name</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Location</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contractor</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Progress %</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Phase</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                      <th class="text-secondary opacity-7"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="project in filteredProjects" :key="project.id">
                      <td class="align-middle text-sm"><strong>{{ project.code }}</strong></td>
                      <td class="align-middle text-sm">{{ project.name }}</td>
                      <td class="align-middle text-sm">{{ project.location }}</td>
                      <td class="align-middle text-sm">{{ project.contractor }}</td>
                      <td class="align-middle text-sm">
                        <div class="d-flex align-items-center gap-2">
                          <div class="progress progress-sm w-100">
                            <div class="progress-bar" :class="progressClass(project.progress)" :style="{ width: project.progress + '%' }"></div>
                          </div>
                          <span class="text-xs text-secondary">{{ project.progress }}%</span>
                        </div>
                      </td>
                      <td class="align-middle text-sm">{{ project.phase }}</td>
                      <td class="align-middle text-sm">
                        <span class="status-pill" :class="project.status">{{ statusLabel(project.status) }}</span>
                      </td>
                      <td class="align-middle text-end">
                        <div class="dropdown">
                          <button
                            class="btn btn-sm btn-icon btn-light text-secondary dropdown-toggle"
                            type="button"
                            id="actionDropdownButton"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                          >
                            <i class="material-icons-round">more_vert</i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionDropdownButton">
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="viewProject(project)">
                                <i class="material-icons-round align-middle me-2">visibility</i>
                                View
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="editProject(project)">
                                <i class="material-icons-round align-middle me-2">edit</i>
                                Edit
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item text-danger" href="#" @click.prevent="deleteProject(project)">
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

      <div class="row mt-4">
        <div class="col-lg-6 mb-3">
          <div class="card card-body border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-secondary text-uppercase text-xxs mb-1">Last Update</p>
                <h6 class="mb-0">{{ lastUpdate }}</h6>
              </div>
              <span class="badge bg-soft-primary text-primary py-2 px-3 rounded-pill"><i class="material-icons-round">update</i></span>
            </div>
          </div>
        </div>
        <div class="col-lg-6 mb-3">
          <div class="card card-body border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-secondary text-uppercase text-xxs mb-1">Regional Efficiency</p>
                <h6 class="mb-0">{{ efficiency }} On-Time</h6>
              </div>
              <span class="badge bg-soft-success text-success py-2 px-3 rounded-pill"><i class="material-icons-round">trending_up</i></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Admin",
  data() {
    return {
      filters: {
        name: "",
        code: "",
        location: "",
        status: "",
        phase: ""
      },
      locations: ["Cagayan", "Isabela", "Nueva Vizcaya", "Quirino"],
      phases: ["Planning", "Foundation", "Construction", "Finishing", "Post-Eval"],
      lastUpdate: "Oct 24, 2024 · 14:30",
      efficiency: "94.2%",
      projects: [
        {
          id: 1,
          code: "BFP-2024-C001",
          name: "Tuguegarao Central Fire Station - Phase II",
          location: "Cagayan",
          contractor: "V.G. Construction Services",
          progress: 45,
          phase: "Construction",
          status: "on_time"
        },
        {
          id: 2,
          code: "BFP-2024-1012",
          name: "Ilagan City Fire Sub-Station Annex",
          location: "Isabela",
          contractor: "BuildRight Partners Corp.",
          progress: 12,
          phase: "Foundation",
          status: "delayed"
        },
        {
          id: 3,
          code: "BFP-2023-N005",
          name: "Bayombong Regional Logistics Hub",
          location: "Nueva Vizcaya",
          contractor: "NorthEdge Engineering",
          progress: 85,
          phase: "Finishing",
          status: "ongoing"
        },
        {
          id: 4,
          code: "BFP-2024-P002",
          name: "Region II Headquarters Renovation",
          location: "Cagayan",
          contractor: "PrimeBuilders Inc.",
          progress: 5,
          phase: "Planning",
          status: "planning"
        },
        {
          id: 5,
          code: "BFP-2023-Q008",
          name: "Quirino Provincial Fire Office - Solar Project",
          location: "Quirino",
          contractor: "EcoPower Solutions",
          progress: 100,
          phase: "Post-Eval",
          status: "completed"
        }
      ]
    };
  },
  computed: {
    filteredProjects() {
      return this.projects.filter((project) => {
        return (
          (!this.filters.name || project.name.toLowerCase().includes(this.filters.name.toLowerCase())) &&
          (!this.filters.code || project.code.toLowerCase().includes(this.filters.code.toLowerCase())) &&
          (!this.filters.location || project.location === this.filters.location) &&
          (!this.filters.status || project.status === this.filters.status) &&
          (!this.filters.phase || project.phase === this.filters.phase)
        );
      });
    }
  },
  methods: {
    resetFilters() {
      this.filters = {
        name: "",
        code: "",
        location: "",
        status: "",
        phase: ""
      };
    },
    statusLabel(status) {
      const labels = {
        on_time: "ON TIME",
        delayed: "DELAYED",
        completed: "COMPLETED",
        ongoing: "ONGOING",
        planning: "PLANNING"
      };
      return labels[status] || status.toUpperCase();
    },
    progressClass(progress) {
      if (progress >= 75) return "bg-success";
      if (progress >= 40) return "bg-warning";
      return "bg-danger";
    },
    viewProject(project) {
      alert(`View project: ${project.name}`);
    },
    editProject(project) {
      alert(`Edit project: ${project.name}`);
    },
    deleteProject(project) {
      alert(`Delete project: ${project.name}`);
    }
  }
};
</script>

<style scoped>
.admin-page {
  min-height: 100vh;
  background: #f8f9fc;
}
.filters-row .form-control,
.filters-row .btn {
  border-radius: 0.85rem;
}
.table thead th {
  border-bottom: none;
  background: #ffffff;
}
.table tbody tr {
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}
.status-pill {
  padding: 0.45rem 0.85rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}
.status-pill.on_time {
  background: rgba(88, 207, 151, 0.12);
  color: #1d7a3f;
}
.status-pill.delayed {
  background: rgba(255, 138, 128, 0.18);
  color: #c62828;
}
.status-pill.completed {
  background: rgba(102, 187, 106, 0.18);
  color: #1b5e20;
}
.status-pill.ongoing {
  background: rgba(255, 202, 40, 0.18);
  color: #f57f17;
}
.status-pill.planning {
  background: rgba(144, 202, 249, 0.18);
  color: #0d47a1;
}
.card .progress {
  background: rgba(0, 0, 0, 0.05);
  height: 0.55rem;
}
.progress-bar {
  height: 0.55rem;
}
.btn-icon {
  width: 2.4rem;
  height: 2.4rem;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.filters-row {
  margin-bottom: 1rem;
}
.filters-row .form-control,
.filters-row .btn {
  min-height: 44px;
}
.table thead th,
.table tbody td {
  vertical-align: middle;
}
.table .progress {
  min-width: 160px;
}
.card-body {
  padding: 1.25rem 1.5rem;
}
.card {
  border: none;
  border-radius: 1rem;
}
.bg-soft-primary {
  background: rgba(13, 110, 253, 0.12) !important;
}
.bg-soft-success {
  background: rgba(25, 135, 84, 0.12) !important;
}
</style>
