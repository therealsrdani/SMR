<?php

function document_preview_text(string $path, string $mime): string
{
    if (!class_exists(ZipArchive::class) || !class_exists(DOMDocument::class) || !class_exists(DOMXPath::class)) {
        throw new RuntimeException('El servidor necesita las extensiones PHP ZIP y DOM para previsualizar este formato.');
    }

    $memberName = $mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ? 'word/document.xml'
        : 'content.xml';
    $archive = new ZipArchive();
    if ($archive->open($path, ZipArchive::RDONLY) !== true) {
        throw new RuntimeException('No se pudo abrir el documento para previsualizarlo.');
    }
    $memberStats = $archive->statName($memberName);
    if ($memberStats === false || !isset($memberStats['size']) || $memberStats['size'] > 8 * 1024 * 1024) {
        $archive->close();
        throw new RuntimeException('El contenido del documento no se puede previsualizar.');
    }
    $xml = $archive->getFromName($memberName);
    $archive->close();
    if (!is_string($xml) || strlen($xml) > 8 * 1024 * 1024) {
        throw new RuntimeException('El contenido del documento no se puede previsualizar.');
    }

    $previousEntitySetting = libxml_use_internal_errors(true);
    $xmlDocument = new DOMDocument();
    $loaded = $xmlDocument->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT);
    libxml_clear_errors();
    libxml_use_internal_errors($previousEntitySetting);
    if (!$loaded) {
        throw new RuntimeException('El contenido del documento no se puede previsualizar.');
    }

    $paragraphs = (new DOMXPath($xmlDocument))->query('//*[local-name()="p" or local-name()="h"]');
    $textLines = [];
    if ($paragraphs !== false) {
        foreach ($paragraphs as $paragraph) {
            if ($paragraph->textContent !== '') {
                $textLines[] = $paragraph->textContent;
            }
        }
    }

    return implode("\n\n", $textLines);
}
