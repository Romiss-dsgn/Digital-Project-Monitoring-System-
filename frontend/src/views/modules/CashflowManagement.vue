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
            <p class="amount">{{ formatCurrency(summary?.planned_total || 0) }}</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="overview-card warning">
            <h6>Actual Expenditure</h6>
            <p class="amount">{{ formatCurrency(summary?.actual_total || 0) }}</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="overview-card success">
            <h6>Remaining Budget</h6>
            <p class="amount">{{ formatCurrency(summary?.remaining_total || 0) }}</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
          <div class="overview-card danger">
            <h6>Variance</h6>
            <p class="amount">{{ formatCurrency(summary?.variance_total || 0) }}</p>
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
                      <td><strong>{{ invoice.invoice_number }}</strong></td>
                      <td>{{ invoice.contract_number }}</td>
                      <td>{{ formatCurrency(invoice.invoice_amount) }}</td>
                      <td>{{ invoice.billing_period }}</td>
                      <td><status-badge :status="invoice.status" /></td>
<td class="align-middle text-end">
                        <div class="dropdown d-inline">
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
                            <li v-if="invoice.status === 'Pending'">
                              <a class="dropdown-item" href="#" @click.prevent="verifyInvoice(invoice)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon">verified</i>
                                Verify
                              </a>
                            </li>
                            <li v-if="invoice.status === 'For Review'">
                              <a class="dropdown-item" href="#" @click.prevent="approveInvoice(invoice)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon">check_circle</i>
                                Approve
                              </a>
                            </li>
                            <li v-if="invoice.status === 'Approved'">
                              <a class="dropdown-item" href="#" @click.prevent="markPaid(invoice)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon">payments</i>
                                Mark as Paid
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
                    <tr v-if="invoices.length === 0">
                      <td colspan="6" class="text-center py-4">
                        <span class="text-secondary">No invoices found</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div v-if="pagination.total > 0" class="d-flex justify-content-between align-items-center mt-3 px-2">
                <span class="text-secondary small">Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} Invoices</span>
                <nav>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                      <a class="page-link" href="#" @click.prevent="loadInvoices(pagination.current_page - 1)">&laquo;</a>
                    </li>
                    <li v-for="page in paginationPages" :key="page" class="page-item" :class="{ active: page === pagination.current_page }">
                      <a class="page-link" href="#" @click.prevent="loadInvoices(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                      <a class="page-link" href="#" @click.prevent="loadInvoices(pagination.current_page + 1)">&raquo;</a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

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
      @confirm="disburseFunds"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">payments</i> Disbursement Details</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Invoice <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">receipt_long</i>
<select class="bfp-input bfp-select" v-model="disbursement.invoice_id">
                <option value="">Select Invoice</option>
                <option v-for="invoice in invoices.filter(i => i.status === 'Approved' || i.status === 'Paid')" :key="invoice.id" :value="invoice.id">
                  {{ invoice.invoice_number }} - {{ formatCurrency(invoice.invoice_amount) }}
                </option>
              </select>
              <div v-if="invoices.filter(i => i.status === 'Approved' || i.status === 'Paid').length === 0" class="text-secondary small mt-1">No approved invoices available to disburse.</div>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Amount (PHP) <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="number" v-model="disbursement.amount" placeholder="0.00" step="0.01" min="0" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Payment Date <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" v-model="disbursement.payment_date" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Payment Method</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payment</i>
              <select class="bfp-input bfp-select" v-model="disbursement.payment_method">
                <option value="Check">Check</option>
                <option value="Direct Deposit">Direct Deposit</option>
                <option value="Cash">Cash</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Remarks</label>
            <div class="bfp-input-wrap">
              <textarea class="bfp-input bfp-textarea" rows="2" v-model="disbursement.remarks" placeholder="Optional remarks"></textarea>
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showAddInvoiceModal"
      :title="editingInvoice ? 'Edit Invoice' : 'Add Invoice'"
      stripe="INVOICE RECORD FORM"
      :confirm-text="editingInvoice ? 'Update Invoice' : 'Save Invoice'"
      confirm-icon="save"
      @close="showAddInvoiceModal = false"
      @confirm="editingInvoice ? updateInvoice() : createInvoice()"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">receipt_long</i> Invoice Information</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Invoice Number <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">tag</i>
              <input class="bfp-input" type="text" v-model="newInvoice.invoice_number" placeholder="INV-004" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Contract <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">assignment</i>
              <select class="bfp-input bfp-select" v-model="newInvoice.contract_id">
                <option value="">Select Contract</option>
                <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                  {{ contract.contract_number }}
                </option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Cashflow Period</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">date_range</i>
              <select class="bfp-input bfp-select" v-model="newInvoice.cashflow_period_id">
                <option value="">Select Period (Optional)</option>
                <option v-for="period in periods" :key="period.id" :value="period.id">
                  {{ period.period_label }}
                </option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Amount <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="number" v-model="newInvoice.invoice_amount" placeholder="PHP amount" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Invoice Date</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" v-model="newInvoice.invoice_date" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Due Date</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" v-model="newInvoice.due_date" />
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Billing Period</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">receipt_long</i>
              <input class="bfp-input" type="text" v-model="newInvoice.billing_period" placeholder="e.g. Jan-2024" />
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
import cashflowService from "@/services/cashflow.service";
import "bootstrap";

