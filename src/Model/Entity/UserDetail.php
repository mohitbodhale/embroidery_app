<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class UserDetail extends Entity
{
    protected array $_accessible = [
        'user_id' => true,
        'phone' => true,
        'bio' => true,
        'avatar' => true,
        'website' => true,
        'location' => true,
        'created_at' => true,
        'updated_at' => true,
        'user' => true,
    ];

    protected array $_hidden = [];
}
