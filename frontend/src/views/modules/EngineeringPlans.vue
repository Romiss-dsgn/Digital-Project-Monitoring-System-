<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <p class="text-secondary small mb-1">
            <span>Infrastructure Plans</span>
            <i class="material-icons-round" style="font-size:0.85rem;vertical-align:middle;margin:0 2px;">chevron_right</i>
            <span>Engineering Plans Management</span>
          </p>
          <h4 class="mb-0">Engineering Plans</h4>
          <p class="text-secondary small">Centralized repository for all technical engineering documentation.</p>
        </div>
        <div class="col-lg-4 text-end d-flex gap-2 justify-content-end align-items-center">
          <button class="btn btn-outline-secondary btn-sm" @click="showExportModal = true">
            <i class="material-icons-round">cloud_download</i> Export All
          </button>
          <button class="btn btn-primary btn-sm" @click="showUploadPlanModal = true">
            <i class="material-icons-round">cloud_upload</i> Upload New Plan
          </button>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <p class="text-secondary small mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.05em;">Total Documents</p>
                <h4 class="mb-0 fw-bold">1,248</h4>
              </div>
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#ede9fe;">
                <i class="material-icons-round" style="color:#7c3aed;">description</i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <p class="text-secondary small mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.05em;">Pending Review</p>
                <h4 class="mb-0 fw-bold">42</h4>
              </div>
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#fef9c3;">
                <i class="material-icons-round" style="color:#ca8a04;">pending_actions</i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <p class="text-secondary small mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.05em;">Approved Plans</p>
                <h4 class="mb-0 fw-bold">1,186</h4>
              </div>
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#dcfce7;">
                <i class="material-icons-round" style="color:#16a34a;">check_circle</i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
          <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
              <div>
                <p class="text-secondary small mb-1" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:.05em;">Revisions Required</p>
                <h4 class="mb-0 fw-bold">20</h4>
              </div>
              <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:42px;height:42px;background:#fee2e2;">
                <i class="material-icons-round" style="color:#dc2626;">edit_document</i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Documents Table -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <!-- Tabs -->
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <ul class="nav nav-tabs border-0 mb-0" style="gap:0.25rem;">
                  <li class="nav-item" v-for="tab in tabs" :key="tab">
                    <a
                      class="nav-link px-3 py-2"
                      :class="{ active: activeTab === tab }"
                      href="#"
                      @click.prevent="activeTab = tab"
                      style="font-size:0.85rem;border:none;border-bottom:2px solid transparent;"
                    >{{ tab }}</a>
                  </li>
                </ul>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-secondary" @click="showFilterModal = true">
                    <i class="material-icons-round" style="font-size:1rem;vertical-align:middle;">filter_list</i> Filter
                  </button>
                  <button class="btn btn-sm btn-outline-secondary" @click="showSortModal = true">
                    <i class="material-icons-round" style="font-size:1rem;vertical-align:middle;">sort</i> Sort: Latest
                  </button>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Document Name</th>
                      <th>Project</th>
                          <th>Type</th>
                          <th>File Type</th>
                          <th>Uploaded By / Date</th>
                          <th>Version</th>
                          <th>Review Status</th>
                          <th style="max-width:220px;">Remarks</th>
                          <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="doc in filteredDocuments" :key="doc.id">
                      <td>
                        <div class="d-flex align-items-center gap-2">
                          <i class="material-icons-round" :style="{ color: doc.iconColor }">{{ doc.icon }}</i>
                          <div>
                            <div class="fw-semibold" style="font-size:0.875rem;">{{ doc.filename }}</div>
                            <div class="text-secondary" style="font-size:0.75rem;">{{ doc.filesize }}</div>
                          </div>
                        </div>
                      </td>
                      <td style="font-size:0.875rem;">{{ doc.project }}</td>
                      <td>
                        <span class="badge" style="background:#f1f5f9;color:#475569;font-weight:500;font-size:0.78rem;">{{ doc.type }}</span>
                      </td>
                      <td style="font-size:0.875rem;color:#475569;">{{ doc.file_type }}</td>
                      <td>
                        <div class="fw-semibold" style="font-size:0.82rem;">{{ doc.uploaded_by }}</div>
                        <div class="text-secondary" style="font-size:0.75rem;">{{ doc.date_uploaded }}</div>
                      </td>
                      <td style="font-size:0.875rem;">{{ doc.version }}</td>
                      <td><status-badge :status="doc.status" /></td>
                      <td style="font-size:0.875rem;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ doc.remarks }}</td>
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
                              <a class="dropdown-item" href="#" @click.prevent="viewDocument(doc)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon view-icon">visibility</i>
                                View
                              </a>
                            </li>
                            <li>
                              <a class="dropdown-item" href="#" @click.prevent="editDocument(doc)">
                                <i class="material-icons-round align-middle me-2 dropdown-icon edit-icon">edit</i>
                                Edit
                              </a>
                            </li>
                            <li><hr class="dropdown-divider" /></li>
                            <li>
                              <a class="dropdown-item text-danger" href="#" @click.prevent="deleteDocument(doc)">
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
              <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
                <p class="text-secondary small mb-0">Showing 1 to {{ filteredDocuments.length }} of 1,248 documents</p>
                <nav>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item"><a class="page-link" href="#"><i class="material-icons-round" style="font-size:0.9rem;vertical-align:middle;">chevron_left</i></a></li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item disabled"><a class="page-link" href="#">...</a></li>
                    <li class="page-item"><a class="page-link" href="#">312</a></li>
                    <li class="page-item"><a class="page-link" href="#"><i class="material-icons-round" style="font-size:0.9rem;vertical-align:middle;">chevron_right</i></a></li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <BfpModal
      :show="showUploadPlanModal"
      title="Upload New Engineering Plan"
      stripe="PLAN DOCUMENT UPLOAD"
      confirm-text="Upload Plan"
      confirm-icon="cloud_upload"
      @close="showUploadPlanModal = false"
      @confirm="showUploadPlanModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">cloud_upload</i> Plan File</div>
        <div
          class="bfp-upload-panel"
          :class="{ 'drag-over': planUploadDragOver }"
          @dragover.prevent="planUploadDragOver = true"
          @dragleave.prevent="planUploadDragOver = false"
          @drop.prevent="handlePlanFileDrop"
        >
          <i class="material-icons-round">cloud_upload</i>
          <p class="bfp-upload-title">Drop plan files here</p>
          <p class="bfp-upload-sub">Accepted formats: PDF, DWG, PNG, DOCX up to 25MB.</p>
          <input
            type="file"
            class="form-control form-control-sm"
            multiple
            accept=".pdf,.dwg,.png,.docx,application/pdf,image/png,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            @change="handlePlanFileSelect"
          />
        </div>
        <div v-if="selectedPlanFiles.length > 0" class="selected-plan-files">
          <div v-for="(file, index) in selectedPlanFiles" :key="`${file.name}-${index}`" class="selected-plan-file">
            <i class="material-icons-round">description</i>
            <span class="selected-plan-file-name">{{ file.name }}</span>
            <span class="selected-plan-file-size">{{ formatFileSize(file.size) }}</span>
            <button type="button" class="selected-plan-file-remove" @click="removePlanFile(index)">
              <i class="material-icons-round">close</i>
            </button>
          </div>
        </div>
      </div>
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">assignment</i> Document Details</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Project <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">business</i>
              <input class="bfp-input" type="text" placeholder="Linked project name" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Plan Type <span class="bfp-required">*</span></label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">category</i>
              <select class="bfp-input bfp-select">
                <option>Architectural</option>
                <option>Structural</option>
                <option>Electrical</option>
                <option>Mechanical</option>
                <option>Plumbing & Sanitary</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Version</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">history</i>
              <input class="bfp-input" type="text" placeholder="e.g. v2.1" />
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Review Status</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">fact_check</i>
              <select class="bfp-input bfp-select">
                <option>For Review</option>
                <option>Approved</option>
                <option>Revision Required</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showFilterModal"
      title="Engineering Plan Filters"
      stripe="DOCUMENT SEARCH PARAMETERS"
      confirm-text="Apply Filters"
      confirm-icon="filter_list"
      @close="showFilterModal = false"
      @confirm="showFilterModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">tune</i> Filter Criteria</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Document Type</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">description</i>
              <select class="bfp-input bfp-select">
                <option>All Documents</option>
                <option v-for="tab in tabs" :key="tab">{{ tab }}</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Status</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">flag</i>
              <select class="bfp-input bfp-select">
                <option>Any Status</option>
                <option>Approved</option>
                <option>For Review</option>
                <option>Revision Required</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-full">
            <label class="bfp-label">Project / Filename</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">search</i>
              <input class="bfp-input" type="text" placeholder="Search plan title, project, or uploader" />
            </div>
          </div>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showExportModal"
      title="Export Engineering Plans"
      stripe="DOCUMENT EXPORT"
      confirm-text="Prepare Export"
      confirm-icon="cloud_download"
      @close="showExportModal = false"
      @confirm="showExportModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">ios_share</i> Export Options</div>
        <div class="bfp-form-grid">
          <label class="bfp-check-option"><input type="checkbox" checked /> Include approved plans</label>
          <label class="bfp-check-option"><input type="checkbox" checked /> Include review queue</label>
          <label class="bfp-check-option"><input type="checkbox" /> Include file metadata</label>
          <label class="bfp-check-option"><input type="checkbox" /> Include uploader history</label>
        </div>
      </div>
    </BfpModal>

    <BfpModal
      :show="showSortModal"
      title="Sort Engineering Plans"
      stripe="DOCUMENT ORDERING"
      confirm-text="Apply Sort"
      confirm-icon="sort"
      @close="showSortModal = false"
      @confirm="showSortModal = false"
    >
      <div class="bfp-section">
        <div class="bfp-section-label"><i class="material-icons-round">sort</i> Sort Settings</div>
        <div class="bfp-form-grid">
          <div class="bfp-field-half">
            <label class="bfp-label">Sort By</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">calendar_month</i>
              <select class="bfp-input bfp-select">
                <option>Latest Upload</option>
                <option>Filename</option>
                <option>Project</option>
                <option>Status</option>
              </select>
            </div>
          </div>
          <div class="bfp-field-half">
            <label class="bfp-label">Direction</label>
            <div class="bfp-input-wrap">
              <i class="material-icons-round bfp-input-icon">swap_vert</i>
              <select class="bfp-input bfp-select">
                <option>Descending</option>
                <option>Ascending</option>
              </select>
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
  name: "EngineeringPlans",
  components: {
    StatusBadge,
    BfpModal
  },
  data() {
    return {
      activeTab: "All Documents",
      showUploadPlanModal: false,
      showFilterModal: false,
      showExportModal: false,
      showSortModal: false,
      planUploadDragOver: false,
      selectedPlanFiles: [],
      maxPlanFileSizeMb: 25,
      allowedPlanExtensions: [".pdf", ".dwg", ".png", ".docx"],
      allowedPlanTypes: [
        "application/pdf",
        "image/png",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        "application/acad",
        "application/autocad_dwg",
        "application/dwg",
        "image/vnd.dwg"
      ],
      tabs: ["All Documents", "Architectural", "Structural", "Electrical", "Mechanical", "Plumbing & Sanitary"],
      documents: [
        {
          id: 1,
          filename: "Main_Bldg_A_Structural_Rev2.pdf",
          filesize: "12.4 MB",
          icon: "picture_as_pdf",
          iconColor: "#2563eb",
          type: "Structural",
          file_type: "PDF",
          project: "City Hall Extension - Phase 2",
          uploaded_by: "Engr. Maria Santos",
          date_uploaded: "Oct 12, 2023 · 09:45 AM",
          version: "v2.4",
          status: "approved",
          remarks: "Final as-built drawings"
        },
        {
          id: 2,
          filename: "Fire_Station_B_Electrical_Layout.dwg",
          filesize: "45.8 MB",
          icon: "description",
          iconColor: "#ea580c",
          type: "Electrical",
          file_type: "DWG",
          project: "Cagayan Valley Regional Hub",
          uploaded_by: "Arch. Rafael Cruz",
          date_uploaded: "Oct 14, 2023 · 02:15 PM",
          version: "v1.1",
          status: "review",
          remarks: "Awaiting electrical compliance check"
        },
        {
          id: 3,
          filename: "San_Mateo_Mechanical_Spec_V2.png",
          filesize: "5.2 MB",
          icon: "image",
          iconColor: "#dc2626",
          type: "Mechanical",
          file_type: "PNG",
          project: "San Mateo Fire Station Repair",
          uploaded_by: "Admin Sarah Lee",
          date_uploaded: "Oct 10, 2023 · 11:20 AM",
          version: "v2.0",
          status: "revision",
          remarks: "Update schematics per consultant notes"
        },
        {
          id: 4,
          filename: "HVAC_Ventilation_Schema_2024.docx",
          filesize: "2.8 MB",
          icon: "article",
          iconColor: "#2563eb",
          type: "Mechanical",
          file_type: "DOCX",
          project: "Isabela Logistic Center",
          uploaded_by: "Engr. Leo Gomez",
          date_uploaded: "Oct 15, 2023 · 04:30 PM",
          version: "v1.0",
          status: "uploaded",
          remarks: "Initial upload - pending metadata"
        }
      ]
    };
  },
  computed: {
    filteredDocuments() {
      if (this.activeTab === "All Documents") return this.documents;
      return this.documents.filter(d => d.type === this.activeTab);
    }
  },
  methods: {
    getFileExtension(file) {
      const dotIndex = file.name.lastIndexOf(".");
      return dotIndex >= 0 ? file.name.slice(dotIndex).toLowerCase() : "";
    },
    isAllowedPlanFile(file) {
      const extension = this.getFileExtension(file);
      return this.allowedPlanExtensions.includes(extension) || this.allowedPlanTypes.includes(file.type);
    },
    isAllowedPlanFileSize(file) {
      return file.size <= this.maxPlanFileSizeMb * 1024 * 1024;
    },
    splitPlanFiles(files) {
      return files.reduce((groups, file) => {
        if (!this.isAllowedPlanFile(file)) {
          groups.invalidType.push(file);
        } else if (!this.isAllowedPlanFileSize(file)) {
          groups.invalidSize.push(file);
        } else {
          groups.valid.push(file);
        }
        return groups;
      }, { valid: [], invalidType: [], invalidSize: [] });
    },
    addPlanFiles(files) {
      const { valid, invalidType, invalidSize } = this.splitPlanFiles(Array.from(files));
      const messages = [];

      if (invalidType.length > 0) {
        messages.push(`Only PDF, DWG, PNG, and DOCX files are allowed.\nRejected type: ${invalidType.map(file => file.name).join(", ")}`);
      }

      if (invalidSize.length > 0) {
        messages.push(`Maximum file size is ${this.maxPlanFileSizeMb}MB.\nToo large: ${invalidSize.map(file => file.name).join(", ")}`);
      }

      if (messages.length > 0) {
        alert(messages.join("\n\n"));
      }

      if (valid.length > 0) {
        this.selectedPlanFiles = [...this.selectedPlanFiles, ...valid];
      }
    },
    handlePlanFileDrop(event) {
      this.planUploadDragOver = false;
      this.addPlanFiles(event.dataTransfer.files);
    },
    handlePlanFileSelect(event) {
      this.addPlanFiles(event.target.files);
      event.target.value = "";
    },
    removePlanFile(index) {
      this.selectedPlanFiles.splice(index, 1);
    },
    formatFileSize(size) {
      if (size >= 1024 * 1024) return `${(size / (1024 * 1024)).toFixed(1)} MB`;
      return `${(size / 1024).toFixed(1)} KB`;
    },
    viewDocument(doc) {
      alert(`View document ${doc.filename}`);
    },
    editDocument(doc) {
      alert(`Edit document ${doc.filename}`);
    },
    deleteDocument(doc) {
      alert(`Delete document ${doc.filename}`);
    }
  }
};
</script>

