<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\OrganizationService;
use App\Support\Validator;
use InvalidArgumentException;
use RuntimeException;

class OrganizationController
{
    public function __construct(
        private OrganizationService $organizations,
        private Validator $validator
    ) {
    }

    public function list(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        if (!$userId) {
            $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            return;
        }

        try {
            $organizations = $this->organizations->listForUser((int)$userId);
            $response->json([
                'success' => true,
                'organizations' => $organizations,
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function create(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        if (!$userId) {
            $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            return;
        }

        $payload = $request->json();
        try {
            $this->validator->validate($payload, [
                'name' => Validator::requiredString(1, 255),
                'description' => Validator::optionalString(0, 2000),
                'contact_email' => Validator::optionalEmail(),
                'contact_phone' => Validator::optionalString(0, 30),
                'address' => Validator::optionalString(0, 255),
            ]);
        } catch (InvalidArgumentException $e) {
            $response->status(422)->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => json_decode($e->getMessage(), true),
            ]);
            return;
        }

        try {
            $organization = $this->organizations->create(
                (int)$userId,
                trim((string)$payload['name']),
                isset($payload['description']) ? trim((string)$payload['description']) : null,
                isset($payload['contact_email']) ? trim((string)$payload['contact_email']) : null,
                isset($payload['contact_phone']) ? trim((string)$payload['contact_phone']) : null,
                isset($payload['address']) ? trim((string)$payload['address']) : null
            );

            $response->status(201)->json([
                'success' => true,
                'organization' => $organization,
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function join(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        if (!$userId) {
            $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            return;
        }

        $payload = $request->json();
        if (empty($payload['invite_code'])) {
            $response->status(422)->json([
                'success' => false,
                'message' => 'Invite code is required.',
            ]);
            return;
        }

        try {
            $organization = $this->organizations->joinByInvite((int)$userId, trim((string)$payload['invite_code']));
            $response->json([
                'success' => true,
                'organization' => $organization,
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function show(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        $organizationId = (int)$request->getAttribute('organization_id', 0);
        if (!$userId || $organizationId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
            return;
        }

        try {
            $details = $this->organizations->getDetails($organizationId, (int)$userId);
            $response->json([
                'success' => true,
                'organization' => $details['organization'],
                'member' => $details['member'],
                'members' => $details['members'],
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        $organizationId = (int)$request->getAttribute('organization_id', 0);
        if (!$userId || $organizationId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
            return;
        }

        $payload = $request->json();
        try {
            $organization = $this->organizations->update($organizationId, (int)$userId, $payload);
            $response->json([
                'success' => true,
                'organization' => $organization,
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function uploadLogo(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        $organizationId = (int)$request->getAttribute('organization_id', 0);
        if (!$userId || $organizationId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
            return;
        }

        $file = $request->file('logo');
        if ($file === null) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Logo file is required.',
            ]);
            return;
        }

        try {
            $organization = $this->organizations->updateLogo($organizationId, (int)$userId, $file);
            $response->json([
                'success' => true,
                'organization' => $organization,
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function refreshInvite(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        $organizationId = (int)$request->getAttribute('organization_id', 0);
        if (!$userId || $organizationId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
            return;
        }

        try {
            $organization = $this->organizations->refreshInvite($organizationId, (int)$userId);
            $response->json([
                'success' => true,
                'organization' => $organization,
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function listMembers(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        $organizationId = (int)$request->getAttribute('organization_id', 0);
        if (!$userId || $organizationId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
            return;
        }

        try {
            $members = $this->organizations->listMembers($organizationId, (int)$userId);
            $response->json([
                'success' => true,
                'members' => $members,
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateMember(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        $organizationId = (int)$request->getAttribute('organization_id', 0);
        $memberId = (int)$request->getAttribute('member_id', 0);
        if (!$userId || $organizationId <= 0 || $memberId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
            return;
        }

        $payload = $request->json();
        if (empty($payload['role']) || !in_array($payload['role'], ['admin', 'member'], true)) {
            $response->status(422)->json([
                'success' => false,
                'message' => 'Invalid role supplied.',
            ]);
            return;
        }

        try {
            $members = $this->organizations->updateMemberRole($organizationId, (int)$userId, $memberId, (string)$payload['role']);
            $response->json([
                'success' => true,
                'members' => $members,
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function removeMember(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        $organizationId = (int)$request->getAttribute('organization_id', 0);
        $memberId = (int)$request->getAttribute('member_id', 0);
        if (!$userId || $organizationId <= 0 || $memberId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid request.',
            ]);
            return;
        }

        try {
            $members = $this->organizations->removeMember($organizationId, (int)$userId, $memberId);
            $response->json([
                'success' => true,
                'members' => $members,
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}


