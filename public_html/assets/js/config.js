const PROXY_URL = window.location.origin + '/asy/proxy';

// CSRF token vindo do backend
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');