<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Cashflow Management</h4>
          <p class="text-secondary small">Monitor budget allocation and expenditures</p>
        </div>
      </div>

      <!-- Budget Overview Cards -->
      <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="overview-card">
            <h6>Planned Budget</h6>
            <p class="amount">₱58,500,000</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="overview-card warning">
            <h6>Actual Expenditure</h6>
            <p class="amount">₱42,300,000</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="overview-card success">
            <h6>Remaining Budget</h6>
            <p class="amount">₱16,200,000</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="overview-card danger">
            <h6>Variance</h6>
            <p class="amount">-₱850,000</p>
          </div>
        </div>
      </div>

      <!-- Budget Comparison Chart -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Budget Vs Actual</h6>
            </div>
            <div class="card-body">
              <div class="chart-placeholder">
                <p class="text-secondary">Chart visualization will be displayed here</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Invoices & Payments -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Invoice Records & Payment Status</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Invoice #</th>
                      <th>Contract</th>
                      <th>Amount</th>
                      <th>Payment Schedule</th>
                      <th>Payment Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="invoice in invoices" :key="invoice.id">
                      <td><strong>{{ invoice.invoice_num }}</strong></td>
                      <td>{{ invoice.contract }}</td>
                      <td>{{ invoice.amount }}</td>
                      <td>{{ invoice.schedule }}</td>
                      <td><status-badge :status="invoice.payment_status" /></td>
                      <td>
                        <button class="btn btn-sm btn-link text-primary">
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
  name: "CashflowManagement",
  components: {
    StatusBadge
  },
  data() {
    return {
      invoices: [
        { id: 1, invoice_num: "INV-001", contract: "REG-II-001", amount: "₱2,500,000.00", schedule: "Jan-2023", payment_status: "paid" },
        { id: 2, invoice_num: "INV-002", contract: "REG-II-002", amount: "₱250,000.00", schedule: "05/01/2023", payment_status: "paid" },
        { id: 3, invoice_num: "INV-003", contract: "REG-II-003", amount: "₱250,000.00", schedule: "05/15/2023", payment_status: "pending" },
        { id: 4, invoice_num: "INV-003", contract: "REG-II-003", amount: "₱200,000.00", schedule: "06/18/2023", payment_status: "pending" }
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

.overview-card {
  padding: 1.5rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.1);
  text-align: center;
}

.overview-card h6 {
  color: #5a6270;
  font-weight: 600;
  margin: 0 0 1rem 0;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.overview-card .amount {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2633;
  margin: 0;
}

.overview-card.warning .amount {
  color: #fb8500;
}

.overview-card.success .amount {
  color: #4caf50;
}

.overview-card.danger .amount {
  color: #d32f2f;
}

.card {
  border: none;
  border-radius: 1rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.1);
}

.chart-placeholder {
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f7fafc;
  border-radius: 0.75rem;
}

.table {
  font-size: 0.875rem;
}
</style>
