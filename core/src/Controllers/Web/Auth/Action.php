<?php

declare(strict_types=1);

namespace MXRVX\Telegram\Bot\Auth\Controllers\Web\Auth;

use MXRVX\ORM\AR\AR;
use MXRVX\ORM\MODX\Entities\Context;
use MXRVX\ORM\MODX\Entities\User;
use MXRVX\Telegram\Bot\Auth\App;
use MXRVX\Telegram\Bot\Auth\Controllers\Controller;
use MXRVX\Telegram\Bot\Auth\Entities\Task;
use MXRVX\Telegram\Bot\Auth\Services\TaskCommandManager;
use MXRVX\Telegram\Bot\Auth\Tools\Caster;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Action extends Controller
{
    protected Context $context;
    protected User $user;
    protected Task $task;

    public function __construct(ContainerInterface $container, protected \modX $modx, protected App $app)
    {
        parent::__construct($container, $modx);

        $modx->getUser();

        /** @psalm-suppress DocblockTypeContradiction */
        $contextId = Caster::string($this->modx->context->get('key') ?? 'web', 50);
        $this->context = $this->findOrMake(Context::class, $contextId, [
            'key' => $contextId,
            'name' => '',
        ]);

        /** @psalm-suppress DocblockTypeContradiction */
        $userId = $this->modx->user?->isAuthenticated($contextId) ? (int) $this->modx->user?->get('id') : 0;
        $this->user = $this->findOrMake(User::class, $userId, [
            'id' => 0,
            'username' => '(anonymous)',
            'Profile' => [
                'id' => 0,
                'internalKey' => 0,
                'fullname' => '(anonymous)',
            ],
        ]);

        $sessionId = Caster::string(\session_id() ?: '');
        $this->task = $this->findOrMake(Task::class, $sessionId, [
            'session_id' => $sessionId,
        ]);

        if (empty($this->task->uuid)) {
            throw new \Exception('Not found task to complete');
        }
    }

    public function post(): ResponseInterface
    {
        if ($url = $this->getActionUrl()) {
            return $this->success(['url' => $url]);
        }

        return $this->failure('Could not get url');
    }

    public function getActionUrl(): ?string
    {
        if (!$this->setTaskCommandAlias()) {
            return null;
        }

        return $this->getTaskActionUrl();
    }

    protected function getCommandAlias(): ?string
    {
        return TaskCommandManager::getAliasByActionClass(static::class);
    }

    protected function setTaskCommandAlias(): bool
    {
        if ($alias = $this->getCommandAlias()) {
            if (!$result = $this->task->reset()->setCommand($alias)->save()) {
                $this->modx->log(\modX::LOG_LEVEL_ERROR, \sprintf("\nCould not set command alias:\n %s", $alias));
            }
            return $result;
        }

        return false;
    }

    protected function getTaskActionUrl(): string
    {
        return $this->app->getTelegramStartUrl($this->task->uuid);
    }

    /**
     * @template T of AR
     * @param class-string<T> $className
     * @param mixed $id
     * @param array<non-empty-string, mixed> $defaults
     * @return T
     */
    protected function findOrMake(string $className, int|string $id, array $defaults): AR
    {
        if ($id) {
            $entity = $className::findByPK($id);
            if ($entity !== null) {
                return $entity;
            }
        }
        /** @var T $entity */
        $entity = $className::make($defaults);
        return $entity;
    }
}
