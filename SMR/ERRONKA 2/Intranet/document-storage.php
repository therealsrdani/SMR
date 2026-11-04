<?php

function document_storage_directory(): string
{
    $documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    if ($documentRoot === false) {
        throw new RuntimeException('No se pudo determinar el directorio público del sitio.');
    }

    return dirname($documentRoot) . DIRECTORY_SEPARATOR . 'kalvex-private-documents';
}

function document_storage_file(string $relativePath): ?string
{
    if (!preg_match('/\A[a-f0-9]{32}\.(?:pdf|docx|odt)\z/', $relativePath)) {
        return null;
    }

    return document_storage_directory() . DIRECTORY_SEPARATOR . basename($relativePath);
}
