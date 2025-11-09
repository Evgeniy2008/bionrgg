<?php

namespace App\Services;

use App\DTO\Profile;
use App\Repositories\ProfileRepository;
use App\Repositories\SocialLinkRepository;
use App\Support\MediaStorage;
use RuntimeException;

class ProfileService
{
    public function __construct(
        private ProfileRepository $profiles,
        private SocialLinkRepository $socialLinks,
        private MediaStorage $storage,
        private ExportService $exporter
    ) {
    }

    public function getOwnProfile(int $userId): Profile
    {
        $profile = $this->profiles->findByUserId($userId);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        return $this->refreshProfile($profile->id);
    }

    public function updateProfile(int $userId, array $data): Profile
    {
        $profile = $this->profiles->findByUserId($userId);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        $this->profiles->update($profile->id, $data);

        return $this->refreshProfile($profile->id);
    }

    public function getPublicProfile(string $slug): Profile
    {
        $profile = $this->profiles->findBySlug($slug);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        return $this->refreshProfile($profile->id);
    }

    /**
     * @param array<string, mixed> $file
     */
    public function updateAvatar(int $userId, array $file): Profile
    {
        $profile = $this->profiles->findByUserId($userId);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        $newPath = $this->storage->storeImage($file, 'avatars');
        if ($profile->avatarPath) {
            $this->storage->delete($profile->avatarPath);
        }

        $this->profiles->updateMedia($profile->id, [
            'avatar_path' => $newPath,
        ]);

        return $this->refreshProfile($profile->id);
    }

    /**
     * @param array<string, mixed> $file
     */
    public function updateBackground(int $userId, array $file): Profile
    {
        $profile = $this->profiles->findByUserId($userId);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        $newPath = $this->storage->storeImage($file, 'backgrounds');
        if ($profile->backgroundPath) {
            $this->storage->delete($profile->backgroundPath);
        }

        $this->profiles->updateMedia($profile->id, [
            'background_path' => $newPath,
        ]);

        return $this->refreshProfile($profile->id);
    }

    public function addSocialLink(int $userId, string $platform, ?string $label, string $url, int $sortOrder): Profile
    {
        $profile = $this->profiles->findByUserId($userId);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        $this->socialLinks->create($profile->id, $platform, $label, $url, $sortOrder);

        return $this->refreshProfile($profile->id);
    }

    public function updateSocialLink(int $userId, int $linkId, array $fields): Profile
    {
        $profile = $this->profiles->findByUserId($userId);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        $link = $this->socialLinks->findById($linkId);
        if (!$link || $link->profileId !== $profile->id) {
            throw new RuntimeException('Link not found.');
        }

        $this->socialLinks->update($linkId, $fields);

        return $this->refreshProfile($profile->id);
    }

    public function deleteSocialLink(int $userId, int $linkId): Profile
    {
        $profile = $this->profiles->findByUserId($userId);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        $link = $this->socialLinks->findById($linkId);
        if (!$link || $link->profileId !== $profile->id) {
            throw new RuntimeException('Link not found.');
        }

        $this->socialLinks->delete($linkId);

        return $this->refreshProfile($profile->id);
    }

    public function generateQrExport(int $userId): Profile
    {
        $profile = $this->profiles->findByUserId($userId);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        $newPath = $this->exporter->generateQr($profile);
        if ($profile->qrSvgPath) {
            $this->storage->delete($profile->qrSvgPath);
        }
        $this->profiles->updateMedia($profile->id, ['qr_svg_path' => $newPath]);

        return $this->refreshProfile($profile->id);
    }

    public function generatePdfExport(int $userId): Profile
    {
        $profile = $this->profiles->findByUserId($userId);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        $newPath = $this->exporter->generatePdf($profile);
        if ($profile->pdfPath) {
            $this->storage->delete($profile->pdfPath);
        }
        $this->profiles->updateMedia($profile->id, ['pdf_path' => $newPath]);

        return $this->refreshProfile($profile->id);
    }

    private function refreshProfile(int $profileId): Profile
    {
        $profile = $this->profiles->findById($profileId);
        if (!$profile) {
            throw new RuntimeException('Profile not found.');
        }

        $links = $this->socialLinks->listByProfile($profileId);
        $profile->socialLinks = array_map(static fn($link) => $link->toArray(), $links);

        return $profile;
    }
}


