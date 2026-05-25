<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Contract Management</h4>
          <p class="text-secondary small">Monitor all contracts and contractor compliance</p>
        </div>
        <div class="col-lg-4 text-end">
          <button class="btn btn-primary btn-sm">
            <i class="material-icons-round">add</i> New Contract
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-2">
          <input type="text" class="form-control" placeholder="Search by Contract ID...">
        </div>
        <div class="col-md-6 col-lg-3 mb-2">
          <select class="form-control">
            <option>All Status</option>
            <option>Active</option>
            <option>Completed</option>
            <option>Flagged</option>
          </select>
        </div>
      </div>

      <!-- Contracts Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Active Contracts</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Contract ID</th>
                      <th>Contractor</th>
                      <th>Project Name</th>
                      <th>Amount</th>
                      <th>Start Date</th>
                      <th>End Date</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="contract in contracts" :key="contract.id">
                      <td><strong>{{ contract.contract_id }}</strong></td>
                      <td>{{ contract.contractor }}</td>
                      <td>{{ contract.project }}</td>
                      <td>{{ contract.amount }}</td>
                      <td>{{ contract.start_date }}</td>
                      <td>{{ contract.end_date }}</td>
                      <td><status-badge :status="contract.status" /></td>
                      <td>
                        <button class="btn btn-sm btn-link text-primary">
                          <i class="material-icons-round">description</i>
                        </button>
                        <button class="btn btn-sm btn-link text-info">
                          <i class="material-icons-round">folder</i>
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
  name: "ContractManagement",
  components: {
    StatusBadge
  },
  data() {
    return {
      contracts: [
        { id: 1, contract_id: "REG-II-001", contractor: "Regi Construction", project: "Construction of New Fire Station - Tuguegarao City", amount: "₱15,500,000.00", start_date: "02/01/2023", end_date: "06/10/2023", status: "active" },
        { id: 2, contract_id: "REG-II-002", contractor: "Nortech Builders", project: "Synergy Builders", amount: "₱95,500,000.00", start_date: "06/15/2023", end_date: "06/30/2023", status: "active" },
        { id: 3, contract_id: "REG-II-003", contractor: "Synergy Builders", project: "Synergy Builders", amount: "₱23,000,000.00", start_date: "02/02/2023", end_date: "06/15/2023", status: "active" },
        { id: 4, contract_id: "REG-II-004", contractor: "Piar Civil Management Corp.", project: "Piar Civil Management Corp.", amount: "₱16,500,000.00", start_date: "05/15/2023", end_date: "06/15/2023", status: "active" }
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
