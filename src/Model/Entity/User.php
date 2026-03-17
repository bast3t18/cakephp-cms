<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

class User extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity()
     */
    protected array $_accessible = [
        'email' => true,
        'password' => true,
        'created' => true,
        'modified' => true,
        'articles' => true,
    ];

    /**
     * Fields that should be hidden when converting to arrays or JSON
     */
    protected array $_hidden = [
        'password',
    ];

    /**
     * Automatically hash passwords when they are set
     */
    protected function _setPassword(string $password): ?string
    {
        if (mb_strlen($password) > 0) {
            return (new DefaultPasswordHasher())->hash($password);
        }

        return null;
    }
}