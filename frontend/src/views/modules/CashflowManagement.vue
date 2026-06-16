<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-6">
          <h4 class="mb-0">Cashflow Management</h4>
          <p class="text-secondary small">Monitor budget allocation and expenditures</p>
        </div>
        <div class="col-lg-6 d-flex justify-content-end align-items-center gap-2">
          <!-- New Project Button -->
          <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" @click="openNewProjectModal">
            <i class="material-icons-round" style="font-size: 1rem;">add</i>
            New Project
          </button>

          <!-- Export Report Dropdown -->
          <div class="dropdown">
            <button
              class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="material-icons-round" style="font-size: 1rem;">download</i>
              Export Report
              <i class="material-icons-round" style="font-size: 1rem;">expand_more</i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li>
                <a class="dropdown-item" href="#" @click.prevent="exportReport('pdf')">
                  <i class="material-icons-round align-middle me-2 dropdown-icon" style="color: #e53935;">picture_as_pdf</i>
                  Export as PDF
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="#" @click.prevent="exportReport('excel')">
                  <i class="material-icons-round align-middle me-2 dropdown-icon" style="color: #2e7d32;">grid_on</i>
                  Export as Excel
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="#" @click.prevent="exportReport('csv')">
                  <i class="material-icons-round align-middle me-2 dropdown-icon" style="color: #1565c0;">table_chart</i>
                  Export as CSV
                </a>
              </li>
            </ul>
          </div>

          <!-- Disburse Funds Button -->
          <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" @click="openDisburseFundsModal">
            <i class="material-icons-round" style="font-size: 1rem;">payments</i>
            Disburse Funds
          </button>
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
              <canvas id="budgetChart" height="120"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Invoices & Payments -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
              <h6 class="mb-0">Invoice Records & Payment Status</h6>
              <button class="btn btn-sm btn-primary d-flex align-items-center gap-1" @click="openAddInvoiceModal">
                <i class="material-icons-round" style="font-size: 1rem;">add</i>
                Add Invoice
              </button>
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
                              <a class="dropdown-item" href="#" @click.prevent="viewInvoice(invoice)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon view-icon">visibility</i>
                                View
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="editInvoice(invoice)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit</i>
                                Edit
                              </a>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                              <a class="dropdown-item text-danger" href="#" @click.prevent="deleteInvoice(invoice)">
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

    <BfpModal
      :show="showNewProjectModal"
      title="New Cashflow Project"
      stripe="BUDGET PROJECT REGISTRATION"
      confirm-text="Create Project"
      confirm-icon="save"
      @close="showNewProjectModal = false"
      @confirm="showNewProjectModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">folder_open</i> Project Budget Profile</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Project Code <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">tag</i>
              <input class="bfp-input" type="text" placeholder="REG-II-001" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Funding Source</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">account_balance</i>
              <select class="bfp-input bfp-select">
                <option>GAA</option>
                <option>Trust Fund</option>
                <option>LGU Counterpart</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Project Name <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">business</i>
              <input class="bfp-input" type="text" placeholder="Official project name" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Planned Budget</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="text" placeholder="PHP amount" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Fiscal Year</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">calendar_month</i>
              <input class="bfp-input" type="number" placeholder="2024" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showExportModal"
      title="Export Cashflow Report"
      stripe="FINANCIAL REPORT EXPORT"
      confirm-text="Export Report"
      confirm-icon="download"
      @close="showExportModal = false"
      @confirm="showExportModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">ios_share</i> Export Settings</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Format</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">file_download</i>
              <select class="bfp-input bfp-select" v-model="exportFormat">
                <option>PDF</option>
                <option>Excel</option>
                <option>CSV</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Period</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">date_range</i>
              <select class="bfp-input bfp-select">
                <option>Current Fiscal Year</option>
                <option>Current Quarter</option>
                <option>Month to Date</option>
              </select>
            </div>
          </div>
          <label class="bfp-check-option bfp-field-full"><input type="checkbox" checked /> Include budget vs actual chart data</label>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showDisburseFundsModal"
      title="Disburse Funds"
      stripe="PAYMENT RELEASE FORM"
      confirm-text="Record Disbursement"
      confirm-icon="payments"
      @close="showDisburseFundsModal = false"
      @confirm="showDisburseFundsModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">payments</i> Disbursement Details</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Contract / Invoice</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">receipt_long</i>
              <input class="bfp-input" type="text" placeholder="INV or contract number" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Amount</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="text" placeholder="PHP amount" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Release Date</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Payment Status</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">verified</i>
              <select class="bfp-input bfp-select">
                <option>Pending</option>
                <option>Paid</option>
                <option>For Review</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showAddInvoiceModal"
      title="Add Invoice"
      stripe="INVOICE RECORD FORM"
      confirm-text="Save Invoice"
      confirm-icon="save"
      @close="showAddInvoiceModal = false"
      @confirm="showAddInvoiceModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">receipt_long</i> Invoice Information</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Invoice Number <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">tag</i>
              <input class="bfp-input" type="text" placeholder="INV-004" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Contract</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">assignment</i>
              <input class="bfp-input" type="text" placeholder="REG-II-003" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Amount</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="text" placeholder="PHP amount" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Payment Schedule</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" />
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
import Chart from "chart.js/auto";

