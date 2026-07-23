import axios from 'axios';
import authHeader from './auth-header';
import Jsona from 'jsona';
import apiBaseUrl from './api-base';

const API_URL = apiBaseUrl();
const dataFormatter = new Jsona();

export default {
  async getProfile() {
    const response = await axios.get(API_URL + "/me", { headers: authHeader() })
    return dataFormatter.deserialize(response.data);
  },

  async editProfile(profile) {
    profile.type = 'profile'
    const newJson = dataFormatter.serialize({ stuff: profile })
    const response = await axios.patch(API_URL + "/me", newJson, { headers: authHeader() })
    return dataFormatter.deserialize(response.data);
  }
}
