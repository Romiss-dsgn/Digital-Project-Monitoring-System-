import axios from "axios";
import apiBaseUrl from "./api-base";
import authHeader from "./auth-header";

const BASE = `${apiBaseUrl()}/admin`;

const UserService = {

  // GET /api/v2/admin/users
  getUsers(params = {}) {
    return axios.get(`${BASE}/users`, {
      headers: authHeader(),
      params,
    });
  },

  // GET /api/v2/admin/users/stats
  getStats() {
    return axios.get(`${BASE}/users/stats`, {
      headers: authHeader(),
    });
  },

  // GET /api/v2/admin/users/{id}
  getUser(id) {
    return axios.get(`${BASE}/users/${id}`, {
      headers: authHeader(),
    });
  },

  // POST /api/v2/admin/users
  createUser(data) {
    return axios.post(`${BASE}/users`, data, {
      headers: {
        ...authHeader(),
        "Content-Type": "application/json",
      },
    });
  },

  // PUT /api/v2/admin/users/{id}
  updateUser(id, data) {
    return axios.put(`${BASE}/users/${id}`, data, {
      headers: {
        ...authHeader(),
        "Content-Type": "application/json",
      },
    });
  },

  // PATCH /api/v2/admin/users/{id}/status
  updateStatus(id, isActive) {
    return axios.patch(
      `${BASE}/users/${id}/status`,
      { is_active: isActive },
      {
        headers: {
          ...authHeader(),
          "Content-Type": "application/json",
        },
      }
    );
  },

  // DELETE /api/v2/admin/users/{id}
  deleteUser(id) {
    return axios.delete(`${BASE}/users/${id}`, {
      headers: authHeader(),
    });
  },

  // GET /api/v2/admin/roles
  getRoles() {
    return axios.get(`${BASE}/roles`, {
      headers: authHeader(),
    });
  },

  // NEW: POST /api/v2/admin/users/{id}/accept
  acceptUser(id) {
    return axios.post(`${BASE}/users/${id}/accept`, {}, {
      headers: authHeader(),
    });
  },

  // NEW: DELETE /api/v2/admin/users/{id}/reject
  rejectUser(id) {
    return axios.delete(`${BASE}/users/${id}/reject`, {
      headers: authHeader(),
    });
  },

};

export default UserService;