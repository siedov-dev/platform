const BASE_URL = import.meta.env.VITE_API_URL || "";

async function request(path, options = {}) {
  const response = await fetch(`${BASE_URL}/api${path}`, {
    headers: { "Content-Type": "application/json", ...options.headers },
    ...options,
  });

  if (!response.ok) {
    let message = response.statusText;
    try {
      const body = await response.json();
      message = body?.error?.message || message;
    } catch {
      message = response.statusText;
    }
    throw new Error(message);
  }

  if (response.status === 204) return null;
  return response.json();
}

export const apiClient = {
  get: (path) => request(path),
  post: (path, body) =>
    request(path, {
      method: "POST",
      body: body ? JSON.stringify(body) : undefined,
    }),
};
