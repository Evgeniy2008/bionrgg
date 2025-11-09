<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\AdminService;
use RuntimeException;

class AdminController
{
    public function __construct(private AdminService $admin)
    {
    }

    public function listUsers(Request $request, Response $response): void
    {
        $adminId = $request->getAttribute('user_id');
        if (!$adminId) {
            $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            return;
        }

        $query = $request->query('q');
        $limit = (int)$request->query('limit', 50);
        $offset = (int)$request->query('offset', 0);

        try {
            $users = $this->admin->listUsers((int)$adminId, $query ? (string)$query : null, $limit, $offset);
            $response->json([
                'success' => true,
                'users' => $users,
            ]);
        } catch (RuntimeException $e) {
            $response->status(403)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function listOrganizations(Request $request, Response $response): void
    {
        $adminId = $request->getAttribute('user_id');
        if (!$adminId) {
            $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            return;
        }

        $query = $request->query('q');
        $limit = (int)$request->query('limit', 50);
        $offset = (int)$request->query('offset', 0);

        try {
            $organizations = $this->admin->listOrganizations((int)$adminId, $query ? (string)$query : null, $limit, $offset);
            $response->json([
                'success' => true,
                'organizations' => $organizations,
            ]);
        } catch (RuntimeException $e) {
            $response->status(403)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateOrganizationStatus(Request $request, Response $response): void
    {
        $adminId = $request->getAttribute('user_id');
        $organizationId = (int)$request->getAttribute('organization_id', 0);

        if (!$adminId || $organizationId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
            return;
        }

        $payload = $request->json();
        $status = $payload['status'] ?? null;
        if (!is_string($status)) {
            $response->status(422)->json([
                'success' => false,
                'message' => 'Status is required.',
            ]);
            return;
        }

        try {
            $organization = $this->admin->updateOrganizationStatus((int)$adminId, $organizationId, $status);
            $response->json([
                'success' => true,
                'organization' => $organization,
            ]);
        } catch (RuntimeException $e) {
            $response->status(403)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function deleteUser(Request $request, Response $response): void
    {
        $adminId = $request->getAttribute('user_id');
        $targetUserId = (int)$request->getAttribute('target_user_id', 0);

        if (!$adminId || $targetUserId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
            return;
        }

        try {
            $this->admin->deleteUser((int)$adminId, $targetUserId);
            $response->status(204)->send();
        } catch (RuntimeException $e) {
            $response->status(403)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function listLogs(Request $request, Response $response): void
    {
        $adminId = $request->getAttribute('user_id');
        if (!$adminId) {
            $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            return;
        }

        $limit = (int)$request->query('limit', 100);
        $offset = (int)$request->query('offset', 0);

        try {
            $logs = $this->admin->listLogs((int)$adminId, $limit, $offset);
            $response->json([
                'success' => true,
                'logs' => $logs,
            ]);
        } catch (RuntimeException $e) {
            $response->status(403)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}


