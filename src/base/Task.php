<?php

namespace Taskforce\base;

use Taskforce\base\actions\ReplyAction;
use Taskforce\base\actions\DeclineAction;
use Taskforce\base\actions\MessageAction;
use Taskforce\base\actions\CancelAction;
use Taskforce\base\actions\DoneAction;
use Taskforce\base\exceptions\RoleException;
use Taskforce\base\exceptions\StatusException;
use Taskforce\base\exceptions\UserException;


require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Class Task
 * @package Taskforce\base
 */
class Task
{
    const STATUS_NEW = 'new';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_WORK = 'work';
    const STATUS_DONE = 'done';
    const STATUS_FAIL = 'fail';

    const ACTION_CANCEL = 'action_cancel';
    const ACTION_REPLY = 'action_reply';
    const ACTION_DONE = 'action_done';
    const ACTION_DECLINE = 'action_decline';
    const ACTION_MESSAGE = 'action_message';

    const ROLE_WORKER = 'worker';
    const ROLE_CLIENT = 'client';

    private $_status;

    private $_workerId;
    private $_clientId;

    private $actions = [
        self::ACTION_CANCEL => 'Отменить',
        self::ACTION_REPLY => 'Откликнуться',
        self::ACTION_DONE => 'Выполнено',
        self::ACTION_DECLINE => 'Отказаться',
        self::ACTION_MESSAGE => 'Написать сообщение'
    ];

    private $statuses = [
        self::STATUS_NEW => 'Новое',
        self::STATUS_CANCELLED => 'Отмененно',
        self::STATUS_WORK => 'В работе',
        self::STATUS_DONE => 'Выполнено',
        self::STATUS_FAIL => 'Провалено'
    ];

    private $availableAction = [
        self::ROLE_WORKER => [
            self::STATUS_NEW => [ReplyAction::class, MessageAction::class],
            self::STATUS_WORK => [DeclineAction::class, MessageAction::class],
            self::STATUS_DONE => [MessageAction::class],
            self::STATUS_CANCELLED => [MessageAction::class],
            self::STATUS_FAIL => [MessageAction::class]
        ],
        self::ROLE_CLIENT => [
            self::STATUS_NEW => [CancelAction::class, MessageAction::class],
            self::STATUS_WORK => [DoneAction::class, MessageAction::class],
            self::STATUS_DONE => [MessageAction::class],
            self::STATUS_CANCELLED => [MessageAction::class],
            self::STATUS_FAIL => [MessageAction::class]
        ]
    ];

    /**
     * Task constructor.
     * @param int|null $workerId
     * @param int $clientId
     * @param string $status
     * @throws StatusException
     * @throws UserException
     */
    public function __construct(?int $workerId, int $clientId, string $status)
    {
        $this->_workerId = $workerId;
        if (!is_null($workerId) && !is_int($workerId)) {
            throw new UserException('Передан неправильный идентификатор исполнителя');
        }

        $this->_clientId = $clientId;
        if (!is_int($clientId)) {
            throw new UserException('Передан неправильный идентификатор заказчика');
        }

        if ($workerId === $clientId) {
            throw new UserException('Идентификаторы пользователя и заказчика совпадают');
        }

        $this->_status =  array_key_exists($status, $this->getStatusesList()) ? $status : self::STATUS_NEW;
        if (!array_key_exists($this->_status, $this->getStatusesList())) {
            throw new StatusException('Неверный статус задания');
        }
    }

    public function getStatusesList(): array
    {
        return $this->statuses;
    }

    public function getActionsList(): array
    {
        return $this->actions;
    }

    /**
     * @param string $status
     * @throws StatusException
     */
    public function setStatus(string $status): void
    {
        $this->_status = $status;
        if (!array_key_exists($status, $this->getStatusesList())) {
            throw new StatusException('Неверно задан статус задания');
        }
    }

    public function getStatus(): string
    {
        return $this->_status;
    }

    /**
     * @param string $action
     * @return string
     * @throws \Exception
     */
    public function getNextStatus(string $action): string
    {
        switch ($action) {
            case self::ACTION_CANCEL:
                return self::STATUS_CANCELLED;
                break;
            case self::ACTION_REPLY:
                return self::STATUS_WORK;
                break;
            case self::ACTION_DONE:
                return self::STATUS_DONE;
                break;
            case self::ACTION_DECLINE:
                return self::STATUS_FAIL;
                break;
            default:
                throw new \Exception ('action <b>' . $action . '</b> doesnt change task status');
        }
    }

    public function getWorkerId() : ?int {
        return $this->_workerId;
    }

    public function getClientId() : int {
        return $this->_clientId;
    }

    /**
     * @param string $role
     * @return array
     * @throws RoleException
     */
    public function getAvailableAction(string $role) : array
    {
        if ($this->availableAction[$role] === null) {
            throw new RoleException('Переданая роль пользователя не существует');
        }

       $arr = [];

       $actions = array_key_exists($this->_status, $this->availableAction[$role]) ? $this->availableAction[$role][$this->_status] : [null];

       foreach ($actions as $action) {
           $arr[$action::getActionName()] = new $action();
       }
       return $arr;
    }

}
