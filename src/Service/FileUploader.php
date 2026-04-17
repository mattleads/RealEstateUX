<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileUploader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/temp')]
        private string $targetDirectory,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $fileName = uniqid('property_', true) . '.' . $file->guessExtension();
        $file->move($this->targetDirectory, $fileName);

        return '/temp/' . $fileName;
    }
}
