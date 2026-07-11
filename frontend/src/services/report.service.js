import axios from "axios";
import apiBaseUrl from "./api-base";
import authHeader from "./auth-header";

const BASE = `${apiBaseUrl()}/admin/reports`;

export default {
  getReport(reportType, params = {}) {
    return axios.get(`${BASE}/${reportType}`, { headers: authHeader(), params });
  },
  exportReport(reportType, format, params = {}) {
    return axios.get(`${BASE}/${reportType}/export`, {
      headers: authHeader(), params: { ...params, format }, responseType: "blob",
    });
  },
};
