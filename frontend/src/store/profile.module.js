import ProfileService from "@/services/profile.service"
const initialState = { userProfile: null };
export const profile = {
    namespaced: true,
    state: initialState,
    actions: {
        async getProfile({ commit, dispatch }) {
            try {
                const userProfile = await ProfileService.getProfile();
                commit('success', userProfile);
                return userProfile;
            } catch (error) {
                if (error.response?.status === 401) {
                    commit("clear");
                    await dispatch("auth/clearLocalSession", null, { root: true });

                    const { default: router } = await import("@/router/index.js");
                    const currentRoute = router.currentRoute.value;

                    if (currentRoute.name !== "Login") {
                        router.replace({
                            name: "Login",
                            query: { redirect: currentRoute.fullPath },
                        }).catch(() => {});
                    }
                }

                throw error;
            }
        },
        async editProfile({ commit }, modifiedProfile) {
            const userProfile = await ProfileService.editProfile(modifiedProfile);
            commit('success', userProfile);
        },
        clearProfile({ commit }) {
            commit("clear");
        },
    },
    mutations: {
        success(state, userProfile) {
            state.userProfile = userProfile;
        },
        clear(state) {
            state.userProfile = null;
        }
    },
    getters: {
        getUserProfile(state){
            return state.userProfile
        }
    }
}
