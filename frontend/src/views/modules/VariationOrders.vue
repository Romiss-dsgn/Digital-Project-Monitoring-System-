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
          <button class="btn btn-primary btn-sm w-100 w-sm-auto" @click="startNewRequest">
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
	                <table class="table align-items-center mb-0 vo-table">
	                  <thead>
	                    <tr>
		                      <th>VO #</th>
		                      <th>Contract / Project</th>
		                      <th>Approved Budget for Contract</th>
		                      <th>Original Contract Cost</th>
		                      <th>VO Amount</th>
		                      <th>Additive</th>
                      <th>Deductive</th>
                      <th>Revised Contract Cost</th>
                      <th>Time Impact (Days)</th>
                      <th>Description</th>
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
	                        <div class="proj-ref text-muted">{{ order.contractor_name || '-' }}</div>
	                      </td>
	                      <td><strong>{{ formatCurrency(getApprovedBudgetForContract(order)) }}</strong></td>
	                      <td><strong>{{ formatCurrency(getOriginalContractAmount(order)) }}</strong></td>
                      <td><strong>{{ formatCurrency(getVariationAmount(order)) }}</strong></td>
                      <td>{{ formatCurrency(getAdditiveAmount(order)) }}</td>
                      <td>{{ formatCurrency(getDeductiveAmount(order)) }}</td>
                      <td><strong>{{ formatCurrency(getRevisedContractAmount(order)) }}</strong></td>
                      <td>{{ order.time_impact_days || '-' }}</td>
                      <td>{{ truncateText(order.description, 40) }}</td>
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
		                      <td colspan="12" class="text-center py-4">
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
        <div class="col-12 mb-3">
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
    <TuaoModal
      :show="showRequestModal"
      :title="editingOrder ? 'Edit Variation Order' : 'New Variation Order Request'"
      stripe="VARIATION ORDER REQUEST"
      :confirm-text="editingOrder ? 'Update Order' : 'Submit Request'"
      confirm-icon="send"
      @close="closeRequestModal"
      @confirm="editingOrder ? updateVariationOrder() : createVariationOrder()"
    >
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">business</i> Contract Snapshot</div>
        <div v-if="selectedContract" class="tuao-form-grid">
          <div class="tuao-field-half">
            <label class="tuao-label">Project</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="selectedContract.project_name || '-'" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Project Reference</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="selectedContract.project_ref || '-'" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Contractor</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="selectedContract.contractor_name || '-'" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Contract No.</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="selectedContract.contract_number || '-'" readonly />
            </div>
          </div>
	          <div class="tuao-field-half">
	            <label class="tuao-label">Approved Budget for Contract</label>
	            <div class="tuao-input-wrap">
	              <input class="tuao-input" type="text" :value="formatCurrency(selectedContract.approved_budget_for_contract)" readonly />
	            </div>
	          </div>
	          <div class="tuao-field-half">
	            <label class="tuao-label">Original Contract Cost</label>
	            <div class="tuao-input-wrap">
	              <input class="tuao-input" type="text" :value="formatCurrency(selectedContract.original_contract_amount)" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Current Revised Cost</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="formatCurrency(selectedContract.revised_contract_amount)" readonly />
            </div>
          </div>
        </div>
        <div v-else class="text-secondary small">
          Select a contract to preview project, contractor, and contract cost details.
        </div>
      </div>

      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">assignment_add</i> Request Details</div>
        <div class="tuao-form-grid">
          <div class="tuao-field-half">
            <label class="tuao-label">VO Number <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">tag</i>
              <input class="tuao-input" type="text" v-model="newOrder.vo_number" placeholder="e.g. VO-2024-001" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Contract <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">folder_open</i>
              <select class="tuao-input tuao-select" v-model="newOrder.contract_id">
                <option value="">Select Contract</option>
                <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                  {{ contract.contract_number }} - {{ contract.contract_title }}
                </option>
              </select>
            </div>
          </div>
          <div class="tuao-field-full">
            <label class="tuao-label">Scope Change Description</label>
            <div class="tuao-input-wrap">
              <textarea class="tuao-input tuao-textarea" rows="3" v-model="newOrder.description" placeholder="Describe additional works, deductions, or design changes"></textarea>
            </div>
          </div>
          <div class="tuao-field-full">
            <label class="tuao-label">Reason</label>
            <div class="tuao-input-wrap">
              <textarea class="tuao-input tuao-textarea" rows="2" v-model="newOrder.reason" placeholder="Reason for variation order"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="tuao-section">
        <div class="tuao-section-label">
          <i class="material-icons-round">table_view</i> Item Worksheet
        </div>
        <div class="vo-summary-strip mb-3">
          <div>
            <span class="small text-secondary">Item Count</span>
            <strong>{{ newOrder.items.length }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Additive Total</span>
            <strong>{{ formatCurrency(getWorksheetAdditiveTotal()) }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Deductive Total</span>
            <strong>{{ formatCurrency(getWorksheetDeductiveTotal()) }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Net VO Amount</span>
            <strong>{{ formatCurrency(getWorksheetNetAmount()) }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Revised Contract</span>
            <strong>{{ formatCurrency(getNewOrderRevisedAmount()) }}</strong>
          </div>
        </div>
	        <div class="vo-worksheet-toolbar mb-3">
	          <button type="button" class="btn btn-sm btn-outline-primary vo-add-item-btn" @click="openItemModal()">
	            <i class="material-icons-round">playlist_add</i> Add VO Item
	          </button>
	        </div>
        <div class="table-responsive vo-item-table-wrap">
          <table class="table table-sm align-items-middle mb-0 vo-item-table">
            <thead>
              <tr>
                <th style="width: 96px;">Line</th>
                <th>Item Description</th>
                <th style="width: 180px;">Original</th>
                <th style="width: 180px;">Additive</th>
                <th style="width: 180px;">Deductive</th>
                <th style="width: 140px;">Net Line</th>
                <th style="width: 96px;" class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in newOrder.items" :key="item.id || index">
                <td>
                  <span class="vo-line-pill">Line {{ item.line_number }}</span>
                </td>
                <td>
                  <div class="vo-item-title">{{ item.item_description || 'Untitled item' }}</div>
                  <div v-if="item.remarks" class="vo-item-note">{{ item.remarks }}</div>
                </td>
                <td>
                  <div class="vo-cell-stack">
                    <strong>{{ formatWorksheetNumber(item.original_qty) }} {{ item.original_unit || '' }}</strong>
                    <span>{{ formatCurrency(item.original_unit_cost) }}</span>
                  </div>
                </td>
                <td>
                  <div class="vo-cell-stack">
                    <strong>{{ formatWorksheetNumber(item.additive_qty) }} {{ item.additive_unit || '' }}</strong>
                    <span>{{ formatCurrency(item.additive_unit_cost) }}</span>
                  </div>
                </td>
                <td>
                  <div class="vo-cell-stack">
                    <strong>{{ formatWorksheetNumber(item.deductive_qty) }} {{ item.deductive_unit || '' }}</strong>
                    <span>{{ formatCurrency(item.deductive_unit_cost) }}</span>
                  </div>
                </td>
                <td>
                  <strong class="vo-num">{{ formatCurrency(getWorksheetLineNetCost(item)) }}</strong>
                </td>
                <td class="text-end">
                  <div class="vo-item-card-actions justify-content-end">
                    <button type="button" class="btn btn-sm btn-light" @click="openItemModal(index)">
                      <i class="material-icons-round">edit</i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light text-danger" @click="removeWorksheetItem(index)">
                      <i class="material-icons-round">delete</i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="newOrder.items.length === 0">
                <td colspan="7" class="text-center text-secondary py-4">
                  No VO items added yet. Use <strong>Add VO Item</strong> to start building the worksheet.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="tuao-form-grid mt-3">
          <div class="tuao-field-half">
            <label class="tuao-label">Time Impact (Days)</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">schedule</i>
              <input class="tuao-input" type="number" v-model="newOrder.time_impact_days" placeholder="Additional days" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Worksheet Status</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="newOrder.items.length ? 'Ready' : 'No items yet'" readonly />
            </div>
          </div>
        </div>
      </div>
    </TuaoModal>

    <TuaoModal
      :show="showItemModal"
      :title="itemEditorIndex === null ? 'Add VO Item' : 'Edit VO Item'"
      stripe="VARIATION ORDER ITEM"
      :confirm-text="itemEditorIndex === null ? 'Add Item' : 'Update Item'"
      confirm-icon="save"
      width="min(920px, calc(100vw - 2rem))"
      @close="closeItemModal"
      @confirm="saveWorksheetItem"
    >
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">assignment</i> Item Details</div>
        <p class="text-secondary small mb-3">
          Capture one line item at a time so the worksheet stays easy to review and totals remain accurate.
        </p>
        <div class="tuao-form-grid">
	          <div class="tuao-field-half">
	            <label class="tuao-label">Line Number</label>
	            <div class="tuao-input-wrap">
	              <input class="tuao-input" type="number" min="1" :value="itemForm.line_number" readonly />
	            </div>
	          </div>
          <div class="tuao-field-full">
            <label class="tuao-label">Item Description <span class="tuao-required">*</span></label>
            <div class="tuao-input-wrap">
              <textarea class="tuao-input tuao-textarea" rows="3" v-model="itemForm.item_description" placeholder="Describe the VO line item"></textarea>
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Original Qty</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="number" step="0.001" min="0" v-model="itemForm.original_qty" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Original Unit</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" v-model="itemForm.original_unit" placeholder="ea" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Original Unit Cost</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="number" step="0.01" min="0" v-model="itemForm.original_unit_cost" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Additive Qty</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="number" step="0.001" min="0" v-model="itemForm.additive_qty" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Additive Unit</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" v-model="itemForm.additive_unit" placeholder="lot" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Additive Unit Cost</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="number" step="0.01" min="0" v-model="itemForm.additive_unit_cost" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Deductive Qty</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="number" step="0.001" min="0" v-model="itemForm.deductive_qty" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Deductive Unit</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" v-model="itemForm.deductive_unit" placeholder="lot" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Deductive Unit Cost</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="number" step="0.01" min="0" v-model="itemForm.deductive_unit_cost" />
            </div>
          </div>
          <div class="tuao-field-full">
            <label class="tuao-label">Remarks</label>
            <div class="tuao-input-wrap">
              <textarea class="tuao-input tuao-textarea" rows="2" v-model="itemForm.remarks" placeholder="Optional item remarks"></textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">calculate</i> Live Cost Preview</div>
        <div class="vo-summary-strip">
          <div>
            <span class="small text-secondary">Original Total</span>
            <strong>{{ formatCurrency(getItemOriginalTotal(itemForm)) }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Additive Total</span>
            <strong>{{ formatCurrency(getItemAdditiveTotal(itemForm)) }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Deductive Total</span>
            <strong>{{ formatCurrency(getItemDeductiveTotal(itemForm)) }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Net Line Cost</span>
            <strong>{{ formatCurrency(getWorksheetLineNetCost(itemForm)) }}</strong>
          </div>
        </div>
      </div>
    </TuaoModal>

    <!-- Filter Modal -->
    <TuaoModal
      :show="showFilterModal"
      title="Variation Order Filters"
      stripe="VO SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showFilterModal = false"
      @confirm="applyFilters"
    >
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">search</i> Search & Status</div>
        <div class="tuao-form-grid">
          <div class="tuao-field-full">
            <label class="tuao-label">Search</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">search</i>
              <input class="tuao-input" type="text" v-model.trim="filters.search" placeholder="VO number, description, or contract title" />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Status</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">flag</i>
              <select class="tuao-input tuao-select" v-model="filters.status">
                <option value="">All Statuses</option>
                <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
              </select>
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Contract</label>
            <div class="tuao-input-wrap">
              <i class="material-icons-round tuao-input-icon">folder</i>
              <select class="tuao-input tuao-select" v-model="filters.contract_id">
                <option value="">All Contracts</option>
                <option v-for="contract in contracts" :key="contract.id" :value="contract.id">
                  {{ contract.contract_number }} - {{ contract.contract_title }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </TuaoModal>

    <!-- Review Modal -->
    <TuaoModal
      :show="showReviewModal"
      :title="reviewAction === 'Approved' ? 'Approve Variation Order' : 'Reject Variation Order'"
      stripe="VARIATION ORDER REVIEW"
      confirm-text="Confirm"
      confirm-icon="check"
      @close="showReviewModal = false"
      @confirm="reviewOrder"
    >
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">rate_review</i> Review Decision</div>
        <div class="tuao-form-grid">
          <div class="tuao-field-full">
            <label class="tuao-label">Review Action</label>
            <div class="tuao-input-wrap">
              <select class="tuao-input tuao-select" v-model="reviewAction">
                <option value="Under Review">Under Review</option>
                <option value="Approved">Approve</option>
                <option value="Rejected">Reject</option>
              </select>
            </div>
          </div>
          <div class="tuao-field-full">
            <label class="tuao-label">Approval Remarks</label>
            <div class="tuao-input-wrap">
              <textarea class="tuao-input tuao-textarea" rows="3" v-model="approvalRemarks" placeholder="Add remarks for this decision"></textarea>
            </div>
          </div>
        </div>
      </div>
    </TuaoModal>

    <TuaoModal
      :show="showSaveResultModal"
      :title="saveResultTitle || 'Variation Order Status'"
      stripe="DATA SUBMISSION RESULT"
      :confirm-text="saveResultStatus === 'success' ? 'OK' : 'Try Again'"
      :confirm-icon="saveResultStatus === 'success' ? 'check_circle' : 'error'"
      :show-cancel="false"
      @close="closeSaveResultModal"
      @confirm="closeSaveResultModal"
    >
      <div class="tuao-section mb-0">
        <div class="tuao-section-label">
          <i class="material-icons-round">{{ saveResultStatus === 'success' ? 'check_circle' : 'error' }}</i>
          {{ saveResultStatus === 'success' ? 'Add Successful' : 'Add Failed' }}
        </div>
        <div class="tuao-form-grid">
          <div class="tuao-field-full">
            <p class="mb-0 text-secondary">
              {{ saveResultMessage }}
            </p>
          </div>
        </div>
      </div>
    </TuaoModal>

    <!-- View Order Detail Modal -->
    <TuaoModal
      :show="showDetailModal"
      :title="'VO Details - ' + (detailOrder?.vo_number || '')"
      stripe="VARIATION ORDER DETAIL"
      confirm-text="Close"
      confirm-icon="close"
      :show-cancel="false"
      @close="closeDetailModal"
    >
      <div v-if="detailOrder" class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">assignment</i> Order Information</div>
        <div class="tuao-form-grid">
          <div class="tuao-field-half">
            <label class="tuao-label">VO Number</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="detailOrder.vo_number" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Contract</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="detailOrder.contract_number || detailOrder.contract_title" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Project</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="detailOrder.project_name || '-'" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Project Reference</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="detailOrder.project_ref || '-'" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Contractor</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="detailOrder.contractor_name || '-'" readonly />
            </div>
          </div>
          <div class="tuao-field-full">
            <label class="tuao-label">Description</label>
            <div class="tuao-input-wrap">
              <textarea class="tuao-input tuao-textarea" rows="3" :value="detailOrder.description" readonly></textarea>
            </div>
          </div>
	          <div class="tuao-field-half">
	            <label class="tuao-label">Approved Budget for Contract</label>
	            <div class="tuao-input-wrap">
	              <input class="tuao-input" type="text" :value="formatCurrency(getApprovedBudgetForContract(detailOrder))" readonly />
	            </div>
	          </div>
	          <div class="tuao-field-half">
	            <label class="tuao-label">Original Contract Cost</label>
	            <div class="tuao-input-wrap">
	              <input class="tuao-input" type="text" :value="formatCurrency(getOriginalContractAmount(detailOrder))" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">VO Amount</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="formatCurrency(getVariationAmount(detailOrder))" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Additive</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="formatCurrency(getAdditiveAmount(detailOrder))" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Deductive</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="formatCurrency(getDeductiveAmount(detailOrder))" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Revised Contract Cost</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="formatCurrency(getRevisedContractAmount(detailOrder))" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Time Impact (Days)</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="detailOrder.time_impact_days || '-'" readonly />
            </div>
          </div>
          <div class="tuao-field-half">
            <label class="tuao-label">Status</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="detailOrder.status" readonly />
            </div>
          </div>
          <div class="tuao-field-full">
            <label class="tuao-label">Approval Remarks</label>
            <div class="tuao-input-wrap">
              <input class="tuao-input" type="text" :value="detailOrder.approval_remarks || '-'" readonly />
            </div>
          </div>
        </div>
      </div>
      <div v-if="detailOrder" class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">table_view</i> Worksheet Breakdown</div>
        <div class="table-responsive vo-line-table-wrap">
          <table class="table table-sm align-items-center mb-0 vo-line-table">
            <thead>
              <tr>
                <th>Line</th>
                <th>Item Description</th>
                <th>Orig Qty</th>
                <th>Orig Unit</th>
                <th>Orig Cost</th>
                <th>Add Qty</th>
                <th>Add Unit</th>
                <th>Add Cost</th>
                <th>Ded Qty</th>
                <th>Ded Unit</th>
                <th>Ded Cost</th>
                <th>Net Line</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in getDetailWorksheetItems()" :key="item.id || `${item.line_number}-${item.item_description}`">
                <td>{{ item.line_number }}</td>
                <td>{{ item.item_description }}</td>
                <td>{{ formatWorksheetNumber(item.original_qty) }}</td>
                <td>{{ item.original_unit || '-' }}</td>
                <td>{{ formatCurrency(item.original_unit_cost) }}</td>
                <td>{{ formatWorksheetNumber(item.additive_qty) }}</td>
                <td>{{ item.additive_unit || '-' }}</td>
                <td>{{ formatCurrency(item.additive_unit_cost) }}</td>
                <td>{{ formatWorksheetNumber(item.deductive_qty) }}</td>
                <td>{{ item.deductive_unit || '-' }}</td>
                <td>{{ formatCurrency(item.deductive_unit_cost) }}</td>
                <td><strong>{{ formatCurrency(item.net_line_cost) }}</strong></td>
              </tr>
              <tr v-if="getDetailWorksheetItems().length === 0">
                <td colspan="12" class="text-center text-secondary py-3">No worksheet line items saved</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="mt-3 vo-summary-strip">
          <div>
            <span class="small text-secondary">Items</span>
            <strong>{{ getDetailWorksheetItems().length }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Additive Total</span>
            <strong>{{ formatCurrency(getDetailAdditiveTotal()) }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Deductive Total</span>
            <strong>{{ formatCurrency(getDetailDeductiveTotal()) }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Net VO Amount</span>
            <strong>{{ formatCurrency(getDetailNetAmount()) }}</strong>
          </div>
          <div>
            <span class="small text-secondary">Revised Contract</span>
            <strong>{{ formatCurrency(getRevisedContractAmount(detailOrder)) }}</strong>
          </div>
        </div>
      </div>
      <div class="tuao-section">
        <div class="tuao-section-label"><i class="material-icons-round">cloud_upload</i> Supporting Documents</div>
        <div v-if="permissions.can_create" class="document-upload-panel mb-3">
          <div class="tuao-form-grid">
            <div class="tuao-field-half">
              <label class="tuao-label">Document Title</label>
              <div class="tuao-input-wrap">
                <i class="material-icons-round tuao-input-icon">badge</i>
                <input
                  class="tuao-input"
                  type="text"
                  v-model="documentUpload.document_title"
                  placeholder="Optional document title"
                />
              </div>
            </div>
            <div class="tuao-field-half">
              <label class="tuao-label">File</label>
              <div class="tuao-input-wrap">
                <input
                  ref="documentFileInput"
                  class="tuao-input"
                  type="file"
                  accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png"
                  @change="handleDocumentFileChange"
                />
              </div>
            </div>
            <div class="tuao-field-full">
              <label class="tuao-label">Remarks</label>
              <div class="tuao-input-wrap">
                <textarea
                  class="tuao-input tuao-textarea"
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
    </TuaoModal>
  </div>
</template>

<script>
import StatusBadge from "@/components/StatusBadge.vue";
import TuaoModal from "@/components/TuaoModal.vue";
import variationOrderService from "@/services/variation-order.service";

export default {
  name: "VariationOrders",
  components: { StatusBadge, TuaoModal },
  data() {
    return {
      showRequestModal: false,
      showItemModal: false,
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
        items: [
          {
            line_number: 1,
            item_description: '',
            original_qty: null,
            original_unit: '',
            original_unit_cost: null,
            additive_qty: null,
            additive_unit: '',
            additive_unit_cost: null,
            deductive_qty: null,
            deductive_unit: '',
            deductive_unit_cost: null,
            remarks: '',
          },
        ],
      },
      editingOrder: null,
      itemEditorIndex: null,
      itemForm: {
        line_number: 1,
        item_description: '',
        original_qty: null,
        original_unit: '',
        original_unit_cost: null,
        additive_qty: null,
        additive_unit: '',
        additive_unit_cost: null,
        deductive_qty: null,
        deductive_unit: '',
        deductive_unit_cost: null,
        remarks: '',
      },
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
    selectedContract() {
      return this.contracts.find((contract) => String(contract.id) === String(this.newOrder.contract_id)) || null;
    },
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

    createWorksheetItem(lineNumber = 1, seed = {}) {
      return {
        line_number: lineNumber,
        item_description: '',
        original_qty: null,
        original_unit: '',
        original_unit_cost: null,
        additive_qty: null,
        additive_unit: '',
        additive_unit_cost: null,
        deductive_qty: null,
        deductive_unit: '',
        deductive_unit_cost: null,
        remarks: '',
        ...seed,
      };
    },

    syncWorksheetLineNumbers() {
      this.newOrder.items = (this.newOrder.items || []).map((item, index) => ({
        ...item,
        line_number: index + 1,
      }));
    },

    addWorksheetItem() {
      this.newOrder.items.push(this.createWorksheetItem(this.newOrder.items.length + 1));
      this.syncWorksheetLineNumbers();
    },

    removeWorksheetItem(index) {
      if (!this.newOrder.items.length) return;
      this.newOrder.items.splice(index, 1);
      this.syncWorksheetLineNumbers();
    },

    isWorksheetItemEmpty(item) {
      if (!item) return true;
      const description = String(item.item_description || '').trim();
      const numericValues = [
        item.original_qty,
        item.original_unit_cost,
        item.additive_qty,
        item.additive_unit_cost,
        item.deductive_qty,
        item.deductive_unit_cost,
      ].map((value) => Number(value || 0));

      return description === '' && numericValues.every((value) => value === 0);
    },

    normalizeWorksheetItems(items = []) {
      return items
        .filter((item) => !this.isWorksheetItemEmpty(item))
        .map((item, index) => {
          const originalQty = Number(item.original_qty || 0);
          const originalUnitCost = Number(item.original_unit_cost || 0);
          const additiveQty = Number(item.additive_qty || 0);
          const additiveUnitCost = Number(item.additive_unit_cost || 0);
          const deductiveQty = Number(item.deductive_qty || 0);
          const deductiveUnitCost = Number(item.deductive_unit_cost || 0);
          const originalTotal = Number((originalQty * originalUnitCost).toFixed(2));
          const additiveTotal = Number((additiveQty * additiveUnitCost).toFixed(2));
          const deductiveTotal = Number((deductiveQty * deductiveUnitCost).toFixed(2));

          return {
            line_number: Number(item.line_number || index + 1),
            item_description: String(item.item_description || '').trim(),
            original_qty: originalQty,
            original_unit: item.original_unit || '',
            original_unit_cost: originalUnitCost,
            original_total_cost: originalTotal,
            additive_qty: additiveQty,
            additive_unit: item.additive_unit || '',
            additive_unit_cost: additiveUnitCost,
            additive_total_cost: additiveTotal,
            deductive_qty: deductiveQty,
            deductive_unit: item.deductive_unit || '',
            deductive_unit_cost: deductiveUnitCost,
            deductive_total_cost: deductiveTotal,
            net_line_cost: Number((additiveTotal - deductiveTotal).toFixed(2)),
            remarks: item.remarks || '',
          };
        });
    },

    serializeWorksheetItems() {
      return this.normalizeWorksheetItems(this.newOrder.items);
    },

    getWorksheetAdditiveTotal(items = this.newOrder.items) {
      return this.normalizeWorksheetItems(items).reduce((sum, item) => sum + Number(item.additive_total_cost || 0), 0);
    },

    getWorksheetDeductiveTotal(items = this.newOrder.items) {
      return this.normalizeWorksheetItems(items).reduce((sum, item) => sum + Number(item.deductive_total_cost || 0), 0);
    },

    getWorksheetNetAmount(items = this.newOrder.items) {
      return Number((this.getWorksheetAdditiveTotal(items) - this.getWorksheetDeductiveTotal(items)).toFixed(2));
    },

    getWorksheetLineNetCost(item) {
      const additiveTotal = Number(item.additive_qty || 0) * Number(item.additive_unit_cost || 0);
      const deductiveTotal = Number(item.deductive_qty || 0) * Number(item.deductive_unit_cost || 0);
      return Number((additiveTotal - deductiveTotal).toFixed(2));
    },

    getItemOriginalTotal(item) {
      return Number((Number(item.original_qty || 0) * Number(item.original_unit_cost || 0)).toFixed(2));
    },

    getItemAdditiveTotal(item) {
      return Number((Number(item.additive_qty || 0) * Number(item.additive_unit_cost || 0)).toFixed(2));
    },

    getItemDeductiveTotal(item) {
      return Number((Number(item.deductive_qty || 0) * Number(item.deductive_unit_cost || 0)).toFixed(2));
    },

    formatWorksheetNumber(value) {
      const numeric = Number(value || 0);
      return Number.isFinite(numeric) ? numeric.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 3 }) : '-';
    },

	    getOriginalContractAmount(order) {
	      if (!order) return 0;
	      return Number(order.original_contract_amount ?? 0);
	    },

	    getApprovedBudgetForContract(order) {
	      if (!order) return 0;
	      return Number(order.approved_budget_for_contract ?? order.approved_budget ?? 0);
	    },

    getVariationAmount(order) {
      if (!order) return 0;
      return Number(order.amount_change ?? 0);
    },

    getAdditiveAmount(order) {
      if (order && order.additive_amount !== undefined && order.additive_amount !== null) {
        return Number(order.additive_amount || 0);
      }
      return Math.max(this.getVariationAmount(order), 0);
    },

    getDeductiveAmount(order) {
      if (order && order.deductive_amount !== undefined && order.deductive_amount !== null) {
        return Number(order.deductive_amount || 0);
      }
      return Math.max(0 - this.getVariationAmount(order), 0);
    },

    getRevisedContractAmount(order) {
      if (!order) return 0;
      if (order.revised_contract_amount !== null && order.revised_contract_amount !== undefined) {
        const backendValue = Number(order.revised_contract_amount);
        if (!Number.isNaN(backendValue)) {
          return backendValue;
        }
      }
      return this.getOriginalContractAmount(order) + this.getVariationAmount(order);
    },

    getNewOrderOriginalAmount() {
      return Number(this.selectedContract?.original_contract_amount ?? 0);
    },

    getNewOrderCurrentRevisedAmount() {
      const revised = Number(this.selectedContract?.revised_contract_amount ?? this.getNewOrderOriginalAmount());
      return Number.isNaN(revised) ? this.getNewOrderOriginalAmount() : revised;
    },

    getNewOrderAdditiveAmount() {
      return this.getWorksheetAdditiveTotal();
    },

    getNewOrderDeductiveAmount() {
      return this.getWorksheetDeductiveTotal();
    },

    getNewOrderRevisedAmount() {
      return Number((this.getNewOrderCurrentRevisedAmount() + this.getWorksheetNetAmount()).toFixed(2));
    },

    getDetailWorksheetItems() {
      return this.getOrderWorksheetItems(this.detailOrder);
    },

    getOrderWorksheetItems(order) {
      if (!order) return [];
      if (Array.isArray(order.items) && order.items.length > 0) {
        return order.items;
      }

      const amountChange = Number(order.amount_change || 0);
      if (!order.description && amountChange === 0) {
        return [];
      }

      return [
        {
          id: `legacy-${order.id || 'vo'}`,
          line_number: 1,
          item_description: order.description || order.reason || 'Legacy variation order line',
          original_qty: 0,
          original_unit: '',
          original_unit_cost: 0,
          original_total_cost: 0,
          additive_qty: amountChange > 0 ? 1 : 0,
          additive_unit: 'lot',
          additive_unit_cost: Math.max(amountChange, 0),
          additive_total_cost: Math.max(amountChange, 0),
          deductive_qty: amountChange < 0 ? 1 : 0,
          deductive_unit: 'lot',
          deductive_unit_cost: Math.max(0 - amountChange, 0),
          deductive_total_cost: Math.max(0 - amountChange, 0),
          net_line_cost: amountChange,
          remarks: order.reason || '',
        },
      ];
    },

    getDetailAdditiveTotal() {
      return this.getWorksheetAdditiveTotal(this.getDetailWorksheetItems());
    },

    getDetailDeductiveTotal() {
      return this.getWorksheetDeductiveTotal(this.getDetailWorksheetItems());
    },

    getDetailNetAmount() {
      return this.getWorksheetNetAmount(this.getDetailWorksheetItems());
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

    startNewRequest() {
      this.editingOrder = null;
      this.resetNewOrder();
      this.showRequestModal = true;
    },

    openRequestModal() {
      this.showRequestModal = true;
    },

    closeRequestModal() {
      this.showRequestModal = false;
      this.editingOrder = null;
    },

    openItemModal(index = null) {
      const source = index === null
        ? this.createWorksheetItem((this.newOrder.items?.length || 0) + 1)
        : { ...this.newOrder.items[index] };

      this.itemEditorIndex = index;
      this.itemForm = this.createWorksheetItem(
        source.line_number || (index === null ? (this.newOrder.items?.length || 0) + 1 : index + 1),
        source
      );
      this.showItemModal = true;
    },

    closeItemModal() {
      this.showItemModal = false;
      this.itemEditorIndex = null;
      this.itemForm = this.createWorksheetItem(1);
    },

    saveWorksheetItem() {
      const description = String(this.itemForm.item_description || '').trim();
      if (!description) {
        this.openSaveResultModal('error', 'Validation Error', 'Item description is required.');
        return;
      }

      const normalized = this.normalizeWorksheetItems([this.itemForm])[0];
      if (!normalized) {
        this.openSaveResultModal('error', 'Validation Error', 'Please complete the item values before saving.');
        return;
      }

      if (this.itemEditorIndex === null) {
        this.newOrder.items.push(normalized);
      } else {
        this.newOrder.items.splice(this.itemEditorIndex, 1, normalized);
      }

      this.syncWorksheetLineNumbers();
      this.closeItemModal();
      this.resetDraftTotals();
    },

    resetDraftTotals() {
      // Totals are computed on demand; this keeps the worksheet state normalized.
      this.newOrder.amount_change = this.getWorksheetNetAmount();
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
	        const items = this.serializeWorksheetItems();
	        if (items.length === 0) {
	          this.openSaveResultModal('error', 'Validation Error', 'Add at least one worksheet line item before submitting.');
	          return;
	        }
	        if (this.getWorksheetDeductiveTotal(items) <= 0) {
	          this.openSaveResultModal('error', 'Validation Error', 'Deductive amount must be greater than zero.');
	          return;
	        }

	        await variationOrderService.createVariationOrder({
          vo_number: this.newOrder.vo_number,
          contract_id: this.newOrder.contract_id,
          description: this.newOrder.description,
          reason: this.newOrder.reason,
          amount_change: this.getWorksheetNetAmount(),
          time_impact_days: this.newOrder.time_impact_days,
          items,
        });

        this.closeRequestModal();
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
	        const items = this.serializeWorksheetItems();
	        if (items.length === 0) {
	          this.openSaveResultModal('error', 'Validation Error', 'Add at least one worksheet line item before updating.');
	          return;
	        }
	        if (this.getWorksheetDeductiveTotal(items) <= 0) {
	          this.openSaveResultModal('error', 'Validation Error', 'Deductive amount must be greater than zero.');
	          return;
	        }

	        await variationOrderService.updateVariationOrder(this.editingOrder.id, {
          vo_number: this.newOrder.vo_number,
          contract_id: this.newOrder.contract_id,
          description: this.newOrder.description,
          reason: this.newOrder.reason,
          amount_change: this.getWorksheetNetAmount(),
          time_impact_days: this.newOrder.time_impact_days,
          items,
        });

        this.closeRequestModal();
        this.resetNewOrder();
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
      const existingItems = Array.isArray(order.items) && order.items.length > 0
        ? order.items
        : this.getOrderWorksheetItems(order);
      this.newOrder = {
        vo_number: order.vo_number,
        contract_id: order.contract_id,
        description: order.description,
        reason: order.reason,
        amount_change: order.amount_change,
        time_impact_days: order.time_impact_days,
        items: existingItems.map((item, index) => this.createWorksheetItem(index + 1, {
          line_number: item.line_number || index + 1,
          item_description: item.item_description || '',
          original_qty: item.original_qty ?? null,
          original_unit: item.original_unit || '',
          original_unit_cost: item.original_unit_cost ?? null,
          additive_qty: item.additive_qty ?? null,
          additive_unit: item.additive_unit || '',
          additive_unit_cost: item.additive_unit_cost ?? null,
          deductive_qty: item.deductive_qty ?? null,
          deductive_unit: item.deductive_unit || '',
          deductive_unit_cost: item.deductive_unit_cost ?? null,
          remarks: item.remarks || '',
        })),
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
        items: [],
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

.vo-table thead th,
.vo-item-table thead th,
.vo-line-table thead th {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #6b7280;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
}

.vo-table tbody tr,
.vo-item-table tbody tr,
.vo-line-table tbody tr {
  transition: background-color 0.18s ease, transform 0.18s ease;
}

.vo-table tbody tr:hover,
.vo-item-table tbody tr:hover,
.vo-line-table tbody tr:hover {
  background: #f8fafc;
}

.vo-table {
  min-width: 1380px;
}

.vo-table th,
.vo-table td {
  vertical-align: middle;
  white-space: nowrap;
}

.vo-table td:nth-child(2),
.vo-table td:nth-child(3) {
  white-space: normal;
  min-width: 190px;
}

.vo-num { color: #7b1113; }

.proj-name { font-weight: 600; font-size: 0.85rem; }
.proj-ref { font-size: 0.75rem; color: #888; }

.vo-line-table-wrap {
  max-width: 100%;
  overflow-x: auto;
}

.vo-line-table {
  min-width: 1600px;
}

.vo-line-table th,
.vo-line-table td {
  vertical-align: middle;
  white-space: nowrap;
}

.vo-line-table input.tuao-input-sm {
  min-height: 34px;
  padding: 0.35rem 0.55rem;
  font-size: 0.8rem;
}

.vo-worksheet-toolbar {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 0.95rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.9rem;
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
}

.vo-worksheet-help {
  font-size: 0.92rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.vo-add-item-btn {
  align-self: flex-start;
  white-space: nowrap;
}

.vo-item-table-wrap {
  max-width: 100%;
  overflow-x: auto;
}

.vo-item-table {
  min-width: 1200px;
}

.vo-line-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 72px;
  padding: 0.3rem 0.55rem;
  border-radius: 999px;
  background: #f3f4f6;
  color: #7b1113;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.vo-item-title {
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.2rem;
}

.vo-item-note {
  color: #6b7280;
  font-size: 0.78rem;
  line-height: 1.35;
}

.vo-cell-stack {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.vo-cell-stack strong {
  color: #111827;
  font-weight: 600;
}

.vo-cell-stack span {
  color: #6b7280;
  font-size: 0.78rem;
}

.vo-summary-strip {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 0.75rem;
  width: 100%;
  padding: 0.9rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.85rem;
  background: #f8fafc;
}

.vo-summary-strip > div {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.vo-item-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.vo-item-card {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  padding: 0.95rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.85rem;
  background: #ffffff;
}

.vo-item-card-main {
  min-width: 0;
}

.vo-item-card-line {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #7b1113;
  margin-bottom: 0.2rem;
}

.vo-item-card-title {
  font-size: 0.94rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.35rem;
}

.vo-item-card-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem 1rem;
  font-size: 0.75rem;
  color: #6b7280;
}

.vo-item-card-actions {
  display: flex;
  flex-shrink: 0;
  gap: 0.5rem;
}

.vo-item-card-actions.justify-content-end {
  justify-content: flex-end;
}

.date-req { font-size: 0.8rem; font-weight: 500; }
.date-app { font-size: 0.75rem; color: #555; }
.date-app.muted { color: #aaa; font-style: italic; }

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

.document-upload-panel .tuao-input[type="file"] {
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
