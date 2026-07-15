import axios from "axios";
import apiBaseUrl from "./api-base";
import authHeader from "./auth-header";

const BASE = `${apiBaseUrl()}/admin/dashboard`;

export default {
  getSummary(params = {}, config = {}) {
    return axios.get(`${BASE}/summary`, {
      headers: authHeader(),
      params,
      ...config,
    });
  },

  exportSummary(params = {}, config = {}) {
    return axios.get(`${BASE}/export`, {
      headers: authHeader(),
      params,
      responseType: "blob",
      ...config,
    });
  },
};
