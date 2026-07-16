<?php

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function requireAuthentication(): void
{
    \App\Middleware\AuthMiddleware::handle();
}

function requireRole(string|array $roles): void
{
    requireAuthentication();

    $roles = (array)$roles;

    $user = currentUser();

    $userRole = $user['role'] ?? '';

    if (!in_array($userRole, $roles, true)) {

        http_response_code(403);

        echo json_encode([
            'success' => false,
            'message' => 'Access denied.'
        ]);

        exit;
    }
}

function requireAdmin(): void
{
    requireRole('admin');
}

function requireAccountant(): void
{
    requireRole([
        'admin',
        'accountant'
    ]);
}

function currentUserId(): ?int
{
    return currentUser()['id'] ?? null;
}

function currentUserEmail(): ?string
{
    return currentUser()['email'] ?? null;
}

function currentUsername(): ?string
{
    return currentUser()['username'] ?? null;
}

function currentUserRole(): ?string
{
    return currentUser()['role'] ?? null;
}

function currentUserFullName(): ?string
{
    return currentUser()['full_name'] ?? null;
}


