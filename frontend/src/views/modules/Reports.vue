<template>
  <div class="module-page"><div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
      <div class="col-lg-8">
        <h4 class="mb-0">Reports Center</h4>
        <p class="text-secondary small mb-0">Generate, view, and export comprehensive project management records.</p>
      </div>
      <div class="col-lg-4 d-flex justify-content-end gap-2 mt-3 mt-lg-0">
        <button class="btn btn-outline-danger btn-sm" @click="showFilterModal = true">
          <i class="bi bi-sliders"></i> Filters<span v-if="activeFilterCount" class="badge bg-danger ms-1">{{ activeFilterCount }}</span>
        </button>
        <button class="btn btn-outline-secondary btn-sm" :disabled="isLoading || !canExport" @click="showExcelModal = true"><i class="bi bi-file-earmark-excel"></i> Excel</button>
        <button class="btn btn-primary btn-sm" :disabled="isLoading || !canExport" @click="showPdfModal = true"><i class="bi bi-file-earmark-pdf"></i> PDF Export</button>
      </div>
    </div>

    <div class="row g-4"><div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
              <h6 class="fw-semibold mb-0">Live Report Preview</h6>
              <div class="text-secondary option-desc">{{ currentReportMeta.label }} — {{ currentReportMeta.description }}</div>
            </div>
            <span v-if="isLoading" class="spinner-border spinner-border-sm text-primary"></span>
          </div>

          <div id="report-print-area" class="report-document p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h5 class="fw-bold mb-1">{{ report.title.toUpperCase() }}</h5>
                <div class="text-secondary small">Report ID: #{{ report.report_id }}</div>
              </div>
              <div class="text-end">
                <div class="fw-semibold small">LGU Tuao</div>
                <div class="text-secondary small">Date Generated: {{ generatedAt }}</div>
              </div>
            </div>
            <hr class="my-2" />

            <div class="row g-2 mb-4">
              <div v-for="stat in report.stats" :key="stat.label" class="col-6 col-md">
                <div class="stat-box"><div class="stat-label">{{ stat.label }}</div><div class="stat-value">{{ stat.value }}</div></div>
              </div>
            </div>

            <div v-if="loadError" class="alert alert-danger small">{{ loadError }}</div>
            <div v-else class="table-responsive report-table-wrap">
              <table class="table table-sm report-table mb-4">
                <thead>
                  <tr><th v-for="column in report.columns" :key="column.key" :class="column.align === 'end' ? 'text-end' : ''">{{ column.label }}</th></tr>
                </thead>
                <tbody>
                  <tr v-for="(row, index) in report.rows" :key="index">
                    <td v-for="column in report.columns" :key="column.key" :class="column.align === 'end' ? 'text-end fw-semibold' : ''">
                      <span v-if="column.key === 'status'" class="badge" :class="statusBadgeClass(row[column.key])">{{ row[column.key] }}</span>
                      <template v-else>{{ row[column.key] }}</template>
                    </td>
                  </tr>
                  <tr v-if="!report.rows.length"><td :colspan="report.columns.length" class="text-center text-secondary py-5">No records match the selected filters.</td></tr>
                </tbody>
              </table>
            </div>

            <div class="footer-preview"><span>Certified by: LGU Tuao Administrative Officer</span><span class="badge bg-dark">OFFICIAL REPORT</span></div>
          </div>
        </div>
      </div>
    </div></div>

    <!-- Filters Modal (Select Report Type + Refine Results) -->
    <TuaoModal :show="showFilterModal" title="Report Filters" stripe="CONFIGURE REPORT" confirm-text="Apply Filters" confirm-icon="filter_alt" @close="showFilterModal=false" @confirm="applyFilters">
      <h6 class="fw-semibold mb-2 small text-uppercase text-secondary">1. Select Report Type</h6>
      <div class="d-flex flex-column gap-2 mb-4">
        <label v-for="report in reportTypes" :key="report.value" class="report-type-option" :class="{ active: draftReport === report.value }">
          <input v-model="draftReport" type="radio" :value="report.value" class="me-2" />
          <div><div class="fw-semibold small">{{ report.label }}</div><div class="text-secondary option-desc">{{ report.description }}</div></div>
        </label>
      </div>

      <h6 class="fw-semibold mb-2 small text-uppercase text-secondary">2. Refine Results</h6>
      <div class="mb-3">
        <label class="tuao-label">Report Month</label>
        <input v-model="draftMonth" type="month" class="tuao-input" />
      </div>
      <div class="mb-3">
        <label class="tuao-label">Project Portfolio</label>
        <select v-model="draftPortfolio" class="tuao-input tuao-select">
          <option value="all">All active projects</option>
          <option value="ongoing">Ongoing / on-track projects</option>
          <option value="completed">Completed projects</option>
        </select>
      </div>
      <div class="mb-2">
        <label class="tuao-label">Lead Contractor</label>
        <select v-model="draftContractor" class="tuao-input tuao-select">
          <option value="">All registered contractors</option>
          <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">{{ contractor.company_name }}</option>
        </select>
      </div>
      <div v-if="filterError" class="text-danger small mt-2">{{ filterError }}</div>
    </TuaoModal>

    <TuaoModal :show="showPrintModal" title="Print Report" stripe="REPORT PRINT SETUP" confirm-text="Print Report" confirm-icon="print" @close="showPrintModal=false" @confirm="printReport">
      <div class="tuao-form-grid">
        <div class="tuao-field-half"><label class="tuao-label">Paper Size</label><select v-model="printPaper" class="tuao-input tuao-select"><option value="a4">A4</option><option value="letter">Letter</option><option value="legal">Legal</option></select></div>
        <div class="tuao-field-half"><label class="tuao-label">Orientation</label><select v-model="printOrientation" class="tuao-input tuao-select"><option value="portrait">Portrait</option><option value="landscape">Landscape</option></select></div>
      </div>
    </TuaoModal>
    <TuaoModal :show="showExcelModal" title="Export Report to Excel" stripe="SPREADSHEET EXPORT" confirm-text="Export Excel" confirm-icon="grid_on" @close="showExcelModal=false" @confirm="exportExcel">
      <p class="mb-0 small">The current report type and filters will be exported to an Excel workbook.</p>
    </TuaoModal>
    <TuaoModal :show="showPdfModal" title="PDF Export" stripe="OFFICIAL REPORT PACKAGE" confirm-text="Generate PDF" confirm-icon="picture_as_pdf" @close="showPdfModal=false" @confirm="exportPdf">
      <p class="mb-0 small">The current report type and filters will be exported as an official PDF.</p>
    </TuaoModal>
  </div></div>
