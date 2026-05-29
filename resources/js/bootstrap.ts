import axios from 'axios';

if (globalThis.window !== undefined) {
    globalThis.window.axios = axios;
    globalThis.window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}
