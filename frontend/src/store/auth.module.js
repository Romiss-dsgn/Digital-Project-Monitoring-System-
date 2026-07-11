import AuthService from '../services/auth.service';
import { clearStoredAuthToken, hasStoredAuthToken } from '../services/auth-token';

const initialState = hasStoredAuthToken() ? { loggedIn: true } : { loggedIn: false };
export const auth = {
  namespaced: true,
  state: initialState,
  actions: {
    async login({ commit }, user) {
      try {
        await AuthService.login(user);
        commit('isLoggedIn', true);
      } catch (error) {
        commit('isLoggedIn', false);
        throw(error)
      }
    },
    async logout({ commit, dispatch }) {
      try {
        await AuthService.logout();
      } catch(error) {
        void error;
      } finally {
        clearStoredAuthToken();
        commit('isLoggedIn', false);
        await dispatch('profile/clearProfile', null, { root: true });
        const { default: router } = await import('@/router/index.js');
        router.push('/login').catch(() => {});
      }
    },
    async clearLocalSession({ commit, dispatch }) {
      clearStoredAuthToken();
      commit('isLoggedIn', false);
      await dispatch('profile/clearProfile', null, { root: true });
    },
    async register({ commit }, user) {
      try {
        const response = await AuthService.register(user);
        commit('isLoggedIn', false);
        return response;
      } catch (error) {
        commit('isLoggedIn', false);
        throw(error)
      }
    },
    // eslint-disable-next-line no-unused-vars
    async passwordForgot({commit}, userEmail){
      await AuthService.passwordForgot(userEmail);
    },
    // eslint-disable-next-line no-unused-vars
    async passwordReset({commit}, passwordDTO){
      await AuthService.passwordReset(passwordDTO);
    },
  },
  mutations: {
    isLoggedIn(state, loggedIn) {
      state.loggedIn = loggedIn
    }
  },
  getters: {
    isLoggedIn(state){
      return state.loggedIn;
    }
  }
};