</template>

<script>
import TuaoModal from "@/components/TuaoModal.vue";
import ReportService from "@/services/report.service";

export default {
  name: "Reports",
  components: { TuaoModal },
  data() {
    return {
      showFilterModal: false,
      showPrintModal: false,
      showExcelModal: false,
      showPdfModal: false,
      printPaper: "a4",
      printOrientation: "portrait",

      // Applied (active) filters — used to actually fetch data
      selectedReport: "project-status",
      selectedMonth: "",
      dateFrom: "",
      dateTo: "",
      selectedPortfolio: "all",
      selectedContractor: "",

      // Draft filters — edited inside the modal, applied on confirm
      draftReport: "project-status",
      draftMonth: "",
      draftPortfolio: "all",
      draftContractor: "",

      isLoading: false,
      loadError: null,
      filterError: null,
      canExport: false,
      contractors: [],
      report: { title: "Project Status Report", report_id: "-", generated_at: null, stats: [], columns: [], rows: [] },
      reportTypes: [
        { value: "project-status", label: "Project Status Report", description: "Milestones, completion %, and delays." },
        { value: "contract-summary", label: "Contract Summary", description: "Legal terms, parties, and amendments." },
        { value: "cashflow-analysis", label: "Cashflow Analysis", description: "Disbursements, remaining budget, and forecasts." },
        { value: "variation-orders", label: "Variation Orders Audit", description: "History of scope changes and costs." },
      ],
    };
  },
  computed: {
    generatedAt() {
      return this.report.generated_at
        ? new Date(this.report.generated_at).toLocaleString("en-PH", { dateStyle: "medium", timeStyle: "short" })
        : "—";
    },
    currentReportMeta() {
      return this.reportTypes.find((r) => r.value === this.selectedReport) || {};
    },
    monthDateFrom() {
      return this.draftMonth ? `${this.draftMonth}-01` : "";
    },
    monthDateTo() {
      if (!this.draftMonth) return "";
      const [y, m] = this.draftMonth.split("-").map(Number);
      const lastDay = new Date(y, m, 0).getDate();
      return `${this.draftMonth}-${String(lastDay).padStart(2, "0")}`;
    },
    activeFilterCount() {
      let count = 0;
      if (this.selectedMonth) count++;
      if (this.selectedPortfolio && this.selectedPortfolio !== "all") count++;
      if (this.selectedContractor) count++;
      return count;
    },
  },
  mounted() {
    this.loadReport();
  },
  methods: {
    params() {
      const p = { portfolio: this.selectedPortfolio };
      if (this.dateFrom) p.date_from = this.dateFrom;
      if (this.dateTo) p.date_to = this.dateTo;
      if (this.selectedContractor) p.contractor_id = this.selectedContractor;
      return p;
    },
    openFilters() {
      // sync draft with currently applied values whenever modal opens
      this.draftReport = this.selectedReport;
      this.draftMonth = this.selectedMonth;
      this.draftPortfolio = this.selectedPortfolio;
      this.draftContractor = this.selectedContractor;
      this.showFilterModal = true;
    },
    async applyFilters() {
      this.filterError = null;
      this.selectedReport = this.draftReport;
      this.selectedMonth = this.draftMonth;
      this.dateFrom = this.monthDateFrom;
      this.dateTo = this.monthDateTo;
      this.selectedPortfolio = this.draftPortfolio;
      this.selectedContractor = this.draftContractor;
      this.showFilterModal = false;
      await this.loadReport();
    },
    async loadReport() {
      this.isLoading = true;
      this.loadError = null;
      this.filterError = null;
      try {
        const response = await ReportService.getReport(this.selectedReport, this.params());
        this.report = response.data.data;
        this.contractors = this.report.filters?.contractors || [];
        this.canExport = !!response.data.meta?.permissions?.can_export;
      } catch (error) {
        this.loadError = error.response?.data?.message || "Unable to load report data.";
      } finally {
        this.isLoading = false;
      }
    },
    statusBadgeClass(status) {
      const value = String(status || "").toLowerCase();
      if (value.includes("delay")) return "bg-danger";
      if (value.includes("complete") || value.includes("approved")) return "bg-primary";
      if (value.includes("ongoing") || value.includes("track") || value.includes("active")) return "bg-success";
      if (value.includes("pending") || value.includes("review")) return "bg-warning text-dark";
      return "bg-secondary";
    },
    async download(format) {
      try {
        const response = await ReportService.exportReport(this.selectedReport, format, {
          ...this.params(),
          paper: this.printPaper,
          orientation: this.printOrientation,
        });
        const url = URL.createObjectURL(new Blob([response.data]));
        const a = document.createElement("a");
        a.href = url;
        a.download = `${this.selectedReport}.${format === "xlsx" ? "xlsx" : "pdf"}`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
      } catch (error) {
        this.loadError = error.response?.data?.message || "Unable to export the report.";
      }
    },
    exportExcel() {
      this.showExcelModal = false;
      this.download("xlsx");
    },
    exportPdf() {
      this.showPdfModal = false;
      this.download("pdf");
    },
    printReport() {
      this.showPrintModal = false;
      const style = `@page { size: ${this.printPaper} ${this.printOrientation}; margin: 14mm; } body{font-family:Arial,sans-serif;color:#1f2937} .report-document{border:0!important} .stat-box{background:#f3f4f6;padding:8px}.row{display:flex;gap:8px}.col-6{flex:1}table{width:100%;border-collapse:collapse}th,td{padding:7px;border-bottom:1px solid #ddd;text-align:left}.text-end{text-align:right}.badge{padding:3px 5px;background:#555;color:#fff}.footer-preview{display:flex;justify-content:space-between;margin-top:20px}`;
      const popup = window.open("", "_blank");
      if (!popup) {
        this.loadError = "Allow pop-ups to print this report.";
        return;
      }
      popup.document.write(`<html><head><title>${this.report.title}</title><style>${style}</style></head><body>${document.getElementById("report-print-area").outerHTML}</body></html>`);
      popup.document.close();
      popup.focus();
      popup.print();
    },
  },
  watch: {
    showFilterModal(val) {
      if (val) this.openFilters();
    },
  },
};
</script>

