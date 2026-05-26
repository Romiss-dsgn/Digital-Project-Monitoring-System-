<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Variation Orders</h4>
          <p class="text-secondary small">Track all contract changes and amendments</p>
        </div>
        <div class="col-lg-4 text-end">
          <button class="btn btn-primary btn-sm">
            <i class="material-icons-round">add</i> New Variation Order
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="summary-card">
            <h6>Approved</h6>
            <p class="count">2</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="summary-card warning">
            <h6>Pending</h6>
            <p class="count">2</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="summary-card danger">
            <h6>Rejected</h6>
            <p class="count">0</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="summary-card info">
            <h6>Total Amount</h6>
            <p class="amount">₱1.2M</p>
          </div>
        </div>
      </div>

      <!-- Variation Orders Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <h6>Variation Order Details</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Order Number</th>
                      <th>Description</th>
                      <th>Amount (PHP)</th>
                      <th>Status</th>
                      <th>Date Requested</th>
                      <th>Date Approved</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="order in orders" :key="order.id">
                      <td><strong>{{ order.order_num }}</strong></td>
                      <td>{{ order.description }}</td>
                      <td>{{ order.amount }}</td>
                      <td><status-badge :status="order.status" /></td>
                      <td>{{ order.date_requested }}</td>
                      <td>{{ order.date_approved }}</td>
                      <td>
                        <div class="dropdown">
                          <button
                            class="btn btn-sm btn-icon btn-light text-secondary dropdown-toggle"
                            type="button"
                            :id="`orderActionDropdown-${order.id}`"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                          >
                            <i class="material-icons-round">more_vert</i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end" :aria-labelledby="`orderActionDropdown-${order.id}`">
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="viewOrder(order)">
                                <i class="material-icons-round align-middle me-2">visibility</i>
                                View
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="editOrder(order)">
                                <i class="material-icons-round align-middle me-2">edit</i>
                                Edit
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item text-danger" href="#" @click.prevent="deleteOrder(order)">
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
  name: "VariationOrders",
  components: {
    StatusBadge
  },
  data() {
    return {
      orders: [
        { id: 1, order_num: "VO-2023-001", description: "Additional Drainage Installation", amount: "₱320,000.00", status: "approved", date_requested: "06/01/2023", date_approved: "06/10/2023" },
        { id: 2, order_num: "VO-2023-002", description: "Asphalt Overlay Adjustment", amount: "₱185,500.00", status: "pending", date_requested: "06/05/2023", date_approved: "-" },
        { id: 3, order_num: "VO-2023-006", description: "Relocation of Electrical Lines", amount: "₱275,000.00", status: "approved", date_requested: "06/07/2023", date_approved: "06/15/2023" },
        { id: 4, order_num: "VO-2023-007", description: "Additional Road Signage", amount: "₱95,000.00", status: "pending", date_requested: "06/10/2023", date_approved: "-" }
      ]
    };
  },
  methods: {
    viewOrder(order) {
      alert(`View order ${order.order_num}`);
    },
    editOrder(order) {
      alert(`Edit order ${order.order_num}`);
    },
    deleteOrder(order) {
      alert(`Delete order ${order.order_num}`);
    }
  }
};
</script>

<style scoped>
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

.summary-card .amount {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2633;
  margin: 0;
}

.summary-card.warning .count {
  color: #fb8500;
}

.summary-card.danger .count {
  color: #d32f2f;
}

.summary-card.info .count {
  color: #0288d1;
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

.table {
  font-size: 0.875rem;
}
</style>
