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
  async getCashflowPeriods(params = {}) {
    const response = await axios.get(`${API_URL}/cashflow-periods`, {
      headers: authHeader(),
      params: { per_page: 100, ...params },
    });

    return response.data;
  },

  async getSummary() {
    const response = await axios.get(`${API_URL}/cashflow-periods/summary`, {
      headers: authHeader(),
    });

    return response.data.data;
  },

  async getOptions() {
    const response = await axios.get(`${API_URL}/cashflow-periods/options`, {
      headers: authHeader(),
    });

    return response.data;
  },

  async createCashflowPeriod(payload) {
    const response = await axios.post(
      `${API_URL}/cashflow-periods`,
      payload,
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async updateCashflowPeriod(id, payload) {
    const response = await axios.patch(
      `${API_URL}/cashflow-periods/${id}`,
      payload,
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async deleteCashflowPeriod(id) {
    await axios.delete(`${API_URL}/cashflow-periods/${id}`, {
      headers: authHeader(),
    });
  },

  async getInvoices(params = {}) {
    const response = await axios.get(`${API_URL}/invoices`, {
      headers: authHeader(),
      params: { per_page: 100, ...params },
    });

    return response.data;
  },

  async getInvoice(id) {
    const response = await axios.get(`${API_URL}/invoices/${id}`, {
      headers: authHeader(),
    });

    return response.data.data;
  },

  async createInvoice(payload) {
    const response = await axios.post(
      `${API_URL}/invoices`,
      payload,
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async updateInvoice(id, payload) {
    const response = await axios.patch(
      `${API_URL}/invoices/${id}`,
      payload,
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async verifyInvoice(id) {
    const response = await axios.patch(
      `${API_URL}/invoices/${id}/verify`,
      {},
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async approveInvoice(id) {
    const response = await axios.patch(
      `${API_URL}/invoices/${id}/approve`,
      {},
      { headers: authHeader() }
    );

    return response.data.data;
  },

  async deleteInvoice(id) {
    await axios.delete(`${API_URL}/invoices/${id}`, {
      headers: authHeader(),
    });
  },

  async uploadDocument(invoiceId, file, remarks = "") {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("remarks", remarks);

    const response = await axios.post(
      `${API_URL}/invoices/${invoiceId}/documents`,
      formData,
      { headers: multipartHeaders() }
    );

    return response.data.data;
  },

  async downloadDocument(document) {
    const response = await axios.get(
      `${API_URL}/invoice-documents/${document.id}/download`,
      { headers: authHeader(), responseType: "blob" }
    );

    const url = URL.createObjectURL(response.data);
    const link = window.document.createElement("a");
    link.href = url;
    link.download = document.file_name;
    link.click();
    URL.revokeObjectURL(url);
  },

  async disburseFunds(payload) {
    const response = await axios.post(
      `${API_URL}/payments`,
      payload,
      { headers: authHeader() }
    );

    return response.data.data;
  },
};