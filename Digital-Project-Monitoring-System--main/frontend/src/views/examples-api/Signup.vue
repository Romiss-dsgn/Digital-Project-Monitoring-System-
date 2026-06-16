<template>
    <div class="access-page">
        <main class="access-center">
            <section class="access-card">
                <aside class="access-panel">
                    <div>
                        <div class="panel-brand">
                            <div class="panel-mark">
                                <img :src="logo" alt="BFP Region II Logo" />
                            </div>
                            <div class="panel-brand-copy">
                                <p class="system-name">BFP Region II</p>
                                <p class="system-subtitle">Bureau of Fire Protection</p>
                            </div>
                        </div>
                    </div>

                    <div class="panel-copy">
                        <p class="panel-label">ConTrackPro Monitoring Portal</p>
                        <h1>Request Secure System Access</h1>
                        <p>
                            Official digital project monitoring portal for BFP Region II
                            infrastructure records, engineering plans, contracts,
                            cashflows, variation orders, accomplishments, contractor
                            performance, notifications, and audit history.
                        </p>
                    </div>

                    <div class="security-note">
                        <div class="security-title">
                            <i class="material-icons-round">verified_user</i>
                            <span>Secure Government Network</span>
                        </div>
                        <p>
                            Access is restricted to authorized Bureau of Fire Protection
                            personnel. Account requests are reviewed before activation,
                            and system activity is logged for compliance and audit
                            accountability.
                        </p>
                    </div>
                </aside>

                <div class="access-form-wrap">
                    <div class="form-heading">
                        <p class="eyebrow">Request System Access</p>
                        <h2>ConTrackPro Personnel Registration</h2>
                        <p>
                            Submit your official personnel details so an administrator can
                            review your role and activate the correct module access.
                        </p>
                    </div>

                    <form class="access-form" @submit.prevent="handleSignup">
                        <div class="form-grid">
                            <div class="field-group">
                                <label for="name">Full Name</label>
                                <div class="input-with-icon">
                                    <i class="material-icons-round">badge</i>
                                    <input
                                        id="name"
                                        v-model="user.name"
                                        type="text"
                                        class="form-control"
                                        placeholder="Juan Dela Cruz"
                                        name="name"
                                    />
                                </div>
                            </div>

                            <div class="field-group">
                                <label for="badgeNumber">Badge Number / ID</label>
                                <div class="input-with-icon">
                                    <i class="material-icons-round">assignment_ind</i>
                                    <input
                                        id="badgeNumber"
                                        v-model="user.badgeNumber"
                                        type="text"
                                        class="form-control"
                                        placeholder="BFP-2024-XXXX"
                                        name="badgeNumber"
                                    />
                                </div>
                            </div>

                            <div class="field-group full-span">
                                <label for="email">Bureau Email Address</label>
                                <div class="input-with-icon">
                                    <i class="material-icons-round">alternate_email</i>
                                    <input
                                        id="email"
                                        v-model="user.email"
                                        type="email"
                                        class="form-control"
                                        placeholder="example@bfp.gov.ph"
                                        name="email"
                                    />
                                </div>
                            </div>

                            <div class="field-group">
                                <label for="department">Department / Unit</label>
                                <div class="input-with-icon select-with-icon">
                                    <i class="material-icons-round">apartment</i>
                                    <select
                                        id="department"
                                        v-model="user.department"
                                        class="form-control"
                                        name="department"
                                    >
                                        <option value="" disabled>Select Department</option>
                                        <option value="contract-documentation">Administrative Staff - Contract Documentation</option>
                                        <option value="records-management">Records Management Unit</option>
                                        <option value="contract-monitoring">Contract Monitoring Unit</option>
                                        <option value="engineering-planning">Engineering - Planning</option>
                                        <option value="engineering-supervision">Engineering - Supervision</option>
                                        <option value="engineering-monitoring">Engineering - Monitoring</option>
                                        <option value="regional-admin">Regional Administration</option>
                                    </select>
                                </div>
                            </div>

                            <div class="field-group">
                                <label for="requestedRole">Requested Role</label>
                                <div class="input-with-icon select-with-icon">
                                    <i class="material-icons-round">admin_panel_settings</i>
                                    <select
                                        id="requestedRole"
                                        v-model="user.requestedRole"
                                        class="form-control"
                                        name="requestedRole"
                                    >
                                        <option value="" disabled>Select Role</option>
                                        <option value="administrative-staff">Administrative Staff - Contract Documentation</option>
                                        <option value="records-management-personnel">Records Management Personnel</option>
                                        <option value="contract-monitoring-personnel">Contract Monitoring Personnel</option>
                                        <option value="engineer-planning">Engineer - Planning</option>
                                        <option value="engineer-supervision">Engineer - Supervision</option>
                                        <option value="engineer-monitoring">Engineer - Monitoring</option>
                                        <option value="system-administrator">System Administrator</option>
                                    </select>
                                </div>
                            </div>

                            <div class="field-group">
                                <label for="password">Password</label>
                                <div class="input-with-icon">
                                    <i class="material-icons-round">lock</i>
                                    <input
                                        id="password"
                                        v-model="user.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        class="form-control password-input"
                                        placeholder="Enter password"
                                        name="password"
                                    />
                                    <button
                                        type="button"
                                        class="password-toggle-btn"
                                        @click="showPassword = !showPassword"
                                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                    >
                                        <i class="material-icons-round">{{ showPassword ? 'visibility_off' : 'visibility' }}</i>
                                    </button>
                                </div>
                            </div>

                            <div class="field-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <div class="input-with-icon">
                                    <i class="material-icons-round">lock_reset</i>
                                    <input
                                        id="confirmPassword"
                                        v-model="user.confirmPassword"
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        class="form-control password-input"
                                        placeholder="Confirm password"
                                        name="confirmPassword"
                                    />
                                    <button
                                        type="button"
                                        class="password-toggle-btn"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                                    >
                                        <i class="material-icons-round">{{ showConfirmPassword ? 'visibility_off' : 'visibility' }}</i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <label class="terms-row">
                            <input v-model="termsChecked" type="checkbox" name="checkbox" />
                            <span>
                                I agree to the account review process, government data
                                privacy requirements, document management rules, audit
                                logging, and system security protocols.
                            </span>
                        </label>

                        <button type="submit" class="btn-request">
                            <span>Submit Access Request</span>
                            <i class="material-icons-round">send</i>
                        </button>

                        <div class="access-footer">
                            <span>Already have an account?</span>
                            <router-link :to="{ name: 'Login' }">Login instead</router-link>
                        </div>

                        <div class="compliance-row">
                            <span><i class="material-icons-round">shield</i>DPA Compliant</span>
                            <span><i class="material-icons-round">lock</i>AES-256 Encryption</span>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</template>

