<template>
    <div class="forgot-page">
        <div class="forgot-center">
            <div class="forgot-card">

                <!-- Icon -->
                <div class="forgot-icon-wrap">
                    <i class="material-icons-round">manage_accounts</i>
                </div>

                <!-- Header -->
                <div class="forgot-header text-center">
                    <span class="forgot-label">ACCOUNT RECOVERY</span>
                    <h2 class="forgot-title">RESET YOUR PASSWORD</h2>
                    <p class="forgot-desc">
                        Enter your registered official LGU email address. Your request will be sent to the
                        <strong>System Administrator</strong> who will process your password reset.
                    </p>
                </div>

                <!-- Form -->
                <form @submit.prevent="handleSubmit">
                    <div class="form-group mb-3">
                        <label class="form-label">Official LGU Email Address</label>
                        <div class="input-with-icon">
                            <i class="material-icons-round text-secondary">alternate_email</i>
                            <input
                                v-model="email"
                                type="email"
                                class="form-control"
                                placeholder="example@tuao.gov.ph"
                                :disabled="submitted"
                            />
                        </div>
                    </div>

                    <!-- Info notice -->
                    <div class="forgot-notice">
                        <i class="material-icons-round">shield</i>
                        <span>Password reset requests are reviewed and processed by the IT Administrator.</span>
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="btn btn-forgot w-100"
                        :disabled="submitted || loading"
                    >
                        <span v-if="loading" class="btn-inner">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            SENDING REQUEST...
                        </span>
                        <span v-else-if="submitted" class="btn-inner">
                            <i class="material-icons-round me-1">check_circle</i>
                            REQUEST SENT
                        </span>
                        <span v-else class="btn-inner">
                            SEND RESET REQUEST
                            <i class="material-icons-round ms-1">send</i>
                        </span>
                    </button>
                </form>

                <!-- Success message -->
                <div v-if="submitted" class="forgot-success">
                    <i class="material-icons-round">info</i>
                    <span>
                        Your request has been submitted. The IT Admin will contact you at
                        <strong>{{ email }}</strong> once your password has been reset.
                    </span>
                </div>

                <!-- Back to login -->
                <div class="forgot-footer text-center">
                    <span>Remembered your password?</span>
                    <router-link :to="{ name: 'Login' }" class="back-link">Back to Login</router-link>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import showSwal from "@/mixins/showSwal";
import bgImage from "@/assets/img/LGU Tuao bg.jpg";

