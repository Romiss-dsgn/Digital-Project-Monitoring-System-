import { createRouter, createWebHistory } from "vue-router";

// Lazy route imports keep module screens out of the initial bundle.
// This makes the login/dashboard load lighter while each module is still loaded on demand.
const Dashboard = () => import("../views/Dashboard.vue");
const Tables = () => import("../views/Tables.vue");
const Billing = () => import("../views/Billing.vue");
const RTL = () => import("../views/Rtl.vue");
const Notifications = () => import("../views/Notifications.vue");
const Profile = () => import("../views/Profile.vue");
const SignIn = () => import("../views/SignIn.vue");
const SignUp = () => import("../views/SignUp.vue");
const Login = () => import("../views/examples-api/Login.vue");
const Signup = () => import("../views/examples-api/Signup.vue");
const PasswordForgot = () => import("../views/examples-api/PasswordForgot.vue");
const PasswordReset = () => import("../views/examples-api/PasswordReset.vue");
const UserProfile = () => import("../views/examples-api/profile/UserProfile.vue");
const Users = () => import("../views/examples-api/users/UsersList.vue");
const ContractManagement = () => import("../views/modules/ContractManagement.vue");
const CashflowManagement = () => import("../views/modules/CashflowManagement.vue");
const EngineeringPlans = () => import("../views/modules/EngineeringPlans.vue");
const VariationOrders = () => import("../views/modules/VariationOrders.vue");
const Accomplishments = () => import("../views/modules/Accomplishments.vue");
const ContractorPerformance = () => import("../views/modules/ContractorPerformance.vue");
const AuditTrail = () => import("../views/modules/AuditTrail.vue");
const NotificationsInbox = () => import("../views/modules/NotificationsInbox.vue");
const InfrastructurePlans = () => import("../views/modules/InfrastructurePlans.vue");
const Reports = () => import("../views/modules/Reports.vue");
const Settings = () => import("../views/modules/Settings.vue");
const UserManagement = () => import("../views/modules/UserManagement.vue");

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
    name: "infrastructure-plans",
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

  ,{
    path: "/user-management",
    name: "user-management",
    component: UserManagement
  }
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  linkActiveClass: "active",
});

export default router;
