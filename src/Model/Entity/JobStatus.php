<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * JobStatus Entity
 *
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string|null $description
 * @property string $color
 * @property int $sort_order
 * @property bool $is_active
 * @property bool $is_terminal
 * @property \Cake\I18n\FrozenTime|null $created_at
 *
 * @property \App\Model\Entity\Job[] $jobs
 */
class JobStatus extends Entity
{
    protected array $_accessible = [
        'name' => true,
        'label' => true,
        'description' => true,
        'color' => true,
        'sort_order' => true,
        'is_active' => true,
        'is_terminal' => true,
        'created_at' => true,
        'jobs' => true,
    ];
}
