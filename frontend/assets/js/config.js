// Default configuration values
const DEFAULT_CONFIG = {
    apiBaseUrl: "http://192.168.253.5/netsentinel/api",
    appName: "netsentinel",
    updateMode: "page_refresh",
    updateInterval: 300,
    timezone: "Europe/Istanbul",
    language: "tr"
};

// Config değerlerini doğrudan kullan
export const API_BASE_URL = () => DEFAULT_CONFIG.apiBaseUrl;
export const APP_NAME = () => DEFAULT_CONFIG.appName;
export const REAL_TIME_INTERVAL = () => DEFAULT_CONFIG.updateInterval * 1000;
export const PAGE_REFRESH_INTERVAL = () => DEFAULT_CONFIG.updateInterval * 1000;
export const ENABLE_REAL_TIME_UPDATES = () => DEFAULT_CONFIG.updateMode === 'real_time';
export const TIMEZONE = () => DEFAULT_CONFIG.timezone;
export const LANGUAGE = () => DEFAULT_CONFIG.language;