<template>
    <div class="login-page">
        <div class="brand-bar">
            <div class="brand-inner">
                <div class="brand-logo">
                    <img :src="logo" alt="BFP Logo" class="logo-image" />
                </div>
                <div class="brand-texts">
                    <span class="brand-title">Bureau of Fire Protection Region II</span>
                    <div class="brand-subtitle-row">
                        <span class="brand-line"></span>
                        <span class="brand-subtitle">Digital Project Monitoring System</span>
                        <span class="brand-line right"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-center">
            <div class="login-card card shadow-lg">
                <div class="card-body">
                    <div class="login-title text-center mb-4">
                        <h4>LOGIN TO YOUR ACCOUNT</h4>
                    </div>

                    <form class="login-form" @submit.prevent="handleLogin">
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-with-icon">
                                <i class="material-icons-round text-secondary">mail</i>
                                <input id="email" v-model="user.email" type="email" class="form-control" placeholder="Enter email" />
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-with-icon">
                                <i class="material-icons-round text-secondary">lock</i>
                                <input
                                    id="password"
                                    v-model="user.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-control password-input"
                                    placeholder="Enter password"
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

                        <div class="d-flex justify-content-end mb-4">
                            <span class="forgot-link" @click="showForgotModal = true">Forgot Password?</span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-login w-100">LOGIN</button>

                        <div class="login-footer mt-4 text-center">
                            <span
                                class="contact-support-link"
                                @click="showContactModal = true"
                            >Contact Support</span>
                            <span class="mx-2 text-muted">or</span>
                            <router-link :to="{ name: 'Signup' }" class="access-link">Request Access</router-link>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Forgot Password Modal -->
        <div v-if="showForgotModal" class="contact-modal-backdrop" @click.self="showForgotModal = false">
            <div class="contact-modal">
                <div class="contact-modal-header">
                    <div class="contact-modal-icon">
                        <i class="material-icons-round">lock_reset</i>
                    </div>
                    <div>
                        <h5>Forgot Password?</h5>
                        <p>Bureau of Fire Protection Region II</p>
                    </div>
                    <button class="contact-modal-close" @click="showForgotModal = false">
                        <i class="material-icons-round">close</i>
                    </button>
                </div>
                <div class="contact-modal-body">
                    <div class="forgot-reminder-banner">
                        <i class="material-icons-round">admin_panel_settings</i>
                        <div>
                            <span class="forgot-reminder-title">Contact the System Administrator</span>
                            <span class="forgot-reminder-desc">
                                Password resets are handled by the IT Admin. Please reach out directly to have your password reset.
                            </span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="material-icons-round">email</i>
                        <div>
                            <span class="contact-label">Email Address</span>
                            <a href="mailto:itsupport@bfp2.gov.ph" class="contact-value contact-link">itsupport@bfp2.gov.ph</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="material-icons-round">phone</i>
                        <div>
                            <span class="contact-label">Office Telephone</span>
                            <a href="tel:+6378846-1234" class="contact-value contact-link">(078) 846-1234</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="material-icons-round">schedule</i>
                        <div>
                            <span class="contact-label">Office Hours</span>
                            <span class="contact-value">Monday – Friday, 8:00 AM – 5:00 PM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Support Modal -->
        <div v-if="showContactModal" class="contact-modal-backdrop" @click.self="showContactModal = false">
            <div class="contact-modal">
                <div class="contact-modal-header">
                    <div class="contact-modal-icon">
                        <i class="material-icons-round">support_agent</i>
                    </div>
                    <div>
                        <h5>IT Support Contact</h5>
                        <p>Bureau of Fire Protection Region II</p>
                    </div>
                    <button class="contact-modal-close" @click="showContactModal = false">
                        <i class="material-icons-round">close</i>
                    </button>
                </div>
                <div class="contact-modal-body">
                    <div class="contact-item">
                        <i class="material-icons-round">person</i>
                        <div>
                            <span class="contact-label">System Administrator</span>
                            <span class="contact-value">IT Admin — BFP Region II</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="material-icons-round">email</i>
                        <div>
                            <span class="contact-label">Email Address</span>
                            <a href="mailto:itsupport@bfp2.gov.ph" class="contact-value contact-link">itsupport@bfp2.gov.ph</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="material-icons-round">phone</i>
                        <div>
                            <span class="contact-label">Office Telephone</span>
                            <a href="tel:+6378846-1234" class="contact-value contact-link">(078) 846-1234</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="material-icons-round">schedule</i>
                        <div>
                            <span class="contact-label">Office Hours</span>
                            <span class="contact-value">Monday – Friday, 8:00 AM – 5:00 PM</span>
                        </div>
                    </div>
                </div>
                <div class="contact-modal-footer">
                    <p>For urgent matters outside office hours, email with subject line <strong>[URGENT] ConTrackPro Support</strong>.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import showSwal from "@/mixins/showSwal";
