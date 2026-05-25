<template>
  <span :class="['badge', `badge-${status}`]">{{ status_text }}</span>
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
        "on_track": "On Track",
        "pending": "Pending",
        "approved": "Approved",
        "rejected": "Rejected",
        "completed": "Completed",
        "delayed": "Delayed",
        "active": "Active",
        "paid": "Paid"
      };
      return statusMap[this.status] || this.status;
    }
  }
};
</script>

<style scoped>
.badge {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.badge-in_progress {
  background: #fff3cd;
  color: #856404;
}

.badge-on_track {
  background: #d1ecf1;
  color: #0c5460;
}

.badge-pending {
  background: #fff3cd;
  color: #856404;
}

.badge-approved {
  background: #d4edda;
  color: #155724;
}

.badge-rejected {
  background: #f8d7da;
  color: #721c24;
}

.badge-completed {
  background: #d4edda;
  color: #155724;
}

.badge-delayed {
  background: #f8d7da;
  color: #721c24;
}

.badge-active {
  background: #d4edda;
  color: #155724;
}

.badge-paid {
  background: #d4edda;
  color: #155724;
}
</style>
