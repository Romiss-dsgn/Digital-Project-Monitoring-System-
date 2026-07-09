export default function authHeader() {
  // user_free stores the raw access_token string directly (set by auth.service.js)
  const token = JSON.parse(localStorage.getItem('user_free'));

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
