import axios from "axios";
import apiBaseUrl from "./api-base";
import authHeader from "./auth-header";

const BASE = `${apiBaseUrl()}/admin`;

const ReportService = {
  getProjectStatus(params = {}) {
    return axios.get(`${BASE}/reports/project-status`, {
      headers: authHeader(),
      params,
    });
  },
};

export default ReportService;
