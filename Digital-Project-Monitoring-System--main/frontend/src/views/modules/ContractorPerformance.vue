<template>
  <div class="module-page">
    <div class="container-fluid py-4">

      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Contractor Performance Rating</h4>
          <p class="text-secondary small">Automated performance assessment based on key indicators</p>
        </div>
        <div class="col-lg-4 text-end d-flex gap-2 justify-content-end">
          <button class="btn btn-outline-secondary btn-sm">
            <i class="material-icons-round">download</i> Export
          </button>
          <button class="btn btn-outline-secondary btn-sm" @click="showFilterModal = true">
            <i class="material-icons-round">filter_list</i> Advanced Filter
          </button>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="row mb-4">
        <div class="col-md-4 col-lg-3 mb-2">
          <select class="form-control" v-model="selectedContractor">
            <option>All Contractors</option>
            <option>Regi Construction</option>
            <option>Nortech Builders</option>
            <option>Synergy Builders</option>
            <option>Piar Civil</option>
          </select>
        </div>
        <div class="col-md-4 col-lg-3 mb-2">
          <select class="form-control" v-model="selectedRating">
            <option>All Ratings</option>
            <option>Excellent</option>
            <option>Good</option>
            <option>Needs Improvement</option>
          </select>
        </div>
      </div>

      <!-- Performance Summary Cards -->
      <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="summary-card">
            <h6>Total Contractors</h6>
            <p class="count" style="color:#1f2633">4</p>
            <span class="sub-label">Active this period</span>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="summary-card excellent-card">
            <h6>Excellent</h6>
            <p class="count">2</p>
            <span class="sub-label">80–100% score</span>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="summary-card warning">
            <h6>Good</h6>
            <p class="count">2</p>
            <span class="sub-label">60–79% score</span>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="summary-card danger">
            <h6>Needs Improvement</h6>
            <p class="count">0</p>
            <span class="sub-label">Below 60%</span>
          </div>
        </div>
      </div>

      <!-- Contractor Ratings Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
              <h6>Contractor Ratings & Indicators</h6>
              <div class="d-flex gap-2 align-items-center">
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" class="btn btn-dark">All</button>
                  <button type="button" class="btn btn-outline-secondary">Excellent</button>
                  <button type="button" class="btn btn-outline-secondary">Good</button>
                </div>
                <button class="btn btn-sm btn-icon btn-light text-secondary">
                  <i class="material-icons-round">more_vert</i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Contractor</th>
                      <th>Overall Score</th>
                      <th>Completion Rate</th>
                      <th>Timeline Compliance</th>
                      <th>Variation Frequency</th>
                      <th>Rating</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="contractor in filteredContractors" :key="contractor.id">
                      <td>
                        <div class="contractor-name">{{ contractor.name }}</div>
                        <div class="contractor-sub">{{ contractor.license }}</div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="score-bar-wrap">
                            <div
                              class="score-bar"
                              :class="'bar-' + contractor.rating.toLowerCase().replace(' ', '_')"
                              :style="{ width: contractor.overall_score + '%' }"
                            ></div>
                          </div>
                          <span class="score-num">{{ contractor.overall_score }}%</span>
                        </div>
                      </td>
                      <td>{{ contractor.completion }}%</td>
                      <td>{{ contractor.timeline }}%</td>
                      <td>
                        <span class="variation-pill" :class="contractor.variations >= 4 ? 'high' : 'low'">
                          {{ contractor.variations }} VOs
                        </span>
                      </td>
                      <td>
                        <span class="score-badge" :class="'score-' + contractor.rating.toLowerCase().replace(' ', '_')">
                          {{ contractor.rating }}
                        </span>
                      </td>
                      <td><status-badge :status="contractor.status" /></td>
                      <td class="align-middle text-end">
                        <div class="dropdown">
                          <button
                            class="btn btn-sm btn-icon btn-light text-secondary"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                          >
                            <i class="material-icons-round">more_vert</i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="viewContractor(contractor)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon view-icon">visibility</i>
                                View
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="editContractor(contractor)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit</i>
                                Edit
                              </a>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                              <a class="dropdown-item text-danger" href="#" @click.prevent="deleteContractor(contractor)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon">delete</i>
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

              <!-- Pagination -->
              <div class="d-flex justify-content-between align-items-center mt-3 px-2">
                <span class="text-secondary small">Showing 1 to 4 of 4 Contractors</span>
                <nav>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Analytics Bottom Row -->
      <div class="row mt-4">
        <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-header pb-0">
              <h6>Score Distribution</h6>
            </div>
            <div class="card-body">
              <div v-for="contractor in contractors" :key="'dist-' + contractor.id" class="monthly-row mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span class="small">{{ contractor.name }}</span>
                  <strong class="small">{{ contractor.overall_score }}%</strong>
                </div>
                <div class="monthly-bar-bg">
                  <div
                    class="monthly-bar"
                    :class="'bar-' + contractor.rating.toLowerCase().replace(' ', '_')"
                    :style="{ width: contractor.overall_score + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
              <h6>Key Indicator Summary</h6>
              <i class="material-icons-round text-secondary" style="font-size:1.1rem">bar_chart</i>
            </div>
            <div class="card-body">
              <div class="monthly-row mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span class="small">Avg. Completion Rate</span>
                  <strong class="small">84.5%</strong>
                </div>
                <div class="monthly-bar-bg">
                  <div class="monthly-bar" style="width:84.5%;background:#4caf50"></div>
                </div>
              </div>
              <div class="monthly-row mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span class="small">Avg. Timeline Compliance</span>
                  <strong class="small">75.3%</strong>
                </div>
                <div class="monthly-bar-bg">
                  <div class="monthly-bar" style="width:75.3%;background:#2563eb"></div>
                </div>
              </div>
              <div class="monthly-row">
                <div class="d-flex justify-content-between mb-1">
                  <span class="small">High Variation Frequency</span>
                  <strong class="small">25%</strong>
                </div>
                <div class="monthly-bar-bg">
                  <div class="monthly-bar" style="width:25%;background:#d32f2f"></div>
                </div>
              </div>
              <div class="mt-4 text-center">
                <a href="#" class="btn btn-sm btn-outline-secondary w-100">View Detailed Performance Report</a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <BfpModal
      :show="showFilterModal"
      title="Contractor Performance Filters"
      stripe="RATING SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showFilterModal = false"
      @confirm="showFilterModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">engineering</i> Contractor Scope</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Contractor</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">groups</i>
              <select class="bfp-input bfp-select" v-model="selectedContractor">
                <option>All Contractors</option>
                <option v-for="contractor in contractors" :key="contractor.id">{{ contractor.name }}</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Rating Band</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">workspace_premium</i>
              <select class="bfp-input bfp-select">
                <option>All Ratings</option>
                <option>Excellent</option>
                <option>Good</option>
                <option>Needs Improvement</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Minimum Score</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">percent</i>
              <input class="bfp-input" type="number" min="0" max="100" placeholder="0" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Variation Frequency</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">sync_alt</i>
              <select class="bfp-input bfp-select">
                <option>Any Frequency</option>
                <option>0-1 variation</option>
                <option>2-3 variations</option>
                <option>4+ variations</option>
              </select>
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

