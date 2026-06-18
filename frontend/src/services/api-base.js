const DEFAULT_API_URL = "http://127.0.0.1:8000/api/v2";

function trimTrailingSlashes(value) {
  return value.replace(/\/+$/, "");
}

export default function apiBaseUrl() {
  const configuredUrl = trimTrailingSlashes(
    process.env.VUE_APP_API_BASE_URL || DEFAULT_API_URL
  );

  if (configuredUrl.endsWith("/api/v2")) {
    return configuredUrl;
  }

  if (configuredUrl.endsWith("/api")) {
    return `${configuredUrl}/v2`;
  }

  return `${configuredUrl}/api/v2`;
}
