import axios from "axios";
import apiBaseUrl from "./api-base";
import authHeader from "./auth-header";

const API_URL = apiBaseUrl();

export default {
  async getContracts() {
    const response = await axios.get(`${API_URL}/contracts`, {
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
};
