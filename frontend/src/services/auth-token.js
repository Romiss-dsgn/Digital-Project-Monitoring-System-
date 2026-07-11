const TOKEN_STORAGE_KEY = "user_free";

function normalizeToken(value) {
  if (typeof value === "string") {
    return value.trim();
  }

  if (value && typeof value === "object") {
    if (typeof value.access_token === "string") {
      return value.access_token.trim();
    }

    if (typeof value.token === "string") {
      return value.token.trim();
    }
  }

  return "";
}

function isUsableToken(token) {
  return !!token && token !== "null" && token !== "undefined";
}

export function getStoredAuthToken() {
  const storedValue = localStorage.getItem(TOKEN_STORAGE_KEY);

  if (!storedValue) {
    return null;
  }

  let parsedValue = storedValue;

  try {
    parsedValue = JSON.parse(storedValue);
  } catch (error) {
    parsedValue = storedValue;
  }

  const token = normalizeToken(parsedValue);

  if (!isUsableToken(token)) {
    clearStoredAuthToken();
    return null;
  }

  return token;
}

export function storeAuthToken(token) {
  const normalizedToken = normalizeToken(token);

  if (!isUsableToken(normalizedToken)) {
    clearStoredAuthToken();
    return null;
  }

  localStorage.setItem(TOKEN_STORAGE_KEY, JSON.stringify(normalizedToken));
  return normalizedToken;
}

export function clearStoredAuthToken() {
  localStorage.removeItem(TOKEN_STORAGE_KEY);
}

export function hasStoredAuthToken() {
  return !!getStoredAuthToken();
}
