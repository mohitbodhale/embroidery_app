<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * JobAttachment Entity
 *
 * @property int $id
 * @property int|null $job_id
 * @property int|null $uploaded_by
 * @property string $file_type
 * @property string $file_path
 * @property string $file_name
 * @property int $file_size
 * @property string $mime_type
 * @property string|null $comments
 * @property \Cake\I18n\FrozenTime|null $created_at
 *
 * @property \App\Model\Entity\Job $job
 * @property \App\Model\Entity\User $uploaded_by_user
 */
class JobAttachment extends Entity
{
    protected array $_accessible = [
        'job_id' => true,
        'uploaded_by' => true,
        'file_type' => true,
        'file_path' => true,
        'file_name' => true,
        'file_size' => true,
        'mime_type' => true,
        'comments' => true,
        'created_at' => true,
        'job' => true,
        'uploaded_by_user' => true,
    ];
}
