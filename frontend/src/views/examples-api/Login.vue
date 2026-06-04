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
                            <label for="username" class="form-label">Username</label>
                            <div class="input-with-icon">
                                <i class="material-icons-round text-secondary">person</i>
                                <input id="username" v-model="user.username" type="text" class="form-control" placeholder="Enter username" />
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-with-icon">
                                <i class="material-icons-round text-secondary">lock</i>
                                <input id="password" v-model="user.password" type="password" class="form-control" placeholder="Enter password" />
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-4">
                            <router-link :to="{ name: 'Password Forgot' }" class="forgot-link">Forgot Password?</router-link>
                        </div>

                        <button type="submit" class="btn btn-primary btn-login w-100">LOGIN</button>

                        <div class="login-footer mt-4 text-center">
                            <span>Contact Support</span>
                            <span class="mx-2 text-muted">or</span>
                            <router-link :to="{ name: 'Signup' }" class="access-link">Request Access</router-link>
                        </div>
                    </form>
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
            user: { username: "admin@jsonapi.com", password: "secret" },
        };
    },
    computed: {
        backgroundImageUrl() {
            // Light red tint so bg.png stays visible throughout the page
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
            if (!this.user.username || !this.user.password) {
                showSwal.methods.showSwal({
                    type: "error",
                    message: "Username and password are required.",
                    width: 500
                });
                return;
            }

            try {
                await this.$store.dispatch('auth/login', {
                    email: this.user.username,
                    password: this.user.password
                });
                this.$router.push({ name: 'Dashboard' });
            } catch (error) {
                showSwal.methods.showSwal({
                    type: "error",
                    message: "Invalid credentials!",
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
    /* Soft red + blue tint — glassy, blends with bg */
    background: linear-gradient(
        135deg,
        rgba(190, 20, 20, 0.45) 0%,
        rgba(160, 15, 15, 0.40) 40%,
        rgba(15, 40, 100, 0.45) 100%
    );
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    /* Triple border: top blue, bottom thick gold, thin white shimmer */
    border-top: 3px solid rgba(80, 130, 220, 0.85);
    border-bottom: 4px solid #FFD700;
    box-shadow:
        0 1px 0 rgba(255, 255, 255, 0.15),   /* white shimmer under gold */
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
    /* Double ring: outer gold, inner blue glow */
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
    /* Bright white with a very subtle gold tint */
    color: #FFFFFF;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    text-shadow:
        0 0 18px rgba(255, 215, 0, 0.45),   /* gold glow */
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
    /* Blue-to-gold gradient for the decorative lines */
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
    /* Bright gold — most highlighted element */
    color: #FFE566;
    letter-spacing: 4px;
    text-transform: uppercase;
    white-space: nowrap;
    text-shadow:
        0 0 12px rgba(255, 215, 0, 0.7),   /* strong gold glow */
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
    /* Blue top accent on the card */
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

/* ─── Links & Buttons ─────────────────────────────────────── */
.forgot-link,
.access-link {
    color: #c0392b;
    font-weight: 600;
    text-decoration: none;
}

.forgot-link:hover,
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
