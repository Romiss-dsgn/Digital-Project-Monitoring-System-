import axios from "axios";
import apiBaseUrl from "./api-base";
import authHeader from "./auth-header";

const BASE = `${apiBaseUrl()}/admin`;

const ProjectService = {
  getProjects(params = {}) {
    return axios.get(`${BASE}/projects`, {
      headers: authHeader(),
      params,
    });
  },

  createProject(data) {
    return axios.post(`${BASE}/projects`, data, {
      headers: {
        ...authHeader(),
        "Content-Type": "application/json",
      },
    });
  },

  updateProject(id, data) {
    return axios.patch(`${BASE}/projects/${id}`, data, {
      headers: {
        ...authHeader(),
        "Content-Type": "application/json",
      },
    });
  },

  deleteProject(id) {
    return axios.delete(`${BASE}/projects/${id}`, {
      headers: authHeader(),
    });
  },
};

export default ProjectService;