export default {
  name: "ContractorPerformance",
  components: { StatusBadge, BfpModal },
  data() {
    return {
      selectedContractor: "All Contractors",
      selectedRating: "All Ratings",
      showFilterModal: false,
      contractors: [
        { id: 1, name: "Regi Construction", license: "PCAB Lic. 12345-A", overall_score: 92, rating: "Excellent", completion: 95, timeline: 88, variations: 2, status: "approved" },
        { id: 2, name: "Nortech Builders", license: "PCAB Lic. 67890-B", overall_score: 85, rating: "Excellent", completion: 90, timeline: 80, variations: 1, status: "approved" },
        { id: 3, name: "Synergy Builders", license: "PCAB Lic. 11223-C", overall_score: 72, rating: "Good", completion: 78, timeline: 68, variations: 4, status: "approved" },
        { id: 4, name: "Piar Civil", license: "PCAB Lic. 44556-A", overall_score: 68, rating: "Good", completion: 75, timeline: 65, variations: 3, status: "approved" }
      ]
    };
  },
  computed: {
    filteredContractors() {
      return this.contractors.filter(c => {
        const matchContractor = this.selectedContractor === "All Contractors" || c.name === this.selectedContractor;
        const matchRating = this.selectedRating === "All Ratings" || c.rating === this.selectedRating;
        return matchContractor && matchRating;
      });
    }
  },
  methods: {
    viewContractor(contractor) {
      alert(`View ${contractor.name}`);
    },
    editContractor(contractor) {
      alert(`Edit ${contractor.name}`);
    },
    deleteContractor(contractor) {
      alert(`Delete ${contractor.name}`);
    }
  }
};
</script>

<style scoped>
/* ── Dropdown menu ── */
.dropdown-menu {
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 0.75rem;
  font-size: 0.85rem;
  min-width: 140px;
  padding: 0.3rem;
}
.dropdown-item {
  border-radius: 0.5rem;
  padding: 0.45rem 0.75rem;
  display: flex;
  align-items: center;
}
.dropdown-item:hover { background: #f3f4f6; }
.dropdown-item.text-danger:hover { background: #fef2f2; }

.dropdown-icon { font-size: 1rem; }
.view-icon { color: #2563eb; }
.edit-icon { color: #d97706; }

.module-page {
  background: #f7fafc;
  min-height: 100vh;
}

.summary-card {
  padding: 1.5rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.1);
  text-align: center;
}

.summary-card h6 {
  color: #5a6270;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
  font-size: 0.875rem;
}

.summary-card .count {
  font-size: 1.75rem;
  font-weight: 700;
  color: #4caf50;
  margin: 0;
}

.summary-card .sub-label {
  font-size: 0.75rem;
  color: #5a6270;
}

.summary-card.excellent-card .count { color: #4caf50; }
.summary-card.warning .count { color: #fb8500; }
.summary-card.danger .count { color: #d32f2f; }

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

.table { font-size: 0.875rem; }

.contractor-name { font-weight: 600; font-size: 0.875rem; }
.contractor-sub { font-size: 0.75rem; color: #888; }

.score-bar-wrap {
  width: 80px;
  height: 6px;
  background: #e0e5ee;
  border-radius: 4px;
  overflow: hidden;
}

.score-bar {
  height: 6px;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.bar-excellent { background: #4caf50; }
.bar-good { background: #fb8500; }
.bar-needs_improvement { background: #d32f2f; }

.score-num { font-weight: 600; font-size: 0.8rem; color: #1f2633; }

.score-badge {
  padding: 0.3rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.score-excellent { background: #4caf50; }
.score-good { background: #fb8500; }
.score-needs_improvement { background: #d32f2f; }

.variation-pill {
  padding: 0.25rem 0.6rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
}
.variation-pill.low { background: #e8f5e9; color: #2e7d32; }
.variation-pill.high { background: #fef3e2; color: #e65100; }

.form-control {
  border-radius: 0.75rem;
  border: 1px solid #dfe4ed;
  font-size: 0.875rem;
}

.monthly-bar-bg {
  background: #eef0f3;
  border-radius: 4px;
  height: 8px;
  overflow: hidden;
}

.monthly-bar {
  height: 8px;
  border-radius: 4px;
}
</style>