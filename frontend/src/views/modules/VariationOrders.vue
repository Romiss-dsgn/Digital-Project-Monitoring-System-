<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Variation Orders Monitoring</h4>
          <p class="text-secondary small">Track, review, and manage contract changes and financial impacts.</p>
        </div>
        <div class="col-lg-4 text-end d-flex gap-2 justify-content-end">
          <button class="btn btn-outline-secondary btn-sm" @click="showFilterModal = true">
            <i class="material-icons-round">filter_list</i> Filter
          </button>
          <button class="btn btn-primary btn-sm" @click="showRequestModal = true">
            <i class="material-icons-round">add</i> New VO Request
          </button>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="row mb-4">
        <div class="col-lg col-md-6 mb-3">
          <div class="summary-card total-card">
            <h6>Total VOs</h6>
            <p class="count" style="color:#1f2633">42</p>
            <span class="trend">+5 this month</span>
          </div>
        </div>
        <div class="col-lg col-md-6 mb-3">
          <div class="summary-card">
            <h6>Approved</h6>
            <p class="count">28</p>
            <span class="sub-label">Ready for payout</span>
          </div>
        </div>
        <div class="col-lg col-md-6 mb-3">
          <div class="summary-card warning">
            <h6>Pending</h6>
            <p class="count">11</p>
            <span class="sub-label">Awaiting signature</span>
          </div>
        </div>
        <div class="col-lg col-md-6 mb-3">
          <div class="summary-card danger">
            <h6>Rejected</h6>
            <p class="count">3</p>
            <span class="sub-label">Needs revision</span>
          </div>
        </div>
        <div class="col-lg col-md-6 mb-3">
          <div class="summary-card cost-card">
            <h6 style="color:#fff;opacity:.85">Total Cost Impact</h6>
            <p class="amount" style="color:#fff;font-size:1.5rem">₱ 14.2M</p>
            <div class="budget-bar-wrap">
              <div class="budget-bar"></div>
            </div>
            <span class="sub-label" style="color:#fff;opacity:.8">75% of VO contingency budget utilized</span>
          </div>
        </div>
      </div>

      <!-- Variation Orders Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
              <h6>Active Variation Orders</h6>
              <div class="d-flex gap-2 align-items-center">
                <div class="btn-group btn-group-sm" role="group">
                  <button type="button" class="btn btn-dark">All</button>
                  <button type="button" class="btn btn-outline-secondary">Requests</button>
                  <button type="button" class="btn btn-outline-secondary">Approvals</button>
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
                      <th>VO #</th>
                      <th>Project Reference</th>
                      <th>Description</th>
                      <th>Amount (PHP)</th>
                      <th>Dates</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="order in orders" :key="order.id">
                      <td><strong class="vo-num">{{ order.order_num }}</strong></td>
                      <td>
                        <div class="proj-name">{{ order.project_name }}</div>
                        <div class="proj-ref">{{ order.project_ref }}</div>
                      </td>
                      <td>{{ order.description }}</td>
                      <td><strong>{{ order.amount }}</strong></td>
                      <td>
                        <div class="date-req">Req: {{ order.date_requested }}</div>
                        <div class="date-app" v-if="order.date_approved !== '-'">App: {{ order.date_approved }}</div>
                        <div class="date-app muted" v-else>{{ order.date_status }}</div>
                      </td>
                      <td><status-badge :status="order.status" /></td>
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
                              <a class="dropdown-item" href="#" @click.prevent="viewOrder(order)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon view-icon">visibility</i>
                                View
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="editOrder(order)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit</i>
                                Edit
                              </a>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                              <a class="dropdown-item text-danger" href="#" @click.prevent="deleteOrder(order)">
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
                <span class="text-secondary small">Showing 1 to 10 of 42 Variation Orders</span>
                <nav>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
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
              <h6>Approval Efficiency</h6>
            </div>
            <div class="card-body">
              <div class="d-flex align-items-baseline gap-3 mb-3">
                <span class="text-secondary small">Average Approval Time:</span>
                <strong>14.2 Days</strong>
                <span class="text-success small">-2.1 Days vs Last Quarter</span>
              </div>
              <div class="approval-pipeline">
                <div class="pipeline-labels">
                  <span>Draft</span>
                  <span>Submission</span>
                  <span>Evaluation</span>
                  <span>Final Approval</span>
                </div>
                <div class="pipeline-track">
                  <div class="pipeline-fill"></div>
                  <div class="pipeline-dot" style="left:0%"></div>
                  <div class="pipeline-dot" style="left:33%"></div>
                  <div class="pipeline-dot active" style="left:66%"></div>
                  <div class="pipeline-dot done" style="left:98%"></div>
                </div>
              </div>
              <p class="text-secondary small mt-3 mb-0">
                Current average bottleneck identified at "Regional Director Evaluation" stage.
              </p>
            </div>
          </div>
        </div>
        <div class="col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
              <h6>Monthly Impact</h6>
              <i class="material-icons-round text-secondary" style="font-size:1.1rem">bar_chart</i>
            </div>
            <div class="card-body">
              <div class="monthly-row mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span class="small">Oct 2023</span>
                  <strong class="small">₱ 3.2M</strong>
                </div>
                <div class="monthly-bar-bg">
                  <div class="monthly-bar" style="width:55%;background:#2563eb"></div>
                </div>
              </div>
              <div class="monthly-row">
                <div class="d-flex justify-content-between mb-1">
                  <span class="small">Nov 2023 (Proportion)</span>
                  <strong class="small">₱ 5.8M</strong>
                </div>
                <div class="monthly-bar-bg">
                  <div class="monthly-bar" style="width:90%;background:#7b1113"></div>
                </div>
              </div>
              <div class="mt-4 text-center">
                <a href="#" class="btn btn-sm btn-outline-secondary w-100">View Detailed Cost Report</a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <BfpModal
      :show="showRequestModal"
      title="New Variation Order Request"
      stripe="VARIATION ORDER REQUEST"
      confirm-text="Submit Request"
      confirm-icon="send"
      @close="showRequestModal = false"
      @confirm="showRequestModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">assignment_add</i> Request Details</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">VO Number <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">tag</i>
              <input class="bfp-input" type="text" placeholder="e.g. VO-2024-001" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Project Reference <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">folder_open</i>
              <input class="bfp-input" type="text" placeholder="BFP-R2-2024-INFRA-001" />
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Scope Change Description</label>
            <div class="bfp-input-wrap">
              <textarea class="bfp-input bfp-textarea" rows="3" placeholder="Describe additional works, deductions, or design changes"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">payments</i> Cost & Review</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Cost Impact</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="text" placeholder="PHP amount" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Review Stage</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">fact_check</i>
              <select class="bfp-input bfp-select">
                <option>Draft</option>
                <option>Submitted</option>
                <option>Under Review</option>
                <option>Approved</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showFilterModal"
      title="Variation Order Filters"
      stripe="VO SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showFilterModal = false"
      @confirm="showFilterModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">tune</i> Status & Stage</div>
        <div class="bfp-filter-grid">
          <label class="bfp-check-option"><input type="checkbox" /> Draft</label>
          <label class="bfp-check-option"><input type="checkbox" /> Submitted</label>
          <label class="bfp-check-option"><input type="checkbox" /> Under Review</label>
          <label class="bfp-check-option"><input type="checkbox" /> Approved</label>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">date_range</i> Date & Amount</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Requested From</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Minimum Cost Impact</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="number" placeholder="0" />
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
  name: "VariationOrders",
  components: { StatusBadge, BfpModal },
  data() {
    return {
      showRequestModal: false,
      showFilterModal: false,
      orders: [
        {
          id: 1,
          order_num: "VO-2023-081",
          project_name: "Tuguegarao Fire Stn Phase 2",
          project_ref: "BFP-R2-2023-INFRA-012",
          description: "Additional site clearing and...",
          amount: "₱850,000.00",
          status: "approved",
          date_requested: "Oct 12, 2023",
          date_approved: "Oct 28, 2023",
          date_status: ""
        },
        {
          id: 2,
          order_num: "VO-2023-094",
          project_name: "Regional Headquarters Refurbishment",
          project_ref: "BFP-R2-2023-INFRA-005",
          description: "Electrical load upgrades an...",
          amount: "₱1,245,500.00",
          status: "under_review",
          date_requested: "Nov 05, 2023",
          date_approved: "-",
          date_status: "In Progress"
        },
        {
          id: 3,
          order_num: "VO-2023-099",
          project_name: "Cauayan City Fire Sub-station",
          project_ref: "BFP-R2-2023-INFRA-019",
          description: "Design modification ...",
          amount: "₱320,000.00",
          status: "draft",
          date_requested: "Nov 15, 2023",
          date_approved: "-",
          date_status: "Drafting"
        },
        {
          id: 4,
          order_num: "VO-2023-102",
          project_name: "Ilagan City Training Center",
          project_ref: "BFP-R2-2023-INFRA-008",
          description: "HVAC system overhaul due ...",
          amount: "₱2,150,000.00",
          status: "submitted",
          date_requested: "Nov 18, 2023",
          date_approved: "-",
          date_status: "Sent Nov 20"
        }
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

.summary-card .amount {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2633;
  margin: 0;
}

.summary-card .sub-label {
  font-size: 0.75rem;
  color: #5a6270;
}

.summary-card .trend {
  font-size: 0.75rem;
  color: #4caf50;
}

.summary-card.warning .count { color: #fb8500; }
.summary-card.danger .count { color: #d32f2f; }
.summary-card.info .count { color: #0288d1; }

.summary-card.cost-card {
  background: #7b1113;
  text-align: left;
}

.budget-bar-wrap {
  background: rgba(255,255,255,0.25);
  border-radius: 4px;
  height: 6px;
  margin: 0.5rem 0;
}

.budget-bar {
  background: #f5c518;
  height: 6px;
  width: 75%;
  border-radius: 4px;
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

.table { font-size: 0.875rem; }

.vo-num { color: #7b1113; }

.proj-name { font-weight: 600; font-size: 0.85rem; }
.proj-ref { font-size: 0.75rem; color: #888; }

.date-req { font-size: 0.8rem; font-weight: 500; }
.date-app { font-size: 0.75rem; color: #555; }
.date-app.muted { color: #aaa; font-style: italic; }

.approval-pipeline { margin: 1rem 0; }

.pipeline-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #888;
  margin-bottom: 0.4rem;
}

.pipeline-track {
  position: relative;
  height: 6px;
  background: #e0e5ee;
  border-radius: 4px;
}

.pipeline-fill {
  position: absolute;
  left: 0;
  width: 72%;
  height: 100%;
  background: #d32f2f;
  border-radius: 4px;
}

.pipeline-dot {
  position: absolute;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #fff;
  border: 2px solid #ccc;
}

.pipeline-dot.active {
  border-color: #fb8500;
  background: #fb8500;
}

.pipeline-dot.done {
  border-color: #4caf50;
  background: #4caf50;
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
