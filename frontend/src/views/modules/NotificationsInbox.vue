<template>
  <div class="module-page">
    <div class="container-fluid py-4">
      <!-- Header -->
      <div class="row mb-4 align-items-center">
        <div class="col-lg-8">
          <h4 class="mb-0">Notifications Inbox</h4>
          <p class="text-secondary small">All pending system alerts and actions</p>
        </div>
        <div class="col-lg-4 text-end">
          <button class="btn btn-sm btn-outline-secondary" @click="markAllAsRead">
            <i class="material-icons-round">done_all</i> Mark All as Read
          </button>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="tabs-navigation">
            <button v-for="tab in tabs" :key="tab.id" :class="['tab-btn', { active: activeTab === tab.id }]" @click="activeTab = tab.id">
              {{ tab.label }}
              <span v-if="tab.count > 0" class="badge">{{ tab.count }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="row">
        <div class="col-12">
          <div class="notifications-list">
            <div v-for="notification in filteredNotifications" :key="notification.id" :class="['notification-item', { unread: !notification.read }]">
              <div class="notification-icon">
                <i :class="['material-icons-round', notification.icon_class]">{{ notification.icon }}</i>
              </div>
              <div class="notification-content">
                <div class="notification-header">
                  <h6>{{ notification.title }}</h6>
                  <span class="notification-time">{{ notification.time }}</span>
                </div>
                <p>{{ notification.message }}</p>
                <div class="notification-actions">
                  <a v-if="notification.action_link" :href="notification.action_link" class="action-link">{{ notification.action_text }}</a>
                  <button class="btn-dismiss" @click="dismissNotification(notification.id)">Dismiss</button>
                </div>
              </div>
              <div v-if="!notification.read" class="unread-indicator"></div>
            </div>
            <div v-if="filteredNotifications.length === 0" class="empty-state">
              <i class="material-icons-round">done_all</i>
              <p>All caught up! No new notifications.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "NotificationsInbox",
  data() {
    return {
      activeTab: "all",
      tabs: [
        { id: "all", label: "All", count: 8 },
        { id: "project", label: "Projects", count: 3 },
        { id: "contract", label: "Contracts", count: 2 },
        { id: "cashflow", label: "Cashflow", count: 2 },
        { id: "approval", label: "Approvals", count: 1 }
      ],
      notifications: [
        { id: 1, category: "project", icon: "warning", icon_class: "text-danger", title: "Project Delayed", message: "Road Widening Project has been delayed by 5 days. Expected completion date is now 06/05/2023.", time: "2 hours ago", read: false, action_text: "View Project", action_link: "#infrastructure-plans" },
        { id: 2, category: "contract", icon: "edit_document", icon_class: "text-warning", title: "Pending Approval", message: "Variation Order VO-2023-002 is waiting for your approval.", time: "4 hours ago", read: false, action_text: "Review & Approve", action_link: "#variation-orders" },
        { id: 3, category: "cashflow", icon: "trending_down", icon_class: "text-warning", title: "Budget Variance Alert", message: "Contract LGU-TUAO-CON-2026-001 is exceeding the allocated budget by ₱850,000.", time: "1 day ago", read: false, action_text: "View Budget", action_link: "#cashflow" },
        { id: 4, category: "project", icon: "check_circle", icon_class: "text-success", title: "Milestone Completed", message: "Milestone 'Site Mobilization' for Municipal Hall Records Room Improvement has been completed.", time: "1 day ago", read: true, action_text: "View Accomplishment", action_link: "#accomplishments" },
        { id: 5, category: "contract", icon: "description", icon_class: "text-info", title: "Contract Document Uploaded", message: "Supplemental Agreement for Contract LGU-TUAO-CON-2026-001 has been uploaded.", time: "2 days ago", read: true, action_text: "View Document", action_link: "#contract-management" },
        { id: 6, category: "approval", icon: "notifications", icon_class: "text-primary", title: "Payment Approval Needed", message: "Invoice INV-003 is awaiting your approval for payment processing.", time: "2 days ago", read: true, action_text: "Approve Payment", action_link: "#cashflow" },
        { id: 7, category: "project", icon: "schedule", icon_class: "text-info", title: "Phase Status Update", message: "Infrastructure Plan LGU-TUAO-PROJ-003 phase has been updated to Construction.", time: "3 days ago", read: true, action_text: "View Project", action_link: "#infrastructure-plans" },
        { id: 8, category: "cashflow", icon: "receipt", icon_class: "text-secondary", title: "Invoice Received", message: "Invoice INV-002 has been received from contractor Tuao Civil Works and Engineering.", time: "4 days ago", read: true, action_text: "Process Invoice", action_link: "#cashflow" }
      ]
    };
  },
  computed: {
    filteredNotifications() {
      if (this.activeTab === "all") {
        return this.notifications;
      }
      return this.notifications.filter(n => n.category === this.activeTab);
    }
  },
  methods: {
    markAllAsRead() {
      this.notifications.forEach(n => n.read = true);
    },
    dismissNotification(id) {
      this.notifications = this.notifications.filter(n => n.id !== id);
    }
  }
};
</script>

<style scoped>
.module-page {
  background: #f7fafc;
  min-height: 100vh;
}

.tabs-navigation {
  display: flex;
  gap: 1rem;
  border-bottom: 2px solid #e0e5ee;
  overflow-x: auto;
  margin-bottom: -2px;
}

.tab-btn {
  padding: 1rem 1.5rem;
  background: transparent;
  border: none;
  color: #5a6270;
  font-weight: 500;
  font-size: 0.95rem;
  cursor: pointer;
  position: relative;
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.tab-btn.active {
  color: #1565C0;
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: #1565C0;
}

.tab-btn .badge {
  background: #d32f2f;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  min-width: 20px;
  text-align: center;
}

.notifications-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
  border-left: 4px solid #e0e5ee;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.notification-item:hover {
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
}

.notification-item.unread {
  background: #f7fafc;
  border-left-color: #c82a3e;
}

.notification-icon {
  min-width: 50px;
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 0.85rem;
  background: #f0f2f5;
}

.notification-icon i {
  font-size: 1.5rem;
}

.notification-content {
  flex: 1;
}

.notification-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.notification-header h6 {
  color: #1f2633;
  font-weight: 600;
  margin: 0;
  font-size: 0.95rem;
}

.notification-time {
  color: #a0aec0;
  font-size: 0.75rem;
  white-space: nowrap;
}

.notification-item p {
  color: #5a6270;
  font-size: 0.875rem;
  margin: 0.5rem 0;
  line-height: 1.5;
}

.notification-actions {
  display: flex;
  gap: 1rem;
  margin-top: 0.75rem;
}

.action-link {
  color: #c82a3e;
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
}

.action-link:hover {
  text-decoration: underline;
}

.btn-dismiss {
  background: transparent;
  border: 1px solid #dfe4ed;
  color: #5a6270;
  padding: 0.35rem 0.75rem;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 0.75rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-dismiss:hover {
  background: #f0f2f5;
  border-color: #c0c7d0;
}

.unread-indicator {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  width: 8px;
  height: 8px;
  background: #c82a3e;
  border-radius: 50%;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 15px 35px rgba(15, 23, 42, 0.08);
}

.empty-state i {
  font-size: 3rem;
  color: #dfe4ed;
  margin-bottom: 1rem;
}

.empty-state p {
  color: #a0aec0;
  margin: 0;
}
</style>
