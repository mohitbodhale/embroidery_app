<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class JobAttachmentsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'job_id' => 1,
                'file_name' => 'design.emb',
                'file_path' => 'uploads/attachments/1/abc123.emb',
                'file_type' => 'emb',
                'file_size' => 4096,
                'mime_type' => 'application/octet-stream',
                'comments' => 'Initial digitized file.',
                'uploaded_by' => 1,
                'created_at' => '2025-01-01 10:00:00',
            ],
            [
                'id' => 2,
                'job_id' => 1,
                'file_name' => 'reference.png',
                'file_path' => 'uploads/attachments/1/ref456.png',
                'file_type' => 'png',
                'file_size' => 128000,
                'mime_type' => 'image/png',
                'comments' => 'Reference photo for colour matching.',
                'uploaded_by' => 2,
                'created_at' => '2025-01-01 11:00:00',
            ],
        ];
        parent::init();
    }
}
