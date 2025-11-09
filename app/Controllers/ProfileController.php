<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\ProfileService;
use App\Support\Validator;
use InvalidArgumentException;
use RuntimeException;

class ProfileController
{
    public function __construct(
        private ProfileService $profiles,
        private Validator $validator
    ) {
    }

    public function me(Request $request, Response $response): void
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
            $profile = $this->profiles->getOwnProfile((int)$userId);

            $response->json([
                'success' => true,
                'profile' => $profile->toArray(true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(404)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, Response $response): void
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
                'first_name' => Validator::optionalString(1, 100),
                'last_name' => Validator::optionalString(0, 100),
                'position_title' => Validator::optionalString(0, 150),
                'bio' => Validator::optionalString(0, 2000),
                'phone' => Validator::optionalString(0, 30),
                'email_public' => Validator::optionalEmail(),
                'address' => Validator::optionalString(0, 255),
                'design_theme' => static fn($value) => $value === null || in_array($value, ['minimal', 'card'], true) ? true : 'Invalid design theme.',
                'language' => static fn($value) => $value === null || in_array($value, ['uk', 'en'], true) ? true : 'Invalid language.',
            ]);
        } catch (InvalidArgumentException $e) {
            $response->status(422)->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => json_decode($e->getMessage(), true),
            ]);
            return;
        }

        $fields = array_intersect_key($payload, array_flip([
            'first_name',
            'last_name',
            'position_title',
            'bio',
            'phone',
            'email_public',
            'address',
            'design_theme',
            'language',
        ]));

        foreach ($fields as $key => $value) {
            if (is_string($value)) {
                $fields[$key] = trim($value);
            }
        }

        try {
            $profile = $this->profiles->updateProfile((int)$userId, $fields);
            $response->json([
                'success' => true,
                'profile' => $profile->toArray(true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(404)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function public(Request $request, Response $response): void
    {
        $slug = $request->getAttribute('slug');
        if (!$slug) {
            $response->status(404)->json([
                'success' => false,
                'message' => 'Profile not found',
            ]);
            return;
        }

        try {
            $profile = $this->profiles->getPublicProfile((string)$slug);
            $response->json([
                'success' => true,
                'profile' => $profile->toArray(false),
            ]);
        } catch (RuntimeException $e) {
            $response->status(404)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function uploadAvatar(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        if (!$userId) {
            $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            return;
        }

        $file = $request->file('avatar');
        if ($file === null) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Avatar file is required.',
            ]);
            return;
        }

        try {
            $profile = $this->profiles->updateAvatar((int)$userId, $file);
            $response->json([
                'success' => true,
                'profile' => $profile->toArray(true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function uploadBackground(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        if (!$userId) {
            $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            return;
        }

        $file = $request->file('background');
        if ($file === null) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Background file is required.',
            ]);
            return;
        }

        try {
            $profile = $this->profiles->updateBackground((int)$userId, $file);
            $response->json([
                'success' => true,
                'profile' => $profile->toArray(true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function listSocialLinks(Request $request, Response $response): void
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
            $profile = $this->profiles->getOwnProfile((int)$userId);
            $response->json([
                'success' => true,
                'links' => $profile->socialLinks,
            ]);
        } catch (RuntimeException $e) {
            $response->status(404)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function createSocialLink(Request $request, Response $response): void
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
                'platform' => Validator::requiredString(1, 50),
                'label' => Validator::optionalString(0, 100),
                'url' => Validator::requiredString(1, 255),
                'sort_order' => static function ($value) {
                    if ($value === null) {
                        return true;
                    }
                    return filter_var($value, FILTER_VALIDATE_INT) !== false ? true : 'Sort order must be an integer.';
                },
            ]);
        } catch (InvalidArgumentException $e) {
            $response->status(422)->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => json_decode($e->getMessage(), true),
            ]);
            return;
        }

        $platform = trim((string)$payload['platform']);
        $label = isset($payload['label']) ? trim((string)$payload['label']) : null;
        $url = trim((string)$payload['url']);
        $sortOrder = isset($payload['sort_order']) ? (int)$payload['sort_order'] : 0;

        try {
            $profile = $this->profiles->addSocialLink((int)$userId, $platform, $label, $url, $sortOrder);
            $response->status(201)->json([
                'success' => true,
                'profile' => $profile->toArray(true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function updateSocialLink(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        if (!$userId) {
            $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            return;
        }

        $linkId = (int)$request->getAttribute('link_id', 0);
        if ($linkId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid link id',
            ]);
            return;
        }

        $payload = $request->json();
        $fields = [];
        if (array_key_exists('platform', $payload)) {
            $fields['platform'] = trim((string)$payload['platform']);
        }
        if (array_key_exists('label', $payload)) {
            $fields['label'] = $payload['label'] !== null ? trim((string)$payload['label']) : null;
        }
        if (array_key_exists('url', $payload)) {
            $fields['url'] = trim((string)$payload['url']);
        }
        if (array_key_exists('sort_order', $payload)) {
            $fields['sort_order'] = (int)$payload['sort_order'];
        }

        try {
            $profile = $this->profiles->updateSocialLink((int)$userId, $linkId, $fields);
            $response->json([
                'success' => true,
                'profile' => $profile->toArray(true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function deleteSocialLink(Request $request, Response $response): void
    {
        $userId = $request->getAttribute('user_id');
        if (!$userId) {
            $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized',
            ]);
            return;
        }

        $linkId = (int)$request->getAttribute('link_id', 0);
        if ($linkId <= 0) {
            $response->status(400)->json([
                'success' => false,
                'message' => 'Invalid link id',
            ]);
            return;
        }

        try {
            $profile = $this->profiles->deleteSocialLink((int)$userId, $linkId);
            $response->json([
                'success' => true,
                'profile' => $profile->toArray(true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function exportQr(Request $request, Response $response): void
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
            $profile = $this->profiles->generateQrExport((int)$userId);
            $response->json([
                'success' => true,
                'profile' => $profile->toArray(true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function exportPdf(Request $request, Response $response): void
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
            $profile = $this->profiles->generatePdfExport((int)$userId);
            $response->json([
                'success' => true,
                'profile' => $profile->toArray(true),
            ]);
        } catch (RuntimeException $e) {
            $response->status(400)->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}


