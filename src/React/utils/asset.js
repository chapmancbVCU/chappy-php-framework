export default function asset(path) {
    return import.meta.env.VITE_APP_DOMAIN + path;
}