<style scoped>
.module-page{min-height:100vh;background:#f8f9fc}
.card{border-radius:1rem}
.report-type-option{display:flex;align-items:flex-start;padding:.65rem .75rem;border:1px solid #e0e0e0;border-radius:.5rem;cursor:pointer}
.report-type-option.active{background:#e8f0fe;border-color:#1565C0;color:#1565C0}
.report-type-option input{margin-top:2px;accent-color:#1565C0}
.option-desc{font-size:.78rem}
.report-document{border:1px solid #e0e0e0;border-radius:.5rem;background:#fff;min-height:70vh}
.report-table-wrap{max-height:none}
.stat-box{background:#f5f5f5;border-radius:.4rem;padding:.6rem .85rem;height:100%}
.stat-label{font-size:.72rem;color:#888}
.stat-value{font-size:1.15rem;font-weight:700}
.report-table thead th{font-size:.82rem;border-bottom:2px solid #dee2e6}
.report-table tbody td{font-size:.85rem;vertical-align:middle}
.footer-preview{display:flex;justify-content:space-between;align-items:center;font-size:.75rem;color:#777}
.tuao-form-grid{display:flex;gap:.75rem}
.tuao-field-half{flex:1}
.tuao-label{font-size:.78rem;font-weight:600;color:#555;display:block;margin-bottom:.25rem}
.tuao-input{width:100%;border:1px solid #dcdcdc;border-radius:.4rem;padding:.4rem .6rem;font-size:.85rem}
.tuao-select{background:#fff}
</style>