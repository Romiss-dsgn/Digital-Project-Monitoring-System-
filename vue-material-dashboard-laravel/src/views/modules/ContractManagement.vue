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
                              <a class="dropdown-item" href="#" @click.prevent="viewContract(contract)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon view-icon">visibility</i>
                                View
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="editContract(contract)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit</i>
                                Edit
                              </a>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                              <a class="dropdown-item text-danger" href="#" @click.prevent="deleteContract(contract)">
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
  },
  methods: {
    viewContract(contract) {
      alert(`View contract ${contract.contract_id}`);
    },
    editContract(contract) {
      alert(`Edit contract ${contract.contract_id}`);
    },
    deleteContract(contract) {
      alert(`Delete contract ${contract.contract_id}`);
    }
  }
};
</script>

<style scoped>
.module-page {
  background: #f7fafc;
  min-height: 100vh;
}

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
