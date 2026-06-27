import axios from "axios";
import apiBaseUrl from "./api-base";
import authHeader from "./auth-header";

const BASE = `${apiBaseUrl()}/admin`;

function jsonHeaders() {
  const auth = authHeader();
  return {
    Authorization: auth.Authorization,
    "Accept": "application/json",
    "Content-Type": "application/json",
  };
}

const AuditService = {
  getLogs(params = {}) {
    return axios.get(`${BASE}/audit-logs`, {
      headers: jsonHeaders(),
      params,
    });
  },
  getStats() {
    return axios.get(`${BASE}/audit-logs/stats`, {
      headers: jsonHeaders(),
    });
  },
  getModules() {
    return axios.get(`${BASE}/audit-logs/modules`, {
      headers: jsonHeaders(),
    });
  },
  getRoles() {
    return axios.get(`${BASE}/audit-logs/roles`, {
      headers: jsonHeaders(),
    });
  },
  exportLogs(payload) {
    const isCsv = ["csv", "excel", "pdf"].includes(payload.format);
    return axios.post(`${BASE}/audit-logs/export`, payload, {
      headers: jsonHeaders(),
      responseType: isCsv ? "blob" : "json",
    });
  },
};

export default AuditService;
