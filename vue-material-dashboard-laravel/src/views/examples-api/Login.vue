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

                    <Form class="login-form" :validation-schema="schema" @submit="handleLogin" @invalid-submit="badSubmit">
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
                    </Form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import showSwal from "@/mixins/showSwal";
import { mapMutations } from "vuex";
import { Form } from "vee-validate";
import * as Yup from 'yup';
import logo from "@/assets/img/BFP 11.png";

export default {
    name: "Login",
    components: {
        Form,
    },
    data() {
        return {
            logo,
            user: { username: "admin@jsonapi.com", password: "secret" },
            schema: Yup.object().shape({
                username: Yup.string().required("Username is required"),
                password: Yup.string().required("Password is required")
            }),
        };
    },
    computed: {
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
        badSubmit() {
            // no-op for validation failures; vee-validate will handle field state
        },
        async handleLogin() {
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
/* ─── Page Layout ─────────────────────────────────────────── */
.login-page {
    min-height: 100vh;
    background-image: linear-gradient(180deg, rgba(200,17,32,.95) 0%, rgba(32,39,65,.92) 70%),
        url('https://images.unsplash.com/photo-1497294815431-9365093b7331?auto=format&fit=crop&w=1600&q=80');
    background-size: cover;
    background-position: center;
    position: relative;
    color: #1f2633;
    display: flex;
    flex-direction: column;
}

/* ─── Brand Header Bar ────────────────────────────────────── */
.brand-bar {
    width: 100%;
    background: linear-gradient(135deg, #8B0000 0%, #CC1C1C 40%, #AA1515 100%);
    border-bottom: 4px solid #FFD700;
}

.brand-inner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 18px;
    padding: 14px 32px;
}

.brand-logo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid #FFD700;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.35);
}

.logo-image {
    width: 68px;
    height: 68px;
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
    font-size: 1.5rem;
    font-weight: 900;
    color: #FFFFFF;
    letter-spacing: 2px;
    text-transform: uppercase;
    text-shadow: 1px 2px 6px rgba(0,0,0,0.45);
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
    width: 60px;
    background: linear-gradient(to right, transparent, #FFD700);
    border-radius: 2px;
}

.brand-line.right {
    background: linear-gradient(to left, transparent, #FFD700);
}

.brand-subtitle {
    font-size: 0.85rem;
    font-weight: 700;
    color: #FFD700;
    letter-spacing: 3px;
    text-transform: uppercase;
    white-space: nowrap;
    text-shadow: 0 1px 4px rgba(0,0,0,0.3);
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
}

.card-body {
    padding: 2rem;
    background: rgba(255,255,255,0.95);
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

/* ─── Links & Buttons ─────────────────────────────────────── */
.forgot-link,
.access-link {
    color: #d32f2f;
    font-weight: 600;
    text-decoration: none;
}

.forgot-link:hover,
.access-link:hover {
    text-decoration: underline;
}

.btn-login {
    background: #d32f2f;
    border: none;
    padding: 0.95rem 1rem;
    border-radius: 0.85rem;
    box-shadow: 0 20px 25px rgba(211, 47, 47, 0.18);
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