<?php
/**
 * Storage Helper
 *
 * Handles all filesystem paths for the project.
 * This layer isolates the storage structure from the rest of the application.
 */

/**
 * Base Storage directory.
 */
function getStoragePath(): string
{
    if (!is_dir(STORAGE_PATH)) {
        mkdir(STORAGE_PATH, 0777, true);
    }

    return STORAGE_PATH;
}
/**
 * Companies root directory.
 */
function getCompaniesPath(): string
{
    $path = getStoragePath() . '/Companies';

    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }

    return $path;
}

/**
 * Company root directory.
 */
function getCompanyPath(string $crn): string {
    $path = getCompaniesPath() . '/' . trim($crn);

    if (!is_dir($path)) {
        mkdir($path, 0777, true);
    }

    return $path;
}

/**
 * Returns full path of a file inside company folder.
 */
function getCompanyFile(string $crn, string $file): string {
    return getCompanyPath($crn) . '/' . ltrim($file, '/');
}

/**
 * Create required company folders.
 */
function ensureCompanyDirectories(string $crn): void {
    $folders = [
        '',
        'compliance',
        'invoices',
        'logs',
        'backup'
    ];

    foreach ($folders as $folder) {

        $path = $folder === ''
            ? getCompanyPath($crn)
            : getCompanyPath($crn) . '/' . $folder;

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}

/**
 * Returns Storage/current_company.json
 */
function getCurrentCompanyStorageFile(): string {
    return getStoragePath() . DIRECTORY_SEPARATOR . 'current_company.json';
}