<script>
import showSwal from "@/mixins/showSwal";
import { mapMutations } from "vuex";
import * as Yup from "yup";
import logo from "@/assets/img/system-logo/logo.jpg";
import bgImage from "@/assets/img/bg.png";

export default {
    name: "Signup",
    data() {
        return {
            logo,
            bgImage,
            termsChecked: true,
            showPassword: false,
            showConfirmPassword: false,
            user: {
                name: "",
                badgeNumber: "",
                email: "",
                department: "",
                requestedRole: "",
                password: "",
                confirmPassword: "",
            },
            schema: Yup.object().shape({
                name: Yup.string().required("Full name is required"),
                badgeNumber: Yup.string().required("Badge number or ID is required"),
                email: Yup.string()
                    .email("Enter a valid Bureau email address")
                    .required("Bureau email is required"),
                department: Yup.string().required("Department is required"),
                requestedRole: Yup.string().required("Requested role is required"),
                password: Yup.string()
                    .required("Password is required")
                    .min(8, "Password must have at least 8 characters"),
                confirmPassword: Yup.string()
                    .required("Confirm password is required")
                    .oneOf([Yup.ref("password")], "Passwords do not match"),
                termsAccepted: Yup.boolean().oneOf(
                    [true],
                    "You must agree to the access request terms"
                ),
            }),
        };
    },
    computed: {
        panelBackgroundImage() {
            return `linear-gradient(175deg, rgba(180, 14, 26, 0.84) 0%, rgba(120, 10, 20, 0.80) 35%, rgba(28, 34, 58, 0.82) 100%), url(${this.bgImage})`;
        },
    },
    beforeMount() {
        this.toggleEveryDisplay();
        this.toggleHideConfig();
    },
    beforeUnmount() {
        this.toggleEveryDisplay();
        this.toggleHideConfig();
    },
    methods: {
        ...mapMutations(["toggleEveryDisplay", "toggleHideConfig"]),
        async handleSignup() {
            try {
                await this.schema.validate(
                    {
                        ...this.user,
                        termsAccepted: this.termsChecked,
                    },
                    { abortEarly: false }
                );

                const response = await this.$store.dispatch("auth/register", this.user);
                showSwal.methods.showSwal({
                    type: "success",
                    message: response.message || "Access request submitted successfully. Please wait for administrator approval.",
                    width: 500,
                });
                this.$router.push({ name: "Login" });
            } catch (error) {
                const message = error.errors ? error.errors[0] : "Unable to submit access request.";

                showSwal.methods.showSwal({
                    type: "error",
                    message,
                    width: 500,
                });
            }
        },
    },
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&display=swap");

.access-page {
    min-height: 100vh;
    background: #f4f6fb;
    color: #1f2633;
    display: flex;
    flex-direction: column;
}

.access-center {
    flex: 1;
    display: flex;
    align-items: stretch;
    justify-content: stretch;
    padding: 0;
}

.access-card {
    width: 100%;
    min-height: 100vh;
    display: grid;
    grid-template-columns: minmax(300px, 34vw) 1fr;
    overflow: hidden;
    border-radius: 0;
    border: 0;
    background: #fff;
}

.access-panel {
    background-image: v-bind(panelBackgroundImage);
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: #fff;
    padding: clamp(2rem, 4vw, 4.25rem);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}

.access-panel::after {
    content: "";
    position: absolute;
    inset: 0;
    background:
        linear-gradient(90deg, rgba(0, 0, 0, 0.18), transparent 38%),
        radial-gradient(circle at 16% 12%, rgba(255, 255, 255, 0.12), transparent 24%);
    pointer-events: none;
}

.access-panel > * {
    position: relative;
    z-index: 1;
}

.panel-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.panel-mark {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.98);
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    overflow: hidden;
    box-shadow:
        0 0 0 1px rgba(255, 255, 255, 0.38),
        0 8px 20px rgba(0, 0, 0, 0.25);
}

