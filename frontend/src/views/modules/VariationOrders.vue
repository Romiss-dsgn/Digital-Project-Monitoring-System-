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
            <p class="count" style="color:#1f2633">{{ summary?.total_vos || 0 }}</p>
            <span class="trend">+{{ calculateMonthlyGrowth() }} this month</span>
          </div>
        </div>
        <div class="col-lg col-md-6 mb-3">
          <div class="summary-card">
            <h6>Approved</h6>
            <p class="count">{{ summary?.approved_count || 0 }}</p>
            <span class="sub-label">Ready for payout</span>
          </div>
        </div>
        <div class="col-lg col-md-6 mb-3">
          <div class="summary-card warning">
            <h6>Pending</h6>
            <p class="count">{{ summary?.pending_count || 0 }}</p>
            <span class="sub-label">Awaiting signature</span>
          </div>
        </div>
        <div class="col-lg col-md-6 mb-3">
          <div class="summary-card danger">
            <h6>Rejected</h6>
            <p class="count">{{ summary?.rejected_count || 0 }}</p>
            <span class="sub-label">Needs revision</span>
          </div>
        </div>
        <div class="col-lg col-md-6 mb-3">
          <div class="summary-card cost-card">
            <h6 style="color:#fff;opacity:.85">Total Cost Impact</h6>
            <p class="amount" style="color:#fff;font-size:1.5rem">{{ formatCurrency(summary?.total_cost_impact || 0) }}</p>
            <div class="budget-bar-wrap">
              <div class="budget-bar" :style="{ width: calculateContingencyUtilization() + '%' }"></div>
            </div>
            <span class="sub-label" style="color:#fff;opacity:.8">{{ calculateContingencyUtilization() }}% of VO contingency budget utilized</span>
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
                  <button type="button" class="btn btn-dark" @click="filterStatus = ''">All</button>
                  <button type="button" class="btn btn-outline-secondary" @click="filterStatus = 'Submitted'">Requests</button>
                  <button type="button" class="btn btn-outline-secondary" @click="filterStatus = 'Under Review'">Approvals</button>
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
                      <th>Time Impact (Days)</th>
                      <th>Dates</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="order in orders" :key="order.id">
                      <td><strong class="vo-num">{{ order.vo_number }}</strong></td>
                      <td>
                        <div class="proj-name">{{ order.contract_title || order.project_name }}</div>
                        <div class="proj-ref">{{ order.contract_number || order.project_ref }}</div>
                      </td>
                      <td>{{ truncateText(order.description, 50) }}</td>
                      <td><strong>{{ formatCurrency(order.amount_change) }}</strong></td>
                      <td>{{ order.time_impact_days || '-' }}</td>
                      <td>
                        <div class="date-req">Req: {{ formatDate(order.submitted_at || order.created_at) }}</div>
                        <div class="date-app" v-if="order.approved_at">App: {{ formatDate(order.approved_at) }}</div>
                        <div class="date-app muted" v-else>{{ getStatusDateLabel(order) }}</div>
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
                             <li v-if="order.status === 'Draft'">
                               <a class="dropdown-item" :class="{ 'disabled-link': !permissions.can_create }" href="#" @click.prevent="submitOrder(order)">
                                 <i class="material-icons-round align-middle me-2 dropdown-icon">send</i>
                                 Submit
                               </a>
                             </li>
                             <li v-if="order.status === 'Submitted' || order.status === 'Under Review'">
                               <a class="dropdown-item" :class="{ 'disabled-link': !permissions.can_approve }" href="#" @click.prevent="openReviewModal(order)">
                                 <i class="material-icons-round align-middle me-2 dropdown-icon">rate_review</i>
                                 Review
                               </a>
                             </li>
                             <li v-if="order.status !== 'Draft' && order.status !== 'Approved' && order.status !== 'Rejected'">
                               <a class="dropdown-item" :class="{ 'disabled-link': !permissions.can_edit }" href="#" @click.prevent="editOrder(order)">
                                 <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit</i>
                                 Edit
                               </a>
                             </li>
                             <li><hr class="dropdown-divider" /></li>
                             <li>
                               <a class="dropdown-item text-danger" :class="{ 'disabled-link': !permissions.can_delete }" href="#" @click.prevent="archiveOrder(order)">
                                 <i class="material-icons-round align-middle me-2 dropdown-icon">archive</i>
                                 Archive
                               </a>
                             </li>
                           </ul>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="orders.length === 0">
                      <td colspan="8" class="text-center py-4">
                        <span class="text-secondary">No variation orders found</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div v-if="pagination.total > 0" class="d-flex justify-content-between align-items-center mt-3 px-2">
                <span class="text-secondary small">Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} Variation Orders</span>
                <nav>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                      <a class="page-link" href="#" @click.prevent="loadOrders(pagination.current_page - 1)">&laquo;</a>
                    </li>
                    <li v-for="page in paginationPages" :key="page" class="page-item" :class="{ active: page === pagination.current_page }">
                      <a class="page-link" href="#" @click.prevent="loadOrders(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                      <a class="page-link" href="#" @click.prevent="loadOrders(pagination.current_page + 1)">&raquo;</a>
                    </li>
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
                <strong>{{ summary?.average_approval_days || 0 }} Days</strong>
                <span class="text-success small" v-if="summary?.average_approval_days">-2.1 Days vs Last Quarter</span>
              </div>
              <div class="approval-pipeline">
                <div class="pipeline-labels">
                  <span>Draft</span>
                  <span>Submission</span>
                  <span>Evaluation</span>
                  <span>Final Approval</span>
                </div>
                <div class="pipeline-track">
                  <div class="pipeline-fill" :style="{ width: pipelineProgress + '%' }"></div>
                  <div class="pipeline-dot" style="left:0%"></div>
                  <div class="pipeline-dot" :style="{ left: '25%' }"></div>
                  <div class="pipeline-dot active" :style="{ left: '50%' }" v-if="summary?.status_distribution?.under_review > 0"></div>
                  <div class="pipeline-dot active" :style="{ left: '50%' }" v-else></div>
                  <div class="pipeline-dot done" :style="{ left: '75%' }" v-if="summary?.status_distribution?.approved > 0"></div>
                  <div class="pipeline-dot done" :style="{ left: '75%' }" v-else></div>
                </div>
              </div>
              <p class="text-secondary small mt-3 mb-0">
                {{ getBottleneckStage() }}
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
               <div v-for="(month, index) in monthlyBreakdown" :key="month.month + index" class="monthly-row mb-3">
                 <div class="d-flex justify-content-between mb-1">
                   <span class="small">{{ month.month }}</span>
                   <strong class="small">{{ formatCurrency(month.amount) }}</strong>
                 </div>
                 <div class="monthly-bar-bg">
                   <div class="monthly-bar" :style="{ width: month.percent + '%', background: index === 0 ? '#2563eb' : '#7b1113' }"></div>
                 </div>
               </div>
               <p v-if="monthlyBreakdown.length === 0" class="text-secondary small text-center mb-0">
                 No monthly data available
               </p>
               <div class="mt-4 text-center">
                 <a href="#" class="btn btn-sm btn-outline-secondary w-100">View Detailed Cost Report</a>
               </div>
             </div>
          </div>
        </div>
      </div>

    </div>

    <!-- New VO Request Modal -->
    <BfpModal
      :show="showRequestModal"
      :title="editingOrder ? 'Edit Variation Order' : 'New Variation Order Request'"
      stripe="VARIATION ORDER REQUEST"
      :confirm-text="editingOrder ? 'Update Order' : 'Submit Request'"
      confirm-icon="send"
      @close="showRequestModal = false"
      @confirm="editingOrder ? updateVariationOrder() : createVariationOrder()"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">assignment_add</i> Request Details</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">VO Number <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">tag</i>
              <input class="bfp-input" type="text" v-model="newOrder.vo_number" placeholder="e.g. VO-2024-001" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Contract <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">folder_open</i>
              <select class="bfp-input bfp-select" v-model="newOrder.contract_id">
                <option value="">Select Contract</option>
                <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                  {{ contract.contract_number }}
                </option>
              </select>
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Scope Change Description</label>
            <div class="bfp-input-wrap">
              <textarea class="bfp-input bfp-textarea" rows="3" v-model="newOrder.description" placeholder="Describe additional works, deductions, or design changes"></textarea>
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Reason</label>
            <div class="bfp-input-wrap">
              <textarea class="bfp-input bfp-textarea" rows="2" v-model="newOrder.reason" placeholder="Reason for variation order"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">payments</i> Cost & Time Impact</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Cost Impact <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="number" v-model="newOrder.amount_change" placeholder="PHP amount" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Time Impact (Days)</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">schedule</i>
              <input class="bfp-input" type="number" v-model="newOrder.time_impact_days" placeholder="Additional days" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <!-- Filter Modal -->
    <BfpModal
      :show="showFilterModal"
      title="Variation Order Filters"
      stripe="VO SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showFilterModal = false"
      @confirm="applyFilters"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">tune</i> Status & Stage</div>
        <div class="bfp-filter-grid">
          <label class="bfp-check-option"><input type="checkbox" v-model="filters.draft"> Draft</label>
          <label class="bfp-check-option"><input type="checkbox" v-model="filters.submitted"> Submitted</label>
          <label class="bfp-check-option"><input type="checkbox" v-model="filters.under_review"> Under Review</label>
          <label class="bfp-check-option"><input type="checkbox" v-model="filters.approved"> Approved</label>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">date_range</i> Date & Amount</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Requested From</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">event</i>
              <input class="bfp-input" type="date" v-model="filters.requested_from" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Minimum Cost Impact</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">payments</i>
              <input class="bfp-input" type="number" v-model="filters.min_amount" placeholder="0" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <!-- Review Modal -->
    <BfpModal
      :show="showReviewModal"
      :title="reviewAction === 'Approved' ? 'Approve Variation Order' : 'Reject Variation Order'"
      stripe="VARIATION ORDER REVIEW"
      confirm-text="Confirm"
      confirm-icon="check"
      @close="showReviewModal = false"
      @confirm="reviewOrder"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">rate_review</i> Review Decision</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-full">
            <label class="bfp-label">Review Action</label>
            <div class="bfp-input-wrap">
              <select class="bfp-input bfp-select" v-model="reviewAction">
                <option value="Under Review">Under Review</option>
                <option value="Approved">Approve</option>
                <option value="Rejected">Reject</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Approval Remarks</label>
            <div class="bfp-input-wrap">
              <textarea class="bfp-input bfp-textarea" rows="3" v-model="approvalRemarks" placeholder="Add remarks for this decision"></textarea>
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <!-- View Order Detail Modal -->
    <BfpModal
      :show="showDetailModal"
      :title="'VO Details - ' + (detailOrder?.vo_number || '')"
      stripe="VARIATION ORDER DETAIL"
      confirm-text="Close"
      confirm-icon="close"
      :show-cancel="false"
      @close="showDetailModal = false"
    >
      <div v-if="detailOrder" class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">assignment</i> Order Information</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">VO Number</label>
            <div class="bfp-input-wrap">
              <input class="bfp-input" type="text" :value="detailOrder.vo_number" readonly />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Contract</label>
            <div class="bfp-input-wrap">
              <input class="bfp-input" type="text" :value="detailOrder.contract_number || detailOrder.contract_title" readonly />
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Description</label>
            <div class="bfp-input-wrap">
              <textarea class="bfp-input bfp-textarea" rows="3" :value="detailOrder.description" readonly></textarea>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Amount Change</label>
            <div class="bfp-input-wrap">
              <input class="bfp-input" type="text" :value="formatCurrency(detailOrder.amount_change)" readonly />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Time Impact (Days)</label>
            <div class="bfp-input-wrap">
              <input class="bfp-input" type="text" :value="detailOrder.time_impact_days || '-'" readonly />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Status</label>
            <div class="bfp-input-wrap">
              <input class="bfp-input" type="text" :value="detailOrder.status" readonly />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Approval Remarks</label>
            <div class="bfp-input-wrap">
              <input class="bfp-input" type="text" :value="detailOrder.approval_remarks || '-'" readonly />
            </div>
          </div>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">cloud_upload</i> Supporting Documents</div>
        <div v-if="detailOrder.documents && detailOrder.documents.length" class="document-list">
          <div v-for="doc in detailOrder.documents" :key="doc.id" class="document-row">
            <div class="document-info">
              <i class="material-icons-round">description</i>
              <div>
                <div class="document-title">{{ doc.document_title || doc.file_name }}</div>
                <div class="document-meta">{{ doc.file_type || 'File' }} · Uploaded by {{ doc.uploaded_by }} on {{ formatDate(doc.uploaded_at) }}</div>
              </div>
            </div>
            <button class="btn btn-sm btn-outline-primary" @click.prevent="downloadDoc(doc)">
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
import variationOrderService from "@/services/variation-order.service";

