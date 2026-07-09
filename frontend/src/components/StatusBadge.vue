<template>
  <span :class="['badge', `badge-${normalizedStatus}`]">
    <span class="badge-dot"></span>
    {{ status_text }}
  </span>
</template>

<script>
export default {
  name: "StatusBadge",
  props: {
    status: {
      type: String,
      default: "pending"
    }
  },
  computed: {
    normalizedStatus() {
      // Convert to lowercase and replace spaces with underscores for CSS class safety
      return this.status.toLowerCase().replace(/\s+/g, "_");
    },
    status_text() {
      const statusMap = {
        // Original statuses
        "in_progress":              "In Progress",
        "on_track":                 "On Track",
        "on_time":                  "On Time",
        "pending":                  "Pending",
        "approved":                 "Approved",
        "rejected":                 "Rejected",
        "completed":                "Completed",
        "delayed":                  "Delayed",
        "active":                   "Active",
        "paid":                     "Paid",
        "within_budget":            "Within Budget",
        "over_budget":              "Over Budget",
        "under_budget":             "Under Budget",
        "ongoing":                  "Ongoing",
        "planning":                 "Planning",
        "suspended":                "Suspended",
        "for_review":               "For Review",
        "revision":                 "Revision Required",
        "uploaded":                 "Uploaded",
        "draft":                    "Draft",
        "submitted":                "Submitted",
        "under_review":             "Under Review",
        // BFP Module statuses
        "dashboard":                "Dashboard",
        "infrastructure_plans":     "Infrastructure Plans",
        "contract_management":      "Contract Management",
        "cashflows_management":     "Cashflows Management",
        "engineering_plans":        "Engineering Plans",
        "variation_orders":         "Variation Orders",
        "project_accomplishments":  "Project Accomplishments",
        "reports":                  "Reports",
        "audit_logs":               "Audit Logs",
        "settings":                 "Settings"
      };
      return statusMap[this.normalizedStatus] || this.status;
    }
  }
};
</script>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 0.35rem 0.8rem;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
}

/* Dot indicator */
.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

/* ── Green — success states ── */
.badge-on_time,
.badge-on_track,
.badge-approved,
.badge-active,
.badge-paid,
.badge-within_budget {
  background: rgba(88, 207, 151, 0.15);
  color: #1d7a3f;
}
.badge-on_time .badge-dot,
.badge-on_track .badge-dot,
.badge-approved .badge-dot,
.badge-active .badge-dot,
.badge-paid .badge-dot,
.badge-within_budget .badge-dot {
  background: #1d7a3f;
}

/* ── Deep green — completed ── */
.badge-completed {
  background: rgba(22, 163, 74, 0.12);
  color: #15803d;
  border: 1px solid rgba(22, 163, 74, 0.3);
}
.badge-completed .badge-dot {
  background: #15803d;
}

/* ── Amber — in progress / pending / ongoing ── */
.badge-in_progress,
.badge-pending,
.badge-ongoing,
.badge-for_review,
.badge-uploaded {
  background: rgba(245, 158, 11, 0.15);
  color: #b45309;
}
.badge-in_progress .badge-dot,
.badge-pending .badge-dot,
.badge-ongoing .badge-dot,
.badge-for_review .badge-dot,
.badge-uploaded .badge-dot {
  background: #d97706;
}

/* ── Blue — planning ── */
.badge-planning {
  background: rgba(59, 130, 246, 0.12);
  color: #1e40af;
}
.badge-planning .badge-dot {
  background: #2563eb;
}

/* ── Amber — draft (planning stage) ── */
.badge-draft {
  background: rgba(245, 158, 11, 0.15);
  color: #b45309;
}
.badge-draft .badge-dot {
  background: #d97706;
}

/* ── Amber — submitted ── */
.badge-submitted {
  background: rgba(245, 158, 11, 0.15);
  color: #b45309;
}
.badge-submitted .badge-dot {
  background: #d97706;
}

/* ── Amber — under review ── */
.badge-under_review {
  background: rgba(245, 158, 11, 0.15);
  color: #b45309;
}
.badge-under_review .badge-dot {
  background: #d97706;
}

/* ── Red — error / delay states ── */
.badge-delayed,
.badge-rejected,
.badge-revision {
  background: rgba(239, 68, 68, 0.12);
  color: #c62828;
}
.badge-delayed .badge-dot,
.badge-rejected .badge-dot,
.badge-revision .badge-dot {
  background: #c62828;
}

/* â”€â”€ Amber/blue â€” under budget â”€â”€ */
.badge-under_budget {
  background: rgba(59, 130, 246, 0.12);
  color: #1d4ed8;
}
.badge-under_budget .badge-dot {
  background: #2563eb;
}

/* â”€â”€ Red â€” over budget â”€â”€ */
.badge-over_budget {
  background: rgba(239, 68, 68, 0.12);
  color: #b91c1c;
}
.badge-over_budget .badge-dot {
  background: #dc2626;
}

/* ── Purple — suspended ── */
.badge-suspended {
  background: rgba(139, 92, 246, 0.12);
  color: #6d28d9;
}
.badge-suspended .badge-dot {
  background: #7c3aed;
}

/* ── BFP Module Statuses ── */

/* Dashboard — slate blue */
.badge-dashboard {
  background: rgba(71, 85, 105, 0.12);
  color: #334155;
}
.badge-dashboard .badge-dot {
  background: #475569;
}

/* Infrastructure Plans — teal */
.badge-infrastructure_plans {
  background: rgba(20, 184, 166, 0.12);
  color: #0f766e;
}
.badge-infrastructure_plans .badge-dot {
  background: #0d9488;
}

/* Contract Management — indigo */
.badge-contract_management {
  background: rgba(99, 102, 241, 0.12);
  color: #3730a3;
}
.badge-contract_management .badge-dot {
  background: #4338ca;
}

/* Cashflows Management — emerald */
.badge-cashflows_management {
  background: rgba(16, 185, 129, 0.12);
  color: #065f46;
}
.badge-cashflows_management .badge-dot {
  background: #059669;
}

/* Engineering Plans — sky blue */
.badge-engineering_plans {
  background: rgba(14, 165, 233, 0.12);
  color: #0369a1;
}
.badge-engineering_plans .badge-dot {
  background: #0284c7;
}

/* Variation Orders — orange */
.badge-variation_orders {
  background: rgba(249, 115, 22, 0.12);
  color: #9a3412;
}
.badge-variation_orders .badge-dot {
  background: #ea580c;
}

/* Project Accomplishments — green */
.badge-project_accomplishments {
  background: rgba(34, 197, 94, 0.12);
  color: #15803d;
}
.badge-project_accomplishments .badge-dot {
  background: #16a34a;
}

/* Reports — violet */
.badge-reports {
  background: rgba(167, 139, 250, 0.15);
  color: #5b21b6;
}
.badge-reports .badge-dot {
  background: #7c3aed;
}

/* Audit Logs — rose */
.badge-audit_logs {
  background: rgba(244, 63, 94, 0.12);
  color: #9f1239;
}
.badge-audit_logs .badge-dot {
  background: #e11d48;
}

/* Settings — neutral gray */
.badge-settings {
  background: rgba(156, 163, 175, 0.2);
  color: #374151;
}
.badge-settings .badge-dot {
  background: #6b7280;
}
</style>