export default {
  name: "CashflowManagement",
  components: {
    StatusBadge,
    BfpModal
  },
  data() {
    return {
      showNewProjectModal: false,
      showExportModal: false,
      showDisburseFundsModal: false,
      showAddInvoiceModal: false,
      exportFormat: "PDF",
      invoices: [
        { id: 1, invoice_num: "INV-001", contract: "REG-II-001", amount: "₱2,500,000.00", schedule: "Jan-2023", payment_status: "paid" },
        { id: 2, invoice_num: "INV-002", contract: "REG-II-002", amount: "₱250,000.00", schedule: "05/01/2023", payment_status: "paid" },
        { id: 3, invoice_num: "INV-003", contract: "REG-II-003", amount: "₱250,000.00", schedule: "05/15/2023", payment_status: "pending" },
        { id: 4, invoice_num: "INV-003", contract: "REG-II-003", amount: "₱200,000.00", schedule: "06/18/2023", payment_status: "pending" }
      ],
      budgetChart: null
    };
  },
  mounted() {
    this.initBudgetChart();
  },
  beforeUnmount() {
    if (this.budgetChart) {
      this.budgetChart.destroy();
    }
  },
  methods: {
    initBudgetChart() {
      const ctx = document.getElementById("budgetChart").getContext("2d");
      this.budgetChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
          datasets: [
            {
              label: "Planned Budget",
              data: [5000000, 4800000, 5200000, 4900000, 5100000, 4700000, 4800000, 5000000, 4600000, 4900000, 4700000, 4500000],
              backgroundColor: "rgba(94, 114, 228, 0.85)",
              borderRadius: 4
            },
            {
              label: "Actual Expenditure",
              data: [3800000, 4100000, 4300000, 3700000, 4000000, 3500000, 3600000, 3900000, 3400000, 3800000, 3600000, 3300000],
              backgroundColor: "rgba(251, 133, 0, 0.85)",
              borderRadius: 4
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              position: "top",
              align: "end",
              labels: {
                boxWidth: 12,
                boxHeight: 12,
                borderRadius: 3,
                usePointStyle: false,
                padding: 16,
                font: { size: 12 }
              }
            },
            tooltip: {
              callbacks: {
                label: (context) => {
                  const value = context.parsed.y;
                  return ` ${context.dataset.label}: ₱${(value / 1000000).toFixed(1)}M`;
                }
              }
            }
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { font: { size: 12 } }
            },
            y: {
              grid: { color: "rgba(0,0,0,0.05)" },
              ticks: {
                font: { size: 12 },
                callback: (value) => `₱${(value / 1000000).toFixed(0)}M`
              }
            }
          }
        }
      });
    },
    openNewProjectModal() {
      this.showNewProjectModal = true;
    },
    exportReport(format) {
      const labels = { pdf: "PDF", excel: "Excel", csv: "CSV" };
      this.exportFormat = labels[format] || "PDF";
      this.showExportModal = true;
    },
    openDisburseFundsModal() {
      this.showDisburseFundsModal = true;
    },
    openAddInvoiceModal() {
      this.showAddInvoiceModal = true;
    },
    viewInvoice(invoice) {
      alert(`View invoice ${invoice.invoice_num}`);
    },
    editInvoice(invoice) {
      alert(`Edit invoice ${invoice.invoice_num}`);
    },
    deleteInvoice(invoice) {
      alert(`Delete invoice ${invoice.invoice_num}`);
    }
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

.table {
  font-size: 0.875rem;
}
</style>
