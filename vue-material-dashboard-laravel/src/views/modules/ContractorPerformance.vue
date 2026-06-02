<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Contractor Performance Rating</h4>
          <p class="text-secondary small">Automated performance assessment based on key indicators</p>
        </div>
      </div>

      <!-- Filter -->
      <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-2">
          <select class="form-control" v-model="selectedContractor">
            <option>All Contractors</option>
            <option>Regi Construction</option>
            <option>Nortech Builders</option>
            <option>Synergy Builders</option>
          </select>
        </div>
        <div class="col-md-6 col-lg-3 mb-2">
          <button class="btn btn-outline-secondary btn-sm" @click="showFilterModal = true">
            <i class="material-icons-round">filter_list</i> Advanced Filter
          </button>
        </div>
      </div>

      <!-- Performance Summary -->
      <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
          <div class="perf-card excellent">
            <h6>Excellent</h6>
            <p class="count">2</p>
            <span>80-100%</span>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
          <div class="perf-card good">
            <h6>Good</h6>
            <p class="count">1</p>
            <span>60-79%</span>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
          <div class="perf-card poor">
            <h6>Needs Improvement</h6>
            <p class="count">0</p>
            <span>Below 60%</span>
          </div>
        </div>
      </div>

      <!-- Contractors Performance -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Contractor Ratings & Indicators</h6>
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
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="contractor in contractors" :key="contractor.id">
                      <td><strong>{{ contractor.name }}</strong></td>
                      <td>
                        <span class="score-badge" :class="'score-' + contractor.rating.toLowerCase()">{{ contractor.overall_score }}%</span>
                      </td>
                      <td>{{ contractor.completion }}%</td>
                      <td>{{ contractor.timeline }}%</td>
                      <td>{{ contractor.variations }}</td>
                      <td><status-badge :status="contractor.status" /></td>
                    </tr>
                  </tbody>
                </table>
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
  components: {
    StatusBadge,
    BfpModal
  },
  data() {
    return {
      selectedContractor: "All Contractors",
      showFilterModal: false,
      contractors: [
        { id: 1, name: "Regi Construction", overall_score: 92, rating: "Excellent", completion: 95, timeline: 88, variations: 2, status: "approved" },
        { id: 2, name: "Nortech Builders", overall_score: 85, rating: "Excellent", completion: 90, timeline: 80, variations: 1, status: "approved" },
        { id: 3, name: "Synergy Builders", overall_score: 72, rating: "Good", completion: 78, timeline: 68, variations: 4, status: "approved" },
        { id: 4, name: "Piar Civil", overall_score: 68, rating: "Good", completion: 75, timeline: 65, variations: 3, status: "approved" }
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

.perf-card {
  padding: 1.5rem;
  border-radius: 1rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.1);
  text-align: center;
  color: white;
}

.perf-card h6 {
  margin: 0 0 0.5rem 0;
  font-weight: 600;
}

.perf-card .count {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.perf-card span {
  font-size: 0.875rem;
  opacity: 0.9;
}

.perf-card.excellent {
  background: linear-gradient(135deg, #4caf50, #66bb6a);
}

.perf-card.good {
  background: linear-gradient(135deg, #fb8500, #ffa930);
}

.perf-card.poor {
  background: linear-gradient(135deg, #d32f2f, #ef5350);
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

.score-badge {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 600;
  color: white;
}

.score-excellent {
  background: #4caf50;
}

.score-good {
  background: #fb8500;
}

.score-poor {
  background: #d32f2f;
}

.form-control {
  border-radius: 0.75rem;
  border: 1px solid #dfe4ed;
}
</style>
