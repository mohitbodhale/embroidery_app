<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * JobLog Entity
 *
 * @property int $id
 * @property int $job_id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $comments
 * @property \Cake\I18n\FrozenTime|null $created_at
 *
 * @property \App\Model\Entity\Job $job
 * @property \App\Model\Entity\User $user
 */
class JobLog extends Entity
{
    protected array $_accessible = [
        'job_id' => true,
        'user_id' => true,
        'action' => true,
        'comments' => true,
        'created_at' => true,
        'job' => true,
        'user' => true,
    ];
}
