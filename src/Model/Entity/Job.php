<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Job Entity
 *
 * @property int $id
 * @property string $job_number
 * @property string $title
 * @property string|null $instructions
 * @property string|null $status
 * @property int|null $created_by
 * @property int|null $operator_id
 * @property int|null $qc_id
 * @property \Cake\I18n\DateTime|null $scheduled_date
 * @property \Cake\I18n\DateTime|null $created_at
 * @property \Cake\I18n\DateTime|null $updated_at
 * @property int $organization_id
 *
 * @property \App\Model\Entity\User $operator
 * @property \App\Model\Entity\User $qc
 * @property \App\Model\Entity\Organization $organization
 * @property \App\Model\Entity\JobAttachment[] $job_attachments
 * @property \App\Model\Entity\JobLog[] $job_logs
 */
class Job extends Entity
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
        'job_number' => true,
        'title' => true,
        'instructions' => true,
        'status' => true,
        'created_by' => true,
        'operator_id' => true,
        'qc_id' => true,
        'scheduled_date' => true,
        'created_at' => true,
        'updated_at' => true,
        'organization_id' => true,
        'operator' => true,
        'qc' => true,
        'organization' => true,
        'job_attachments' => true,
        'job_logs' => true,
    ];
}
