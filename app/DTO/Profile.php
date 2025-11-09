<?php

namespace App\DTO;

final class Profile
{
    /**
     * @param array<string, mixed> $socialLinks
     */
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly string $usernameSlug,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $positionTitle,
        public readonly ?string $bio,
        public readonly ?string $phone,
        public readonly ?string $emailPublic,
        public readonly ?string $address,
        public readonly ?string $avatarPath,
        public readonly ?string $backgroundPath,
        public readonly string $designTheme,
        public readonly string $language,
        public readonly ?string $qrSvgPath,
        public readonly ?string $pdfPath,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public array $socialLinks = []
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromArray(array $row): self
    {
        return new self(
            (int)$row['id'],
            (int)$row['user_id'],
            (string)$row['username_slug'],
            (string)$row['first_name'],
            (string)$row['last_name'],
            $row['position_title'] !== null ? (string)$row['position_title'] : null,
            $row['bio'] !== null ? (string)$row['bio'] : null,
            $row['phone'] !== null ? (string)$row['phone'] : null,
            $row['email_public'] !== null ? (string)$row['email_public'] : null,
            $row['address'] !== null ? (string)$row['address'] : null,
            $row['avatar_path'] !== null ? (string)$row['avatar_path'] : null,
            $row['background_path'] !== null ? (string)$row['background_path'] : null,
            (string)$row['design_theme'],
            (string)$row['language'],
            $row['qr_svg_path'] !== null ? (string)$row['qr_svg_path'] : null,
            $row['pdf_path'] !== null ? (string)$row['pdf_path'] : null,
            (string)$row['created_at'],
            (string)$row['updated_at']
        );
    }

    public function toArray(bool $includeSensitive = false): array
    {
        return [
            'id' => $this->id,
            'user_id' => $includeSensitive ? $this->userId : null,
            'slug' => $this->usernameSlug,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'position_title' => $this->positionTitle,
            'bio' => $this->bio,
            'phone' => $includeSensitive ? $this->phone : null,
            'email_public' => $this->emailPublic,
            'address' => $includeSensitive ? $this->address : null,
            'avatar' => $this->avatarPath,
            'background' => $this->backgroundPath,
            'design_theme' => $this->designTheme,
            'language' => $this->language,
            'qr_svg' => $this->qrSvgPath,
            'pdf' => $this->pdfPath,
            'social_links' => $this->socialLinks,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}


