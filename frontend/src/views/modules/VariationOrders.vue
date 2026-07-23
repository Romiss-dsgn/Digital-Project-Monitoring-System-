<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <div v-if="errorMessage" class="alert alert-danger py-2 px-3 mb-3">{{ errorMessage }}</div>
      <!-- Header -->
      <div class="row mb-4 align-items-center gy-3">
        <div class="col-12 col-lg-8">
          <h4 class="mb-0">Variation Orders Monitoring</h4>
          <p class="text-secondary small">Track, review, and manage contract changes and financial impacts.</p>
        </div>
        <div class="col-12 col-lg-4 d-flex flex-column flex-sm-row justify-content-lg-end align-items-stretch align-items-sm-center gap-2">
          <button class="btn btn-outline-secondary btn-sm w-100 w-sm-auto" @click="showFilterModal = true">
            <i class="material-icons-round">filter_list</i> Filter
          </button>
          <button class="btn btn-primary btn-sm w-100 w-sm-auto" @click="showRequestModal = true">
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
            <div class="card-header pb-0 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2">
              <h6>Active Variation Orders</h6>
              <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center w-100 w-lg-auto">
                <div class="btn-group btn-group-sm vo-status-group" role="group">
                  <button type="button" class="btn" :class="filterStatus === '' ? 'btn-dark' : 'btn-outline-secondary'" @click="setQuickFilter('')">All</button>
                  <button type="button" class="btn" :class="filterStatus === 'Submitted' ? 'btn-dark' : 'btn-outline-secondary'" @click="setQuickFilter('Submitted')">Requests</button>
                  <button type="button" class="btn" :class="filterStatus === 'Under Review' ? 'btn-dark' : 'btn-outline-secondary'" @click="setQuickFilter('Under Review')">Approvals</button>
                </div>
                <button class="btn btn-sm btn-icon btn-light text-secondary vo-more-btn">
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
                        <div class="vo-actions-menu">
                          <button
                            class="btn btn-sm btn-icon btn-light text-secondary"
                            type="button"
                            :aria-expanded="openActionMenuId === order.id"
                            title="Variation order actions"
                            @click.stop="toggleActionMenu(order.id, $event)"
                          >
                            <i class="material-icons-round">more_vert</i>
                          </button>
                          <div
                            v-if="openActionMenuId === order.id"
                            class="vo-action-menu"
                            :style="{
                              top: `${actionMenuPosition.top}px`,
                              left: `${actionMenuPosition.left}px`,
                            }"
                            role="menu"
                            @click.stop
                          >
                            <button class="vo-action-menu-item" type="button" role="menuitem" @click="handleViewOrder(order)">
                              <i class="material-icons-round dropdown-icon view-icon">visibility</i>
                              View
                            </button>
                            <button
                              v-if="order.status === 'Draft'"
                              class="vo-action-menu-item"
                              :disabled="!permissions.can_create"
                              type="button"
                              role="menuitem"
                              @click="handleSubmitOrder(order)"
                            >
                              <i class="material-icons-round dropdown-icon">send</i>
                              Submit
                            </button>
                            <button
                              v-if="order.status === 'Submitted' || order.status === 'Under Review'"
                              class="vo-action-menu-item"
                              :disabled="!permissions.can_approve"
                              type="button"
                              role="menuitem"
                              @click="handleReviewOrder(order)"
                            >
                              <i class="material-icons-round dropdown-icon">rate_review</i>
                              Review
                            </button>
                            <button
                              v-if="order.status !== 'Draft' && order.status !== 'Approved' && order.status !== 'Rejected'"
                              class="vo-action-menu-item"
                              :disabled="!permissions.can_edit"
                              type="button"
                              role="menuitem"
                              @click="handleEditOrder(order)"
                            >
                              <i class="material-icons-round dropdown-icon edit-icon">edit</i>
                              Edit
                            </button>
                            <button
                              class="vo-action-menu-item danger"
                              :disabled="!permissions.can_delete"
                              type="button"
                              role="menuitem"
                              @click="handleArchiveOrder(order)"
                            >
                              <i class="material-icons-round dropdown-icon">archive</i>
                              Archive
                            </button>
                          </div>
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
              <div v-if="pagination.total > 0" class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mt-3 px-2">
                <span class="text-secondary small">Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} Variation Orders</span>
                <nav class="w-100 w-sm-auto">
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
        <div class="col-12 col-md-6 mb-3">
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
        <div class="col-12 col-md-6 mb-3">
          <div class="card h-100">
            <div class="card-header pb-0 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
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
        <div class="bfp-section-label"><i class="material-icons-round">search</i> Search & Status</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-full">
            <label class="bfp-label">Search</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">search</i>
              <input class="bfp-input" type="text" v-model.trim="filters.search" placeholder="VO number, description, or contract title" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Status</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">flag</i>
              <select class="bfp-input bfp-select" v-model="filters.status">
                <option value="">All Statuses</option>
                <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Contract</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">folder</i>
              <select class="bfp-input bfp-select" v-model="filters.contract_id">
                <option value="">All Contracts</option>
                <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                  {{ contract.contract_number }} - {{ contract.contract_title }}
                </option>
              </select>
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

    <BfpModal
      :show="showSaveResultModal"
      :title="saveResultTitle || 'Variation Order Status'"
      stripe="DATA SUBMISSION RESULT"
      :confirm-text="saveResultStatus === 'success' ? 'OK' : 'Try Again'"
      :confirm-icon="saveResultStatus === 'success' ? 'check_circle' : 'error'"
      :show-cancel="false"
      @close="closeSaveResultModal"
      @confirm="closeSaveResultModal"
    >
      <div class="bfp-section mb-0">
        <div class="bfp-section-label">
          <i class="material-icons-round">{{ saveResultStatus === 'success' ? 'check_circle' : 'error' }}</i>
          {{ saveResultStatus === 'success' ? 'Add Successful' : 'Add Failed' }}
        </div>
        <div class="bfp-form-grid">
          <div class="bfp-field-full">
            <p class="mb-0 text-secondary">
              {{ saveResultMessage }}
            </p>
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
      @close="closeDetailModal"
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
        <div v-if="permissions.can_create" class="document-upload-panel mb-3">
          <div class="bfp-form-grid">
            <div class="bfp-field-half">
              <label class="bfp-label">Document Title</label>
              <div class="bfp-input-wrap">
                <i class="material-icons-round bfp-input-icon">badge</i>
                <input
                  class="bfp-input"
                  type="text"
                  v-model="documentUpload.document_title"
                  placeholder="Optional document title"
                />
              </div>
            </div>
            <div class="bfp-field-half">
              <label class="bfp-label">File</label>
              <div class="bfp-input-wrap">
                <input
                  ref="documentFileInput"
                  class="bfp-input"
                  type="file"
                  accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png"
                  @change="handleDocumentFileChange"
                />
              </div>
            </div>
            <div class="bfp-field-full">
              <label class="bfp-label">Remarks</label>
              <div class="bfp-input-wrap">
                <textarea
                  class="bfp-input bfp-textarea"
                  rows="2"
                  v-model="documentUpload.remarks"
                  placeholder="Optional remarks"
                ></textarea>
              </div>
            </div>
          </div>
          <div class="mt-3 d-flex justify-content-end">
            <button
              type="button"
              class="btn btn-sm btn-primary"
              :disabled="uploadingDocument || !documentUpload.file"
              @click="uploadDetailDocument"
            >
              <span v-if="uploadingDocument" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <i v-else class="material-icons-round me-1" style="font-size: 1rem; vertical-align: middle;">upload</i>
              {{ uploadingDocument ? 'Uploading...' : 'Upload Document' }}
            </button>
          </div>
        </div>
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
      showSaveResultModal: false,
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
      isLoadingOrders: false,
      filters: {
        search: '',
        status: '',
        contract_id: '',
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
      saveResultStatus: 'success',
      saveResultTitle: '',
      saveResultMessage: '',
      monthlyBreakdown: [],
      showDetailModal: false,
      detailOrder: null,
      uploadingDocument: false,
      documentUpload: {
        file: null,
        document_title: '',
        remarks: '',
      },
      errorMessage: '',
      openActionMenuId: null,
      actionMenuPosition: {
        top: 0,
        left: 0,
      },
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
    document.addEventListener('click', this.closeActionMenu);
    window.addEventListener('resize', this.closeActionMenu);
    window.addEventListener('scroll', this.closeActionMenu, true);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.closeActionMenu);
    window.removeEventListener('resize', this.closeActionMenu);
    window.removeEventListener('scroll', this.closeActionMenu, true);
  },
  methods: {
    toggleActionMenu(orderId, event) {
      if (this.openActionMenuId === orderId) {
        this.closeActionMenu();
        return;
      }

      this.positionActionMenu(event.currentTarget);
      this.openActionMenuId = orderId;
    },

    positionActionMenu(trigger) {
      const rect = trigger.getBoundingClientRect();
      const menuWidth = 150;
      const menuHeight = 186;
      const margin = 8;
      const viewportPadding = 8;
      const left = Math.max(
        viewportPadding,
        Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - viewportPadding)
      );
      const opensUp = rect.bottom + menuHeight + margin > window.innerHeight;

      this.actionMenuPosition = {
        top: opensUp ? Math.max(viewportPadding, rect.top - menuHeight - margin) : rect.bottom + margin,
        left,
      };
    },

    closeActionMenu() {
      this.openActionMenuId = null;
    },

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
        this.errorMessage = '';
      } catch (error) {
        this.setError(error, 'Failed to load variation order permissions.');
      }
    },

    async loadSummary() {
       try {
         const data = await variationOrderService.getSummary();
         this.summary = data;
         if (data.monthly_breakdown) {
           this.monthlyBreakdown = data.monthly_breakdown;
         }
         this.errorMessage = '';
       } catch (error) {
         this.setError(error, 'Failed to load variation order summary.');
       }
     },

    async loadOrders(page = 1) {
      this.isLoadingOrders = true;
      try {
        const params = {
          page,
          search: this.filters.search || undefined,
          status: this.filters.status || this.filterStatus || undefined,
          contract_id: this.filters.contract_id || undefined,
        };
        const response = await variationOrderService.getVariationOrders(params);
        this.orders = response.data || [];
        this.pagination = response.meta || this.pagination;
        this.errorMessage = '';
      } catch (error) {
        this.setError(error, 'Failed to load variation orders.');
      } finally {
        this.isLoadingOrders = false;
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
      const months = (this.summary?.monthly_breakdown || this.monthlyBreakdown || []).filter(Boolean);
      if (months.length < 2) return 0;

      const latestAmount = Number(months[months.length - 1]?.amount || 0);
      const previousAmount = Number(months[months.length - 2]?.amount || 0);

      if (previousAmount === 0) return latestAmount > 0 ? 100 : 0;
      return Math.round(((latestAmount - previousAmount) / previousAmount) * 100);
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

    openSaveResultModal(status, title, message) {
      this.saveResultStatus = status;
      this.saveResultTitle = title;
      this.saveResultMessage = message;
      this.showSaveResultModal = true;
    },

    closeSaveResultModal() {
      this.showSaveResultModal = false;
      this.saveResultStatus = 'success';
      this.saveResultTitle = '';
      this.saveResultMessage = '';
    },

    async applyFilters() {
      this.showFilterModal = false;
      await this.loadOrders(1);
    },

    async setQuickFilter(status) {
      this.filterStatus = status;
      this.filters.status = status;
      await this.loadOrders(1);
    },

    resetFilters() {
      this.filterStatus = '';
      this.filters = {
        search: '',
        status: '',
        contract_id: '',
      };
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
        await this.loadOrders(1);
        await this.loadSummary();
        this.openSaveResultModal(
          'success',
          'Variation Order Added',
          'The variation order was added successfully.'
        );
      } catch (error) {
        const message = error?.response?.data?.message || error?.message || 'Unable to add variation order.';
        this.openSaveResultModal('error', 'Add Failed', message);
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
        await this.loadOrders(this.pagination.current_page);
        await this.loadSummary();
        this.openSaveResultModal(
          'success',
          'Variation Order Updated',
          'The variation order was updated successfully.'
        );
      } catch (error) {
        const message = error?.response?.data?.message || error?.message || 'Unable to update variation order.';
        this.openSaveResultModal('error', 'Update Failed', message);
      }
    },

    async submitOrder(order) {
      if (!confirm(`Are you sure you want to submit variation order ${order.vo_number}?`)) return;
      try {
        await variationOrderService.submitVariationOrder(order.id);
        await this.loadOrders(this.pagination.current_page);
        await this.loadSummary();
        this.openSaveResultModal(
          'success',
          'Variation Order Submitted',
          `Variation order ${order.vo_number} was submitted successfully.`
        );
      } catch (error) {
        const message = error?.response?.data?.message || error?.message || 'Unable to submit variation order.';
        this.openSaveResultModal('error', 'Submit Failed', message);
      }
    },

    openReviewModal(order) {
      this.selectedOrder = order;
      this.reviewAction = 'Approved';
      this.approvalRemarks = '';
      this.showReviewModal = true;
    },

    handleViewOrder(order) {
      this.closeActionMenu();
      this.viewOrder(order);
    },

    handleSubmitOrder(order) {
      this.closeActionMenu();
      this.submitOrder(order);
    },

    handleReviewOrder(order) {
      this.closeActionMenu();
      this.openReviewModal(order);
    },

    handleEditOrder(order) {
      this.closeActionMenu();
      this.editOrder(order);
    },

    handleArchiveOrder(order) {
      this.closeActionMenu();
      this.archiveOrder(order);
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
        await this.loadOrders(this.pagination.current_page);
        await this.loadSummary();
        this.openSaveResultModal(
          'success',
          'Variation Order Reviewed',
          `Variation order ${this.selectedOrder.vo_number} was reviewed successfully.`
        );
      } catch (error) {
        const message = error?.response?.data?.message || error?.message || 'Unable to review variation order.';
        this.openSaveResultModal('error', 'Review Failed', message);
      }
    },

    async archiveOrder(order) {
      if (!confirm(`Are you sure you want to archive variation order ${order.vo_number}?`)) return;
      try {
        await variationOrderService.archiveVariationOrder(order.id);
        await this.loadOrders(this.pagination.current_page);
        await this.loadSummary();
        this.openSaveResultModal(
          'success',
          'Variation Order Archived',
          `Variation order ${order.vo_number} was archived successfully.`
        );
      } catch (error) {
        const message = error?.response?.data?.message || error?.message || 'Unable to archive variation order.';
        this.openSaveResultModal('error', 'Archive Failed', message);
      }
    },

    async viewOrder(order) {
      try {
        const response = await variationOrderService.getVariationOrder(order.id);
        const data = response?.data?.data || response?.data || {};
        if (!data.id) {
          this.errorMessage = 'Unable to load variation order details.';
          return;
        }
        this.detailOrder = data;
        this.resetDocumentUpload();
        this.showDetailModal = true;
      } catch (error) {
        this.showError(error);
      }
    },

    closeDetailModal() {
      this.showDetailModal = false;
      this.detailOrder = null;
      this.resetDocumentUpload();
    },

    handleDocumentFileChange(event) {
      const file = event?.target?.files?.[0] || null;
      this.documentUpload.file = file;
    },

    resetDocumentUpload() {
      this.documentUpload = {
        file: null,
        document_title: '',
        remarks: '',
      };

      if (this.$refs.documentFileInput) {
        this.$refs.documentFileInput.value = '';
      }
    },

    async uploadDetailDocument() {
      if (!this.detailOrder || !this.documentUpload.file) return;

      try {
        this.uploadingDocument = true;
        const uploaded = await variationOrderService.uploadDocument(
          this.detailOrder.id,
          this.documentUpload.file,
          this.documentUpload.document_title,
          this.documentUpload.remarks
        );

        this.detailOrder = {
          ...this.detailOrder,
          documents: [uploaded, ...(this.detailOrder.documents || [])],
        };
        this.resetDocumentUpload();
        this.openSaveResultModal(
          'success',
          'Document Uploaded',
          'The variation order document was uploaded successfully.'
        );
      } catch (error) {
        const message = error?.response?.data?.message || error?.message || 'Unable to upload document.';
        this.openSaveResultModal('error', 'Upload Failed', message);
      } finally {
        this.uploadingDocument = false;
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
      const message = this.setError(error, 'Something went wrong.');
      alert(message);
    },

    setError(error, fallback) {
      const message = error?.response?.data?.message || error.message || fallback;
      this.errorMessage = message;
      return message;
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
.vo-actions-menu {
  display: inline-flex;
  position: relative;
}

.vo-action-menu {
  position: fixed;
  min-width: 150px;
  padding: 0.35rem;
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 8px;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.18);
  z-index: 1200;
}

.vo-action-menu-item {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  width: 100%;
  min-height: 34px;
  padding: 0.45rem 0.65rem;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: #374151;
  font-size: 0.84rem;
  font-weight: 700;
  text-align: left;
}

.vo-action-menu-item:hover:not(:disabled) {
  background: #f3f4f6;
  color: #111827;
}

.vo-action-menu-item:disabled {
  cursor: not-allowed;
  opacity: 0.45;
}

.vo-action-menu-item.danger {
  color: #dc2626;
}

.vo-action-menu-item.danger:hover:not(:disabled) {
  background: #fef2f2;
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

.vo-status-group {
  flex-wrap: wrap;
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

.document-upload-panel {
  padding: 1rem;
  border: 1px dashed #cbd5e1;
  border-radius: 0.75rem;
  background: #f8fafc;
}

.document-upload-panel .bfp-input[type="file"] {
  padding-left: 12px;
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

@media (max-width: 576px) {
  .vo-status-group {
    width: 100%;
  }

  .vo-status-group > .btn {
    flex: 1 1 0;
  }

  .vo-more-btn {
    width: 100%;
  }

  .vo-actions-menu > .btn {
    width: 32px;
    height: 32px;
  }

  .vo-action-menu {
    min-width: 160px;
    max-width: calc(100vw - 1.5rem);
  }

  .pipeline-labels {
    flex-wrap: wrap;
    gap: 6px 10px;
    justify-content: flex-start;
  }

  .pipeline-labels span {
    flex: 1 1 calc(50% - 10px);
    min-width: 120px;
  }

  .monthly-row .d-flex {
    gap: 8px;
  }

  .document-row {
    align-items: flex-start;
    flex-direction: column;
    gap: 10px;
  }

  .document-info {
    align-items: flex-start;
  }
}
</style>