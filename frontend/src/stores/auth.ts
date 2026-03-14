import { defineStore } from "pinia";
import router from "@/router";
type AuthErrors = Record<string, string[]>;


export const useAuthStore = defineStore("authStore", {
    state: () => {
        return {
            user: null,
            twoFactorRequired: false,
            errors: {} as AuthErrors,
            errorMessage: "",
        }
    },
    getters: {},
    actions: {
        async getUser() {
            const token = localStorage.getItem('token');
            if (token) {
                try {
                    const res = await fetch('/api/user', {
                        headers: {
                            authorization: `Bearer ${token}`
                        },
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.user = data.user;
                        this.twoFactorRequired = data.two_factor_required ?? false;
                    }
                } catch (e) {
                    // Backend unreachable - treat as not logged in
                    this.user = null;
                    this.twoFactorRequired = false;
                }
            }
        },
        async authenticate(formData: { email: string, password: string }, apiRoute: string) {
            const res = await fetch(`/api/${apiRoute}`, {
                method: "POST",
                body: JSON.stringify({ ...formData }),
            });
            const data = await res.json();
            if (data.errors) {
                this.errors = data.errors as AuthErrors;

                this.errorMessage = Object.values(this.errors)
                .flat()
                .filter((msg) => typeof msg === "string" && msg.trim() !== "")
                .join(", ");
            }
            else if (!res.ok) {
                this.errorMessage = data.message || "An error occurred.";
            }
            else {
                localStorage.setItem('token', data.token)
                this.user = data.user;
                this.twoFactorRequired = true;
                this.errors = {};
                this.errorMessage = "";
                router.push({ name: "verify" });
            }
        },
        async verifyTwoFactor(code: string) {
            const token = localStorage.getItem('token');
            this.errorMessage = "";
            const res = await fetch('/api/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    authorization: `Bearer ${token}`,
                },
                body: JSON.stringify({ code: parseInt(code) }),
            });
            const data = await res.json();
            if (res.ok) {
                this.twoFactorRequired = false;
                this.user = data.user;
                router.push({ name: 'home' });
            } else {
                this.errorMessage = data.message;
            }
        },
        async resendCode() {
            const token = localStorage.getItem('token');
            const res = await fetch('/api/verify/resend', {
                method: 'POST',
                headers: {
                    authorization: `Bearer ${token}`,
                },
            });
            const data = await res.json();
            return data;
        },
        async logout() {
            const token = localStorage.getItem('token');
            await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    authorization: `Bearer ${token}`,
                },
            });
            this.user = null;
            this.twoFactorRequired = false;
            localStorage.removeItem('token');
            router.push({ name: 'login' });
        },
        async forgotPassword(email: string) {
            this.errorMessage = "";
            this.errors = {};

            const res = await fetch('/api/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email }),
            });

            const data = await res.json();

            if (data.errors) {
                this.errors = data.errors as AuthErrors;
                this.errorMessage = Object.values(this.errors)
                    .flat()
                    .filter((msg) => typeof msg === "string" && msg.trim() !== "")
                    .join(", ");

                return { ok: false, message: this.errorMessage };
            }

            if (!res.ok) {
                this.errorMessage = data.message || "An error occurred.";
                return { ok: false, message: this.errorMessage };
            }

            return { ok: true, message: data.message as string };
        },
        async resetPassword(formData: {
            token: string;
            email: string;
            password: string;
            password_confirmation: string;
        }) {
            this.errorMessage = "";
            this.errors = {};

            const res = await fetch('/api/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            const data = await res.json();

            if (data.errors) {
                this.errors = data.errors as AuthErrors;
                this.errorMessage = Object.values(this.errors)
                    .flat()
                    .filter((msg) => typeof msg === "string" && msg.trim() !== "")
                    .join(", ");

                return { ok: false, message: this.errorMessage };
            }

            if (!res.ok) {
                this.errorMessage = data.message || "An error occurred.";
                return { ok: false, message: this.errorMessage };
            }

            return { ok: true, message: data.message as string };
        }
    }
});
