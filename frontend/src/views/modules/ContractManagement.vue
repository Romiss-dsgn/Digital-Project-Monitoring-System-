<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <div v-if="apiError" class="alert alert-danger py-2 px-3 mb-3">
        {{ apiError }}
      </div>

      <!-- ── Top Info Card + Stats ── -->
      <div class="row mb-4">
        <div class="col-lg-6 mb-3 mb-lg-0">
          <div class="card h-100" style="border-radius:1rem;">
            <div class="card-body d-flex flex-column justify-content-between p-4">
              <div>
                <h6 class="fw-bold mb-1" style="color:#c0392b;font-size:0.95rem;">Active Contracts & Procurement</h6>
                <p class="text-secondary small mb-4">
                  Manage fire station construction, equipment procurement, and maintenance
                  services across Region II. Monitor timelines, budget utilization, and document
                  compliance in real-time.
                </p>
              </div>
              <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">
                <button v-if="permissions.create" class="btn btn-primary btn-sm" @click="openCreateContractModal">
                  <i class="material-icons-round" style="font-size:15px;vertical-align:-3px">add</i>
                  New Contract
                </button>
                <button class="btn btn-outline-secondary btn-sm" @click="showAdvancedFilterModal = true">
                  <i class="material-icons-round" style="font-size:15px;vertical-align:-3px">tune</i>
                  Advanced Filter
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="col-lg-6">
          <div class="row g-3">
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card stat-card h-100">
                <div class="card-body p-3">
                  <p class="stat-label">ONGOING PROJECTS</p>
                  <p class="stat-value">{{ summary.ongoing_projects }}</p>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card stat-card h-100">
                <div class="card-body p-3">
                  <p class="stat-label">PENDING REVIEW</p>
                  <p class="stat-value">{{ summary.pending_review }}</p>
                  <div class="pending-bar mt-1"><div class="pending-bar-fill" :style="{ width: pendingReviewWidth + '%' }"></div></div>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card stat-card h-100">
                <div class="card-body p-3">
                  <p class="stat-label">TOTAL VALUE</p>
                  <p class="stat-value">{{ formatCompactPeso(summary.total_value) }}</p>
                  <p class="stat-sub">Fiscal Year {{ summary.fiscal_year }}</p>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
              <div class="card stat-card h-100">
                <div class="card-body p-3">
                  <p class="stat-label">DOCS COMPLIANCE</p>
                  <p class="stat-value compliance-val">{{ summary.docs_compliance }}%</p>
                  <p class="stat-sub compliance-sub">
                    {{ summary.approved_documents }} of {{ summary.total_documents }} approved
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Contract Records Table ── -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header pt-3 px-3 px-sm-4">
              <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-2">
                <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-2 gap-sm-3">
                  <span class="fw-bold" style="font-size:0.875rem;color:#374151;">Contract Records</span>
                  <div class="d-flex flex-wrap gap-1">
                    <button
                      v-for="tab in ['All', 'Active', 'Expired']"
                      :key="tab"
                      type="button"
                      class="tab-pill border-0"
                      :class="{ 'active-tab': activeStatusTab === tab }"
                      @click="setStatusTab(tab)"
                    >
                      {{ tab }}
                    </button>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="text-secondary small">Show</span>
                  <select v-model.number="rowsPerPage" class="form-select form-select-sm" style="width:100px;" @change="currentPage = 1">
                    <option :value="10">10 rows</option>
                    <option :value="25">25 rows</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table align-middle mb-0 stitch-table">
                  <thead>
                    <tr>
                      <th style="width:140px;">CONTRACT ID</th>
                      <th>PROJECT DETAILS</th>
                      <th>CONTRACTOR</th>
                      <th>FINANCIALS</th>
                      <th>TIMELINE</th>
                      <th style="width:60px;"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="isLoading">
                      <td colspan="6" class="text-center py-4 text-secondary">Loading contract records...</td>
                    </tr>
                    <tr v-else-if="filteredContracts.length === 0">
                      <td colspan="6" class="text-center py-4 text-secondary">No contract records found.</td>
                    </tr>
                    <tr v-for="contract in isLoading ? [] : displayedContracts" :key="contract.id">
                      <td>
                        <strong class="contract-id-text">{{ contract.contract_id }}</strong>
                      </td>
                      <td>
                        <div class="fw-600" style="font-size:0.875rem;color:#111827;">{{ contract.project }}</div>
                        <span class="project-tag">{{ contract.category }}</span>
                      </td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <div class="contractor-avatar">{{ contract.initials }}</div>
                          <span style="font-size:0.875rem;color:#374151;">{{ contract.contractor }}</span>
                        </div>
                      </td>
                      <td>
                        <div class="fw-600" style="font-size:0.875rem;color:#111827;">{{ contract.amount }}</div>
                        <div style="font-size:0.75rem;color:#9ca3af;">{{ contract.payment_type }}</div>
                      </td>
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <span style="font-size:0.875rem;color:#6b7280;">{{ contract.duration }}</span>
                          <span v-if="contract.duration !== '—'" class="timeline-divider">|</span>
                          <span :class="timelineClass(contract.timeline_status)">{{ contract.timeline_status }}</span>
                        </div>
                        <div v-if="contract.timeline_bar" class="timeline-bar mt-1">
                          <div class="timeline-bar-fill" :style="{ width: contract.timeline_bar + '%', background: timelineBarColor(contract.timeline_status) }"></div>
                        </div>
                      </td>
                      <td class="text-end">
                        <div class="contract-actions-menu">
                          <button
                            class="btn btn-sm btn-icon btn-light text-secondary"
                            type="button"
                            :aria-expanded="openActionMenuId === contract.id"
                            aria-label="Contract actions"
                            title="Contract actions"
                            @click.stop="toggleActionMenu(contract.id, $event)"
                          >
                            <i class="material-icons-round">more_vert</i>
                          </button>
                          <div
                            v-if="openActionMenuId === contract.id"
                            class="contract-action-menu"
                            :style="{
                              top: `${actionMenuPosition.top}px`,
                              left: `${actionMenuPosition.left}px`,
                            }"
                            role="menu"
                            @click.stop
                          >
                            <button class="contract-action-menu-item" type="button" role="menuitem" @click="handleViewContract(contract)">
                              <i class="material-icons-round dropdown-icon view-icon">visibility</i>
                              View
                            </button>
                            <button
                              v-if="permissions.edit"
                              class="contract-action-menu-item"
                              type="button"
                              role="menuitem"
                              @click="handleEditContract(contract)"
                            >
                              <i class="material-icons-round dropdown-icon edit-icon">edit</i>
                              Edit
                            </button>
                            <button
                              v-if="permissions.delete"
                              class="contract-action-menu-item danger"
                              type="button"
                              role="menuitem"
                              @click="handleDeleteContract(contract)"
                            >
                              <i class="material-icons-round dropdown-icon">archive</i>
                              Archive
                            </button>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 px-3 px-sm-4 py-3 border-top">
                <span class="text-secondary small">
                  Showing {{ paginationFrom }}-{{ paginationTo }} of {{ filteredContracts.length }} contract records
                </span>
                <div class="d-flex flex-wrap gap-1">
                  <button class="btn btn-sm btn-light border pagination-btn" :disabled="currentPage === 1" @click="currentPage--">
                    <i class="material-icons-round" style="font-size:16px;vertical-align:-3px">chevron_left</i>
                  </button>
                  <button
                    v-for="page in totalPages"
                    :key="page"
                    class="btn btn-sm pagination-btn"
                    :class="page === currentPage ? 'btn-primary' : 'btn-light border'"
                    @click="currentPage = page"
                  >{{ page }}</button>
                  <button class="btn btn-sm btn-light border pagination-btn" :disabled="currentPage === totalPages" @click="currentPage++">
                    <i class="material-icons-round" style="font-size:16px;vertical-align:-3px">chevron_right</i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Bottom: Quick Upload ── -->
      <div v-if="documentPermissions.create" class="row">
        <!-- Quick Upload → opens Upload Batch modal -->
        <div class="col-lg-12 mb-4">
          <div class="card h-100">
            <div class="card-body p-3 p-sm-4">
              <h6 class="fw-bold mb-1" style="font-size:0.9rem;">Upload Files</h6>
              <p class="text-secondary small mb-3">
                Drag and drop any contract-related document (PDF, DOCX, XLSX) to automatically link it to the relevant project record.
              </p>
              <div
                class="quick-upload-area"
                :class="{ 'drag-over': dragOver }"
                @click="showUploadModal = true"
                @dragover.prevent="dragOver = true"
                @dragleave.prevent="dragOver = false"
                @drop.prevent="handleFileDrop"
              >
                <i class="material-icons-round" style="font-size:36px;color:#c0392b;display:block;margin-bottom:8px;">add_circle_outline</i>
                <span style="font-size:0.875rem;color:#374151;">Click to select files</span><br>
                <span style="font-size:0.75rem;color:#9ca3af;">PDF, DOCX, XLSX only. Maximum file size: 25MB</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- ═══════════════════════════════════════════
         NEW PROJECT MODAL (unchanged)
    ═══════════════════════════════════════════ -->
    <div v-if="showNewProjectModal" class="modal-overlay" @click.self="showNewProjectModal = false">
      <div class="bfp-modal">
        <div class="bfp-modal-header">
          <div class="bfp-modal-header-left">
            <div class="bfp-modal-emblem">
              <img :src="bfpLogo" alt="BFP Logo" class="bfp-logo-img" />
            </div>
            <div>
              <p class="bfp-modal-agency">Bureau of Fire Protection</p>
              <h5 class="bfp-modal-title">{{ contractForm.id ? "Edit Contract" : "New Contract" }}</h5>
            </div>
          </div>
          <button class="bfp-modal-close" @click="showNewProjectModal = false">
            <i class="material-icons-round">close</i>
          </button>
        </div>
        <div class="bfp-modal-stripe">
          <span>REGION II — CAGAYAN VALLEY</span>
          <span>PROJECT REGISTRATION FORM</span>
        </div>
        <div class="bfp-modal-body">
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">folder_open</i> Project Identification</div>
            <div class="bfp-form-grid">
              <div class="bfp-field-full">
                <label class="bfp-label">Project <span class="bfp-required">*</span></label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">folder_open</i>
                  <select v-model="contractForm.project_id" class="bfp-input bfp-select">
                    <option value="">Select project</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                      {{ project.project_code }} - {{ project.project_name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="bfp-field-half">
                <label class="bfp-label">Contract Title <span class="bfp-required">*</span></label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">business</i>
                  <input v-model="contractForm.contract_title" type="text" class="bfp-input" placeholder="Enter contract title" />
                </div>
              </div>
              <div class="bfp-field-half">
                <label class="bfp-label">Contract Number <span class="bfp-required">*</span></label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">tag</i>
                  <input v-model="contractForm.contract_number" type="text" class="bfp-input" placeholder="e.g. BFP-R2-CON-2024-011" />
                </div>
              </div>
            </div>
          </div>
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">engineering</i> Contractor & Budget</div>
            <div class="bfp-form-grid">
              <div class="bfp-field-full">
                <label class="bfp-label">Contractor / Firm <span class="bfp-required">*</span></label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">groups</i>
                  <select v-model="contractForm.contractor_id" class="bfp-input bfp-select">
                    <option value="">Select contractor</option>
                    <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">
                      {{ contractor.company_name }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="bfp-field-full">
                <label class="bfp-label">Project Budget (₱) <span class="bfp-required">*</span></label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">payments</i>
                  <input v-model="contractForm.original_contract_amount" type="number" min="0" class="bfp-input" placeholder="e.g. 15500000" />
                </div>
              </div>
            </div>
          </div>
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">date_range</i> Schedule</div>
            <div class="bfp-form-grid">
              <div class="bfp-field-half">
                <label class="bfp-label">Start Date</label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">event</i>
                  <input v-model="contractForm.start_date" type="date" class="bfp-input" />
                </div>
              </div>
              <div class="bfp-field-half">
                <label class="bfp-label">End Date</label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">event_available</i>
                  <input v-model="contractForm.end_date" type="date" class="bfp-input" />
                </div>
              </div>
              <div class="bfp-field-full">
                <label class="bfp-label">Status</label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">fact_check</i>
                  <select v-model="contractForm.status" class="bfp-input bfp-select">
                    <option v-for="status in contractStatusOptions" :key="status" :value="status">{{ status }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">notes</i> Additional Remarks</div>
            <div class="bfp-form-grid">
              <div class="bfp-field-full">
                <div class="bfp-input-wrap">
                  <textarea v-model="contractForm.remarks" class="bfp-input bfp-textarea" rows="3" placeholder="Optional — observations, special conditions, remarks..."></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bfp-modal-footer">
          <div class="bfp-footer-note">
            <i class="material-icons-round" style="font-size:14px;vertical-align:-2px">info</i>
            Fields marked <span style="color:#c0392b;font-weight:700">*</span> are required.
          </div>
          <div class="bfp-footer-actions">
            <button class="bfp-btn-cancel" @click="showNewProjectModal = false">Cancel</button>
            <button class="bfp-btn-save" @click="saveContract" :disabled="isSaving">
              <i class="material-icons-round">save</i>
              {{ isSaving ? "Saving..." : contractForm.id ? "Update Contract" : "Create Contract" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════
         UPLOAD BATCH MODAL (unchanged)
         — triggered by Quick Upload area
    ═══════════════════════════════════════════ -->
    <div v-if="showUploadModal" class="modal-overlay" @click.self="showUploadModal = false">
      <div class="bfp-modal">
        <div class="bfp-modal-header">
          <div class="bfp-modal-header-left">
            <div class="bfp-modal-emblem">
              <img :src="bfpLogo" alt="BFP Logo" class="bfp-logo-img" />
            </div>
            <div>
              <p class="bfp-modal-agency">Bureau of Fire Protection</p>
              <h5 class="bfp-modal-title">Upload Batch Documents</h5>
            </div>
          </div>
          <button class="bfp-modal-close" @click="showUploadModal = false">
            <i class="material-icons-round">close</i>
          </button>
        </div>
        <div class="bfp-modal-stripe">
          <span>REGION II — CAGAYAN VALLEY</span>
          <span>BATCH DOCUMENT UPLOAD</span>
        </div>
        <div class="bfp-modal-body">
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">cloud_upload</i> Upload Files</div>
            <div
              class="bfp-upload-area"
              :class="{ 'drag-over': dragOver }"
              @dragover.prevent="dragOver = true"
              @dragleave.prevent="dragOver = false"
              @drop.prevent="handleFileDrop"
            >
              <i class="material-icons-round bfp-upload-icon">cloud_upload</i>
              <p class="bfp-upload-title">Drag and drop files here</p>
              <p class="bfp-upload-sub">or click to browse from your computer</p>
              <label>
                <input
                  type="file"
                  multiple
                  hidden
                  accept=".pdf,.docx,.xlsx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                  @change="handleFileSelect"
                />
                <span class="bfp-upload-btn">
                  <i class="material-icons-round" style="font-size:15px;vertical-align:-3px">folder_open</i>
                  Choose Files
                </span>
              </label>
              <div class="bfp-format-pills">
                <span class="bfp-format-pill">PDF</span>
                <span class="bfp-format-pill">DOCX</span>
                <span class="bfp-format-pill">XLSX</span>
              </div>
            </div>
            <div v-if="uploadedFiles.length > 0" class="bfp-file-list">
              <div v-for="(file, index) in uploadedFiles" :key="index" class="bfp-file-item">
                <i class="material-icons-round bfp-file-icon">description</i>
                <span class="bfp-file-name">{{ file.name }}</span>
                <span class="bfp-file-size">{{ (file.size / 1024).toFixed(1) }} KB</span>
                <button class="bfp-file-remove" @click="removeFile(index)">
                  <i class="material-icons-round">close</i>
                </button>
              </div>
            </div>
          </div>
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">label</i> Upload Options</div>
            <div class="bfp-form-grid">
              <div class="bfp-field-half">
                <label class="bfp-label">Document Type</label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">article</i>
                  <select v-model="uploadForm.document_category" class="bfp-input bfp-select">
                    <option value="">Select type</option>
                    <option>Contract Document</option>
                    <option>Procurement Record</option>
                    <option>Compliance Report</option>
                    <option>Inspection Report</option>
                  </select>
                </div>
              </div>
              <div class="bfp-field-half">
                <label class="bfp-label">Related Contract</label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">tag</i>
                  <select v-model="uploadForm.contract_id" class="bfp-input bfp-select">
                    <option value="">Select contract</option>
                    <option v-for="c in contracts" :key="c.id" :value="c.id">{{ c.contract_id }}</option>
                  </select>
                </div>
              </div>
              <div class="bfp-field-full">
                <label class="bfp-label">Remarks</label>
                <div class="bfp-input-wrap">
                  <textarea v-model="uploadForm.remarks" class="bfp-input bfp-textarea" rows="2" placeholder="Optional — describe the batch contents…"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bfp-modal-footer">
          <div class="bfp-footer-note">
            <i class="material-icons-round" style="font-size:14px;vertical-align:-2px">info</i>
            {{ uploadedFiles.length > 0 ? `${uploadedFiles.length} file(s) selected.` : 'No files selected.' }}
          </div>
          <div class="bfp-footer-actions">
            <button class="bfp-btn-cancel" @click="showUploadModal = false">Cancel</button>
            <button class="bfp-btn-save" :disabled="isUploading" @click="uploadContractDocuments">
              <i class="material-icons-round">upload</i>
              {{ isUploading ? 'Uploading...' : 'Upload Files' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════
         ADVANCED FILTER MODAL
    ═══════════════════════════════════════════ -->
    <div v-if="showAdvancedFilterModal" class="modal-overlay" @click.self="showAdvancedFilterModal = false">
      <div class="bfp-modal" style="width: 700px;">
        <div class="bfp-modal-header">
          <div class="bfp-modal-header-left">
            <div class="bfp-modal-emblem">
              <img :src="bfpLogo" alt="BFP Logo" class="bfp-logo-img" />
            </div>
            <div>
              <p class="bfp-modal-agency">Bureau of Fire Protection</p>
              <h5 class="bfp-modal-title">Advanced Filter</h5>
            </div>
          </div>
          <button class="bfp-modal-close" @click="showAdvancedFilterModal = false">
            <i class="material-icons-round">close</i>
          </button>
        </div>
        <div class="bfp-modal-stripe">
          <span>REGION II — CAGAYAN VALLEY</span>
          <span>CONTRACT SEARCH & FILTERING</span>
        </div>
        <div class="bfp-modal-body">
          <!-- Category Filter -->
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">category</i> Category</div>
            <div class="filter-checkbox-group">
              <label v-for="category in categoryOptions" :key="category" class="filter-checkbox">
                <input type="checkbox" v-model="filters.categories" :value="category" />
                <span>{{ category }}</span>
              </label>
            </div>
          </div>

          <!-- Payment Type Filter -->
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">payments</i> Payment Type</div>
            <div class="filter-checkbox-group">
              <label v-for="paymentType in paymentTypeOptions" :key="paymentType" class="filter-checkbox">
                <input type="checkbox" v-model="filters.paymentTypes" :value="paymentType" />
                <span>{{ paymentType }}</span>
              </label>
            </div>
          </div>

          <!-- Timeline Status Filter -->
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">schedule</i> Timeline Status</div>
            <div class="filter-checkbox-group">
              <label v-for="status in timelineStatusOptions" :key="status" class="filter-checkbox">
                <input type="checkbox" v-model="filters.timelineStatus" :value="status" />
                <span>{{ status }}</span>
              </label>
            </div>
          </div>

          <!-- Contract Status Filter -->
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">check_circle</i> Contract Status</div>
            <div class="filter-checkbox-group">
              <label v-for="status in contractStatusOptions" :key="status" class="filter-checkbox">
                <input type="checkbox" v-model="filters.contractStatus" :value="status" />
                <span>{{ status }}</span>
              </label>
            </div>
          </div>

          <!-- Compliance Status Filter -->
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">verified</i> Compliance Status</div>
            <div class="filter-checkbox-group">
              <label v-for="compliance in complianceStatusOptions" :key="compliance" class="filter-checkbox">
                <input type="checkbox" v-model="filters.complianceStatus" :value="compliance" />
                <span>{{ compliance }}</span>
              </label>
            </div>
          </div>

          <!-- Budget Range Filter -->
          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">trending_up</i> Budget Range (₱)</div>
            <div class="bfp-form-grid">
              <div class="bfp-field-half">
                <label class="bfp-label">Minimum</label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">payments</i>
                  <input type="number" class="bfp-input" v-model="filters.budgetMin" placeholder="e.g. 1000000" />
                </div>
              </div>
              <div class="bfp-field-half">
                <label class="bfp-label">Maximum</label>
                <div class="bfp-input-wrap">
                  <i class="material-icons-round bfp-input-icon">payments</i>
                  <input type="number" class="bfp-input" v-model="filters.budgetMax" placeholder="e.g. 50000000" />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bfp-modal-footer">
          <div class="bfp-footer-note">
            <i class="material-icons-round" style="font-size:14px;vertical-align:-2px">info</i>
            {{ hasActiveFilters() ? 'Filters applied' : 'No filters selected' }}
          </div>
          <div class="bfp-footer-actions">
            <button class="bfp-btn-cancel" @click="clearFilters(); showAdvancedFilterModal = false">Clear All</button>
            <button class="bfp-btn-save" @click="applyFilters"><i class="material-icons-round">filter_list</i> Apply Filters</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Database-backed contract details and document review. -->
    <div v-if="showDetailsModal && selectedContract" class="modal-overlay" @click.self="showDetailsModal = false">
      <div class="bfp-modal" style="width:760px;">
        <div class="bfp-modal-header">
          <div>
            <p class="bfp-modal-agency">Contract Record</p>
            <h5 class="bfp-modal-title">{{ selectedContract.contract_number }}</h5>
          </div>
          <button class="bfp-modal-close" @click="showDetailsModal = false">
            <i class="material-icons-round">close</i>
          </button>
        </div>
        <div class="bfp-modal-body">
          <div class="bfp-form-grid mb-3">
            <div class="bfp-field-half"><strong>Title</strong><div>{{ selectedContract.contract_title }}</div></div>
            <div class="bfp-field-half"><strong>Status</strong><div>{{ selectedContract.status }}</div></div>
            <div class="bfp-field-half"><strong>Project</strong><div>{{ selectedContract.project_name }}</div></div>
            <div class="bfp-field-half"><strong>Contractor</strong><div>{{ selectedContract.contractor_name }}</div></div>
            <div class="bfp-field-half"><strong>Value</strong><div>{{ formatPeso(selectedContract.revised_contract_amount) }}</div></div>
            <div class="bfp-field-half"><strong>Schedule</strong><div>{{ selectedContract.start_date }} to {{ selectedContract.end_date }}</div></div>
          </div>

          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">folder</i> Contract Documents</div>
            <p v-if="!selectedContract.documents?.length" class="text-secondary small mb-0">No documents uploaded.</p>
            <div v-for="document in selectedContract.documents" :key="document.id" class="bfp-file-item mb-2">
              <i class="material-icons-round bfp-file-icon">description</i>
              <div class="flex-grow-1">
                <div class="bfp-file-name">{{ document.file_name }}</div>
                <small class="text-secondary">{{ document.document_category }} · {{ document.status }}</small>
              </div>
              <button class="btn btn-sm btn-light" title="Download" @click="downloadContractDocument(document)">
                <i class="material-icons-round">download</i>
              </button>
              <button v-if="documentPermissions.approve" class="btn btn-sm btn-success" title="Approve" @click="reviewContractDocument(document, 'Approved')">
                <i class="material-icons-round">check</i>
              </button>
              <button v-if="documentPermissions.approve" class="btn btn-sm btn-outline-danger" title="Reject" @click="reviewContractDocument(document, 'Rejected')">
                <i class="material-icons-round">close</i>
              </button>
            </div>
          </div>

          <div class="bfp-section">
            <div class="bfp-section-label"><i class="material-icons-round">date_range</i> Contract Dates</div>
            <div class="bfp-form-grid">
              <div class="bfp-field-half">
                <label class="bfp-label">Start Date From</label>
                <input v-model="filters.startDateFrom" type="date" class="bfp-input" />
              </div>
              <div class="bfp-field-half">
                <label class="bfp-label">Start Date To</label>
                <input v-model="filters.startDateTo" type="date" class="bfp-input" />
              </div>
              <div class="bfp-field-half">
                <label class="bfp-label">End Date From</label>
                <input v-model="filters.endDateFrom" type="date" class="bfp-input" />
              </div>
              <div class="bfp-field-half">
                <label class="bfp-label">End Date To</label>
                <input v-model="filters.endDateTo" type="date" class="bfp-input" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showSaveResultModal" class="modal-overlay" @click.self="closeSaveResultModal">
      <div class="bfp-modal" style="width: 520px;">
        <div class="bfp-modal-header">
          <div>
            <p class="bfp-modal-agency">Contract Management</p>
            <h5 class="bfp-modal-title">{{ saveResultTitle }}</h5>
          </div>
          <button class="bfp-modal-close" @click="closeSaveResultModal">
            <i class="material-icons-round">close</i>
          </button>
        </div>
        <div class="bfp-modal-stripe">
          <span>REGION II — CAGAYAN VALLEY</span>
          <span>{{ saveResultStatus === "success" ? "DATA SAVED" : "SAVE FAILED" }}</span>
        </div>
        <div class="bfp-modal-body">
          <div class="save-result-card" :class="saveResultStatus">
            <i class="material-icons-round save-result-icon">
              {{ saveResultStatus === "success" ? "check_circle" : "error" }}
            </i>
            <div>
              <div class="save-result-message">{{ saveResultMessage }}</div>
              <div class="save-result-subtext">
                {{ saveResultStatus === "success" ? "You can continue working or close this message." : "Please correct the issue and try again." }}
              </div>
            </div>
          </div>
        </div>
        <div class="bfp-modal-footer">
          <div class="bfp-footer-note">
            <i class="material-icons-round" style="font-size:14px;vertical-align:-2px">info</i>
            {{ saveResultStatus === "success" ? "Operation completed successfully." : "Operation did not complete." }}
          </div>
          <div class="bfp-footer-actions">
            <button class="bfp-btn-save" @click="closeSaveResultModal">
              <i class="material-icons-round">check</i>
              OK
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import bfpLogo from "@/assets/img/BFP 11.png";
import contractService from "@/services/contract.service";

const emptyContractForm = () => ({
  id: null,
  contract_number: "",
  contract_title: "",
  project_id: "",
  contractor_id: "",
  contract_type: "Infrastructure Works",
  original_contract_amount: "",
  start_date: "",
  end_date: "",
  status: "Draft",
  remarks: "",
});

export default {
  name: "ContractManagement",
  data() {
    return {
      bfpLogo,
      showNewProjectModal: false,
      showUploadModal: false,
      showAdvancedFilterModal: false,
      showDetailsModal: false,
      showSaveResultModal: false,
      isLoading: false,
      isSaving: false,
      isUploading: false,
      apiError: "",
      projects: [],
      contractors: [],
      selectedContract: null,
      saveResultStatus: "success",
      saveResultTitle: "",
      saveResultMessage: "",
      activeStatusTab: "All",
      currentPage: 1,
      rowsPerPage: 10,
      summary: {
        ongoing_projects: 0,
        active_contracts: 0,
        pending_review: 0,
        total_value: 0,
        docs_compliance: 0,
        approved_documents: 0,
        total_documents: 0,
        fiscal_year: new Date().getFullYear(),
        recent_documents: [],
      },
      permissions: {
        view: false,
        create: false,
        edit: false,
        delete: false,
        approve: false,
        export: false,
      },
      documentPermissions: {
        view: false,
        create: false,
        edit: false,
        delete: false,
        approve: false,
        export: false,
      },
      contractForm: emptyContractForm(),
      uploadForm: {
        contract_id: "",
        document_category: "Contract Document",
        remarks: "",
      },
      dragOver: false,
      uploadedFiles: [],
      maxUploadFileSizeMb: 25,
      allowedUploadExtensions: ['.pdf', '.docx', '.xlsx'],
      allowedUploadTypes: [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      ],
      // Filter states
      filters: {
        categories: [],
        paymentTypes: [],
        timelineStatus: [],
        contractStatus: [],
        contractors: [],
        complianceStatus: [],
        budgetMin: null,
        budgetMax: null,
        startDateFrom: null,
        startDateTo: null,
        endDateFrom: null,
        endDateTo: null
      },
      // Filter options
      categoryOptions: [],
      paymentTypeOptions: [],
      timelineStatusOptions: ['Draft', 'Pending Review', 'Active', 'Delayed', 'Completed', 'Rejected', 'Terminated', 'Expired'],
      contractStatusOptions: ['Draft', 'Pending Review', 'Active', 'Delayed', 'Completed', 'Rejected', 'Terminated', 'Expired'],
      complianceStatusOptions: ['Full Compliance', 'Minor Issues', 'Major Issues', 'Pending Review'],
      contracts: [],
      openActionMenuId: null,
      actionMenuPosition: {
        top: 0,
        left: 0,
      },
    };
  },
  computed: {
    filteredContracts() {
      return this.getFilteredContracts();
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.filteredContracts.length / this.rowsPerPage));
    },
    displayedContracts() {
      const start = (this.currentPage - 1) * this.rowsPerPage;
      return this.filteredContracts.slice(start, start + this.rowsPerPage);
    },
    paginationFrom() {
      return this.filteredContracts.length === 0
        ? 0
        : (this.currentPage - 1) * this.rowsPerPage + 1;
    },
    paginationTo() {
      return Math.min(this.currentPage * this.rowsPerPage, this.filteredContracts.length);
    },
    pendingReviewWidth() {
      if (!this.contracts.length) return 0;
      return Math.min(100, Math.round((this.summary.pending_review / this.contracts.length) * 100));
    },
  },
  async mounted() {
    await this.loadContractManagement();
    document.addEventListener("click", this.closeActionMenu);
    window.addEventListener("resize", this.closeActionMenu);
    window.addEventListener("scroll", this.closeActionMenu, true);
  },
  beforeUnmount() {
    document.removeEventListener("click", this.closeActionMenu);
    window.removeEventListener("resize", this.closeActionMenu);
    window.removeEventListener("scroll", this.closeActionMenu, true);
  },
  methods: {
    toggleActionMenu(contractId, event) {
      if (this.openActionMenuId === contractId) {
        this.closeActionMenu();
        return;
      }

      this.positionActionMenu(event.currentTarget);
      this.openActionMenuId = contractId;
    },
    positionActionMenu(trigger) {
      const rect = trigger.getBoundingClientRect();
      const menuWidth = 148;
      const menuHeight = 132;
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
    handleViewContract(contract) {
      this.closeActionMenu();
      this.viewContract(contract);
    },
    handleEditContract(contract) {
      this.closeActionMenu();
      this.editContract(contract);
    },
    handleDeleteContract(contract) {
      this.closeActionMenu();
      this.deleteContract(contract);
    },
    openCreateContractModal() {
      this.apiError = "";
      this.contractForm = emptyContractForm();
      this.showNewProjectModal = true;
    },
    openSaveResultModal(status, title, message) {
      this.saveResultStatus = status;
      this.saveResultTitle = title;
      this.saveResultMessage = message;
      this.showSaveResultModal = true;
    },
    closeSaveResultModal() {
      this.showSaveResultModal = false;
      this.saveResultStatus = "success";
      this.saveResultTitle = "";
      this.saveResultMessage = "";
    },
    async loadContractManagement() {
      this.isLoading = true;
      this.apiError = "";

      try {
        const [contractResponse, options, summary] = await Promise.all([
          contractService.getContracts(),
          contractService.getOptions(),
          contractService.getSummary(),
        ]);

        this.contracts = (contractResponse.data || []).map(this.mapApiContractToTable);
        this.projects = options.projects || [];
        this.contractors = options.contractors || [];
        this.contractStatusOptions = options.statuses || this.contractStatusOptions;
        this.permissions = options.permissions || summary.permissions || this.permissions;
        this.documentPermissions = options.document_permissions || this.documentPermissions;
        this.summary = summary;
        this.categoryOptions = [...new Set(this.contracts.map((contract) => contract.category).filter(Boolean))];
        this.paymentTypeOptions = [...new Set(this.contracts.map((contract) => contract.payment_type).filter(Boolean))];
        this.currentPage = 1;
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to load contract records.");
      } finally {
        this.isLoading = false;
      }
    },
    async saveContract() {
      this.apiError = "";

      const validationError = this.validateContractForm();
      if (validationError) {
        this.apiError = validationError;
        this.openSaveResultModal("error", "Save Failed", validationError);
        return;
      }

      this.isSaving = true;
      const payload = this.contractPayload();
      const isEditing = !!this.contractForm.id;

      try {
        if (isEditing) {
          await contractService.updateContract(this.contractForm.id, payload);
        } else {
          await contractService.createContract(payload);
        }

        this.showNewProjectModal = false;
        this.contractForm = emptyContractForm();
        this.openSaveResultModal(
          "success",
          isEditing ? "Contract Updated Successfully" : "Contract Added Successfully",
          isEditing ? "The contract data has been updated successfully." : "The contract data has been added successfully."
        );
        await this.loadContractManagement().catch((error) => {
          this.apiError = this.errorMessage(error, "Contract saved, but the table could not refresh.");
        });
      } catch (error) {
        const message = this.errorMessage(error, "Unable to save contract.");
        this.apiError = message;
        this.openSaveResultModal("error", "Save Failed", message);
      } finally {
        this.isSaving = false;
      }
    },
    async viewContract(contract) {
      this.apiError = "";

      try {
        this.selectedContract = await contractService.getContract(contract.id);
        this.showDetailsModal = true;
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to load contract details.");
      }
    },
    editContract(contract) {
      const raw = contract.raw || contract;
      this.contractForm = {
        id: raw.id,
        contract_number: raw.contract_number,
        contract_title: raw.contract_title,
        project_id: raw.project_id,
        contractor_id: raw.contractor_id,
        contract_type: raw.contract_type || "Infrastructure Works",
        original_contract_amount: raw.original_contract_amount,
        start_date: raw.start_date,
        end_date: raw.end_date,
        status: raw.status || "Draft",
        remarks: raw.remarks || "",
      };
      this.apiError = "";
      this.showNewProjectModal = true;
    },
    contractPayload() {
      // Send only API fields, normalized to the data types expected by Laravel validation.
      return {
        contract_number: this.normalizeContractNumber(this.contractForm.contract_number),
        contract_title: String(this.contractForm.contract_title || "").trim(),
        project_id: Number(this.contractForm.project_id),
        contractor_id: Number(this.contractForm.contractor_id),
        contract_type: String(this.contractForm.contract_type || "Infrastructure Works").trim(),
        original_contract_amount: Number(this.contractForm.original_contract_amount),
        start_date: this.contractForm.start_date || null,
        end_date: this.contractForm.end_date || null,
        status: this.contractForm.status || "Draft",
        remarks: String(this.contractForm.remarks || "").trim() || null,
      };
    },
    normalizeContractNumber(value) {
      return String(value || "").trim().toUpperCase();
    },
    validateContractForm() {
      const contractNumber = this.normalizeContractNumber(this.contractForm.contract_number);
      const amount = Number(this.contractForm.original_contract_amount);

      if (!this.contractForm.project_id) {
        return "Select a project before saving the contract.";
      }
      if (!this.contractForm.contractor_id) {
        return "Select a contractor before saving the contract.";
      }
      if (!String(this.contractForm.contract_title || "").trim()) {
        return "Enter a contract title before saving.";
      }
      if (!/^BFP-R2-CON-\d{4}-\d{3,}$/i.test(contractNumber)) {
        return "Use a valid contract number format: BFP-R2-CON-YYYY-NNN.";
      }
      if (this.contractForm.original_contract_amount === "" || Number.isNaN(amount) || amount < 0) {
        return "Enter a valid project budget amount.";
      }
      if (
        this.contractForm.start_date &&
        this.contractForm.end_date &&
        new Date(this.contractForm.end_date) < new Date(this.contractForm.start_date)
      ) {
        return "End date must be the same as or later than the start date.";
      }

      return "";
    },
    async deleteContract(contract) {
      if (!confirm(`Archive ${contract.contract_id}?`)) {
        return;
      }

      try {
        await contractService.archiveContract(contract.id);
        await this.loadContractManagement();
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to archive contract.");
      }
    },
    mapApiContractToTable(contract) {
      return {
        id: contract.id,
        contract_id: contract.contract_number,
        contractor: contract.contractor_name || "Unassigned",
        initials: this.getInitials(contract.contractor_name),
        project: contract.contract_title,
        category: contract.project_type || "INFRASTRUCTURE",
        amount: this.formatPeso(contract.revised_contract_amount),
        payment_type: contract.contract_type || "Contract",
        duration: this.formatDuration(contract.start_date, contract.end_date),
        timeline_status: contract.status || "Draft",
        timeline_bar: this.getTimelineProgress(contract.start_date, contract.end_date),
        status: contract.status || "Draft",
        compliance: this.complianceLabel(contract.document_count, contract.document_compliance),
        raw: contract,
      };
    },
    getInitials(name) {
      return String(name || "NA")
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join("")
        .toUpperCase();
    },
    formatPeso(value) {
      return new Intl.NumberFormat("en-PH", {
        style: "currency",
        currency: "PHP",
        maximumFractionDigits: 2,
      }).format(Number(value || 0));
    },
    formatCompactPeso(value) {
      const amount = Number(value || 0);
      if (amount >= 1000000000) return `₱${(amount / 1000000000).toFixed(1)}B`;
      if (amount >= 1000000) return `₱${(amount / 1000000).toFixed(1)}M`;
      return this.formatPeso(amount);
    },
    complianceLabel(documentCount, percentage) {
      if (!documentCount) return "Pending Review";
      if (Number(percentage) === 100) return "Full Compliance";
      if (Number(percentage) >= 50) return "Minor Issues";
      return "Major Issues";
    },
    errorMessage(error, fallback) {
      const errors = error?.response?.data?.errors;
      if (errors && typeof errors === "object" && !Array.isArray(errors)) {
        return Object.values(errors).flat().join(" ");
      }
      return error?.response?.data?.message || fallback;
    },
    setStatusTab(tab) {
      this.activeStatusTab = tab;
      this.currentPage = 1;
    },
    formatDuration(start, end) {
      if (!start || !end) {
        return "—";
      }

      const startDate = new Date(start);
      const endDate = new Date(end);
      const days = Math.ceil((endDate - startDate) / 86400000);

      return `${days}d`;
    },
    getTimelineProgress(start, end) {
      if (!start || !end) {
        return null;
      }

      const startDate = new Date(start).getTime();
      const endDate = new Date(end).getTime();
      const today = Date.now();

      if (today <= startDate) {
        return 0;
      }

      if (today >= endDate) {
        return 100;
      }

      return Math.round(((today - startDate) / (endDate - startDate)) * 100);
    },
    isValidFileType(file) {
      const fileExtension = file.name.slice(file.name.lastIndexOf('.')).toLowerCase();
      return this.allowedUploadTypes.includes(file.type) || this.allowedUploadExtensions.includes(fileExtension);
    },
    isValidFileSize(file) {
      return file.size <= this.maxUploadFileSizeMb * 1024 * 1024;
    },
    splitUploadFiles(files) {
      return files.reduce((groups, file) => {
        if (!this.isValidFileType(file)) {
          groups.invalidType.push(file);
        } else if (!this.isValidFileSize(file)) {
          groups.invalidSize.push(file);
        } else {
          groups.valid.push(file);
        }
        return groups;
      }, { valid: [], invalidType: [], invalidSize: [] });
    },
    showUploadRestrictionAlert(invalidType, invalidSize) {
      const messages = [];
      if (invalidType.length > 0) {
        messages.push(`Only PDF, DOCX, and XLSX files are allowed.\nRejected type: ${invalidType.map(f => f.name).join(', ')}`);
      }
      if (invalidSize.length > 0) {
        messages.push(`Maximum file size is ${this.maxUploadFileSizeMb}MB.\nToo large: ${invalidSize.map(f => f.name).join(', ')}`);
      }
      if (messages.length > 0) {
        alert(messages.join('\n\n'));
      }
    },
    handleFileDrop(event) {
      this.dragOver = false;
      const files = Array.from(event.dataTransfer.files);
      const groups = this.splitUploadFiles(files);
      this.showUploadRestrictionAlert(groups.invalidType, groups.invalidSize);
      if (groups.valid.length > 0) {
        this.uploadedFiles = [...this.uploadedFiles, ...groups.valid];
        this.showUploadModal = true;
      }
    },
    handleFileSelect(event) {
      const files = Array.from(event.target.files);
      const groups = this.splitUploadFiles(files);
      this.showUploadRestrictionAlert(groups.invalidType, groups.invalidSize);
      if (groups.valid.length > 0) {
        this.uploadedFiles = [...this.uploadedFiles, ...groups.valid];
      }
    },
    removeFile(index) { this.uploadedFiles.splice(index, 1); },
    async uploadContractDocuments() {
      if (!this.uploadForm.contract_id || this.uploadedFiles.length === 0) {
        this.apiError = "Select a contract and at least one valid file.";
        return;
      }

      this.isUploading = true;
      this.apiError = "";

      try {
        await contractService.uploadDocuments(
          this.uploadForm.contract_id,
          this.uploadedFiles,
          this.uploadForm
        );
        this.uploadedFiles = [];
        this.uploadForm = { contract_id: "", document_category: "Contract Document", remarks: "" };
        this.showUploadModal = false;
        await this.loadContractManagement();
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to upload contract documents.");
      } finally {
        this.isUploading = false;
      }
    },
    async reviewContractDocument(document, status) {
      try {
        await contractService.updateDocumentStatus(document.id, status);
        this.selectedContract = await contractService.getContract(this.selectedContract.id);
        await this.loadContractManagement();
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to review document.");
      }
    },
    async downloadContractDocument(document) {
      try {
        await contractService.downloadDocument(document);
      } catch (error) {
        this.apiError = this.errorMessage(error, "Unable to download document.");
      }
    },
    // Filter methods
    getFilteredContracts() {
      return this.contracts.filter(contract => {
        if (this.activeStatusTab !== "All" && contract.status !== this.activeStatusTab) {
          return false;
        }
        // Category filter
        if (this.filters.categories.length > 0 && !this.filters.categories.includes(contract.category)) {
          return false;
        }
        // Payment type filter
        if (this.filters.paymentTypes.length > 0 && !this.filters.paymentTypes.includes(contract.payment_type)) {
          return false;
        }
        // Timeline status filter
        if (this.filters.timelineStatus.length > 0 && !this.filters.timelineStatus.includes(contract.timeline_status)) {
          return false;
        }
        // Contract status filter
        if (this.filters.contractStatus.length > 0 && !this.filters.contractStatus.includes(contract.status)) {
          return false;
        }
        // Contractor filter
        if (this.filters.contractors.length > 0 && !this.filters.contractors.includes(contract.contractor)) {
          return false;
        }
        // Compliance status filter
        if (this.filters.complianceStatus.length > 0 && !this.filters.complianceStatus.includes(contract.compliance)) {
          return false;
        }
        // Budget range filter
        const amount = Number(String(contract.amount).replace(/[^\d.-]/g, ""));
        if (this.filters.budgetMin && amount < parseInt(this.filters.budgetMin)) {
          return false;
        }
        if (this.filters.budgetMax && amount > parseInt(this.filters.budgetMax)) {
          return false;
        }
        const startDate = contract.raw?.start_date;
        const endDate = contract.raw?.end_date;
        if (this.filters.startDateFrom && startDate < this.filters.startDateFrom) return false;
        if (this.filters.startDateTo && startDate > this.filters.startDateTo) return false;
        if (this.filters.endDateFrom && endDate < this.filters.endDateFrom) return false;
        if (this.filters.endDateTo && endDate > this.filters.endDateTo) return false;
        return true;
      });
    },
    applyFilters() {
      this.currentPage = 1;
      this.showAdvancedFilterModal = false;
    },
    clearFilters() {
      this.filters = {
        categories: [],
        paymentTypes: [],
        timelineStatus: [],
        contractStatus: [],
        contractors: [],
        complianceStatus: [],
        budgetMin: null,
        budgetMax: null,
        startDateFrom: null,
        startDateTo: null,
        endDateFrom: null,
        endDateTo: null
      };
      this.currentPage = 1;
    },
    hasActiveFilters() {
      return this.filters.categories.length > 0 || 
             this.filters.paymentTypes.length > 0 || 
             this.filters.timelineStatus.length > 0 || 
             this.filters.contractStatus.length > 0 || 
             this.filters.contractors.length > 0 || 
             this.filters.complianceStatus.length > 0 || 
             this.filters.budgetMin || 
             this.filters.budgetMax ||
             this.filters.startDateFrom ||
             this.filters.startDateTo ||
             this.filters.endDateFrom ||
             this.filters.endDateTo;
    },
    timelineClass(status) {
      const label = String(status || "");
      if (label === "Completed") return "timeline-completed";
      if (label === "Pending Start" || label === "Pending Review" || label === "Draft") return "timeline-pending";
      if (label === "Delayed" || label.includes("left")) return "timeline-urgent";
      return "timeline-default";
    },
    timelineBarColor(status) {
      const label = String(status || "");
      if (label === "Completed") return "#22c55e";
      if (label === "Delayed" || label.includes("left")) return "#f97316";
      return "#c0392b";
    }
  }
};
</script>

<style scoped>
/* ════════════════════════════════════════════
   PAGE BASE
════════════════════════════════════════════ */
.module-page {
  background: #f5f7fa;
  min-height: 100vh;
}

.btn-primary {
  background: #c0392b;
  border-color: #c0392b;
  color: #fff;
}
.btn-primary:hover {
  background: #a93226;
  border-color: #a93226;
}
.btn-outline-secondary {
  color: #6c757d;
  border-color: #dfe4ed;
}
.btn-outline-secondary:hover {
  background: #f3f4f6;
  border-color: #dfe4ed;
  color: #495057;
}

.card {
  border: none;
  border-radius: 1rem;
  box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
  background: #ffffff;
}
.card-header {
  background: transparent;
  border-bottom: 1px solid #e5e7eb;
}

/* ════════════════════════════════════════════
   STAT CARDS
════════════════════════════════════════════ */
.stat-card {
  border-radius: 1rem;
}
.stat-label {
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6b7280;
  margin-bottom: 6px;
}
.stat-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #111827;
  margin: 0;
  line-height: 1;
}
.stat-sub {
  font-size: 11px;
  color: #9ca3af;
  margin: 4px 0 0;
}
.pending-val { color: #111827; }
.pending-bar {
  height: 4px;
  background: #e5e7eb;
  border-radius: 99px;
  margin-top: 8px;
}
.pending-bar-fill {
  width: 35%;
  height: 100%;
  background: #f59e0b;
  border-radius: 99px;
}
.compliance-val { color: #16a34a; }
.compliance-sub { color: #16a34a; font-size: 11px; margin: 4px 0 0; }

/* ════════════════════════════════════════════
   TABLE TABS
════════════════════════════════════════════ */
.tab-pill {
  padding: 5px 14px;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  color: #6b7280;
  background: transparent;
}
.tab-pill.active-tab {
  background: #c0392b;
  color: #fff;
}
.tab-pill:not(.active-tab):hover {
  background: #f3f4f6;
  color: #374151;
}

/* ════════════════════════════════════════════
   STITCH TABLE
════════════════════════════════════════════ */
.stitch-table { font-size: 0.875rem; }
.stitch-table thead th {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: #6b7280;
  border-bottom: 1px solid #e5e7eb;
  padding: 12px 16px;
  background: #f9fafb;
}
.stitch-table tbody td {
  padding: 14px 16px;
  border-bottom: 1px solid #f3f4f6;
  color: #374151;
  vertical-align: middle;
}
.stitch-table tbody tr:hover { background: #fafafa; }

.contract-id-text {
  font-size: 0.875rem;
  color: #1d4ed8;
  font-weight: 700;
}

.project-tag {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: #6b7280;
  background: #f3f4f6;
  border-radius: 4px;
  padding: 2px 7px;
  margin-top: 3px;
}

.contractor-avatar {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: #1a1a2e;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.timeline-divider { color: #d1d5db; }
.timeline-urgent  { color: #f97316; font-weight: 600; font-size: 0.875rem; }
.timeline-completed { color: #16a34a; font-weight: 600; font-size: 0.875rem; }
.timeline-pending { color: #6b7280; font-size: 0.875rem; }
.timeline-default { color: #374151; font-size: 0.875rem; }

.timeline-bar {
  height: 3px;
  background: #e5e7eb;
  border-radius: 99px;
  width: 100px;
}
.timeline-bar-fill {
  height: 100%;
  border-radius: 99px;
  transition: width 0.3s;
}

/* ════════════════════════════════════════════
   PAGINATION
════════════════════════════════════════════ */
.pagination-btn {
  width: 32px;
  height: 32px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 7px !important;
}

/* ════════════════════════════════════════════
   DOCUMENT FEED
════════════════════════════════════════════ */
.doc-feed { display: flex; flex-direction: column; gap: 12px; }
.doc-feed-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid #f3f4f6;
}
.doc-feed-item:last-child { border-bottom: none; }
.doc-feed-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.doc-feed-content { flex: 1; }
.doc-feed-title {
  font-size: 0.8rem;
  font-weight: 500;
  color: #111827;
  margin: 0 0 2px;
}
.doc-feed-meta {
  font-size: 0.75rem;
  color: #9ca3af;
  margin: 0;
}
.doc-feed-action {
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 7px;
  padding: 4px 12px;
  flex-shrink: 0;
}

/* ════════════════════════════════════════════
   QUICK UPLOAD
════════════════════════════════════════════ */
.quick-upload-area {
  border: 2px dashed #e5e7eb;
  border-radius: 12px;
  padding: 32px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: #fafafa;
}
.quick-upload-area:hover,
.quick-upload-area.drag-over {
  border-color: #c0392b;
  background: rgba(192, 57, 43, 0.04);
}

/* ════════════════════════════════════════════
   DROPDOWN
════════════════════════════════════════════ */
.contract-actions-menu {
  display: inline-flex;
  position: relative;
}
.contract-action-menu {
  position: fixed;
  min-width: 148px;
  padding: 0.35rem;
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.12);
  border-radius: 8px;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.18);
  z-index: 1200;
}
.contract-action-menu-item {
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
.contract-action-menu-item:hover {
  background: #f3f4f6;
  color: #111827;
}
.contract-action-menu-item.danger {
  color: #dc2626;
}
.contract-action-menu-item.danger:hover {
  background: #fef2f2;
}
.dropdown-icon { font-size: 1rem; }
.view-icon { color: #2563eb; }
.edit-icon { color: #d97706; }

/* ════════════════════════════════════════════
   MODAL (BFP Styled)
════════════════════════════════════════════ */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(10, 10, 20, 0.60);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1055;
  padding: 1rem;
}
.bfp-modal {
  background: #ffffff;
  border-radius: 16px;
  width: 600px;
  max-width: 100%;
  max-height: 92vh;
  overflow-y: auto;
  overflow-x: hidden;
  box-shadow: 0 24px 60px rgba(0,0,0,0.25);
  display: flex;
  flex-direction: column;
}
.bfp-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 22px 14px;
  background: #1a1a2e;
  border-radius: 16px 16px 0 0;
}
.bfp-modal-header-left { display: flex; align-items: center; gap: 14px; }
.bfp-modal-emblem {
  width: 52px; height: 52px;
  background: rgba(255,255,255,0.08);
  border: 1.5px solid rgba(255,255,255,0.15);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; overflow: hidden;
}
.bfp-logo-img { width: 100%; height: 100%; object-fit: cover; border-radius: 11px; }
.bfp-modal-agency {
  font-size: 10.5px; font-weight: 600; letter-spacing: 0.1em;
  text-transform: uppercase; color: rgba(255,255,255,0.55); margin: 0 0 3px;
}
.bfp-modal-title { font-size: 17px; font-weight: 700; color: #ffffff; margin: 0; }
.bfp-modal-close {
  width: 34px; height: 34px;
  background: rgba(255,255,255,0.08);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 8px;
  display: inline-flex; align-items: center; justify-content: center;
  cursor: pointer; color: rgba(255,255,255,0.7);
  transition: all 0.15s;
}
.bfp-modal-close:hover { background: rgba(192,57,43,0.6); color: #fff; border-color: transparent; }
.bfp-modal-close .material-icons-round { font-size: 18px; }
.bfp-modal-stripe {
  background: linear-gradient(90deg, #c0392b 0%, #922b21 100%);
  padding: 7px 22px;
  display: flex; justify-content: space-between; align-items: center;
}
.bfp-modal-stripe span {
  font-size: 9.5px; font-weight: 700; letter-spacing: 0.12em;
  text-transform: uppercase; color: rgba(255,255,255,0.85);
}
.bfp-modal-body { padding: 20px 22px; flex: 1; }
.bfp-section { margin-bottom: 20px; }
.bfp-section:last-child { margin-bottom: 0; }
.bfp-section-label {
  display: flex; align-items: center; gap: 7px;
  font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
  text-transform: uppercase; color: #7f1d1d;
  background: #fef2f2; border-left: 3px solid #c0392b;
  padding: 7px 12px; border-radius: 0 8px 8px 0; margin-bottom: 14px;
}
.bfp-section-label .material-icons-round { font-size: 15px; color: #c0392b; }
.bfp-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.bfp-field-half { grid-column: span 1; }
.bfp-field-full { grid-column: 1 / -1; }
.bfp-label {
  display: flex; justify-content: space-between; align-items: center;
  font-size: 11.5px; font-weight: 600; color: #374151;
  margin-bottom: 5px; letter-spacing: 0.01em;
}
.bfp-required { color: #c0392b; font-size: 13px; }
.bfp-input-wrap { position: relative; }
.bfp-input-icon {
  position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
  font-size: 16px; color: #9ca3af; pointer-events: none;
}
.bfp-input {
  width: 100%; padding: 9px 12px 9px 36px;
  border: 1.5px solid #e5e7eb; border-radius: 9px;
  font-size: 13px; color: #111827; background: #fafafa;
  outline: none; transition: border-color 0.15s, box-shadow 0.15s;
  -webkit-appearance: none; appearance: none;
}
.bfp-input:focus { border-color: #c0392b; background: #fff; box-shadow: 0 0 0 3px rgba(192,57,43,0.10); }
.bfp-select {
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 12px center; padding-right: 32px;
}
.bfp-textarea { padding: 10px 12px; resize: vertical; min-height: 72px; line-height: 1.5; }
.bfp-modal-footer {
  display: flex; justify-content: space-between; align-items: center;
  padding: 14px 22px; background: #f9fafb;
  border-top: 1px solid #f0f0f0; border-radius: 0 0 16px 16px;
}
.bfp-footer-note { font-size: 11.5px; color: #6b7280; }
.bfp-footer-actions { display: flex; gap: 10px; align-items: center; }
.bfp-btn-cancel {
  padding: 9px 18px; border: 1.5px solid #e5e7eb; border-radius: 9px;
  background: transparent; font-size: 13px; font-weight: 500; color: #6b7280;
  cursor: pointer; transition: all 0.15s;
}
.bfp-btn-cancel:hover { background: #f3f4f6; border-color: #d1d5db; color: #374151; }
.bfp-btn-save {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 9px 22px; background: #c0392b; border: none; border-radius: 9px;
  font-size: 13px; font-weight: 600; color: #fff;
  cursor: pointer; transition: all 0.15s;
}
.bfp-btn-save:hover { background: #a93226; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(192,57,43,0.35); }
.bfp-btn-save .material-icons-round { font-size: 17px; }

/* Upload Modal Styles */
.bfp-upload-area {
  border: 2px dashed #e5e7eb; border-radius: 12px;
  padding: 28px 20px; text-align: center; cursor: pointer;
  transition: all 0.2s; background: #fafafa;
}
.bfp-upload-area:hover, .bfp-upload-area.drag-over { border-color: #c0392b; background: rgba(192,57,43,0.04); }
.bfp-upload-icon { font-size: 36px !important; color: #c0392b; margin-bottom: 8px; display: block; }
.bfp-upload-title { font-size: 14px; font-weight: 600; color: #1a202c; margin-bottom: 4px; }
.bfp-upload-sub { font-size: 12px; color: #9ca3af; margin-bottom: 14px; }
.bfp-upload-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 16px; border: 1.5px solid #c0392b; border-radius: 8px;
  background: transparent; color: #c0392b; font-size: 12px; font-weight: 600; cursor: pointer;
}
.bfp-upload-btn:hover { background: rgba(192,57,43,0.08); }
.bfp-format-pills { display: flex; justify-content: center; gap: 6px; margin-top: 12px; }
.bfp-format-pill {
  padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600;
  background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb;
}
.bfp-file-list { display: flex; flex-direction: column; gap: 8px; margin-top: 14px; }
.bfp-file-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;
}
.bfp-file-icon { font-size: 20px !important; color: #9ca3af; }
.bfp-file-name { font-size: 13px; color: #374151; font-weight: 500; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bfp-file-size { font-size: 11px; color: #9ca3af; white-space: nowrap; }
.bfp-file-remove { background: transparent; border: none; cursor: pointer; color: #9ca3af; display: flex; align-items: center; padding: 0; }
.bfp-file-remove:hover { color: #c0392b; }
.bfp-file-remove .material-icons-round { font-size: 18px; }
.bfp-modal::-webkit-scrollbar { width: 5px; }
.bfp-modal::-webkit-scrollbar-track { background: transparent; }
.bfp-modal::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 99px; }

.save-result-card {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 16px 18px;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  background: #f9fafb;
}

.save-result-card.success {
  border-color: rgba(34, 197, 94, 0.2);
  background: rgba(34, 197, 94, 0.06);
}

.save-result-card.error {
  border-color: rgba(239, 68, 68, 0.2);
  background: rgba(239, 68, 68, 0.06);
}

.save-result-icon {
  font-size: 1.8rem;
  line-height: 1;
  margin-top: 1px;
}

.save-result-card.success .save-result-icon {
  color: #16a34a;
}

.save-result-card.error .save-result-icon {
  color: #dc2626;
}

.save-result-message {
  font-size: 0.95rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 4px;
}

.save-result-subtext {
  font-size: 0.84rem;
  color: #6b7280;
  line-height: 1.45;
}

@media (max-width: 576px) {
  .bfp-form-grid { grid-template-columns: 1fr; }
  .bfp-field-half { grid-column: span 1; }
  .quick-upload-area { padding: 24px 16px; }
  .tab-pill { padding: 4px 10px; font-size: 0.75rem; }
  .pagination-btn { width: 30px; height: 30px; }
  .contract-action-menu {
    min-width: 136px;
    max-width: calc(100vw - 1.5rem);
  }
  .bfp-modal-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  .bfp-modal-header-left {
    width: 100%;
  }
  .bfp-modal-close {
    align-self: flex-end;
  }
  .bfp-modal-stripe {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    padding: 8px 18px;
  }
  .bfp-modal-body { padding: 16px 18px; }
  .bfp-modal-footer { flex-direction: column; gap: 10px; align-items: stretch; }
  .bfp-footer-actions {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }
  .bfp-btn-cancel,
  .bfp-btn-save {
    width: 100%;
    justify-content: center;
  }
  .bfp-footer-note { width: 100%; }
  .bfp-upload-area { padding: 24px 16px; }
  .bfp-format-pills { flex-wrap: wrap; }
  .bfp-file-item { align-items: flex-start; flex-wrap: wrap; }
  .bfp-file-name { white-space: normal; overflow: visible; text-overflow: initial; }
  .bfp-file-size { margin-left: 30px; }
}

@media (max-width: 420px) {
  .stat-label { font-size: 10px; }
  .stat-value { font-size: 1.5rem; }
  .stat-sub,
  .compliance-sub { font-size: 10px; }
  .bfp-modal-title { font-size: 15px; }
  .bfp-modal-agency { font-size: 10px; }
  .bfp-section-label { font-size: 10px; padding: 6px 10px; }
  .bfp-upload-title { font-size: 13px; }
  .bfp-upload-sub { font-size: 11px; }
  .stitch-table thead th,
  .stitch-table tbody td {
    padding-left: 12px;
    padding-right: 12px;
  }
}

/* ════════════════════════════════════════════
   FILTER CHECKBOXES
════════════════════════════════════════════ */
.filter-checkbox-group {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  margin-top: 10px;
}

.filter-checkbox {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
  padding: 8px 12px;
  border-radius: 8px;
  transition: all 0.2s;
  font-size: 0.875rem;
  color: #374151;
}

.filter-checkbox:hover {
  background: #f3f4f6;
}

.filter-checkbox input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: #c0392b;
}

.filter-checkbox span {
  flex: 1;
}

@media (max-width: 768px) {
  .filter-checkbox-group {
    grid-template-columns: 1fr;
  }
}
</style>