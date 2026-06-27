import axios from 'axios';
import authHeader from './auth-header';
import apiBaseUrl from './api-base';

const API_URL = apiBaseUrl();

export default {
  /**
   * Fetch all projects for the upload form dropdown.
   */
  async getProjects() {
    return axios.get(`${API_URL}/admin/projects`, {
      headers: authHeader()
    });
  },

  /**
   * Fetch all engineering plans.
   */
  async getAll(params = {}) {
    return axios.get(`${API_URL}/admin/engineering-plans`, {
      headers: authHeader(),
      params
    });
  },

  /**
   * Fetch one engineering plan record.
   */
  async getById(id) {
    return axios.get(`${API_URL}/admin/engineering-plans/${id}`, {
      headers: authHeader()
    });
  },

  /**
   * Upload a new engineering plan.
   * Expects a FormData object containing the file + metadata fields.
   */
  async create(formData) {
    return axios.post(`${API_URL}/admin/engineering-plans`, formData, {
      headers: {
        ...authHeader(),
        'Content-Type': 'multipart/form-data'
      }
    });
  },

  /**
   * Update an existing engineering plan.
   */
  async update(id, formData) {
    // Laravel does not support PUT with multipart — use POST + _method spoofing
    formData.append('_method', 'PUT');
    return axios.post(`${API_URL}/admin/engineering-plans/${id}`, formData, {
      headers: {
        ...authHeader(),
        'Content-Type': 'multipart/form-data'
      }
    });
  },

  /**
   * Update only the review status/remarks. The file remains unchanged.
   */
  async updateStatus(id, payload) {
    return axios.patch(`${API_URL}/admin/engineering-plans/${id}/status`, payload, {
      headers: {
        ...authHeader(),
        'Content-Type': 'application/json'
      }
    });
  },

  /**
   * Download the stored engineering plan file.
   */
  async download(id) {
    return axios.get(`${API_URL}/admin/engineering-plans/${id}/download`, {
      headers: authHeader(),
      responseType: 'blob'
    });
  },

  /**
   * Delete an engineering plan by ID.
   */
  async remove(id) {
    return axios.delete(`${API_URL}/admin/engineering-plans/${id}`, {
      headers: authHeader()
    });
  }
};
