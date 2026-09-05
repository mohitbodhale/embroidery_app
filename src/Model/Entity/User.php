<?php
declare(strict_types=1);

namespace App\Model\Entity;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property \Cake\I18n\DateTime|null $created_at
 * @property int $organization_id
 *
 * @property \App\Model\Entity\Organization $organization
 * @property \App\Model\Entity\JobLog[] $job_logs
 */
class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'email' => true,
        'password' => true,
        'role' => true,
        'created_at' => true,
        'organization_id' => true,
        'organization' => true,
        'job_logs' => true,
        'must_change_password' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'password',
    ];

    // Password Mutator Function
    protected function _setPassword(string $password): ?string
    {
        if (strlen($password) > 0 && !str_starts_with($password, '$2y$')) {
            return (new DefaultPasswordHasher())->hash($password);
        }
        return $password;
    }
}