import { mapMutations } from "vuex";
import logo from "@/assets/img/BFP 11.png";
import bgImage from "@/assets/img/bg.png";

export default {
    name: "Login",
    data() {
        return {
            logo,
            bgImage,
            user: { email: "admin@contrackpro.test", password: "password" },
            showPassword: false,
            showContactModal: false,
            showForgotModal: false,
        };
    },
    computed: {
        backgroundImageUrl() {
            return `linear-gradient(180deg, rgba(200,17,32,.45) 0%, rgba(20,40,90,.55) 100%), url(${this.bgImage})`;
        },
        loggedIn() {
            return this.$store.state.auth.loggedIn;
        }
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
        async handleLogin() {
            if (!this.user.email || !this.user.password) {
                showSwal.methods.showSwal({
                    type: "error",
                    message: "Email and password are required.",
                    width: 500
                });
                return;
            }

            try {
                await this.$store.dispatch('auth/login', {
                    email: this.user.email,
                    password: this.user.password
                });
                this.$router.push({ name: 'Dashboard' });
            } catch (error) {
                const apiDetail = error.response?.data?.errors?.[0]?.detail;
                const message = apiDetail
                    || (error.code === "ECONNABORTED"
                        ? "Login timed out. Make sure the backend is running on port 8000."
                        : error.response
                            ? "Invalid credentials!"
                            : "Unable to reach the server. Check that the backend is running.");

                showSwal.methods.showSwal({
                    type: "error",
                    message,
                    width: 500
                });
            }
        },
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&display=swap');

/* ─── Page Layout ─────────────────────────────────────────── */
.login-page {
    min-height: 100vh;
    background-image: v-bind(backgroundImageUrl);
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    position: relative;
    color: #1f2633;
    display: flex;
    flex-direction: column;
}

/* ─── Brand Header Bar ────────────────────────────────────── */
.brand-bar {
    width: 100%;
    background: linear-gradient(
        135deg,
        rgba(190, 20, 20, 0.45) 0%,
        rgba(160, 15, 15, 0.40) 40%,
        rgba(15, 40, 100, 0.45) 100%
    );
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-top: 3px solid rgba(80, 130, 220, 0.85);
    border-bottom: 4px solid #FFD700;
    box-shadow:
        0 1px 0 rgba(255, 255, 255, 0.15),
        0 6px 24px rgba(0, 0, 0, 0.25);
}

.brand-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    padding: 14px 32px;
}

/* ─── Logo Circle ─────────────────────────────────────────── */
.brand-logo {
    width: 82px;
    height: 82px;
    border-radius: 50%;
    border: 3px solid #FFD700;
    outline: 2px solid rgba(80, 130, 220, 0.6);
    outline-offset: 3px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow:
        0 0 0 6px rgba(255, 215, 0, 0.18),
        0 4px 18px rgba(0, 0, 0, 0.4);
}

.logo-image {
    width: 70px;
    height: 70px;
    object-fit: contain;
    border-radius: 50%;
}

/* ─── Text Block ──────────────────────────────────────────── */
.brand-texts {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.brand-title {
    font-family: 'Cinzel', serif;
    font-size: 1.55rem;
    font-weight: 900;
    color: #FFFFFF;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    text-shadow:
        0 0 18px rgba(255, 215, 0, 0.45),
        1px 2px 8px rgba(0, 0, 0, 0.55);
    white-space: nowrap;
}

/* ─── Subtitle with decorative lines ─────────────────────── */
.brand-subtitle-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.brand-line {
    display: inline-block;
    height: 2px;
    width: 55px;
    background: linear-gradient(to right, rgba(80, 130, 220, 0.3), #FFD700);
    border-radius: 2px;
}

.brand-line.right {
    background: linear-gradient(to left, rgba(80, 130, 220, 0.3), #FFD700);
}

.brand-subtitle {
    font-family: 'Cinzel', serif;
    font-size: 0.78rem;
    font-weight: 700;
    color: #FFE566;
    letter-spacing: 4px;
    text-transform: uppercase;
    white-space: nowrap;
    text-shadow:
        0 0 12px rgba(255, 215, 0, 0.7),
        0 1px 4px rgba(0, 0, 0, 0.45);
}

/* ─── Login Card ──────────────────────────────────────────── */
.login-center {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 1rem;
}

.login-card {
    width: min(100%, 440px);
    border-radius: 1.25rem;
    overflow: hidden;
    border-top: 3px solid rgba(80, 130, 220, 0.6) !important;
}

.card-body {
    padding: 2rem;
    background: rgba(255, 255, 255, 0.96);
}

.login-title h4 {
    margin: 0;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: #1f2633;
}

/* ─── Form Inputs ─────────────────────────────────────────── */
.input-with-icon {
    position: relative;
}

.input-with-icon .material-icons-round {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.2rem;
    pointer-events: none;
}

.input-with-icon input {
    padding-left: 3rem;
}

.form-control {
    border-radius: 0.85rem;
    height: 3.4rem;
    border: 1px solid #dfe4ed;
    box-shadow: none;
}

.form-control:focus {
    border-color: rgba(80, 130, 220, 0.6);
    box-shadow: 0 0 0 3px rgba(80, 130, 220, 0.15);
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
    font-size: 1.2rem;
    position: static;
    transform: none;
}

/* ─── Links & Buttons ─────────────────────────────────────── */
.forgot-link {
    color: #c0392b;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: color 0.15s;
}

.forgot-link:hover {
    color: #1a4fa0;
    text-decoration: underline;
}

.access-link {
    color: #c0392b;
    font-weight: 600;
    text-decoration: none;
}

.access-link:hover {
    color: #1a4fa0;
    text-decoration: underline;
}

.btn-login {
    background: linear-gradient(135deg, #c0392b 0%, #1a4fa0 100%);
    border: none;
    padding: 0.95rem 1rem;
    border-radius: 0.85rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    box-shadow: 0 8px 24px rgba(160, 20, 20, 0.25);
    transition: opacity 0.2s;
}

.btn-login:hover {
    opacity: 0.92;
}

/* ─── Footer ──────────────────────────────────────────────── */
.login-footer {
    font-size: 0.95rem;
    color: #495057;
}

.login-footer span {
    font-weight: 500;
}

.contact-support-link {
    color: #c0392b;
    font-weight: 600;
    cursor: pointer;
    transition: color 0.15s;
    text-decoration: none;
}

.contact-support-link:hover {
    color: #1a4fa0;
    text-decoration: underline;
}

/* ─── Shared Modal Styles ─────────────────────────────────── */
.contact-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 25, 50, 0.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 1rem;
}

.contact-modal {
    background: #fff;
    border-radius: 1.25rem;
    width: min(100%, 420px);
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border-top: 3px solid #c0392b;
    animation: modal-in 0.2s ease;
}

@keyframes modal-in {
    from { opacity: 0; transform: translateY(12px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.contact-modal-header {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #edf0f5;
}

.contact-modal-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #c0392b, #1a4fa0);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.contact-modal-icon .material-icons-round {
    color: #fff;
    font-size: 1.3rem;
}

.contact-modal-header h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1f2633;
    line-height: 1.2;
}

.contact-modal-header p {
    margin: 0;
    font-size: 0.78rem;
    color: #8a93a5;
}

.contact-modal-close {
    margin-left: auto;
    background: none;
    border: none;
    padding: 0.25rem;
    cursor: pointer;
    color: #8a93a5;
    display: flex;
    align-items: center;
    border-radius: 0.5rem;
    transition: color 0.15s, background 0.15s;
}

.contact-modal-close:hover {
    color: #1f2633;
    background: #f4f6fb;
}

.contact-modal-close .material-icons-round {
    font-size: 1.2rem;
}

.contact-modal-body {
    padding: 1rem 1.25rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
}

.contact-item > .material-icons-round {
    font-size: 1.15rem;
    color: #c0392b;
    margin-top: 0.1rem;
    flex-shrink: 0;
}

.contact-item > div {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.contact-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: #8a93a5;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.contact-value {
    font-size: 0.9rem;
    color: #1f2633;
    font-weight: 500;
}

.contact-link {
    color: #1a4fa0;
    text-decoration: none;
    font-weight: 600;
}

.contact-link:hover {
    text-decoration: underline;
    color: #c0392b;
}

.contact-modal-footer {
    padding: 0.85rem 1.5rem;
    background: #f8f9fc;
    border-top: 1px solid #edf0f5;
}

.contact-modal-footer p {
    margin: 0.75;
    font-size: 0.8rem;
    color: #667085;
    line-height: 1.5;
}

/* ─── Forgot Password Reminder Banner ────────────────────── */
.forgot-reminder-banner {
    display: flex;
    align-items: flex-start;
    gap: 1.3rem;
    background: linear-gradient(135deg, rgba(192, 57, 43, 0.07), rgba(26, 79, 160, 0.07));
    border: 1px solid rgba(192, 57, 43, 0.2);
    border-radius: 0.85rem;
    padding: 1rem;
}

.forgot-reminder-banner > .material-icons-round {
    color: #c0392b;
    font-size: 0.85rem;
    flex-shrink: 0;
    margin-top: 0.15rem;
}

.forgot-reminder-title {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: #1f2633;
    margin-bottom: 0.2rem;
}

.forgot-reminder-desc {
    display: block;
    font-size: 0.8rem;
    color: #667085;
    line-height: 1.5;
}

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 576px) {
    .brand-inner {
        flex-direction: column;
        text-align: center;
        padding: 12px 16px;
    }

    .brand-title {
        font-size: 1rem;
        letter-spacing: 1px;
        white-space: normal;
        text-align: center;
    }

    .brand-line {
        width: 30px;
    }

    .login-center {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }
}
</style>