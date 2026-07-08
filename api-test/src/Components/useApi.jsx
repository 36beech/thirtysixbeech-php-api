import { useState, useCallback } from 'react';

const BASE_URL = 'https://localhost:58166'; // adjust to your Lando env

export const useApi = () => {
  const [data, setData] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(false);

  const request = useCallback(async (endpoint, options = {}) => {
    setLoading(true);
    setError(null);

    try {
      const token = sessionStorage.getItem('authToken');

      const response = await fetch(`${BASE_URL}${endpoint}`, {
        ...options,
        headers: {
          'Content-Type': 'application/json',
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
          ...options.headers,
        },
      });

      const contentType = response.headers.get('content-type');
      const isJson = contentType && contentType.includes('application/json');
      const body = isJson ? await response.json() : await response.text();

      if (!response.ok) {
        throw new Error(body?.message || `Request failed with status ${response.status}`);
      }

      setData(body);
      return body;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  const get = useCallback((endpoint) => request(endpoint, { method: 'GET' }), [request]);

  const post = useCallback(
    (endpoint, body) => request(endpoint, { method: 'POST', body: JSON.stringify(body) }),
    [request]
  );

  const put = useCallback(
    (endpoint, body) => request(endpoint, { method: 'PUT', body: JSON.stringify(body) }),
    [request]
  );

  const del = useCallback((endpoint) => request(endpoint, { method: 'DELETE' }), [request]);

  return { data, error, loading, request, get, post, put, del };
};