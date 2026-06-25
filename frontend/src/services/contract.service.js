import axios from "axios";
import apiBaseUrl from "./api-base";
import authHeader from "./auth-header";

const API_URL = apiBaseUrl();

function multipartHeaders() {
  const headers = { ...authHeader() };

  // Axios must generate the multipart boundary instead of using JSON:API.
  delete headers["Content-Type"];
  return headers;
}

export default {
  async getContracts(params = {}) {
    const response = await axios.get(`${API_URL}/contracts`, {
      headers: authHeader(),
      params: { per_page: 100, ...params },
    });

    return response.data;
  },

  async getSummary() {
    const response = await axios.get(`${API_URL}/contract-management/summary`, {
      headers: authHeader(),
    });

    return response.data.data;
  },

  async getOptions() {
    const response = await axios.get(`${API_URL}/contract-management/options`, {
      headers: authHeader(),
    });

    return response.data;
  },

  async getContract(id) {
    const response = await axios.get(`${API_URL}/contracts/${id}`, {
      headers: authHeader(),
    });

    return response.data.data;
  },

  async createContract(payload) {
    const response = await axios.post(`${API_URL}/contracts`, payload, {
      headers: authHeader(),
    });

    return response.data.data;
  },

  async updateContract(id, payload) {
    const response = await axios.patch(`${API_URL}/contracts/${id}`, payload, {
      headers: authHeader(),
    });

    return response.data.data;
  },

  async archiveContract(id) {
    await axios.delete(`${API_URL}/contracts/${id}`, {
      headers: authHeader(),
    });
  },

  async uploadDocuments(contractId, files, fields = {}) {
    const formData = new FormData();
    files.forEach((file) => formData.append("files[]", file));
    formData.append("document_category", fields.document_category || "Contract Document");
    formData.append("remarks", fields.remarks || "");

    const response = await axios.post(
      `${API_URL}/contracts/${contractId}/documents`,
      formData,
      { headers: multipartHeaders() }
    );

    return response.data.data;
  },

  async updateDocumentStatus(documentId, status, remarks = "") {
    const response = await axios.patch(
      `${API_URL}/contract-documents/${documentId}/status`,
      { status, remarks },
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async archiveDocument(documentId) {
    await axios.delete(`${API_URL}/contract-documents/${documentId}`, {
      headers: authHeader(),
    });
  },

  async downloadDocument(document) {
    const response = await axios.get(
      `${API_URL}/contract-documents/${document.id}/download`,
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