<style scoped>
/* ── No changes to existing styles ── */
.module-page {
  background: #f7fafc;
  min-height: 100vh;
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

.card-header h6 {
  color: #1f2633;
  font-weight: 700;
  margin: 0;
}

.table {
  font-size: 0.875rem;
}

.table th,
.table td {
  vertical-align: middle;
}

.table .material-icons-round {
  vertical-align: middle;
}

.form-control {
  border-radius: 0.75rem;
  border: 1px solid #dfe4ed;
}

.bfp-upload-panel.drag-over {
  border-color: #c0392b;
  background: rgba(192, 57, 43, 0.04);
}

.selected-plan-files {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 12px;
}

.selected-plan-file {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #f9fafb;
}

.selected-plan-file .material-icons-round {
  color: #9ca3af;
  font-size: 20px;
}

.selected-plan-file-name {
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #374151;
  font-size: 13px;
  font-weight: 500;
}

.selected-plan-file-size {
  color: #9ca3af;
  font-size: 11px;
  white-space: nowrap;
}

.selected-plan-file-remove {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 0;
  background: transparent;
  color: #9ca3af;
  cursor: pointer;
}

.selected-plan-file-remove:hover,
.selected-plan-file-remove:hover .material-icons-round {
  color: #c0392b;
}
</style>
