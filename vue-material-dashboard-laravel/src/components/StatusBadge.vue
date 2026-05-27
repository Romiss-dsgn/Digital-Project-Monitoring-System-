<template>
  <span :class="['badge', `badge-${status}`]">
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
    status_text() {
      const statusMap = {
        "in_progress": "In Progress",
        "on_track":    "On Track",
        "on_time":     "On Time",
        "pending":     "Pending",
        "approved":    "Approved",
        "rejected":    "Rejected",
        "completed":   "Completed",
        "delayed":     "Delayed",
        "active":      "Active",
        "paid":        "Paid",
        "ongoing":     "Ongoing",
        "planning":    "Planning",
        "suspended":   "Suspended"
      };
      return statusMap[this.status] || this.status;
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
.badge-paid {
  background: rgba(88, 207, 151, 0.15);
  color: #1d7a3f;
}
.badge-on_time .badge-dot,
.badge-on_track .badge-dot,
.badge-approved .badge-dot,
.badge-active .badge-dot,
.badge-paid .badge-dot {
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
.badge-ongoing {
  background: rgba(245, 158, 11, 0.15);
  color: #b45309;
}
.badge-in_progress .badge-dot,
.badge-pending .badge-dot,
.badge-ongoing .badge-dot {
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

/* ── Red — error / delay states ── */
.badge-delayed,
.badge-rejected {
  background: rgba(239, 68, 68, 0.12);
  color: #c62828;
}
.badge-delayed .badge-dot,
.badge-rejected .badge-dot {
  background: #c62828;
}

/* ── Purple — suspended ── */
.badge-suspended {
  background: rgba(139, 92, 246, 0.12);
  color: #6d28d9;
}
.badge-suspended .badge-dot {
  background: #7c3aed;
}
</style>