<template>
    <div class="forgot-page">
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

        <main class="forgot-center">
            <section class="forgot-card card shadow-lg">
                <div class="card-body">
                    <div class="recovery-icon">
                        <i class="material-icons-round">lock_reset</i>
                    </div>

                    <div class="forgot-title text-center">
                        <p class="eyebrow">Account Recovery</p>
                        <h4>RESET YOUR PASSWORD</h4>
                        <p>
                            Enter your registered Bureau email address. We will send a
                            secure password reset link after verifying your account.
                        </p>
                    </div>

                    <form class="forgot-form" @submit.prevent="handleReset">
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Bureau Email Address</label>
                            <div class="input-with-icon">
                                <i class="material-icons-round text-secondary">alternate_email</i>
                                <input
                                    id="email"
                                    v-model="userEmail"
                                    type="email"
                                    class="form-control"
                                    placeholder="example@bfp.gov.ph"
                                    name="email"
                                />
                            </div>
                        </div>

                        <div class="recovery-note">
                            <i class="material-icons-round">verified_user</i>
                            <span>Reset links are time-sensitive and should not be shared.</span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-reset w-100">
                            <span>Send Reset Link</span>
                            <i class="material-icons-round">send</i>
                        </button>

                        <div class="forgot-footer text-center">
                            <span>Remembered your password?</span>
                            <router-link :to="{ name: 'Login' }" class="login-link">Back to Login</router-link>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</template>

<script>
import showSwal from "@/mixins/showSwal";
import * as Yup from "yup";
import logo from "@/assets/img/BFP 11.png";
import bgImage from "@/assets/img/bg.png";

