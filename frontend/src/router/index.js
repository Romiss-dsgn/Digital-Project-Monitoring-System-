import { createRouter, createWebHistory } from "vue-router";
import ProfileService from "@/services/profile.service";
import { clearStoredAuthToken, hasStoredAuthToken } from "@/services/auth-token";

// Lazy route imports keep module screens out of the initial bundle.
// This makes the login/dashboard load lighter while each module is still loaded on demand.
const Dashboard = () => import("../views/Dashboard.vue");
const Login = () => import("../views/examples-api/Login.vue");
const Signup = () => import("../views/examples-api/Signup.vue");
const PasswordForgot = () => import("../views/examples-api/PasswordForgot.vue");
const PasswordReset = () => import("../views/examples-api/PasswordReset.vue");
const ContractManagement = () => import("../views/modules/ContractManagement.vue");
const CashflowManagement = () => import("../views/modules/CashflowManagement.vue");
const EngineeringPlans = () => import("../views/modules/EngineeringPlans.vue");
const VariationOrders = () => import("../views/modules/VariationOrders.vue");
const Accomplishments = () => import("../views/modules/Accomplishments.vue");
const AuditTrail = () => import("../views/modules/AuditTrail.vue");
const InfrastructurePlans = () => import("../views/modules/InfrastructurePlans.vue");
const Reports = () => import("../views/modules/Reports.vue");
const Settings = () => import("../views/modules/Settings.vue");
const UserProfile = () => import("../views/examples-api/profile/UserProfile.vue");
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
    redirect: "/dashboard",
  },
  {
    path: "/billing",
    redirect: "/dashboard",
  },
  {
    path: "/rtl-page",
    redirect: "/dashboard",
  },
  {
    path: "/notifications",
    redirect: "/dashboard",
  },
  {
    path: "/profile",
    redirect: "/settings",
  },
  {
    path: "/sign-in",
    redirect: "/login",
  },
  {
    path: "/sign-up",
    redirect: "/signup",
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
    meta: { public: true, hideAppShell: true },
  },
  {
    path: "/signup",
    name: "Signup",
    component: Signup,
    meta: { public: true, hideAppShell: true },
  },
  {
    path: "/password-forgot",
    name: "Password Forgot",
    component: PasswordForgot,
    meta: { public: true, hideAppShell: true },
  },
  {
    path: "/password-reset",
    name: "Password Reset",
    component: PasswordReset,
    meta: { public: true, hideAppShell: true },
  },
  {
    path: "/user-profile",
    name: "User Profile",
    component: UserProfile,
  },
  {
    path: '/users',
    redirect: "/user-management",
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
    redirect: "/dashboard",
  },
  {
    path: "/audit-trail",
    name: "audit-trail",
    component: AuditTrail
  },
  {
    path: "/notifications-inbox",
    redirect: "/dashboard",
  },
  {
    path: "/user-management",
    name: "user-management",
    component: UserManagement
  },
  {
    path: "/:pathMatch(.*)*",
    redirect: "/dashboard",
  }
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  linkActiveClass: "active",
});

const authPageNames = ["Login", "Signup"];

async function clearLocalSession() {
  clearStoredAuthToken();

  try {
    const { default: store } = await import("@/store");
    await store.dispatch("auth/clearLocalSession");
  } catch (error) {
    void error;
  }
}

async function loadAuthenticatedProfile() {
  const { default: store } = await import("@/store");
  const cachedProfile = store.getters["profile/getUserProfile"];

  if (cachedProfile?.id) {
    return true;
  }

  try {
    const userProfile = await ProfileService.getProfile();
    store.commit("profile/success", userProfile);
    return true;
  } catch (error) {
    if (error.response?.status === 401) {
      await clearLocalSession();
    }

    return false;
  }
}

// Keep protected pages from rendering before auth is known.
// Auth pages declare `hideAppShell`, so the sidebar/navbar never flash before redirect.
router.beforeEach(async (to, from, next) => {
  const hasToken = hasStoredAuthToken();
  const isPublic = to.matched.some((record) => record.meta.public);

  if (!isPublic && !hasToken) {
    next({ name: "Login", query: { redirect: to.fullPath } });
    return;
  }

  if (!isPublic && hasToken) {
    if (!(await loadAuthenticatedProfile())) {
      next({ name: "Login", query: { redirect: to.fullPath } });
      return;
    }
  }

  if (isPublic && hasToken && authPageNames.includes(to.name)) {
    if (await loadAuthenticatedProfile()) {
      next({ name: "Dashboard" });
      return;
    }
  }

  next();
});

export default router;