export default {
    name: "Password Forgot",
    data() {
        return {
            bgImage,
            email: "",
            loading: false,
            submitted: false,
        };
    },
    computed: {
        backgroundImageUrl() {
            return `linear-gradient(180deg, rgba(200,17,32,.45) 0%, rgba(20,40,90,.55) 100%), url(${this.bgImage})`;
        },
    },
    methods: {
        async handleSubmit() {
            if (!this.email) {
                showSwal.methods.showSwal({
                    type: "error",
                    message: "Please enter your email address.",
                    width: 500,
                });
                return;
            }

            this.loading = true;
            try {
                await this.$store.dispatch("auth/forgotPassword", { email: this.email });
                this.submitted = true;
                showSwal.methods.showSwal({
                    type: "success",
                    message: "Your password reset request has been sent to the IT Administrator.",
                    width: 500,
                });
            } catch (error) {
                showSwal.methods.showSwal({
                    type: "error",
                    message: "Oops, something went wrong. Please try again.",
                    width: 500,
                });
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&display=swap');

/* ─── Page Layout ─────────────────────────────────────────── */
.forgot-page {
    min-height: 100vh;
    background-image: v-bind(backgroundImageUrl);
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

/* ─── Card ────────────────────────────────────────────────── */
.forgot-center {
    width: 100%;
    display: flex;
    justify-content: center;
}

.forgot-card {
    width: min(100%, 440px);
    background: rgba(255, 255, 255, 0.97);
    border-radius: 1.25rem;
    padding: 2.5rem 2rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    border-top: 3px solid rgba(80, 130, 220, 0.6);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* ─── Icon ────────────────────────────────────────────────── */
.forgot-icon-wrap {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #c0392b 0%, #1a4fa0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: 0 6px 20px rgba(160, 20, 20, 0.3);
}

.forgot-icon-wrap .material-icons-round {
    color: #fff;
    font-size: 1.75rem;
}

/* ─── Header ──────────────────────────────────────────────── */
.forgot-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 3px;
    color: #c0392b;
    text-transform: uppercase;
    margin-bottom: 0.4rem;
}

.forgot-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1f2633;
    letter-spacing: 0.04em;
    margin-bottom: 0.6rem;
}

.forgot-desc {
    font-size: 0.88rem;
    color: #667085;
    line-height: 1.6;
    margin: 0;
}

/* ─── Input ───────────────────────────────────────────────── */
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
    font-size: 0.92rem;
}

.form-control:focus {
    border-color: rgba(80, 130, 220, 0.6);
    box-shadow: 0 0 0 3px rgba(80, 130, 220, 0.15);
}

.form-control:disabled {
    background: #f4f6fb;
    color: #8a93a5;
}

/* ─── Notice ──────────────────────────────────────────────── */
.forgot-notice {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    background: #fff8f8;
    border: 1px solid rgba(192, 57, 43, 0.2);
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    margin-bottom: 0.25rem;
}

.forgot-notice .material-icons-round {
    color: #c0392b;
    font-size: 1.1rem;
    flex-shrink: 0;
    margin-top: 0.05rem;
}

.forgot-notice span {
    font-size: 0.82rem;
    color: #7b2020;
    line-height: 1.5;
}

/* ─── Button ──────────────────────────────────────────────── */
.btn-forgot {
    background: linear-gradient(135deg, #c0392b 0%, #1a4fa0 100%);
    border: none;
    padding: 0.95rem 1rem;
    border-radius: 0.85rem;
    font-weight: 700;
    letter-spacing: 1.5px;
    color: #fff;
    font-size: 0.88rem;
    box-shadow: 0 8px 24px rgba(160, 20, 20, 0.25);
    transition: opacity 0.2s;
    cursor: pointer;
}

.btn-forgot:hover:not(:disabled) {
    opacity: 0.92;
}

.btn-forgot:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
}

.btn-inner .material-icons-round {
    font-size: 1.1rem;
}

/* ─── Success Notice ──────────────────────────────────────── */
.forgot-success {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    background: #f0f9f4;
    border: 1px solid rgba(26, 79, 160, 0.2);
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
}

.forgot-success .material-icons-round {
    color: #1a4fa0;
    font-size: 1.1rem;
    flex-shrink: 0;
    margin-top: 0.05rem;
}

.forgot-success span {
    font-size: 0.82rem;
    color: #1f3a5f;
    line-height: 1.5;
}

/* ─── Footer ──────────────────────────────────────────────── */
.forgot-footer {
    font-size: 0.9rem;
    color: #495057;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.back-link {
    color: #c0392b;
    font-weight: 600;
    text-decoration: none;
}

.back-link:hover {
    color: #1a4fa0;
    text-decoration: underline;
}

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 576px) {
    .forgot-page {
        background-attachment: scroll;
    }

    .forgot-card {
        padding: 2rem 1.25rem;
    }

    .forgot-center {
        padding: 1.5rem 0.75rem;
    }

    .forgot-title {
        font-size: 1.25rem;
    }

    .forgot-footer {
        flex-direction: column;
        gap: 0.2rem;
    }
}

@media (max-width: 420px) {
    .forgot-card {
        padding: 1.25rem 1rem;
    }

    .recovery-icon {
        width: 56px;
        height: 56px;
    }

    .recovery-icon i {
        font-size: 1.65rem;
    }

    .forgot-title h4 {
        font-size: 1.1rem;
    }
}
</style>