export default {
    name: "PasswordForgot",
    data() {
        return {
            logo,
            bgImage,
            userEmail: "",
            schema: Yup.object().shape({
                email: Yup.string()
                    .email("Enter a valid Bureau email address")
                    .required("Bureau email is required"),
            }),
        };
    },
    computed: {
        backgroundImageUrl() {
            return `linear-gradient(180deg, rgba(200,17,32,.45) 0%, rgba(20,40,90,.55) 100%), url(${this.bgImage})`;
        },
    },
    methods: {
        async handleReset() {
            try {
                await this.schema.validate(
                    { email: this.userEmail },
                    { abortEarly: false }
                );

                await this.$store.dispatch("auth/passwordForgot", this.userEmail);
                showSwal.methods.showSwal({
                    type: "success",
                    message: "Password reset link sent to your email.",
                    width: 500,
                });
                this.$router.push({ name: "Login" });
            } catch (error) {
                const message = error.errors
                    ? error.errors[0]
                    : "Unable to send password reset link.";

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

.forgot-page {
    min-height: 100vh;
    background-image: v-bind(backgroundImageUrl);
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: #1f2633;
    display: flex;
    flex-direction: column;
}

.brand-bar {
    width: 100%;
    background: linear-gradient(
        135deg,
        rgba(190, 20, 20, 0.45) 0%,
        rgba(160, 15, 15, 0.4) 40%,
        rgba(15, 40, 100, 0.45) 100%
    );
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-top: 3px solid rgba(80, 130, 220, 0.85);
    border-bottom: 4px solid #ffd700;
    box-shadow: 0 1px 0 rgba(255, 255, 255, 0.15), 0 6px 24px rgba(0, 0, 0, 0.25);
}

.brand-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    padding: 14px 32px;
}

.brand-logo {
    width: 82px;
    height: 82px;
    border-radius: 50%;
    border: 3px solid #ffd700;
    outline: 2px solid rgba(80, 130, 220, 0.6);
    outline-offset: 3px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 0 0 6px rgba(255, 215, 0, 0.18), 0 4px 18px rgba(0, 0, 0, 0.4);
}

.logo-image {
    width: 70px;
    height: 70px;
    object-fit: contain;
    border-radius: 50%;
}

.brand-texts {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.brand-title {
    font-family: "Cinzel", serif;
    font-size: 1.55rem;
    font-weight: 900;
    color: #fff;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    text-shadow: 0 0 18px rgba(255, 215, 0, 0.45), 1px 2px 8px rgba(0, 0, 0, 0.55);
    white-space: nowrap;
}

.brand-subtitle-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.brand-line {
    display: inline-block;
    height: 2px;
    width: 55px;
    background: linear-gradient(to right, rgba(80, 130, 220, 0.3), #ffd700);
    border-radius: 2px;
}

.brand-line.right {
    background: linear-gradient(to left, rgba(80, 130, 220, 0.3), #ffd700);
}

.brand-subtitle {
    font-family: "Cinzel", serif;
    font-size: 0.78rem;
    font-weight: 700;
    color: #ffe566;
    letter-spacing: 4px;
    text-transform: uppercase;
    white-space: nowrap;
    text-shadow: 0 0 12px rgba(255, 215, 0, 0.7), 0 1px 4px rgba(0, 0, 0, 0.45);
}

.forgot-center {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4rem 1rem;
}

.forgot-card {
    width: min(100%, 460px);
    border-radius: 1.25rem;
    overflow: hidden;
    border-top: 3px solid rgba(80, 130, 220, 0.6) !important;
}

.card-body {
    padding: 2rem;
    background: rgba(255, 255, 255, 0.96);
}

.recovery-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1.1rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #c0392b 0%, #1a4fa0 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 28px rgba(160, 20, 20, 0.25);
}

.recovery-icon i {
    font-size: 2rem;
}

.forgot-title {
    margin-bottom: 1.4rem;
}

.eyebrow {
    margin: 0 0 0.35rem;
    color: #c0392b;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.forgot-title h4 {
    margin: 0;
    color: #1f2633;
    font-weight: 800;
    letter-spacing: 0.04em;
}

.forgot-title p:last-child {
    margin: 0.65rem 0 0;
    color: #667085;
    font-size: 0.92rem;
    line-height: 1.55;
}

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
    height: 3.4rem;
    border: 1px solid #dfe4ed;
    border-radius: 0.85rem;
    box-shadow: none;
}

.form-control:focus {
    border-color: rgba(80, 130, 220, 0.6);
    box-shadow: 0 0 0 3px rgba(80, 130, 220, 0.15);
}

.recovery-note {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    margin: 1rem 0 1.35rem;
    padding: 0.85rem 0.95rem;
    border: 1px solid #edf0f5;
    border-radius: 0.85rem;
    background: #f8fafc;
    color: #596273;
    font-size: 0.84rem;
    line-height: 1.4;
}

.recovery-note i {
    color: #c0392b;
    font-size: 1.05rem;
}

.btn-reset {
    background: linear-gradient(135deg, #c0392b 0%, #1a4fa0 100%);
    border: none;
    border-radius: 0.85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    padding: 0.95rem 1rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    box-shadow: 0 8px 24px rgba(160, 20, 20, 0.25);
    transition: opacity 0.2s, transform 0.2s;
}

.btn-reset:hover {
    opacity: 0.92;
    transform: translateY(-1px);
}

.btn-reset i {
    font-size: 1.1rem;
}

.forgot-footer {
    display: flex;
    justify-content: center;
    gap: 0.45rem;
    margin-top: 1.3rem;
    color: #495057;
    font-size: 0.95rem;
}

.forgot-footer span {
    font-weight: 500;
}

.login-link {
    color: #c0392b;
    font-weight: 700;
    text-decoration: none;
}

.login-link:hover {
    color: #1a4fa0;
    text-decoration: underline;
}

@media (max-width: 576px) {
    .forgot-page {
        background-attachment: scroll;
    }

    .brand-inner {
        flex-direction: column;
        text-align: center;
        padding: 12px 16px;
    }

    .brand-subtitle-row {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.35rem;
    }

    .brand-title {
        font-size: 1rem;
        letter-spacing: 1px;
        white-space: normal;
        text-align: center;
    }

    .brand-line {
        width: 24px;
    }

    .brand-subtitle {
        font-size: 0.68rem;
        letter-spacing: 2px;
        white-space: normal;
    }

    .forgot-center {
        padding-top: 2rem;
        padding-bottom: 2rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    .forgot-footer {
        flex-direction: column;
        align-items: center;
    }
}

@media (max-width: 420px) {
    .forgot-center {
        padding: 1.5rem 0.75rem;
    }

    .forgot-card {
        padding: 1.5rem 1rem;
    }

    .recovery-icon {
        width: 56px;
        height: 56px;
    }

    .recovery-icon i {
        font-size: 1.6rem;
    }

    .forgot-title h4 {
        font-size: 1.05rem;
    }
}
</style>