.panel-mark img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    transition: transform 0.25s ease;
}

.panel-brand:hover .panel-mark img {
    transform: scale(1.06);
}

.panel-brand-copy {
    min-width: 0;
}

.system-name {
    margin: 0;
    color: #fff;
    font-size: 0.98rem;
    font-weight: 800;
    line-height: 1.08;
    letter-spacing: 0.01em;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.34);
    white-space: nowrap;
}

.system-subtitle {
    margin: 0.2rem 0 0;
    color: rgba(255, 255, 255, 0.94);
    font-size: 0.7rem;
    font-weight: 700;
    line-height: 1.1;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    white-space: nowrap;
}

.panel-copy {
    margin-top: 4rem;
}

.panel-label {
    margin-bottom: 0.85rem;
    font-size: 0.78rem;
    font-weight: 800;
    line-height: 1.3;
    text-transform: uppercase;
    color: #ffe566;
    letter-spacing: 0.08em;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.35);
}

.panel-copy h1 {
    max-width: 420px;
    margin: 0 0 1.1rem;
    color: #fff;
    font-size: clamp(2.45rem, 4vw, 4.05rem);
    font-weight: 900;
    line-height: 1.02;
    letter-spacing: 0;
    text-wrap: balance;
    text-shadow: 0 3px 14px rgba(0, 0, 0, 0.34);
}

.panel-copy p,
.security-note p {
    margin: 0;
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.88rem;
    line-height: 1.55;
    max-width: 520px;
}

.security-note {
    padding-top: 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.18);
}

.security-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.8rem;
    color: #ffe566;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.security-title i {
    font-size: 1.1rem;
}

.access-form-wrap {
    width: min(100%, 820px);
    align-self: center;
    justify-self: center;
    padding: clamp(2rem, 4vw, 4.75rem);
}

