import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "../views/Dashboard.vue";
import Tables from "../views/Tables.vue";
import Billing from "../views/Billing.vue";
import RTL from "../views/Rtl.vue";
import Notifications from "../views/Notifications.vue";
import Profile from "../views/Profile.vue";
import SignIn from "../views/SignIn.vue";
import SignUp from "../views/SignUp.vue";
import Login from "../views/examples-api/Login.vue";
import Signup from "../views/examples-api/Signup.vue";
import PasswordForgot from "../views/examples-api/PasswordForgot.vue";
import PasswordReset from "../views/examples-api/PasswordReset.vue";
import UserProfile from "../views/examples-api/profile/UserProfile.vue";
import Users from "../views/examples-api/users/UsersList.vue";

// Module imports
import ContractManagement from "../views/modules/ContractManagement.vue";
import CashflowManagement from "../views/modules/CashflowManagement.vue";
import EngineeringPlans from "../views/modules/EngineeringPlans.vue";
import VariationOrders from "../views/modules/VariationOrders.vue";
import Accomplishments from "../views/modules/Accomplishments.vue";
import ContractorPerformance from "../views/modules/ContractorPerformance.vue";
import AuditTrail from "../views/modules/AuditTrail.vue";
import NotificationsInbox from "../views/modules/NotificationsInbox.vue";
import InfrastructurePlans from "../views/modules/InfrastructurePlans.vue";
import Reports from "../views/modules/Reports.vue";
import Settings from "../views/modules/Settings.vue";

const routes = [
  {
    path: "/",
    name: "/",
    redirect: "/login",
  },
  {
    path: "/dashboard",
    name: "Dashboard",
    component: Dashboard,
  },
  {
    path: "/tables",
    name: "Tables",
    component: Tables,
  },
  {
    path: "/billing",
    name: "Billing",
    component: Billing,
  },
  {
    path: "/rtl-page",
    name: "RTL",
    component: RTL,
  },
  {
    path: "/notifications",
    name: "Notifications",
    component: Notifications,
  },
  {
    path: "/profile",
    name: "Profile",
    component: Profile,
  },
  {
    path: "/sign-in",
    name: "SignIn",
    component: SignIn,
  },
  {
    path: "/sign-up",
    name: "SignUp",
    component: SignUp,
  },
  {
    path: "/login",
    name: "Login",
    component: Login
  },
  {
    path: "/signup",
    name: "Signup",
    component: Signup
  },
  {
    path: "/password-forgot",
    name: "Password Forgot",
    component: PasswordForgot
  },
  {
    path: "/password-reset",
    name: "Password Reset",
    component: PasswordReset
  },
  {
    path: "/user-profile",
    name: "User Profile",
    component: UserProfile
  },
  {
    path: '/users',
    name: "Users",
    component: Users
  },
  // Module routes
  {
    path: "/infrastructure-plans",
    alias: ["/Admin", "/admin"],
    name: "Infrastructure Plans",
    component: InfrastructurePlans
  },
  {
    path: "/contract-management",
    name: "contract-management",
    component: ContractManagement
  },
  {
    path: "/cashflow",
    name: "cashflow",
    component: CashflowManagement
  },
  {
    path: "/engineering-plans",
    name: "engineering-plans",
    component: EngineeringPlans
  },
  {
    path: "/variation-orders",
    name: "variation-orders",
    component: VariationOrders
  },
  {
    path: "/accomplishments",
    name: "accomplishments",
    component: Accomplishments
  },
  {
    path: "/reports",
    name: "Reports",
    component: Reports
  },
  {
    path: "/settings",
    name: "Settings",
    component: Settings
  },
  {
    path: "/contractor-performance",
    name: "contractor-performance",
    component: ContractorPerformance
  },
  {
    path: "/audit-trail",
    name: "audit-trail",
    component: AuditTrail
  },
  {
    path: "/notifications-inbox",
    name: "notifications-inbox",
    component: NotificationsInbox
  }
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  linkActiveClass: "active",
});

export default router;
