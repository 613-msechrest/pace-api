<?php

namespace Pace\RestServices;

use Pace\RestService;

class MobileTodoItems extends RestService
{
    /**
     * Get todo items for user.
     *
     * @param string $userId
     * @param string|null $txnId
     * @return array
     */
    public function getTodoItems($userId, $txnId = null)
    {
        $params = ['userId' => $userId];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('/mobileTodoItems/getTodoItems', $params);

        return $response;
    }

    /**
     * Create a new todo item.
     *
     * @param string $userId
     * @param array $todoItem
     * @param string|null $txnId
     * @return array
     */
    public function createTodoItem($userId, array $todoItem, $txnId = null)
    {
        $data = [
            'userId' => $userId,
            'todoItem' => $todoItem,
        ];

        $params = [];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post('/mobileTodoItems/createTodoItem', $data, $params);

        return $response;
    }

    /**
     * Update a todo item.
     *
     * @param string $todoItemId
     * @param array $todoItem
     * @param string|null $txnId
     * @return array
     */
    public function updateTodoItem($todoItemId, array $todoItem, $txnId = null)
    {
        $data = ['todoItem' => $todoItem];

        $params = ['todoItemId' => $todoItemId];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->put('/mobileTodoItems/updateTodoItem', $data, $params);

        return $response;
    }

    /**
     * Delete a todo item.
     *
     * @param string $todoItemId
     * @param string|null $txnId
     * @return array
     */
    public function deleteTodoItem($todoItemId, $txnId = null)
    {
        $params = ['todoItemId' => $todoItemId];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->delete('/mobileTodoItems/deleteTodoItem', $params);

        return $response;
    }

    /**
     * Mark todo item as complete.
     *
     * @param string $todoItemId
     * @param string|null $txnId
     * @return array
     */
    public function completeTodoItem($todoItemId, $txnId = null)
    {
        $data = ['completed' => true];

        $params = ['todoItemId' => $todoItemId];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->put('/mobileTodoItems/completeTodoItem', $data, $params);

        return $response;
    }
}
