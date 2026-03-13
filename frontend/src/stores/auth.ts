import { defineStore } from "pinia";

export const useAuthStore = defineStore("authStore", {
    state: () => {
        return {
            user: "jon",
            errors: {
                email: "",
                password: "",
                loginError: ""
            }
        }
    },
    getters: {},
    actions: {
        async authenticate(formData: { email: string, password: string }, apiRoute: string) {
            const res = await fetch(`/api/${apiRoute}`, {
                method: "POST",
                body: JSON.stringify({ ...formData }),
            });
            const data = await res.json();
            if (data.errors) {
                this.errors = data.errors;
            }
        }
    }
});
