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
              <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" @click="openCreateContractModal">
                  <i class="material-icons-round" style="font-size:15px;vertical-align:-3px">add</i>
                  New Project
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
            <div class="col-6">
              <div class="card stat-card h-100">
                <div class="card-body p-3">
                  <p class="stat-label">ONGOING PROJECTS</p>
                  <p class="stat-value">24</p>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="card stat-card h-100">
                <div class="card-body p-3">
                  <p class="stat-label">PENDING REVIEW</p>
                  <p class="stat-value">08</p>
                  <div class="pending-bar mt-1"><div class="pending-bar-fill"></div></div>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="card stat-card h-100">
                <div class="card-body p-3">
                  <p class="stat-label">TOTAL VALUE</p>
                  <p class="stat-value">₱142.5M</p>
                  <p class="stat-sub">Fiscal Year 2024</p>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="card stat-card h-100">
                <div class="card-body p-3">
                  <p class="stat-label">DOCS COMPLIANCE</p>
                  <p class="stat-value compliance-val">92%</p>
                  <p class="stat-sub compliance-sub">+5% from last month</p>
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
            <div class="card-header pt-3 px-4">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-3">
                  <span class="fw-bold" style="font-size:0.875rem;color:#374151;">Contract Records</span>
                  <div class="d-flex gap-1">
                    <span class="tab-pill active-tab">All</span>
                    <span class="tab-pill">Active</span>
                    <span class="tab-pill">Expired</span>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="text-secondary small">Show</span>
                  <select class="form-select form-select-sm" style="width:100px;">
                    <option>10 rows</option>
                    <option>25 rows</option>
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
                    <tr v-else-if="getFilteredContracts().length === 0">
                      <td colspan="6" class="text-center py-4 text-secondary">No contract records found.</td>
                    </tr>
                    <tr v-for="contract in isLoading ? [] : getFilteredContracts()" :key="contract.id">
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
                        <div class="dropdown">
                          <button class="btn btn-sm btn-icon btn-light text-secondary" type="button" data-bs-toggle="dropdown">
                            <i class="material-icons-round">more_vert</i>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="viewContract(contract)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon view-icon">visibility</i> View
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="editContract(contract)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit</i> Edit
                              </a>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                              <a class="dropdown-item text-danger" href="#" @click.prevent="deleteContract(contract)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon">delete</i> Delete
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
              <div class="d-flex align-items-center justify-content-between px-4 py-3 border-top">
                <span class="text-secondary small">Showing {{ getFilteredContracts().length }} contract records</span>
                <div class="d-flex gap-1">
                  <button class="btn btn-sm btn-light border pagination-btn">
                    <i class="material-icons-round" style="font-size:16px;vertical-align:-3px">chevron_left</i>
                  </button>
                  <button class="btn btn-sm btn-primary pagination-btn">1</button>
                  <button class="btn btn-sm btn-light border pagination-btn">2</button>
                  <button class="btn btn-sm btn-light border pagination-btn">3</button>
                  <button class="btn btn-sm btn-light border pagination-btn">
                    <i class="material-icons-round" style="font-size:16px;vertical-align:-3px">chevron_right</i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── Bottom: Quick Upload ── -->
      <div class="row">
        <!-- Quick Upload → opens Upload Batch modal -->
        <div class="col-lg-12 mb-4">
          <div class="card h-100">
            <div class="card-body p-4">
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
              <h5 class="bfp-modal-title">{{ contractForm.id ? "Edit Contract" : "New Contract Project" }}</h5>
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
                <label class="bfp-label">Project Budget (₱)</label>
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
                    <option>Draft</option>
                    <option>Pending Review</option>
                    <option>Active</option>
                    <option>Delayed</option>
                    <option>Completed</option>
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
                  <select class="bfp-input bfp-select">
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
                  <select class="bfp-input bfp-select">
                    <option value="">Select contract</option>
                    <option v-for="c in contracts" :key="c.id">{{ c.contract_id }}</option>
                  </select>
                </div>
              </div>
              <div class="bfp-field-full">
                <label class="bfp-label">Remarks</label>
                <div class="bfp-input-wrap">
                  <textarea class="bfp-input bfp-textarea" rows="2" placeholder="Optional — describe the batch contents…"></textarea>
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
            <button class="bfp-btn-save"><i class="material-icons-round">upload</i> Upload Files</button>
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
            <button class="bfp-btn-cancel" @click="clearFilters; showAdvancedFilterModal = false">Clear All</button>
            <button class="bfp-btn-save" @click="applyFilters"><i class="material-icons-round">filter_list</i> Apply Filters</button>
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
      isLoading: false,
      isSaving: false,
      apiError: "",
      projects: [],
      contractors: [],
      contractForm: emptyContractForm(),
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
      categoryOptions: ['INFRASTRUCTURE', 'EQUIPMENT', 'SERVICE', 'MAINTENANCE'],
      paymentTypeOptions: ['Lump Sum', 'Fixed Price', 'Annual', 'Milestone-based'],
      timelineStatusOptions: ['Completed', 'Pending Start', 'In Progress', 'Delayed/At Risk'],
      contractStatusOptions: ['Active', 'Expired', 'Terminated', 'Archived'],
      complianceStatusOptions: ['Full Compliance', 'Minor Issues', 'Major Issues', 'Pending Review'],
      contracts: [],
      docFeed: [
        { icon: "cloud_done", iconBg: "#e8f5e9", iconColor: "#2e7d32", title: "Vanguard Const. uploaded \"Progress Report 4\"", meta: "2 minutes ago • BFP2-2024-001", action: "View", actionClass: "btn-outline-primary" },
        { icon: "warning_amber", iconBg: "#fff8e1", iconColor: "#f59e0b", title: "Automated Alert: Insurance Expiry Approaching", meta: "1 hour ago • SafeFirst Int'l", action: "Review", actionClass: "btn-outline-warning" }
      ]
    };
  },
  async mounted() {
    await this.loadContractManagement();
  },
  methods: {
    openCreateContractModal() {
      this.apiError = "";
      this.contractForm = emptyContractForm();
      this.showNewProjectModal = true;
    },
    async loadContractManagement() {
      this.isLoading = true;
      this.apiError = "";

      try {
        const [contracts, options] = await Promise.all([
          contractService.getContracts(),
          contractService.getOptions(),
        ]);

        this.contracts = contracts.map(this.mapApiContractToTable);
        this.projects = options.projects || [];
        this.contractors = options.contractors || [];
      } catch (error) {
        this.apiError = error?.response?.data?.message || "Unable to load contract records.";
      } finally {
        this.isLoading = false;
      }
    },
    async saveContract() {
      this.isSaving = true;
      this.apiError = "";

      try {
        if (this.contractForm.id) {
          await contractService.updateContract(this.contractForm.id, this.contractForm);
        } else {
          await contractService.createContract(this.contractForm);
        }

        this.showNewProjectModal = false;
        this.contractForm = emptyContractForm();
        await this.loadContractManagement();
      } catch (error) {
        this.apiError = error?.response?.data?.message || "Unable to save contract.";
      } finally {
        this.isSaving = false;
      }
    },
    viewContract(contract) {
      const raw = contract.raw || contract;
      alert(`${raw.contract_number}\n${raw.contract_title}\nContractor: ${raw.contractor_name}\nAmount: ${this.formatPeso(raw.original_contract_amount)}`);
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
    async deleteContract(contract) {
      if (!confirm(`Archive ${contract.contract_id}?`)) {
        return;
      }

      try {
        await contractService.archiveContract(contract.id);
        await this.loadContractManagement();
      } catch (error) {
        this.apiError = error?.response?.data?.message || "Unable to archive contract.";
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
        amount: this.formatPeso(contract.original_contract_amount),
        payment_type: contract.contract_type || "Contract",
        duration: this.formatDuration(contract.start_date, contract.end_date),
        timeline_status: contract.status || "Draft",
        timeline_bar: this.getTimelineProgress(contract.start_date, contract.end_date),
        status: contract.status || "Draft",
        compliance: "Pending Review",
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
      const validFiles = files.filter(file => this.isValidFileType(file) && this.isValidFileSize(file));
      const invalidFiles = files.filter(file => !this.isValidFileType(file));
      if (invalidFiles.length > 0) {
        alert(`⚠️ Invalid file type(s) detected.\n\nOnly PDF, DOCX, and XLSX files are allowed.\n\nRejected: ${invalidFiles.map(f => f.name).join(', ')}`);
      }
      if (validFiles.length > 0) {
        this.uploadedFiles = [...this.uploadedFiles, ...validFiles];
        this.showUploadModal = true;
      }
    },
    handleFileSelect(event) {
      const files = Array.from(event.target.files);
      const validFiles = files.filter(file => this.isValidFileType(file) && this.isValidFileSize(file));
      const invalidFiles = files.filter(file => !this.isValidFileType(file));
      if (invalidFiles.length > 0) {
        alert(`⚠️ Invalid file type(s) detected.\n\nOnly PDF, DOCX, and XLSX files are allowed.\n\nRejected: ${invalidFiles.map(f => f.name).join(', ')}`);
      }
      if (validFiles.length > 0) {
        this.uploadedFiles = [...this.uploadedFiles, ...validFiles];
      }
    },
    removeFile(index) { this.uploadedFiles.splice(index, 1); },
    // Filter methods
    getFilteredContracts() {
      return this.contracts.filter(contract => {
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
        return true;
      });
    },
    applyFilters() {
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
    },
    hasActiveFilters() {
      return this.filters.categories.length > 0 || 
             this.filters.paymentTypes.length > 0 || 
             this.filters.timelineStatus.length > 0 || 
             this.filters.contractStatus.length > 0 || 
             this.filters.contractors.length > 0 || 
             this.filters.complianceStatus.length > 0 || 
             this.filters.budgetMin || 
             this.filters.budgetMax;
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
.dropdown-menu {
  border: 1px solid rgba(0,0,0,0.08);
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

@media (max-width: 576px) {
  .bfp-form-grid { grid-template-columns: 1fr; }
  .bfp-field-half { grid-column: span 1; }
  .bfp-modal-footer { flex-direction: column; gap: 10px; align-items: stretch; }
  .bfp-footer-actions { justify-content: flex-end; }
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
