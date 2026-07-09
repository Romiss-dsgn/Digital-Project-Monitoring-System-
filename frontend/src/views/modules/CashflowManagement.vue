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

      <div class="row mb-4">
        <div class="col-lg-6 col-md-6 mb-3">
          <div class="overview-card">
            <h6>Revised Contract Amount</h6>
            <p class="amount">{{ formatCurrency(summary?.revised_contract_amount || 0) }}</p>
            <div class="text-secondary small mt-1">Approved VO impact reflected in the summary</div>
          </div>
        </div>
        <div class="col-lg-6 col-md-6 mb-3">
          <div class="overview-card">
            <h6>Budget Status</h6>
            <div class="mt-2">
              <status-badge :status="summary?.budget_status || 'Within Budget'" />
            </div>
            <div class="text-secondary small mt-2">Derived from planned vs actual totals with a 1% tolerance band</div>
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

      <!-- Cashflow Tabs -->
      <div class="row mb-3">
        <div class="col-12">
          <div class="card p-2">
            <div class="d-flex flex-wrap gap-2">
              <button class="btn btn-sm" :class="activeTab === 'invoices' ? 'btn-primary' : 'btn-outline-secondary'" @click="activeTab = 'invoices'">
                Invoices
              </button>
              <button class="btn btn-sm" :class="activeTab === 'periods' ? 'btn-primary' : 'btn-outline-secondary'" @click="activeTab = 'periods'">
                Cashflow Periods
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Cashflow Periods -->
      <div v-if="activeTab === 'periods'" class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
              <div>
                <h6 class="mb-0">Cashflow Periods</h6>
                <small class="text-secondary">Manage planned vs actual spending windows by contract</small>
              </div>
              <button class="btn btn-sm btn-primary d-flex align-items-center gap-1" @click="openPeriodModal()">
                <i class="material-icons-round" style="font-size: 1rem;">add</i>
                Add Period
              </button>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Period</th>
                      <th>Contract</th>
                      <th>Dates</th>
                      <th>Planned</th>
                      <th>Actual</th>
                      <th>Variance</th>
                      <th>Budget Status</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="period in periods" :key="period.id">
                      <td>
                        <strong>{{ period.period_label }}</strong>
                      </td>
                      <td>{{ period.contract_number }}</td>
                      <td>
                        <div>{{ period.period_start || 'N/A' }} to {{ period.period_end || 'N/A' }}</div>
                      </td>
                      <td>{{ formatCurrency(period.planned_amount) }}</td>
                      <td>{{ formatCurrency(period.actual_amount) }}</td>
                      <td :class="Number(period.variance || 0) < 0 ? 'text-danger' : 'text-success'">
                        {{ formatCurrency(period.variance) }}
                      </td>
                      <td><status-badge :status="period.budget_status || 'Within Budget'" /></td>
                      <td><status-badge :status="period.status" /></td>
                      <td class="align-middle text-end">
                        <div class="d-inline-flex gap-2">
                          <button class="btn btn-sm btn-outline-secondary" @click="editPeriod(period)">
                            Edit
                          </button>
                          <button class="btn btn-sm btn-outline-danger" @click="archivePeriod(period)">
                            Archive
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="periods.length === 0">
                      <td colspan="9" class="text-center py-4">
                        <span class="text-secondary">No cashflow periods found</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Invoices -->
      <div v-else class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
              <div>
                <h6 class="mb-0">Invoice Records & Payment Status</h6>
                <small class="text-secondary">
                  <span v-if="selectedCashflowPeriod">Filtered by {{ selectedCashflowPeriod.period_label }}</span>
                  <span v-else>Showing all invoices</span>
                </small>
              </div>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <select class="form-select form-select-sm" style="min-width: 220px;" v-model="selectedPeriodId" @change="loadInvoices(selectedPeriodId)">
                  <option value="">All Periods</option>
                  <option v-for="period in periods" :key="period.id" :value="period.id">
                    {{ period.period_label }}
                  </option>
                </select>
                <button class="btn btn-sm btn-primary d-flex align-items-center gap-1" @click="openAddInvoiceModal">
                  <i class="material-icons-round" style="font-size: 1rem;">add</i>
                  Add Invoice
                </button>
              </div>
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
                            <li v-if="invoice.status === 'Approved' && Number(invoice.remaining_balance || 0) > 0">
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
              <div v-if="!selectedPeriodId && pagination.total > 0" class="d-flex justify-content-between align-items-center mt-3 px-2">
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
      :title="disbursementMode === 'mark-paid' ? 'Mark Invoice as Paid' : 'Disburse Funds'"
      stripe="PAYMENT RELEASE FORM"
      :confirm-text="disbursementMode === 'mark-paid' ? 'Mark as Paid' : 'Record Disbursement'"
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
              <select class="bfp-input bfp-select" v-model="disbursement.invoice_id" :disabled="disbursementMode === 'mark-paid'">
                <option value="">Select Invoice</option>
                <option v-for="invoice in payableInvoices" :key="invoice.id" :value="invoice.id">
                  {{ invoice.invoice_number }} - {{ formatCurrency(invoice.remaining_balance) }} remaining
                </option>
              </select>
              <div v-if="payableInvoices.length === 0" class="text-secondary small mt-1">No payable invoices available to disburse.</div>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Amount (PHP) <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="number" v-model="disbursement.amount" placeholder="0.00" step="0.01" min="0" />
            </div>
            <div v-if="selectedPaymentInvoice" class="text-secondary small mt-1">
              Remaining balance: {{ formatCurrency(selectedPaymentInvoice.remaining_balance) }}
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
      :show="showPeriodModal"
      :title="editingPeriod ? 'Edit Cashflow Period' : 'Add Cashflow Period'"
      stripe="CASHFLOW PERIOD FORM"
      :confirm-text="editingPeriod ? 'Update Period' : 'Save Period'"
      confirm-icon="save"
      @close="showPeriodModal = false"
      @confirm="savePeriod"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">date_range</i> Period Information</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Contract <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">assignment</i>
              <select class="bfp-input bfp-select" v-model="newPeriod.contract_id">
                <option value="">Select Contract</option>
                <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                  {{ contract.contract_number }}
                </option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Period Label <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">tag</i>
              <input class="bfp-input" type="text" v-model="newPeriod.period_label" placeholder="e.g. BFP-R2-CON-2024-001 - 2026-01" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Start Date</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" v-model="newPeriod.period_start" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">End Date</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" v-model="newPeriod.period_end" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Planned Amount <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="number" v-model="newPeriod.planned_amount" placeholder="0.00" step="0.01" min="0" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Status</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">flag</i>
              <select class="bfp-input bfp-select" v-model="newPeriod.status">
                <option v-for="status in periodStatuses" :key="status" :value="status">
                  {{ status }}
                </option>
              </select>
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

    <BfpModal
      :show="showInvoiceDetailModal"
      title="Invoice Details"
      stripe="INVOICE DETAIL VIEW"
      :show-footer="false"
      width="760px"
      @close="closeInvoiceDetailModal"
    >
      <div v-if="selectedInvoice" class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">receipt_long</i> Invoice Overview</div>
        <div class="invoice-detail-grid">
          <div class="detail-card">
            <span class="detail-label">Invoice #</span>
            <strong>{{ selectedInvoice.invoice_number }}</strong>
          </div>
          <div class="detail-card">
            <span class="detail-label">Contract</span>
            <strong>{{ selectedInvoice.contract_number || 'N/A' }}</strong>
          </div>
          <div class="detail-card">
            <span class="detail-label">Status</span>
            <strong>{{ selectedInvoice.status }}</strong>
          </div>
          <div class="detail-card">
            <span class="detail-label">Amount</span>
            <strong>{{ formatCurrency(selectedInvoice.invoice_amount) }}</strong>
          </div>
          <div class="detail-card">
            <span class="detail-label">Paid</span>
            <strong>{{ formatCurrency(selectedInvoice.paid_amount) }}</strong>
          </div>
          <div class="detail-card">
            <span class="detail-label">Remaining</span>
            <strong>{{ formatCurrency(selectedInvoice.remaining_balance) }}</strong>
          </div>
          <div class="detail-card">
            <span class="detail-label">Invoice Date</span>
            <strong>{{ formatDisplayDate(selectedInvoice.invoice_date) }}</strong>
          </div>
          <div class="detail-card">
            <span class="detail-label">Due Date</span>
            <strong>{{ formatDisplayDate(selectedInvoice.due_date) }}</strong>
          </div>
          <div class="detail-card">
            <span class="detail-label">Billing Period</span>
            <strong>{{ selectedInvoice.billing_period || 'N/A' }}</strong>
          </div>
          <div class="detail-card detail-card-full">
            <span class="detail-label">Remarks</span>
            <strong>{{ selectedInvoice.remarks || 'No remarks' }}</strong>
          </div>
        </div>
      </div>

      <div v-if="selectedInvoice" class="bfp-section">
        <div v-if="permissions.can_create" class="document-upload-panel mb-3">
          <div class="bfp-section-label"><i class="material-icons-round">cloud_upload</i> Upload Invoice Document</div>
          <div class="bfp-form-grid">
            <div class="bfp-field-full">
              <label class="bfp-label">File <span class="bfp-required">*</span></label>
              <div class="bfp-input-wrap">
                <input
                  ref="invoiceDocumentFileInput"
                  class="bfp-input"
                  type="file"
                  accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png"
                  @change="handleInvoiceDocumentFileChange"
                />
              </div>
            </div>
            <div class="bfp-field-full">
              <label class="bfp-label">Remarks</label>
              <div class="bfp-input-wrap">
                <textarea
                  class="bfp-input bfp-textarea"
                  rows="2"
                  v-model="invoiceDocumentUpload.remarks"
                  placeholder="Optional remarks"
                ></textarea>
              </div>
            </div>
          </div>
          <div class="mt-3 d-flex justify-content-end">
            <button
              type="button"
              class="btn btn-sm btn-primary"
              :disabled="uploadingInvoiceDocument || !invoiceDocumentUpload.file"
              @click="uploadInvoiceDocument"
            >
              <span v-if="uploadingInvoiceDocument" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <i v-else class="material-icons-round me-1" style="font-size: 1rem; vertical-align: middle;">upload</i>
              {{ uploadingInvoiceDocument ? 'Uploading...' : 'Upload Document' }}
            </button>
          </div>
        </div>

        <div class="bfp-section-label"><i class="material-icons-round">description</i> Invoice Documents</div>
        <div v-if="selectedInvoice.documents && selectedInvoice.documents.length" class="document-list">
          <div v-for="document in selectedInvoice.documents" :key="document.id" class="document-row">
            <div class="document-info">
              <i class="material-icons-round">attach_file</i>
              <div>
                <div class="document-title">{{ document.document_title || document.file_name }}</div>
                <div class="document-meta">
                  {{ document.file_type || 'File' }} · Uploaded by {{ document.uploaded_by || 'Unknown' }} on {{ formatDisplayDateTime(document.uploaded_at) }}
                </div>
              </div>
            </div>
            <button class="btn btn-sm btn-outline-primary" @click.prevent="downloadInvoiceDocument(document)">
              <i class="material-icons-round">download</i> Download
            </button>
          </div>
        </div>
        <div v-else class="text-secondary small text-center py-3">No documents uploaded</div>
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
      disbursementMode: "manual",
      activeTab: "invoices",
      showPeriodModal: false,
      editingPeriod: null,
      editingPeriodId: null,
      selectedPeriodId: "",
      showAddInvoiceModal: false,
      showInvoiceDetailModal: false,
      selectedInvoice: null,
      loadingInvoiceDetail: false,
      uploadingInvoiceDocument: false,
      invoiceDocumentUpload: {
        file: null,
        remarks: "",
      },
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
      periodStatuses: ["On Track", "At Risk", "Delayed", "Completed"],
      newPeriod: {
        id: null,
        contract_id: "",
        period_label: "",
        period_start: "",
        period_end: "",
        planned_amount: 0,
        status: "On Track",
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
    },

    payableInvoices() {
      return this.invoices.filter((invoice) => {
        const remainingBalance = Number(invoice.remaining_balance || 0);
        return remainingBalance > 0 && (invoice.status === 'Approved' || invoice.status === 'Paid');
      });
    },

    selectedPaymentInvoice() {
      return this.invoices.find((invoice) => String(invoice.id) === String(this.disbursement.invoice_id)) || null;
    },

    selectedCashflowPeriod() {
      return this.periods.find((period) => String(period.id) === String(this.selectedPeriodId)) || null;
    }
  },
  mounted() {
    this.loadPermissions();
    this.loadSummary();
    this.loadPeriods();
    this.loadInvoices(1);
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
      } catch (error) {
        console.error('Failed to load cashflow permissions:', error);
      }
    },

    async loadPeriods() {
      try {
        const response = await cashflowService.getCashflowPeriods({ per_page: 100 });
        this.periods = response.data || [];
      } catch (error) {
        console.error('Failed to load cashflow periods:', error);
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

    async loadInvoices(pageOrPeriod = 1) {
      try {
        if (pageOrPeriod && typeof pageOrPeriod !== 'number' && pageOrPeriod !== '') {
          const invoices = await cashflowService.getPeriodInvoices(pageOrPeriod);
          this.invoices = invoices || [];
          this.pagination = {
            current_page: 1,
            last_page: 1,
            per_page: invoices?.length || 0,
            total: invoices?.length || 0,
            from: invoices?.length ? 1 : 0,
            to: invoices?.length || 0,
          };
          return;
        }

        if (this.selectedPeriodId) {
          const invoices = await cashflowService.getPeriodInvoices(this.selectedPeriodId);
          this.invoices = invoices || [];
          this.pagination = {
            current_page: 1,
            last_page: 1,
            per_page: invoices?.length || 0,
            total: invoices?.length || 0,
            from: invoices?.length ? 1 : 0,
            to: invoices?.length || 0,
          };
          return;
        }

        const page = Number.isInteger(pageOrPeriod) && pageOrPeriod > 0 ? pageOrPeriod : 1;
        const response = await cashflowService.getInvoices({ page });
        this.invoices = response.data || [];
        this.pagination = response.meta || this.pagination;
      } catch (error) {
        console.error('Failed to load invoices:', error);
      }
    },

    openPeriodModal(period = null) {
      this.resetPeriodForm();
      if (period) {
        const normalizedPeriod = {
          id: period.id ?? null,
          contract_id: period.contract_id ?? "",
          period_label: period.period_label ?? "",
          period_start: period.period_start || "",
          period_end: period.period_end || "",
          planned_amount: period.planned_amount ?? 0,
          status: period.status || "On Track",
        };

        this.editingPeriod = { ...normalizedPeriod };
        this.editingPeriodId = normalizedPeriod.id;
        Object.assign(this.newPeriod, normalizedPeriod);
        console.log('MODAL FORM STATE:', this.newPeriod);
      }
      this.showPeriodModal = true;
    },

    resetPeriodForm() {
      this.editingPeriod = null;
      this.editingPeriodId = null;
      Object.assign(this.newPeriod, {
        id: null,
        contract_id: "",
        period_label: "",
        period_start: "",
        period_end: "",
        planned_amount: 0,
        status: "On Track",
      });
    },

    async savePeriod() {
      try {
        if (!this.newPeriod.contract_id) {
          throw new Error('Please select a contract');
        }
        if (!this.newPeriod.period_label) {
          throw new Error('Please enter a period label');
        }

        const payload = {
          contract_id: this.newPeriod.contract_id,
          period_label: this.newPeriod.period_label,
          period_start: this.newPeriod.period_start || null,
          period_end: this.newPeriod.period_end || null,
          planned_amount: this.newPeriod.planned_amount,
        };

        const isEditing = this.editingPeriod !== null;
        const periodId = this.newPeriod.id ?? this.editingPeriodId ?? this.editingPeriod?.id ?? null;
        console.log('SAVE PERIOD - form state:', this.newPeriod, 'ID specifically:', this.newPeriod.id);

        if (isEditing && (periodId === null || periodId === undefined || periodId === '')) {
          throw new Error('Unable to update cashflow period because the period ID is missing.');
        }

        if (isEditing) {
          payload.status = this.newPeriod.status;
          await cashflowService.updateCashflowPeriod(periodId, payload);
        } else {
          await cashflowService.createCashflowPeriod(payload);
        }

        this.showPeriodModal = false;
        this.resetPeriodForm();
        await this.loadPeriods();
        await this.loadSummary();

        if (this.selectedPeriodId) {
          await this.loadInvoices(this.selectedPeriodId);
        } else {
          await this.loadInvoices(1);
        }
      } catch (error) {
        console.error('Failed to save cashflow period:', error);
        alert(error.response?.data?.message || 'Failed to save cashflow period');
      }
    },

    async editPeriod(period) {
      try {
        console.log('EDIT CLICKED - period data:', period);
        const detail = await cashflowService.getCashflowPeriod(period.id);
        this.openPeriodModal(detail || period);
      } catch (error) {
        console.error('Failed to load cashflow period detail:', error);
        this.openPeriodModal(period);
      }
    },

    async archivePeriod(period) {
      if (!confirm(`Archive period ${period.period_label}?`)) {
        return;
      }

      try {
        await cashflowService.deleteCashflowPeriod(period.id);
        await this.loadPeriods();
        await this.loadSummary();
        if (String(this.selectedPeriodId) === String(period.id)) {
          this.selectedPeriodId = '';
          await this.loadInvoices(1);
        }
      } catch (error) {
        console.error('Failed to archive cashflow period:', error);
        alert(error.response?.data?.message || 'Failed to archive cashflow period');
      }
    },

    initBudgetChart() {
      if (this.budgetChart) {
        this.budgetChart.destroy();
      }
      const ctx = document.getElementById("budgetChart")?.getContext("2d");
      if (!ctx) return;

      const monthlyData = this.summary?.monthly_breakdown || [];
      const labels = monthlyData.map((m) => m.month);
      const plannedData = monthlyData.map((m) => m.planned);
      const actualData = monthlyData.map((m) => m.actual);

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

    todayString() {
      const now = new Date();
      const year = now.getFullYear();
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },

    openDisburseFundsModal(invoice = null) {
      this.resetDisbursement();
      this.disbursementMode = invoice ? 'mark-paid' : 'manual';
      if (invoice) {
        this.disbursement.invoice_id = invoice.id;
        this.disbursement.amount = Number(invoice.remaining_balance || 0);
        this.disbursement.payment_date = this.todayString();
        this.disbursement.payment_method = 'Check';
      }
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
      this.disbursementMode = 'manual';
    },

    async disburseFunds() {
      try {
        if (!this.disbursement.invoice_id) {
          throw new Error('Please select an invoice');
        }
        if (Number(this.disbursement.amount) <= 0) {
          throw new Error('Please enter a payment amount');
        }
        await cashflowService.disburseFunds({
          invoice_id: this.disbursement.invoice_id,
          amount: this.disbursement.amount,
          payment_date: this.disbursement.payment_date,
          payment_method: this.disbursement.payment_method,
          remarks: this.disbursement.remarks,
        });
        this.showDisburseFundsModal = false;
        this.loadInvoices(this.pagination.current_page);
        this.loadSummary();
        alert(this.disbursementMode === 'mark-paid' ? 'Invoice marked as paid successfully' : 'Disbursement recorded successfully');
      } catch (error) {
        console.error('Failed to disburse funds:', error);
        alert(error.response?.data?.message || 'Failed to record disbursement');
      }
    },

    async markPaid(invoice) {
      if (Number(invoice.remaining_balance || 0) <= 0) {
        alert('This invoice is already fully paid.');
        return;
      }
      this.openDisburseFundsModal(invoice);
    },

    formatDisplayDate(value) {
      if (!value) return 'N/A';
      const date = new Date(value);
      if (Number.isNaN(date.getTime())) return value;
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
      });
    },

    formatDisplayDateTime(value) {
      if (!value) return 'N/A';
      const date = new Date(value);
      if (Number.isNaN(date.getTime())) return value;
      return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
      });
    },

    async viewInvoice(invoice) {
      try {
        this.loadingInvoiceDetail = true;
        const detail = await cashflowService.getInvoice(invoice.id);
        this.selectedInvoice = detail || invoice;
        this.showInvoiceDetailModal = true;
      } catch (error) {
        console.error('Failed to load invoice details:', error);
        alert(error.response?.data?.message || 'Failed to load invoice details');
      } finally {
        this.loadingInvoiceDetail = false;
      }
    },

    closeInvoiceDetailModal() {
      this.showInvoiceDetailModal = false;
      this.selectedInvoice = null;
      this.resetInvoiceDocumentUpload();
    },

    handleInvoiceDocumentFileChange(event) {
      this.invoiceDocumentUpload.file = event?.target?.files?.[0] || null;
    },

    resetInvoiceDocumentUpload() {
      this.invoiceDocumentUpload = {
        file: null,
        remarks: "",
      };

      if (this.$refs.invoiceDocumentFileInput) {
        this.$refs.invoiceDocumentFileInput.value = '';
      }
    },

    async downloadInvoiceDocument(document) {
      try {
        await cashflowService.downloadDocument(document);
      } catch (error) {
        console.error('Failed to download invoice document:', error);
        alert(error.response?.data?.message || 'Failed to download document');
      }
    },

    async uploadInvoiceDocument() {
      if (!this.selectedInvoice || !this.invoiceDocumentUpload.file) {
        return;
      }

      try {
        this.uploadingInvoiceDocument = true;
        await cashflowService.uploadDocument(
          this.selectedInvoice.id,
          this.invoiceDocumentUpload.file,
          this.invoiceDocumentUpload.remarks
        );

        const refreshedInvoice = await cashflowService.getInvoice(this.selectedInvoice.id);
        this.selectedInvoice = refreshedInvoice || this.selectedInvoice;
        this.resetInvoiceDocumentUpload();
      } catch (error) {
        console.error('Failed to upload invoice document:', error);
        alert(error.response?.data?.message || 'Failed to upload document');
      } finally {
        this.uploadingInvoiceDocument = false;
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

.invoice-detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.detail-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 12px 14px;
  background: #fafafa;
}

.detail-card-full {
  grid-column: 1 / -1;
}

.detail-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6b7280;
  margin-bottom: 6px;
}

.document-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.document-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  padding: 12px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
}

.document-info {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.document-info .material-icons-round {
  color: #c0392b;
  font-size: 20px;
  flex-shrink: 0;
}

.document-title {
  font-weight: 700;
  color: #111827;
}

.document-meta {
  font-size: 12px;
  color: #6b7280;
}

@media (max-width: 576px) {
  .invoice-detail-grid {
    grid-template-columns: 1fr;
  }

  .document-row {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