export default {
  name: "VariationOrders",
  components: { StatusBadge, BfpModal },
  data() {
    return {
      showRequestModal: false,
      showFilterModal: false,
      showReviewModal: false,
      orders: [],
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
      filterStatus: '',
      filters: {
        draft: true,
        submitted: true,
        under_review: true,
        approved: true,
        rejected: false,
        requested_from: '',
        min_amount: '',
      },
      newOrder: {
        vo_number: '',
        contract_id: '',
        description: '',
        reason: '',
        amount_change: 0,
        time_impact_days: null,
      },
      editingOrder: null,
      selectedOrder: null,
      reviewAction: 'Approved',
      approvalRemarks: '',
      monthlyBreakdown: [],
      showDetailModal: false,
      detailOrder: null,
      uploadingDocument: null,
      errorMessage: '',
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
    pipelineProgress() {
      const dist = this.summary?.status_distribution || {};
      const total = (dist.draft || 0) + (dist.submitted || 0) + (dist.under_review || 0) + (dist.approved || 0);
      if (total === 0) return 0;
      const completed = (dist.approved || 0) + (dist.submitted || 0) + (dist.under_review || 0);
      return Math.min(Math.round((completed / total) * 100), 100);
    }
  },
  mounted() {
    this.loadPermissions();
    this.loadSummary();
    this.loadOrders(1);
  },
  methods: {
    async loadPermissions() {
      try {
        const options = await variationOrderService.getOptions();
        const raw = options.permissions || {};
        this.permissions = {
          can_view: raw.view ?? false,
          can_create: raw.create ?? false,
          can_edit: raw.edit ?? false,
          can_delete: raw.delete ?? false,
          can_approve: raw.approve ?? false,
          can_export: raw.export ?? false,
        };
        this.contracts = options.contracts || [];
      } catch (error) {
        console.error('Failed to load variation order permissions:', error);
      }
    },

async loadSummary() {
       try {
         const data = await variationOrderService.getSummary();
         this.summary = data;
         if (data.monthly_breakdown) {
           this.monthlyBreakdown = data.monthly_breakdown;
         }
       } catch (error) {
         console.error('Failed to load variation order summary:', error);
       }
     },

    async loadOrders(page = 1) {
      try {
        const params = { page };
        if (this.filterStatus) {
          params.status = this.filterStatus;
        }
        const response = await variationOrderService.getVariationOrders(params);
        this.orders = response.data || [];
        this.pagination = response.meta || this.pagination;
      } catch (error) {
        console.error('Failed to load variation orders:', error);
      }
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
      }).format(value || 0);
    },

    formatDate(date) {
      if (!date) return '-';
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },

    truncateText(text, maxLength) {
      if (!text) return '';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },

    getStatusDateLabel(order) {
      if (order.status === 'Draft') return 'Not submitted';
      if (order.status === 'Submitted') return `Submitted ${this.formatDate(order.submitted_at)}`;
      if (order.status === 'Under Review') return 'In Progress';
      return '-';
    },

    calculateMonthlyGrowth() {
      const months = this.monthlyBreakdown || [];
      if (months.length < 2) return 0;
      const latest = months[months.length - 1].amount || 0;
      const previous = months[months.length - 2].amount || 0;
      if (previous === 0) return latest > 0 ? 100 : 0;
      return Math.round(((latest - previous) / previous) * 100);
    },

    calculateContingencyUtilization() {
      const total = this.summary?.total_cost_impact || 0;
      const maxBudget = 20000000;
      return Math.min(Math.round((total / maxBudget) * 100), 100);
    },

    getBottleneckStage() {
      const dist = this.summary?.status_distribution || {};
      const maxCount = Math.max(dist.draft || 0, dist.submitted || 0, dist.under_review || 0, dist.approved || 0, dist.rejected || 0);
      
      if (maxCount === 0) return 'No data available';
      
      if (dist.under_review >= maxCount && maxCount > 0) {
        return 'Current bottleneck identified at "Under Review" stage.';
      }
      if (dist.submitted >= maxCount && maxCount > 0) {
        return 'Current bottleneck identified at "Submission" stage.';
      }
      if (dist.draft >= maxCount && maxCount > 0) {
        return 'Most orders are in Draft stage.';
      }
      return 'No significant bottlenecks identified.';
    },

    openRequestModal() {
      this.showRequestModal = true;
    },

    applyFilters() {
      this.showFilterModal = false;
      this.loadOrders(1);
    },

    async createVariationOrder() {
      try {
        await variationOrderService.createVariationOrder({
          vo_number: this.newOrder.vo_number,
          contract_id: this.newOrder.contract_id,
          description: this.newOrder.description,
          reason: this.newOrder.reason,
          amount_change: this.newOrder.amount_change,
          time_impact_days: this.newOrder.time_impact_days,
        });

        this.showRequestModal = false;
        this.resetNewOrder();
        this.loadOrders(1);
        this.loadSummary();
      } catch (error) {
        this.showError(error);
      }
    },

    async updateVariationOrder() {
      if (!this.editingOrder) return;
      try {
        await variationOrderService.updateVariationOrder(this.editingOrder.id, {
          vo_number: this.newOrder.vo_number,
          contract_id: this.newOrder.contract_id,
          description: this.newOrder.description,
          reason: this.newOrder.reason,
          amount_change: this.newOrder.amount_change,
          time_impact_days: this.newOrder.time_impact_days,
        });

        this.showRequestModal = false;
        this.editingOrder = null;
        this.loadOrders(this.pagination.current_page);
        this.loadSummary();
      } catch (error) {
        this.showError(error);
      }
    },

    async submitOrder(order) {
      if (!confirm(`Are you sure you want to submit variation order ${order.vo_number}?`)) return;
      try {
        await variationOrderService.submitVariationOrder(order.id);
        this.loadOrders(this.pagination.current_page);
      } catch (error) {
        this.showError(error);
      }
    },

    openReviewModal(order) {
      this.selectedOrder = order;
      this.reviewAction = 'Approved';
      this.approvalRemarks = '';
      this.showReviewModal = true;
    },

    async reviewOrder() {
      if (!this.selectedOrder) return;

      const confirmMessage = this.reviewAction === 'Rejected'
        ? `Are you sure you want to reject variation order ${this.selectedOrder.vo_number}?`
        : `Are you sure you want to ${this.reviewAction.toLowerCase()} variation order ${this.selectedOrder.vo_number}?`;

      if (!confirm(confirmMessage)) return;

      try {
        await variationOrderService.reviewVariationOrder(
          this.selectedOrder.id,
          this.reviewAction,
          this.approvalRemarks
        );

        this.showReviewModal = false;
        this.loadOrders(this.pagination.current_page);
        this.loadSummary();
      } catch (error) {
        this.showError(error);
      }
    },

    async archiveOrder(order) {
      if (!confirm(`Are you sure you want to archive variation order ${order.vo_number}?`)) return;
      try {
        await variationOrderService.archiveVariationOrder(order.id);
        this.loadOrders(this.pagination.current_page);
      } catch (error) {
        this.showError(error);
      }
    },

    async viewOrder(order) {
      try {
        const response = await variationOrderService.getVariationOrder(order.id);
        const data = response?.data?.data || response?.data || {};
        if (!data.id) {
          console.warn('[VariationOrders] Unexpected detail response shape', response);
        }
        this.detailOrder = data;
        this.showDetailModal = true;
      } catch (error) {
        this.showError(error);
      }
    },

    async downloadDoc(doc) {
      try {
        await variationOrderService.downloadDocument(doc);
      } catch (error) {
        this.showError(error);
      }
    },

    showError(error) {
      const message = error?.response?.data?.message || error.message || 'Something went wrong.';
      alert(message);
    },

    editOrder(order) {
      this.editingOrder = order;
      this.newOrder = {
        vo_number: order.vo_number,
        contract_id: order.contract_id,
        description: order.description,
        reason: order.reason,
        amount_change: order.amount_change,
        time_impact_days: order.time_impact_days,
      };
      this.showRequestModal = true;
    },

    resetNewOrder() {
      this.newOrder = {
        vo_number: '',
        contract_id: '',
        description: '',
        reason: '',
        amount_change: 0,
        time_impact_days: null,
      };
    }
  }
};
</script>

<style scoped>
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

.disabled-link {
  opacity: 0.5;
  pointer-events: none;
}

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

.document-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.document-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem;
  background: #f7fafc;
  border-radius: 0.5rem;
  border: 1px solid #e0e5ee;
}

.document-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.document-info i {
  color: #7b1113;
  font-size: 1.5rem;
}

.document-title {
  font-weight: 600;
  font-size: 0.875rem;
  color: #1f2633;
}

.document-meta {
  font-size: 0.75rem;
  color: #5a6270;
}
</style>