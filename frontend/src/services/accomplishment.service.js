import axios from "axios";
import apiBaseUrl from "./api-base";
import authHeader from "./auth-header";

const API_URL = apiBaseUrl();

function multipartHeaders() {
  const headers = { ...authHeader() };
  delete headers["Content-Type"];
  return headers;
}

function toFormData(payload) {
  const formData = new FormData();

  // ── NULL fix: only skip values that are truly absent (null/undefined,
  // e.g. no file picked). Empty strings (like an unfilled "remarks" field)
  // are still sent explicitly, instead of being dropped from the request
  // entirely — which previously caused the backend to never receive the
  // key at all and fall back to a NULL column value.
  Object.entries(payload).forEach(([key, value]) => {
    if (value === null || value === undefined) return;
    formData.append(key, value);
  });

  return formData;
}

export default {
  async getAccomplishments(params = {}) {
    const response = await axios.get(`${API_URL}/project-accomplishments`, {
      headers: authHeader(),
      params: { per_page: 10, ...params },
    });

    return response.data;
  },

  async getSummary() {
    const response = await axios.get(`${API_URL}/project-accomplishments/summary`, {
      headers: authHeader(),
    });

    return response.data.data;
  },

  async getOptions(params = {}) {
    const response = await axios.get(`${API_URL}/project-accomplishments/options`, {
      headers: authHeader(),
      params,
    });

    return response.data;
  },

  async createAccomplishment(payload) {
    const response = await axios.post(
      `${API_URL}/project-accomplishments`,
      toFormData(payload),
      { headers: multipartHeaders() }
    );

    return response.data.data;
  },

  async updateAccomplishment(id, payload) {
    const response = await axios.patch(
      `${API_URL}/project-accomplishments/${id}`,
      payload,
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async validateAccomplishment(id) {
    const response = await axios.patch(
      `${API_URL}/project-accomplishments/${id}/validate`,
      {},
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async uploadDocument(id, attachment, remarks = "") {
    const formData = new FormData();
    formData.append("attachment", attachment);
    formData.append("remarks", remarks);

    const response = await axios.post(
      `${API_URL}/project-accomplishments/${id}/documents`,
      formData,
      { headers: multipartHeaders() }
    );

    return response.data.data;
  },

  async archiveAccomplishment(id) {
    await axios.delete(`${API_URL}/project-accomplishments/${id}`, {
      headers: authHeader(),
    });
  },

  async downloadDocument(document) {
    const response = await axios.get(
      `${API_URL}/accomplishment-documents/${document.id}/download`,
      { headers: authHeader(), responseType: "blob" }
    );

    const url = URL.createObjectURL(response.data);
    const link = window.document.createElement("a");
    link.href = url;
    link.download = document.file_name;
    link.click();
    URL.revokeObjectURL(url);
  },
};