export default {
  name: "CashflowManagement",
  components: {
    StatusBadge,
    BfpModal
  },
  data() {
    return {
      showExportModal: false,
      showDisburseFundsModal: false,
      showAddInvoiceModal: false,
      exportFormat: "PDF",
      invoices: [],
      periods: [],
      contracts: [],
      summary: {},
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0,
      },
      permissions: {
        can_view: false,
        can_create: false,
        can_edit: false,
        can_delete: false,
        can_approve: false,
      },
      disbursement: {
        invoice_id: '',
        amount: 0,
        payment_date: '',
        payment_method: 'Check',
        remarks: '',
      },
      newInvoice: {
        invoice_number: "",
        contract_id: "",
        cashflow_period_id: "",
        invoice_amount: 0,
        invoice_date: "",
        due_date: "",
        billing_period: "",
      },
      editingInvoice: null,
      budgetChart: null
    };
  },
  computed: {
    paginationPages() {
      const pages = [];
      for (let i = 1; i <= this.pagination.last_page; i++) {
        pages.push(i);
      }
      return pages;
    }
  },
  mounted() {
    this.loadPermissions();
    this.loadSummary();
    this.loadInvoices(1);
    this.loadOptions();
    this.initBudgetChart();
  },
  beforeUnmount() {
    if (this.budgetChart) {
      this.budgetChart.destroy();
    }
  },
  methods: {
    async loadPermissions() {
      try {
        const options = await cashflowService.getOptions();
        this.permissions = options.permissions || this.permissions;
        this.contracts = options.contracts || [];
        this.periods = options.cashflow_periods || [];
      } catch (error) {
        console.error('Failed to load cashflow permissions:', error);
      }
    },

    async loadSummary() {
      try {
        this.summary = await cashflowService.getSummary();
        this.initBudgetChart();
      } catch (error) {
        console.error('Failed to load cashflow summary:', error);
      }
    },

    async loadOptions() {
      try {
        const options = await cashflowService.getOptions();
        this.contracts = options.contracts || [];
        this.periods = options.cashflow_periods || [];
      } catch (error) {
        console.error('Failed to load cashflow options:', error);
      }
    },

async loadInvoices(page = 1) {
      try {
        const response = await cashflowService.getInvoices({ page });
        this.invoices = response.data || [];
        this.pagination = response.meta || this.pagination;
      } catch (error) {
        console.error('Failed to load invoices:', error);
      }
    },

    initBudgetChart() {
      if (this.budgetChart) {
        this.budgetChart.destroy();
      }
      const ctx = document.getElementById("budgetChart")?.getContext("2d");
      if (!ctx) return;

      const monthlyData = this.summary?.monthly_breakdown || [];
      const labels = monthlyData.map(m => m.month) || ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
      const plannedData = monthlyData.map(m => m.planned) || [5000000, 4800000, 5200000, 4900000, 5100000, 4700000, 4800000, 5000000, 4600000, 4900000, 4700000, 4500000];
      const actualData = monthlyData.map(m => m.actual) || [3800000, 4100000, 4300000, 3700000, 4000000, 3500000, 3600000, 3900000, 3400000, 3800000, 3600000, 3300000];

      this.budgetChart = new Chart(ctx, {
        type: "bar",
        data: {
          labels: labels,
          datasets: [
            {
              label: "Planned Budget",
              data: plannedData,
              backgroundColor: "rgba(94, 114, 228, 0.85)",
              borderRadius: 4
            },
            {
              label: "Actual Expenditure",
              data: actualData,
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

    formatCurrency(value) {
      return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
      }).format(value || 0);
    },

    openDisburseFundsModal() {
      this.resetDisbursement();
      this.showDisburseFundsModal = true;
    },

    resetDisbursement() {
      this.disbursement = {
        invoice_id: '',
        amount: 0,
        payment_date: '',
        payment_method: 'Check',
        remarks: '',
      };
    },

    async disburseFunds() {
      try {
        if (!this.disbursement.invoice_id) {
          throw new Error('Please select an invoice');
        }
        await cashflowService.disburseFunds({
          invoice_id: this.disbursement.invoice_id,
          amount: this.disbursement.amount,
          payment_date: this.disbursement.payment_date,
          payment_method: this.disbursement.payment_method,
          remarks: this.disbursement.remarks,
        });
        this.showDisburseFundsModal = false;
        this.loadInvoices(1);
        this.loadSummary();
        alert('Disbursement recorded successfully');
      } catch (error) {
        console.error('Failed to disburse funds:', error);
        alert(error.response?.data?.message || 'Failed to record disbursement');
      }
    },

    async markPaid(invoice) {
      try {
        await cashflowService.updateInvoice(invoice.id, { status: 'Paid' });
        this.loadInvoices(this.pagination.current_page);
      } catch (error) {
        console.error('Failed to mark invoice as paid:', error);
      }
    },

    openAddInvoiceModal() {
      this.resetNewInvoice();
      this.showAddInvoiceModal = true;
    },

exportReport(format) {
      const labels = { pdf: "PDF", excel: "Excel", csv: "CSV" };
      this.exportFormat = labels[format] || "PDF";
      this.showExportModal = true;
    },

    async createInvoice() {
      try {
        if (!this.newInvoice.contract_id) {
          throw new Error('Please select a contract');
        }
        await cashflowService.createInvoice({
          invoice_number: this.newInvoice.invoice_number,
          contract_id: this.newInvoice.contract_id,
          cashflow_period_id: this.newInvoice.cashflow_period_id || null,
          invoice_amount: this.newInvoice.invoice_amount,
          invoice_date: this.newInvoice.invoice_date,
          due_date: this.newInvoice.due_date,
          billing_period: this.newInvoice.billing_period,
        });

        this.showAddInvoiceModal = false;
        this.loadInvoices(1);
        this.loadSummary();
        alert('Invoice created successfully');
      } catch (error) {
        console.error('Failed to create invoice:', error);
        alert(error.response?.data?.message || 'Failed to create invoice');
      }
    },

    async verifyInvoice(invoice) {
      try {
        await cashflowService.verifyInvoice(invoice.id);
        this.loadInvoices(this.pagination.current_page);
      } catch (error) {
        console.error('Failed to verify invoice:', error);
      }
    },

    async approveInvoice(invoice) {
      try {
        await cashflowService.approveInvoice(invoice.id);
        this.loadInvoices(this.pagination.current_page);
      } catch (error) {
        console.error('Failed to approve invoice:', error);
      }
    },

    async deleteInvoice(invoice) {
      if (confirm(`Are you sure you want to delete invoice ${invoice.invoice_number}?`)) {
        try {
          await cashflowService.deleteInvoice(invoice.id);
          this.loadInvoices(this.pagination.current_page);
        } catch (error) {
          console.error('Failed to delete invoice:', error);
        }
      }
    },

    viewInvoice(invoice) {
      alert(`View invoice ${invoice.invoice_number}`);
    },

    editInvoice(invoice) {
      this.editingInvoice = invoice;
      this.newInvoice = {
        invoice_number: invoice.invoice_number,
        contract_id: invoice.contract_id,
        cashflow_period_id: invoice.cashflow_period_id || "",
        invoice_amount: invoice.invoice_amount,
        invoice_date: invoice.invoice_date || "",
        due_date: invoice.due_date || "",
        billing_period: invoice.billing_period || "",
      };
      this.showAddInvoiceModal = true;
    },

    async updateInvoice() {
      if (!this.editingInvoice) return;
      try {
        await cashflowService.updateInvoice(this.editingInvoice.id, {
          invoice_number: this.newInvoice.invoice_number,
          contract_id: this.newInvoice.contract_id,
          cashflow_period_id: this.newInvoice.cashflow_period_id || null,
          invoice_amount: this.newInvoice.invoice_amount,
          invoice_date: this.newInvoice.invoice_date,
          due_date: this.newInvoice.due_date,
          billing_period: this.newInvoice.billing_period,
        });
        this.showAddInvoiceModal = false;
        this.editingInvoice = null;
        this.loadInvoices(this.pagination.current_page);
        this.loadSummary();
      } catch (error) {
        console.error('Failed to update invoice:', error);
      }
    },

    resetNewInvoice() {
      this.newInvoice = {
        invoice_number: "",
        contract_id: "",
        cashflow_period_id: "",
        invoice_amount: 0,
        invoice_date: "",
        due_date: "",
        billing_period: "",
      };
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