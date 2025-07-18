<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Repositories;

use Cycle\ActiveRecord\Query\ActiveQuery;
use Cycle\ActiveRecord\Repository\ActiveRepository;
use Cycle\ORM\ORMInterface;
use MXRVX\ORM\MODX\Entities\User as ModxUser;
use MXRVX\ORM\MODX\Entities\UserProfile as ModxUserProfile;
use MXRVX\Telegram\Bot\Auth\Entities\User;

/**
 * @method ModxUserQuery select()
 * @extends ActiveRepository<ModxUser>
 *
 * @psalm-suppress InternalClass
 * @psalm-suppress InternalMethod
 */
class ModxUserRepository extends ActiveRepository implements ModxUserRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(ModxUser::class);
    }

    #[\Override]
    public function initSelect(ORMInterface $orm, string $role): ModxUserQuery
    {
        return new ModxUserQuery();
    }

    public function makeUser(User $user): ModxUser
    {
        $entity = ModxUser::make([
            'username' => $user->getUsername(),
            'remote_key' => \sprintf('tg::%s', $user->getId()),
        ]);

        $entity->Profile = ModxUserProfile::make([
            'fullname' => $user->getFullName() ?? $user->getUsername(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'mobilephone' => $user->getPhone(),
        ]);

        return $entity;
    }

    public function createOrUpdate(User $user): ModxUser
    {
        $entity = $this->getUserByEmail($user->getEmail());
        if (!$entity) {
            $entity = $this->makeUser($user);
        }

        $entity->remote_key = \sprintf('tg::%s', $user->getId());
        $entity->Profile?->fromArray([
            'fullname' => $user->getFullName() ?? $user->getUsername(),
            'phone' => $user->getPhone(),
            'mobilephone' => $user->getPhone(),
        ]);

        return $entity;
    }

    public function getUserById(int $id): ?ModxUser
    {
        return ModxUser::findByPK($id);
    }

    public function getUserByPhone(string $phone): ?ModxUser
    {
        $builder = (new ActiveQuery(ModxUserProfile::class))->getBuilder();
        $sql = ($query = $builder->getQuery()) ? $query->columns(['internalKey'])->sqlStatement() : '';
        if (!$sql) {
            return null;
        }

        $db = $builder->getLoader()->getSource()->getDatabase();

        $cleanPhone = (string) \preg_replace('/[-\s().+]/', '', $phone);
        if (\in_array($cleanPhone[0] ?? '', ['7', '8'])) {
            $cleanPhone = \substr($cleanPhone, 1);
        }

        $expr = <<<SQL
        CASE
        WHEN LEFT(
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(%s, '-', ''), ' ', ''), '(', ''), ')', ''), '.', ''), '+', ''),
            1
        ) IN ('7', '8')
        THEN SUBSTRING(
            REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(%s, '-', ''), ' ', ''), '(', ''), ')', ''), '.', ''), '+', ''),
            2
        )
        ELSE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(%s, '-', ''), ' ', ''), '(', ''), ')', ''), '.', ''), '+', '')
        END
        SQL;

        $id =
            (int) $db->query(\sprintf('%s WHERE %s', $sql, \sprintf(
                '(%s = "%s" OR %s = "%s")',
                \str_replace('%s', 'phone', $expr),
                $cleanPhone,
                \str_replace('%s', 'mobilephone', $expr),
                $cleanPhone,
            )))
                ->fetch(\PDO::FETCH_COLUMN);
        if (!$id) {
            return null;
        }

        return ModxUser::findByPK($id);
    }

    public function getUserByEmail(string $email): ?ModxUser
    {
        $builder = (new ActiveQuery(ModxUserProfile::class))->getBuilder();
        $sql = ($query = $builder->getQuery()) ? $query->columns(['internalKey'])->sqlStatement() : '';
        if (!$sql) {
            return null;
        }

        $db = $builder->getLoader()->getSource()->getDatabase();
        $id =
            (int) $db->query(\sprintf('%s WHERE %s', $sql, \sprintf(
                '(LOWER(%s) = LOWER("%s"))',
                'email',
                $email,
            )))
                ->fetch(\PDO::FETCH_COLUMN);

        if (!$id) {
            return null;
        }

        return ModxUser::findByPK($id);
    }
}