.form-heading {
    margin-bottom: 1.7rem;
}

.eyebrow {
    margin: 0 0 0.35rem;
    color: #c0392b;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.form-heading h2 {
    margin: 0;
    color: #1f2633;
    font-size: 1.55rem;
    font-weight: 800;
    letter-spacing: 0.02em;
}

.form-heading p:last-child {
    margin: 0.45rem 0 0;
    color: #667085;
    font-size: 0.92rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.field-group {
    min-width: 0;
}

.full-span {
    grid-column: 1 / -1;
}

.field-group label {
    display: block;
    margin-bottom: 0.45rem;
    color: #4b5671;
    font-size: 0.82rem;
    font-weight: 600;
}

.input-with-icon {
    position: relative;
}

.input-with-icon .material-icons-round {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #7b849a;
    font-size: 1.15rem;
    pointer-events: none;
}

.form-control {
    width: 100%;
    height: 3.15rem;
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 1px solid #dfe4ed;
    border-radius: 0.85rem;
    color: #1f2633;
    background-color: #fff;
    font-size: 0.92rem;
    box-shadow: none;
}

.select-with-icon select {
    appearance: none;
    background-image: linear-gradient(45deg, transparent 50%, #6b7280 50%),
        linear-gradient(135deg, #6b7280 50%, transparent 50%);
    background-position: calc(100% - 18px) 50%, calc(100% - 13px) 50%;
    background-size: 5px 5px, 5px 5px;
    background-repeat: no-repeat;
    padding-right: 2.6rem;
}

.form-control:focus {
    border-color: rgba(80, 130, 220, 0.7);
    box-shadow: 0 0 0 3px rgba(80, 130, 220, 0.15);
    outline: none;
}

/* ─── Password Toggle ─────────────────────────────────────── */
.password-input {
    padding-right: 3rem;
}

.password-toggle-btn {
    position: absolute;
    right: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: #7b849a;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s;
    line-height: 1;
}

.password-toggle-btn:hover {
    color: #1a4fa0;
}

.password-toggle-btn .material-icons-round {
    font-size: 1.15rem;
    position: static;
    transform: none;
}

.terms-row {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    margin: 1.25rem 0 1.4rem;
    color: #4b5671;
    font-size: 0.84rem;
    line-height: 1.45;
}

.terms-row input {
    width: 1rem;
    height: 1rem;
    margin-top: 0.1rem;
    flex: 0 0 auto;
    accent-color: #c0392b;
}

.btn-request {
    width: 100%;
    min-height: 3.35rem;
    border: 0;
    border-radius: 0.85rem;
    background: linear-gradient(135deg, #c0392b 0%, #1a4fa0 100%);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.65rem;
    font-size: 0.82rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    box-shadow: 0 8px 24px rgba(160, 20, 20, 0.25);
    transition: opacity 0.2s, transform 0.2s;
}

.btn-request:hover {
    opacity: 0.94;
    transform: translateY(-1px);
}

.btn-request i {
    font-size: 1.1rem;
}

.access-footer {
    display: flex;
    justify-content: center;
    gap: 0.45rem;
    margin-top: 1.35rem;
    color: #4b5671;
    font-size: 0.9rem;
}

.access-footer a {
    color: #c0392b;
    font-weight: 700;
    text-decoration: none;
}

.access-footer a:hover {
    color: #1a4fa0;
    text-decoration: underline;
}

.compliance-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1.05rem;
    border-top: 1px solid #edf0f5;
    color: #8a93a5;
    font-size: 0.8rem;
}

.compliance-row span {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.compliance-row i {
    font-size: 0.95rem;
}

@media (max-width: 991px) {
    .access-card {
        grid-template-columns: 1fr;
        min-height: auto;
    }

    .access-panel {
        min-height: 360px;
        gap: 1.5rem;
    }

    .access-form-wrap {
        width: 100%;
        padding: 2.2rem;
    }
}

@media (max-width: 576px) {
    .access-center {
        padding: 0;
    }

    .access-form-wrap {
        padding: 1.5rem;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .access-footer,
    .compliance-row {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
}
</style>