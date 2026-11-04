<?php

function permissions_for_role(string $role): array
{
    $role = strtolower(trim($role));
    $isAdmin = in_array($role, ['admin', 'director ejecutivo'], true);
    $isDirector = $role === 'director';
    $isLegacyManager = in_array($role, ['director', 'director ejecutivo', 'gerente'], true);

    return [
        'create_product' => $isAdmin || $isLegacyManager || $role === 'empleado',
        'update_product' => $isAdmin || $isLegacyManager || $role === 'empleado',
        'delete_product' => $isAdmin || $isLegacyManager,
        'create_schedule' => $isAdmin || $isLegacyManager || $role === 'recursos humanos',
        'update_schedule' => $isAdmin || $isLegacyManager,
        'delete_schedule' => $isAdmin || $isLegacyManager,
        'view_documents' => true,
        'upload_documents' => $role === 'admin',
        'delete_documents' => $role === 'admin',
        'download_documents' => $role !== 'guest',
        'view_all_requests' => $isAdmin || $isDirector || $role === 'recursos humanos',
        'change_request_status' => $isAdmin || $isDirector,
        'reply_to_others' => $isAdmin || $isDirector || $role === 'recursos humanos',
        'create_request' => $role !== 'guest',
        'reply_to_own_requests' => $role !== 'guest',
    ];
}
