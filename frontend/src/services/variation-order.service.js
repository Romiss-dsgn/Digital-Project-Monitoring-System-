import axios from "axios";
import apiBaseUrl from "./api-base";
import authHeader from "./auth-header";

const API_URL = apiBaseUrl();

function multipartHeaders() {
  const headers = { ...authHeader() };
  delete headers["Content-Type"];
  return headers;
}

export default {
  async getVariationOrders(params = {}) {
    const response = await axios.get(`${API_URL}/variation-orders`, {
      headers: authHeader(),
      params: { per_page: 100, ...params },
    });

    return response.data;
  },

  async getVariationOrder(id) {
    const response = await axios.get(`${API_URL}/variation-orders/${id}`, {
      headers: authHeader(),
    });

    return response;
  },

  async getSummary() {
    const response = await axios.get(`${API_URL}/variation-orders/summary`, {
      headers: authHeader(),
    });

    return response.data.data;
  },

  async getOptions() {
    const response = await axios.get(`${API_URL}/variation-orders/options`, {
      headers: authHeader(),
    });

    return response.data;
  },

  async createVariationOrder(payload) {
    const response = await axios.post(
      `${API_URL}/variation-orders`,
      payload,
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async updateVariationOrder(id, payload) {
    const response = await axios.patch(
      `${API_URL}/variation-orders/${id}`,
      payload,
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async submitVariationOrder(id) {
    const response = await axios.patch(
      `${API_URL}/variation-orders/${id}/submit`,
      {},
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async reviewVariationOrder(id, reviewAction, approvalRemarks = "") {
    const response = await axios.patch(
      `${API_URL}/variation-orders/${id}/review`,
      { review_action: reviewAction, approval_remarks: approvalRemarks },
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async archiveVariationOrder(id) {
    await axios.delete(`${API_URL}/variation-orders/${id}`, {
      headers: authHeader(),
    });
  },

  async uploadDocument(variationOrderId, file, documentTitle = "", remarks = "") {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("document_title", documentTitle);
    formData.append("remarks", remarks);

    const response = await axios.post(
      `${API_URL}/variation-orders/${variationOrderId}/documents`,
      formData,
      { headers: multipartHeaders() }
    );

    return response.data.data;
  },

  async downloadDocument(document) {
    const response = await axios.get(
      `${API_URL}/variation-order-documents/${document.id}/download`,
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