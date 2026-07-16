/**
 * ============================================================================
 * ALWADEH ZATCA
 * API Helper
 * ----------------------------------------------------------------------------
 * Centralized wrapper for all HTTP requests.
 *
 * Responsibilities:
 *  - Send GET/POST requests.
 *  - Parse JSON responses.
 *  - Handle HTTP errors.
 *  - Handle API business errors.
 *  - Provide a single place for future enhancements such as:
 *      - Authentication
 *      - Global Loader
 *      - Retry Logic
 *      - Request Timeout
 *      - Logging
 *      - CSRF Protection
 * ============================================================================
 */

/**
 * Base API directory.
 *
 * Change this value only if the API location changes.
 */
const API_BASE = "api/";

/**
 * Sends an HTTP request to the API.
 *
 * @async
 * @param {string} endpoint
 * Relative API endpoint.
 *
 * Examples:
 *  companies.php
 *  companies.php?action=list
 *
 * @param {Object} [options={}]
 * Standard Fetch API options.
 *
 * @returns {Promise<Object>}
 * Returns the parsed JSON response.
 *
 * @throws {Error}
 * Throws an Error when:
 *  - Network request fails.
 *  - HTTP response is not OK.
 *  - Response is not valid JSON.
 *  - API returns success = false.
 */
async function apiRequest(endpoint, options = {}) {

    const response = await fetch(API_BASE + endpoint, {
        headers: {
            "Content-Type": "application/json",
            ...(options.headers || {})
        },
        ...options
    });

    let result;

    try {
        result = await response.json();
    }
    catch (e) {
        throw new Error("Invalid response received from the server.");
    }

    if (!response.ok) {

        throw new Error(
            result.message ||
            `HTTP Error (${response.status})`
        );

    }

    if (result.success === false) {
        throw new Error(result.message || "Request failed.");
    }

    return result;
}

/**
 * Sends a GET request.
 *
 * @async
 * @param {string} endpoint
 * Relative API endpoint.
 *
 * @returns {Promise<Object>}
 *
 * @example
 * const result = await apiGet("companies.php?action=list");
 */
async function apiGet(endpoint) {

    return apiRequest(endpoint, {
        method: "GET"
    });

}

/**
 * Sends a POST request.
 *
 * Automatically serializes the supplied data into JSON.
 *
 * @async
 * @param {string} endpoint
 * Relative API endpoint.
 *
 * @param {Object} data
 * Request body.
 *
 * @returns {Promise<Object>}
 *
 * @example
 * const result = await apiPost("companies.php", {
 *     action: "add",
 *     company_name: "ALWADEH"
 * });
 */
async function apiPost(endpoint, data = {}) {

    return apiRequest(endpoint, {
        method: "POST",
        body: JSON.stringify(data)
    });

}

/**
 * Sends a PUT request.
 *
 * Reserved for future use.
 *
 * @async
 * @param {string} endpoint
 * @param {Object} data
 *
 * @returns {Promise<Object>}
 */
async function apiPut(endpoint, data = {}) {

    return apiRequest(endpoint, {
        method: "PUT",
        body: JSON.stringify(data)
    });

}

/**
 * Sends a DELETE request.
 *
 * Reserved for future use.
 *
 * @async
 * @param {string} endpoint
 * @param {Object} data
 *
 * @returns {Promise<Object>}
 */
async function apiDelete(endpoint, data = {}) {

    return apiRequest(endpoint, {
        method: "DELETE",
        body: JSON.stringify(data)
    });

}