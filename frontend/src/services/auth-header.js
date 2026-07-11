import { getStoredAuthToken } from "./auth-token";

export default function authHeader() {
  const token = getStoredAuthToken();

  if (token) {
    return {
      Authorization: 'Bearer ' + token,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
  } else {
    return {};
  }
}
