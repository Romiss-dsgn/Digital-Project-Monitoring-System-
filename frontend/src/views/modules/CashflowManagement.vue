<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <div v-if="apiError" class="alert alert-danger py-2 px-3 mb-3">{{ apiError }}</div>
      <!-- Header -->
      <div class="row mb-4 align-items-center gy-3">
        <div class="col-12 col-lg-6">
          <h4 class="mb-0">Cashflow Management</h4>
          <p class="text-secondary small">Monitor budget allocation and expenditures</p>
        </div>
        <div class="col-12 col-lg-6 d-flex flex-column flex-sm-row justify-content-lg-end align-items-stretch align-items-sm-center gap-2">
          <!-- Export Report Dropdown -->
          <div class="cashflow-actions-menu">
            <button
              class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
              type="button"
              :aria-expanded="showExportMenu"
              @click.stop="toggleExportMenu($event)"
            >
              <i class="material-icons-round" style="font-size: 1rem;">download</i>
              Export Report
              <i class="material-icons-round" style="font-size: 1rem;">expand_more</i>
            </button>
            <div
              v-if="showExportMenu"
              class="cashflow-action-menu cashflow-export-menu"
              :style="{
                top: `${exportMenuPosition.top}px`,
                left: `${exportMenuPosition.left}px`,
              }"
              role="menu"
              @click.stop
            >
              <button class="cashflow-action-menu-item" type="button" role="menuitem" @click="handleExportReport('pdf')">
                <i class="material-icons-round align-middle me-2 dropdown-icon" style="color: #1565C0;">picture_as_pdf</i>
                Export as PDF
              </button>
              <button class="cashflow-action-menu-item" type="button" role="menuitem" @click="handleExportReport('excel')">
                <i class="material-icons-round align-middle me-2 dropdown-icon" style="color: #2e7d32;">grid_on</i>
                Export as Excel
              </button>
              <button class="cashflow-action-menu-item" type="button" role="menuitem" @click="handleExportReport('csv')">
                <i class="material-icons-round align-middle me-2 dropdown-icon" style="color: #1565c0;">table_chart</i>
                Export as CSV
              </button>
            </div>
          </div>

          <!-- Disburse Funds Button -->
          <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 cashflow-action-trigger" @click="openDisburseFundsModal">
            <i class="material-icons-round" style="font-size: 1rem;">payments</i>
            Disburse Funds
          </button>
        </div>
      </div>

      <!-- Budget Overview Cards -->
      <div class="row mb-4">
        <div class="col-12 col-md-6 col-lg-3 mb-3">
          <div class="overview-card">
            <h6>Planned Budget</h6>
            <p class="amount">{{ formatCurrency(summary?.planned_total || 0) }}</p>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-3">
          <div class="overview-card warning">
            <h6>Actual Expenditure</h6>
            <p class="amount">{{ formatCurrency(summary?.actual_total || 0) }}</p>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-3">
          <div class="overview-card success">
            <h6>Remaining Budget</h6>
            <p class="amount">{{ formatCurrency(summary?.remaining_total || 0) }}</p>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3 mb-3">
          <div class="overview-card danger">
            <h6>Variance</h6>
            <p class="amount">{{ formatCurrency(summary?.variance_total || 0) }}</p>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-12 col-md-6 mb-3">
          <div class="overview-card">
            <h6>Revised Contract Amount</h6>
            <p class="amount">{{ formatCurrency(summary?.revised_contract_amount || 0) }}</p>
            <div class="text-secondary small mt-1">Approved VO impact reflected in the summary</div>
          </div>
        </div>
        <div class="col-12 col-md-6 mb-3">
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
            <div class="card-header pb-0 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
              <div>
                <h6 class="mb-0">Cashflow Periods</h6>
                <small class="text-secondary">Manage planned vs actual spending windows by contract</small>
              </div>
              <button class="btn btn-sm btn-primary d-flex align-items-center gap-1 cashflow-action-trigger" @click="openPeriodModal()">
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
                        <div class="d-inline-flex flex-wrap gap-2 justify-content-end">
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
            <div class="card-header pb-0 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2">
              <div>
                <h6 class="mb-0">Invoice Records & Payment Status</h6>
                <small class="text-secondary">
                  <span v-if="selectedCashflowPeriod">Filtered by {{ selectedCashflowPeriod.period_label }}</span>
                  <span v-else>Showing all invoices</span>
                </small>
              </div>
              <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 w-100 w-lg-auto">
                <select class="form-select form-select-sm cashflow-period-select" v-model="selectedPeriodId" @change="loadInvoices(selectedPeriodId)">
                  <option value="">All Periods</option>
                  <option v-for="period in periods" :key="period.id" :value="period.id">
                    {{ period.period_label }}
                  </option>
                </select>
                <button class="btn btn-sm btn-primary d-flex align-items-center justify-content-center gap-1 cashflow-action-trigger flex-shrink-0" style="white-space: nowrap;" @click="openAddInvoiceModal">
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
                      <th>Previous Accomplishment (%)</th>
                      <th>Accomplishment Today (%)</th>
                      <th>Payment Schedule</th>
                      <th>Payment Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="invoice in invoices" :key="invoice.id">
                      <td><strong>{{ invoice.invoice_number }}</strong></td>
                      <td>{{ invoice.contract_number }}</td>
                      <td>{{ invoice.previous_accomplishment_percent !== null ? invoice.previous_accomplishment_percent + '%' : 'N/A' }}</td>
                      <td>{{ invoice.accomplishment_today_percent !== null ? invoice.accomplishment_today_percent + '%' : 'N/A' }}</td>
                      <td>{{ invoice.billing_period }}</td>
                      <td><status-badge :status="invoice.status" /></td>
                      <td class="align-middle text-end cashflow-actions-cell">
                        <div class="cashflow-actions-menu">
                          <button
                            class="btn btn-sm btn-icon btn-light text-secondary cashflow-action-btn"
                            type="button"
                            :aria-expanded="openInvoiceActionMenuId === invoice.id"
                            title="Invoice actions"
                            @click.stop="toggleInvoiceActionMenu(invoice.id, $event)"
                          >
                            <i class="material-icons-round">more_vert</i>
                          </button>
                          <div
                            v-if="openInvoiceActionMenuId === invoice.id"
                            class="cashflow-action-menu"
                            :style="{
                              top: `${invoiceMenuPosition.top}px`,
                              left: `${invoiceMenuPosition.left}px`,
                            }"
                            role="menu"
                            @click.stop
                          >
                            <button class="cashflow-action-menu-item" type="button" role="menuitem" @click="handleViewInvoice(invoice)">
                              <i class="material-icons-round align-middle me-2 dropdown-icon view-icon">visibility</i>
                              View
                            </button>
                            <button class="cashflow-action-menu-item" type="button" role="menuitem" @click="handleEditInvoice(invoice)">
                              <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit</i>
                              Edit
                            </button>
                            <button
                              v-if="invoice.status === 'Pending'"
                              class="cashflow-action-menu-item"
                              type="button"
                              role="menuitem"
                              @click="handleVerifyInvoice(invoice)"
                            >
                              <i class="material-icons-round align-middle me-2 dropdown-icon">verified</i>
                              Verify
                            </button>
                            <button
                              v-if="invoice.status === 'For Review'"
                              class="cashflow-action-menu-item"
                              type="button"
                              role="menuitem"
                              @click="handleApproveInvoice(invoice)"
                            >
                              <i class="material-icons-round align-middle me-2 dropdown-icon">check_circle</i>
                              Approve
                            </button>
                            <button
                              v-if="invoice.status === 'Approved' && Number(invoice.remaining_balance || 0) > 0"
                              class="cashflow-action-menu-item"
                              type="button"
                              role="menuitem"
                              @click="handleMarkPaid(invoice)"
                            >
                              <i class="material-icons-round align-middle me-2 dropdown-icon">payments</i>
                              Mark as Paid
                            </button>
                            <button class="cashflow-action-menu-item danger" type="button" role="menuitem" @click="handleDeleteInvoice(invoice)">
                              <i class="material-icons-round align-middle me-2 dropdown-icon">delete</i>
                              Delete
                            </button>
                          </div>
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
              <div v-if="!selectedPeriodId && pagination.total > 0" class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mt-3 px-2">
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

    <TuaoModal
      :show="showExportModal"
      title="Export Cashflow Report"
      stripe="FINANCIAL REPORT EXPORT"
      :confirm-text="exportingReport ? 'Exporting...' : 'Export Report'"
      confirm-icon="download"
      @close="showExportModal = false"
      @confirm="confirmExportReport"
    >
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">ios_share</i> Export Settings</div>
        <div class="tuao-form-grid">
          <div class="tuao-field-half">
            <label class="tuao-label">Format</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">file_download</i>
              <select class="tuao-input tuao-select" v-model="exportFormat">
                <option>PDF</option>
                <option>Excel</option>
                <option>CSV</option>
              </select>
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Period</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">date_range</i>
              <select class="tuao-input tuao-select" v-model="exportPeriod">
                <option>Current Fiscal Year</option>
                <option>Current Quarter</option>
                <option>Month to Date</option>
              </select>
            </div>
          </div>
          <label class="tuao-check-option tuao-field-full"><input type="checkbox" v-model="exportIncludeChart" /> Include budget vs actual chart data</label>
        </div>
      </div>
    </TuaoModal>

    <TuaoModal
      :show="showDisburseFundsModal"
      :title="disbursementMode === 'mark-paid' ? 'Mark Invoice as Paid' : 'Disburse Funds'"
      stripe="PAYMENT RELEASE FORM"
      :confirm-text="disbursementMode === 'mark-paid' ? 'Mark as Paid' : 'Record Disbursement'"
      confirm-icon="payments"
      @close="showDisburseFundsModal = false"
      @confirm="disburseFunds"
    >
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">payments</i> Disbursement Details</div>
        <div class="tuao-form-grid">
          <div class="tuao-field-half">
            <label class="tuao-label">Invoice <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">receipt_long</i>
              <select class="tuao-input tuao-select" v-model="disbursement.invoice_id" :disabled="disbursementMode === 'mark-paid'">
                <option value="">Select Invoice</option>
                <option v-for="invoice in payableInvoices" :key="invoice.id" :value="invoice.id">
                  {{ invoice.invoice_number }} - {{ formatCurrency(invoice.remaining_balance) }} remaining
                </option>
              </select>
              <div v-if="payableInvoices.length === 0" class="text-secondary small mt-1">No payable invoices available to disburse.</div>
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Amount (PHP) <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">payments</i>
              <input class="tuao-input" type="number" v-model="disbursement.amount" placeholder="0.00" step="0.01" min="0" />
            </div>
            <div v-if="selectedPaymentInvoice" class="text-secondary small mt-1">
              Remaining balance: {{ formatCurrency(selectedPaymentInvoice.remaining_balance) }}
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Payment Date <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">event</i>
              <input class="tuao-input" type="date" v-model="disbursement.payment_date" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Payment Method</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">payment</i>
              <select class="tuao-input tuao-select" v-model="disbursement.payment_method">
                <option value="Check">Check</option>
                <option value="Direct Deposit">Direct Deposit</option>
                <option value="Cash">Cash</option>
              </select>
            </div>
          </div>
          <div class="tuao-field-full">
            <label class="tuao-label">Remarks</label>
            <div class="tuao-input-wrap">
              <textarea class="tuao-input tuao-textarea" rows="2" v-model="disbursement.remarks" placeholder="Optional remarks"></textarea>
            </div>
          </div>
        </div>
      </div>
    </TuaoModal>

    <TuaoModal
      :show="showPeriodModal"
      :title="editingPeriod ? 'Edit Cashflow Period' : 'Add Cashflow Period'"
      stripe="CASHFLOW PERIOD FORM"
      :confirm-text="editingPeriod ? 'Update Period' : 'Save Period'"
      confirm-icon="save"
      @close="showPeriodModal = false"
      @confirm="savePeriod"
    >
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">date_range</i> Period Information</div>
        <div class="tuao-form-grid">
          <div class="tuao-field-half">
            <label class="tuao-label">Contract <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">assignment</i>
              <select class="tuao-input tuao-select" v-model="newPeriod.contract_id">
                <option value="">Select Contract</option>
                <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                  {{ contract.contract_number }}
                </option>
              </select>
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Period Label <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">tag</i>
              <input class="tuao-input" type="text" v-model="newPeriod.period_label" placeholder="e.g. LGU-TUAO-CON-2026-001 - Mobilization" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Start Date</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">event</i>
              <input class="tuao-input" type="date" v-model="newPeriod.period_start" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">End Date</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">event</i>
              <input class="tuao-input" type="date" v-model="newPeriod.period_end" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Planned Amount <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">payments</i>
              <input class="tuao-input" type="number" v-model="newPeriod.planned_amount" placeholder="0.00" step="0.01" min="0" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Status</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">flag</i>
              <select class="tuao-input tuao-select" v-model="newPeriod.status">
                <option v-for="status in periodStatuses" :key="status" :value="status">
                  {{ status }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </TuaoModal>

    <TuaoModal
      :show="showAddInvoiceModal"
      :title="editingInvoice ? 'Edit Invoice' : 'Add Invoice'"
      stripe="INVOICE RECORD FORM"
      :confirm-text="editingInvoice ? 'Update Invoice' : 'Save Invoice'"
      confirm-icon="save"
      @close="showAddInvoiceModal = false"
      @confirm="editingInvoice ? updateInvoice() : createInvoice()"
    >
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">receipt_long</i> Invoice Information</div>
        <div class="tuao-form-grid">
          <div class="tuao-field-half">
            <label class="tuao-label">Invoice Number <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">tag</i>
              <input class="tuao-input" type="text" v-model="newInvoice.invoice_number" placeholder="INV-004" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Contract <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">assignment</i>
              <select class="tuao-input tuao-select" v-model="newInvoice.contract_id">
                <option value="">Select Contract</option>
                <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                  {{ contract.contract_number }}
                </option>
              </select>
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Cashflow Period</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">date_range</i>
              <select class="tuao-input tuao-select" v-model="newInvoice.cashflow_period_id">
                <option value="">Select Period (Optional)</option>
                <option v-for="period in periods" :key="period.id" :value="period.id">
                  {{ period.period_label }}
                </option>
              </select>
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Amount <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">payments</i>
              <input class="tuao-input" type="number" v-model="newInvoice.invoice_amount" placeholder="PHP amount" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Previous Accomplishment (%)</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">trending_up</i>
              <input class="tuao-input" type="number" min="0" max="100" step="0.01" v-model="newInvoice.previous_accomplishment_percent" placeholder="0.00" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Accomplishment Today (%)</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">trending_up</i>
              <input class="tuao-input" type="number" min="0" max="100" step="0.01" v-model="newInvoice.accomplishment_today_percent" placeholder="0.00" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Invoice Date</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">event</i>
              <input class="tuao-input" type="date" v-model="newInvoice.invoice_date" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Due Date</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">event</i>
              <input class="tuao-input" type="date" v-model="newInvoice.due_date" />
            </div>
          </div>
          <div class="tuao-field-full">
            <label class="tuao-label">Billing Period</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">receipt_long</i>
              <input class="tuao-input" type="text" v-model="newInvoice.billing_period" placeholder="e.g. Jan-2024" />
            </div>
          </div>
        </div>
      </div>
    </TuaoModal>

    <TuaoModal
      :show="showInvoiceDetailModal"
      title="Invoice Details"
      stripe="INVOICE DETAIL VIEW"
      :show-footer="false"
      width="760px"
      @close="closeInvoiceDetailModal"
    >
      <div v-if="selectedInvoice" class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">receipt_long</i> Invoice Overview</div>
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

      <div v-if="selectedInvoice" class="tuao-section">
        <div v-if="permissions.can_create" class="document-upload-panel mb-3">
          <div class="tuao-section-label"><i class="material-icons-round">cloud_upload</i> Upload Invoice Document</div>
          <div class="tuao-form-grid">
            <div class="tuao-field-full">
              <label class="tuao-label">File <span class="tuao-required">*</span></label>
              <div class="tuao-input-wrap">
                <input
                  ref="invoiceDocumentFileInput"
                  class="tuao-input"
                  type="file"
                  accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png"
                  @change="handleInvoiceDocumentFileChange"
                />
              </div>
            </div>
            <div class="tuao-field-full">
              <label class="tuao-label">Remarks</label>
              <div class="tuao-input-wrap">
                <textarea
                  class="tuao-input tuao-textarea"
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

        <div class="tuao-section-label"><i class="material-icons-round">description</i> Invoice Documents</div>
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
    </TuaoModal>
  </div>
</template>

<script>
import StatusBadge from "@/components/StatusBadge.vue";
import TuaoModal from "@/components/TuaoModal.vue";
import Chart from "chart.js/auto";
import cashflowService from "@/services/cashflow.service";
import "bootstrap";

export default {
  name: "CashflowManagement",
  components: {
    StatusBadge,
    TuaoModal
  },
  data() {
      return {
      showExportModal: false,
      showExportMenu: false,
      exportMenuPosition: {
        top: 0,
        left: 0,
      },
      openInvoiceActionMenuId: null,
      invoiceMenuPosition: {
        top: 0,
        left: 0,
      },
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
      exportPeriod: "Current Fiscal Year",
      exportIncludeChart: true,
      exportingReport: false,
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
      apiError: "",
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
        previous_accomplishment_percent: null,
        accomplishment_today_percent: null,
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
    document.addEventListener("click", this.closeActionMenus);
    window.addEventListener("resize", this.closeActionMenus);
    window.addEventListener("scroll", this.closeActionMenus, true);
  },
  beforeUnmount() {
    if (this.budgetChart) {
      this.budgetChart.destroy();
    }
    document.removeEventListener("click", this.closeActionMenus);
    window.removeEventListener("resize", this.closeActionMenus);
    window.removeEventListener("scroll", this.closeActionMenus, true);
  },
  methods: {
    setApiError(error, fallback, notify = false) {
      const message = error?.response?.data?.message || error.message || fallback;
      this.apiError = message;
      if (notify) {
        alert(message);
      }
      return message;
    },

    toggleExportMenu(event) {
      if (this.showExportMenu) {
        this.closeActionMenus();
        return;
      }

      this.positionFloatingMenu(event.currentTarget, "exportMenuPosition", 190, 132);
      this.openInvoiceActionMenuId = null;
      this.showExportMenu = true;
    },

    toggleInvoiceActionMenu(invoiceId, event) {
      if (this.openInvoiceActionMenuId === invoiceId) {
        this.closeActionMenus();
        return;
      }

      this.positionFloatingMenu(event.currentTarget, "invoiceMenuPosition", 180, 260);
      this.showExportMenu = false;
      this.openInvoiceActionMenuId = invoiceId;
    },

    positionFloatingMenu(trigger, positionKey, menuWidth, menuHeight) {
      const rect = trigger.getBoundingClientRect();
      const margin = 8;
      const viewportPadding = 8;
      const left = Math.max(
        viewportPadding,
        Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - viewportPadding)
      );
      const opensUp = rect.bottom + menuHeight + margin > window.innerHeight;

      this[positionKey] = {
        top: opensUp ? Math.max(viewportPadding, rect.top - menuHeight - margin) : rect.bottom + margin,
        left,
      };
    },

    closeActionMenus() {
      this.showExportMenu = false;
      this.openInvoiceActionMenuId = null;
    },

    handleExportReport(format) {
      this.closeActionMenus();
      this.exportReport(format);
    },

    handleViewInvoice(invoice) {
      this.closeActionMenus();
      this.viewInvoice(invoice);
    },

    handleEditInvoice(invoice) {
      this.closeActionMenus();
      this.editInvoice(invoice);
    },

    handleVerifyInvoice(invoice) {
      this.closeActionMenus();
      this.verifyInvoice(invoice);
    },

    handleApproveInvoice(invoice) {
      this.closeActionMenus();
      this.approveInvoice(invoice);
    },

    handleMarkPaid(invoice) {
      this.closeActionMenus();
      this.markPaid(invoice);
    },

    handleDeleteInvoice(invoice) {
      this.closeActionMenus();
      this.deleteInvoice(invoice);
    },

    async loadPermissions() {
      try {
        const options = await cashflowService.getOptions();
        this.permissions = options.permissions || this.permissions;
        this.contracts = options.contracts || [];
        this.apiError = "";
      } catch (error) {
        this.setApiError(error, 'Failed to load cashflow permissions');
      }
    },

    async loadPeriods() {
      try {
        const response = await cashflowService.getCashflowPeriods({ per_page: 100 });
        this.periods = response.data || [];
        this.apiError = "";
      } catch (error) {
        this.setApiError(error, 'Failed to load cashflow periods');
      }
    },

    async loadSummary() {
      try {
        this.summary = await cashflowService.getSummary();
        this.initBudgetChart();
        this.apiError = "";
      } catch (error) {
        this.setApiError(error, 'Failed to load cashflow summary');
      }
    },

    async loadInvoices(pageOrPeriod = 1) {
      this.closeActionMenus();
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
        this.apiError = "";
      } catch (error) {
        this.setApiError(error, 'Failed to load invoices');
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
        this.setApiError(error, 'Failed to save cashflow period', true);
      }
    },

    async editPeriod(period) {
      try {
        const detail = await cashflowService.getCashflowPeriod(period.id);
        this.openPeriodModal(detail || period);
      } catch (error) {
        this.setApiError(error, 'Failed to load cashflow period detail');
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
        this.setApiError(error, 'Failed to archive cashflow period', true);
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
        this.setApiError(error, 'Failed to record disbursement', true);
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
        this.setApiError(error, 'Failed to load invoice details', true);
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
        this.setApiError(error, 'Failed to download document', true);
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
        this.setApiError(error, 'Failed to upload document', true);
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

    async confirmExportReport() {
      if (this.exportingReport) {
        return;
      }

      this.exportingReport = true;
      try {
        const range = this.getExportDateRange(this.exportPeriod);

        // Pull the full invoice list (not just the current page) for the export
        let allInvoices = this.invoices;
        try {
          const invoicesResponse = await cashflowService.getInvoices({ per_page: 1000 });
          allInvoices = invoicesResponse?.data || this.invoices;
        } catch (fetchError) {
          this.setApiError(fetchError, 'Unable to load full invoice list; exporting currently loaded invoices only.');
        }

        const filteredInvoices = this.filterByDateRange(allInvoices, 'invoice_date', range);
        const filteredPeriods = this.filterPeriodsByRange(this.periods, range);
        const filenameBase = `cashflow-report-${this.todayString()}`;
        const format = (this.exportFormat || 'PDF').toLowerCase();

        if (format === 'csv') {
          this.downloadCsvReport(filteredPeriods, filteredInvoices, filenameBase);
        } else if (format === 'excel') {
          this.downloadExcelReport(filteredPeriods, filteredInvoices, filenameBase);
        } else {
          this.openPdfReportPreview(filteredPeriods, filteredInvoices, range, filenameBase);
        }

        this.showExportModal = false;
      } catch (error) {
        this.setApiError(error, 'Failed to export report', true);
      } finally {
        this.exportingReport = false;
      }
    },

    getExportDateRange(periodLabel) {
      const now = new Date();
      const end = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59);
      let start;

      if (periodLabel === 'Current Quarter') {
        const quarterStartMonth = Math.floor(now.getMonth() / 3) * 3;
        start = new Date(now.getFullYear(), quarterStartMonth, 1);
      } else if (periodLabel === 'Month to Date') {
        start = new Date(now.getFullYear(), now.getMonth(), 1);
      } else {
        start = new Date(now.getFullYear(), 0, 1);
      }

      return { start, end };
    },

    filterByDateRange(items, dateField, range) {
      return (items || []).filter((item) => {
        if (!item[dateField]) return false;
        const date = new Date(item[dateField]);
        if (Number.isNaN(date.getTime())) return false;
        return date >= range.start && date <= range.end;
      });
    },

    filterPeriodsByRange(periods, range) {
      return (periods || []).filter((period) => {
        if (!period.period_start) return true;
        const start = new Date(period.period_start);
        const end = period.period_end ? new Date(period.period_end) : start;
        return start <= range.end && end >= range.start;
      });
    },

    csvEscape(value) {
      const str = String(value ?? '');
      if (/[",\n]/.test(str)) {
        return `"${str.replace(/"/g, '""')}"`;
      }
      return str;
    },

    buildReportCsvContent(periods, invoices) {
      const lines = [];

      lines.push('Cashflow Summary');
      lines.push(`Planned Budget,${this.summary?.planned_total || 0}`);
      lines.push(`Actual Expenditure,${this.summary?.actual_total || 0}`);
      lines.push(`Remaining Budget,${this.summary?.remaining_total || 0}`);
      lines.push(`Variance,${this.summary?.variance_total || 0}`);
      lines.push(`Revised Contract Amount,${this.summary?.revised_contract_amount || 0}`);
      lines.push(`Budget Status,${this.csvEscape(this.summary?.budget_status || 'Within Budget')}`);
      lines.push('');

      if (this.exportIncludeChart && this.summary?.monthly_breakdown?.length) {
        lines.push('Budget vs Actual (Monthly)');
        lines.push('Month,Planned,Actual');
        this.summary.monthly_breakdown.forEach((m) => {
          lines.push(`${this.csvEscape(m.month)},${m.planned || 0},${m.actual || 0}`);
        });
        lines.push('');
      }

      lines.push('Cashflow Periods');
      lines.push('Period,Contract,Start,End,Planned,Actual,Variance,Budget Status,Status');
      periods.forEach((p) => {
        lines.push([
          this.csvEscape(p.period_label),
          this.csvEscape(p.contract_number),
          p.period_start || '',
          p.period_end || '',
          p.planned_amount || 0,
          p.actual_amount || 0,
          p.variance || 0,
          this.csvEscape(p.budget_status || ''),
          this.csvEscape(p.status || ''),
        ].join(','));
      });
      lines.push('');

      lines.push('Invoices');
      lines.push('Invoice #,Contract,Amount,Billing Period,Status');
      invoices.forEach((inv) => {
        lines.push([
          this.csvEscape(inv.invoice_number),
          this.csvEscape(inv.contract_number),
          inv.invoice_amount || 0,
          this.csvEscape(inv.billing_period || ''),
          this.csvEscape(inv.status || ''),
        ].join(','));
      });

      return lines.join('\n');
    },

    downloadCsvReport(periods, invoices, filenameBase) {
      const csvContent = this.buildReportCsvContent(periods, invoices);
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      this.triggerDownload(blob, `${filenameBase}.csv`);
    },

    downloadExcelReport(periods, invoices, filenameBase) {
      const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

      let rows = '';
      rows += '<tr><td colspan="5"><b>Cashflow Summary</b></td></tr>';
      rows += `<tr><td>Planned Budget</td><td>${this.summary?.planned_total || 0}</td></tr>`;
      rows += `<tr><td>Actual Expenditure</td><td>${this.summary?.actual_total || 0}</td></tr>`;
      rows += `<tr><td>Remaining Budget</td><td>${this.summary?.remaining_total || 0}</td></tr>`;
      rows += `<tr><td>Variance</td><td>${this.summary?.variance_total || 0}</td></tr>`;
      rows += `<tr><td>Revised Contract Amount</td><td>${this.summary?.revised_contract_amount || 0}</td></tr>`;
      rows += `<tr><td>Budget Status</td><td>${escapeHtml(this.summary?.budget_status || 'Within Budget')}</td></tr>`;
      rows += '<tr><td>&nbsp;</td></tr>';

      if (this.exportIncludeChart && this.summary?.monthly_breakdown?.length) {
        rows += '<tr><td colspan="5"><b>Budget vs Actual (Monthly)</b></td></tr>';
        rows += '<tr><th>Month</th><th>Planned</th><th>Actual</th></tr>';
        this.summary.monthly_breakdown.forEach((m) => {
          rows += `<tr><td>${escapeHtml(m.month)}</td><td>${m.planned || 0}</td><td>${m.actual || 0}</td></tr>`;
        });
        rows += '<tr><td>&nbsp;</td></tr>';
      }

      rows += '<tr><td colspan="9"><b>Cashflow Periods</b></td></tr>';
      rows += '<tr><th>Period</th><th>Contract</th><th>Start</th><th>End</th><th>Planned</th><th>Actual</th><th>Variance</th><th>Budget Status</th><th>Status</th></tr>';
      periods.forEach((p) => {
        rows += `<tr><td>${escapeHtml(p.period_label)}</td><td>${escapeHtml(p.contract_number)}</td><td>${p.period_start || ''}</td><td>${p.period_end || ''}</td><td>${p.planned_amount || 0}</td><td>${p.actual_amount || 0}</td><td>${p.variance || 0}</td><td>${escapeHtml(p.budget_status || '')}</td><td>${escapeHtml(p.status || '')}</td></tr>`;
      });
      rows += '<tr><td>&nbsp;</td></tr>';

      rows += '<tr><td colspan="5"><b>Invoices</b></td></tr>';
      rows += '<tr><th>Invoice #</th><th>Contract</th><th>Amount</th><th>Billing Period</th><th>Status</th></tr>';
      invoices.forEach((inv) => {
        rows += `<tr><td>${escapeHtml(inv.invoice_number)}</td><td>${escapeHtml(inv.contract_number)}</td><td>${inv.invoice_amount || 0}</td><td>${escapeHtml(inv.billing_period || '')}</td><td>${escapeHtml(inv.status || '')}</td></tr>`;
      });

      const html = `<html><head><meta charset="UTF-8"></head><body><table border="1">${rows}</table></body></html>`;
      const blob = new Blob(['\ufeff', html], { type: 'application/vnd.ms-excel' });
      this.triggerDownload(blob, `${filenameBase}.xls`);
    },

    openPdfReportPreview(periods, invoices, range, filenameBase) {
      const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

      const periodRows = periods.map((p) => `
        <tr>
          <td>${escapeHtml(p.period_label)}</td>
          <td>${escapeHtml(p.contract_number)}</td>
          <td>${p.period_start || 'N/A'} to ${p.period_end || 'N/A'}</td>
          <td>${this.formatCurrency(p.planned_amount)}</td>
          <td>${this.formatCurrency(p.actual_amount)}</td>
          <td>${this.formatCurrency(p.variance)}</td>
          <td>${escapeHtml(p.budget_status || '')}</td>
          <td>${escapeHtml(p.status || '')}</td>
        </tr>
      `).join('');

      const invoiceRows = invoices.map((inv) => `
        <tr>
          <td>${escapeHtml(inv.invoice_number)}</td>
          <td>${escapeHtml(inv.contract_number)}</td>
          <td>${this.formatCurrency(inv.invoice_amount)}</td>
          <td>${escapeHtml(inv.billing_period || '')}</td>
          <td>${escapeHtml(inv.status || '')}</td>
        </tr>
      `).join('');

      const chartSection = this.exportIncludeChart && this.summary?.monthly_breakdown?.length ? `
        <h3>Budget vs Actual (Monthly)</h3>
        <table>
          <thead><tr><th>Month</th><th>Planned</th><th>Actual</th></tr></thead>
          <tbody>
            ${this.summary.monthly_breakdown.map((m) => `
              <tr><td>${escapeHtml(m.month)}</td><td>${this.formatCurrency(m.planned)}</td><td>${this.formatCurrency(m.actual)}</td></tr>
            `).join('')}
          </tbody>
        </table>
      ` : '';

      const html = `
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="UTF-8">
          <title>Cashflow Report - ${filenameBase}</title>
          <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #1f2633; background: #eef1f5; }
            .toolbar {
              position: sticky;
              top: 0;
              z-index: 10;
              display: flex;
              justify-content: space-between;
              align-items: center;
              background: #1f2633;
              color: #fff;
              padding: 12px 24px;
            }
            .toolbar span { font-size: 14px; font-weight: 600; }
            .toolbar-actions { display: flex; gap: 10px; }
            .toolbar button {
              border: none;
              border-radius: 6px;
              padding: 8px 16px;
              font-size: 13px;
              font-weight: 600;
              cursor: pointer;
            }
            .btn-download { background: #1565C0; color: #fff; }
            .btn-close { background: #4b5563; color: #fff; }
            .report-sheet {
              max-width: 900px;
              margin: 24px auto;
              background: #fff;
              padding: 32px;
              border-radius: 8px;
              box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            }
            h1 { font-size: 20px; margin-bottom: 4px; }
            h2 { font-size: 14px; color: #5a6270; margin-top: 0; font-weight: normal; }
            h3 { font-size: 15px; margin-top: 28px; }
            table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
            th, td { border: 1px solid #d0d5dd; padding: 6px 8px; text-align: left; }
            th { background: #f3f4f6; }
            .summary-grid { display: flex; gap: 16px; margin-top: 16px; flex-wrap: wrap; }
            .summary-card { border: 1px solid #d0d5dd; border-radius: 8px; padding: 10px 14px; min-width: 150px; }
            .summary-card span { display: block; font-size: 11px; color: #5a6270; text-transform: uppercase; }
            .summary-card strong { font-size: 16px; }
            @media print {
              .toolbar { display: none; }
              body { background: #fff; }
              .report-sheet { box-shadow: none; margin: 0; max-width: 100%; }
            }
          </style>
        </head>
        <body>
          <div class="toolbar">
            <span>Cashflow Report Preview</span>
            <div class="toolbar-actions">
              <button class="btn-download" onclick="window.print()">Download as PDF</button>
              <button class="btn-close" onclick="window.close()">Close</button>
            </div>
          </div>

          <div class="report-sheet">
            <h1>ConTrackPro — Cashflow Report</h1>
            <h2>LGU Tuao · Generated ${new Date().toLocaleString('en-US')} · Range: ${range.start.toLocaleDateString('en-US')} to ${range.end.toLocaleDateString('en-US')}</h2>

            <div class="summary-grid">
              <div class="summary-card"><span>Planned Budget</span><strong>${this.formatCurrency(this.summary?.planned_total)}</strong></div>
              <div class="summary-card"><span>Actual Expenditure</span><strong>${this.formatCurrency(this.summary?.actual_total)}</strong></div>
              <div class="summary-card"><span>Remaining Budget</span><strong>${this.formatCurrency(this.summary?.remaining_total)}</strong></div>
              <div class="summary-card"><span>Variance</span><strong>${this.formatCurrency(this.summary?.variance_total)}</strong></div>
              <div class="summary-card"><span>Revised Contract Amount</span><strong>${this.formatCurrency(this.summary?.revised_contract_amount)}</strong></div>
              <div class="summary-card"><span>Budget Status</span><strong>${escapeHtml(this.summary?.budget_status || 'Within Budget')}</strong></div>
            </div>

            ${chartSection}

            <h3>Cashflow Periods</h3>
            <table>
              <thead><tr><th>Period</th><th>Contract</th><th>Dates</th><th>Planned</th><th>Actual</th><th>Variance</th><th>Budget Status</th><th>Status</th></tr></thead>
              <tbody>${periodRows || '<tr><td colspan="8">No cashflow periods found</td></tr>'}</tbody>
            </table>

            <h3>Invoices</h3>
            <table>
              <thead><tr><th>Invoice #</th><th>Contract</th><th>Amount</th><th>Billing Period</th><th>Status</th></tr></thead>
              <tbody>${invoiceRows || '<tr><td colspan="5">No invoices found</td></tr>'}</tbody>
            </table>
          </div>
        </body>
        </html>
      `;

      const previewWindow = window.open('', '_blank');
      if (!previewWindow) {
        alert('Please allow pop-ups to preview the report.');
        return;
      }
      previewWindow.document.open();
      previewWindow.document.write(html);
      previewWindow.document.close();
    },

    triggerDownload(blob, filename) {
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
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
          previous_accomplishment_percent: this.newInvoice.previous_accomplishment_percent,
          accomplishment_today_percent: this.newInvoice.accomplishment_today_percent,
          invoice_date: this.newInvoice.invoice_date,
          due_date: this.newInvoice.due_date,
          billing_period: this.newInvoice.billing_period,
        });

        this.showAddInvoiceModal = false;
        this.loadInvoices(1);
        this.loadSummary();
        alert('Invoice created successfully');
      } catch (error) {
        this.setApiError(error, 'Failed to create invoice', true);
      }
    },

    async verifyInvoice(invoice) {
      try {
        await cashflowService.verifyInvoice(invoice.id);
        this.loadInvoices(this.pagination.current_page);
      } catch (error) {
        this.setApiError(error, 'Failed to verify invoice', true);
      }
    },

    async approveInvoice(invoice) {
      try {
        await cashflowService.approveInvoice(invoice.id);
        this.loadInvoices(this.pagination.current_page);
      } catch (error) {
        this.setApiError(error, 'Failed to approve invoice', true);
      }
    },

    async deleteInvoice(invoice) {
      if (confirm(`Are you sure you want to delete invoice ${invoice.invoice_number}?`)) {
        try {
          await cashflowService.deleteInvoice(invoice.id);
          this.loadInvoices(this.pagination.current_page);
        } catch (error) {
          this.setApiError(error, 'Failed to delete invoice', true);
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
        previous_accomplishment_percent: invoice.previous_accomplishment_percent,
        accomplishment_today_percent: invoice.accomplishment_today_percent,
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
          previous_accomplishment_percent: this.newInvoice.previous_accomplishment_percent,
          accomplishment_today_percent: this.newInvoice.accomplishment_today_percent,
          invoice_date: this.newInvoice.invoice_date,
          due_date: this.newInvoice.due_date,
          billing_period: this.newInvoice.billing_period,
        });
        this.showAddInvoiceModal = false;
        this.editingInvoice = null;
        this.loadInvoices(this.pagination.current_page);
        this.loadSummary();
      } catch (error) {
        this.setApiError(error, 'Failed to update invoice', true);
      }
    },

    resetNewInvoice() {
      this.newInvoice = {
        invoice_number: "",
        contract_id: "",
        cashflow_period_id: "",
        invoice_amount: 0,
        previous_accomplishment_percent: null,
        accomplishment_today_percent: null,
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

.cashflow-actions-cell {
  overflow: visible;
}

.cashflow-actions-menu {
  display: inline-flex;
  justify-content: flex-end;
}

.cashflow-action-btn {
  position: relative;
  z-index: 1;
}

.cashflow-action-menu {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
  display: flex;
  flex-direction: column;
  min-width: 180px;
  padding: 6px;
  position: fixed;
  z-index: 10060;
}

.cashflow-export-menu {
  min-width: 190px;
}

.cashflow-action-menu-item {
  align-items: center;
  background: transparent;
  border: 0;
  border-radius: 8px;
  color: #374151;
  display: flex;
  font-size: 0.84rem;
  font-weight: 700;
  gap: 8px;
  padding: 9px 10px;
  text-align: left;
  width: 100%;
}

.cashflow-action-menu-item:hover {
  background: #f3f4f6;
}

.cashflow-action-menu-item.danger {
  color: #dc2626;
}

.cashflow-action-menu-item.danger:hover {
  background: #fef2f2;
}

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
  color: #1565C0;
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

  .cashflow-actions-menu,
  .cashflow-action-trigger,
  .cashflow-period-select {
    width: 100%;
  }

  .cashflow-actions-menu {
    position: relative;
  }

  .cashflow-actions-menu > button {
    width: 100%;
    justify-content: center;
  }

  .cashflow-action-menu {
    min-width: 160px;
    max-width: calc(100vw - 1.5rem);
  }

  .cashflow-action-btn {
    width: 32px;
    height: 32px;
  }

  .overview-card {
    padding: 1.25rem;
  }
}
</style>
