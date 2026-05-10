<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function processUpload(UploadedFile $file, string $type): array
    {
        $directory = $this->getDirectory($type);
        $path      = $file->store($directory, 'public');

        $data = [
            'media_url'       => $path,
            'media_size'      => $file->getSize(),
            'media_mime_type' => $file->getMimeType(),
        ];

        if (in_array($type, ['image', 'gif'])) {
            $data['media_thumbnail'] = $this->generateImageThumbnail($path);
        }

        if (in_array($type, ['video', 'audio', 'voice_note'])) {
            $data['media_duration'] = $this->getMediaDuration($path);
        }

        return $data;
    }

    public function processStoryMedia(UploadedFile $file, string $type): array
    {
        $path = $file->store('stories', 'public');

        $data = [
            'media_url'  => $path,
            'media_size' => $file->getSize(),
        ];

        if ($type === 'image') {
            $data['thumbnail_url'] = $this->generateImageThumbnail($path);
        }

        if ($type === 'video') {
            $data['thumbnail_url'] = $this->generateVideoThumbnail($path);
            $data['duration']      = $this->getMediaDuration($path);
        }

        return $data;
    }

    public function uploadAvatar(UploadedFile $file, int $userId): string
    {
        $filename = "avatars/user_{$userId}_" . time() . '.webp';
        $path     = $file->store('avatars', 'public');
        return $path;
    }

    public function uploadCoverPhoto(UploadedFile $file, int $userId): string
    {
        return $file->store('covers', 'public');
    }

    private function generateImageThumbnail(string $path): string
    {
        // Thumbnail path — in production, use Intervention Image
        return $path;
    }

    private function generateVideoThumbnail(string $path): string
    {
        // In production, use FFmpeg to extract first frame
        return $path;
    }

    private function getMediaDuration(string $path): int
    {
        // In production, use FFmpeg/getID3 to get duration
        return 0;
    }

    private function getDirectory(string $type): string
    {
        return match ($type) {
            'image'      => 'chat/images',
            'video'      => 'chat/videos',
            'audio'      => 'chat/audio',
            'voice_note' => 'chat/voice',
            'file'       => 'chat/files',
            'gif'        => 'chat/gifs',
            default      => 'chat/misc',
        };
    }
}
