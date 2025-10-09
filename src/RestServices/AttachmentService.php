<?php

namespace Pace\RestServices;

use Finfo;
use Pace\RestService;

class AttachmentService extends RestService
{
    /**
     * Add a new attachment to the vault.
     *
     * @param string $object
     * @param mixed $key
     * @param string|null $field
     * @param string $name
     * @param string $content
     * @param string|null $attachmentCategory
     * @param string|null $note
     * @param string|null $txnId
     * @return string
     */
    public function add($object, $key, $field, $name, $content, $attachmentCategory = null, $note = null, $txnId = null)
    {
        $attachment = [
            'name' => $name,
            'content' => base64_encode($content),
            'mimeType' => $this->guessMimeType($name, $content),
            'fileExtension' => pathinfo($name, PATHINFO_EXTENSION),
        ];

        $params = [
            'type' => $object,
            'pKey' => $key,
            'attribute' => $field,
        ];

        if ($attachmentCategory !== null) {
            $params['attachmentCategory'] = $attachmentCategory;
        }

        if ($note !== null) {
            $params['note'] = $note;
        }

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->post('AttachmentService/addAttachmentFullDetails', $attachment, $params);

        return $response;
    }

    /**
     * Get an attachment from the vault by the specified key.
     *
     * @param string $key
     * @param string|null $txnId
     * @return array
     */
    public function getByKey($key, $txnId = null)
    {
        $params = ['attachmentKey' => $key];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('AttachmentService/getAttachmentFromKey', $params);

        $attachment = $response;
        $attachment['content'] = base64_decode($attachment['content']);

        return $attachment;
    }

    /**
     * Get all attachments for an object.
     *
     * @param string $object
     * @param mixed $key
     * @param string $field
     * @param string|null $txnId
     * @return array
     */
    public function getAll($object, $key, $field, $txnId = null)
    {
        $params = [
            'type' => $object,
            'pKey' => $key,
            'attribute' => $field,
        ];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('AttachmentService/getAllAttachments', $params);

        // Decode base64 content for all attachments
        foreach ($response as &$attachment) {
            if (isset($attachment['content'])) {
                $attachment['content'] = base64_decode($attachment['content']);
            }
        }

        return $response;
    }

    /**
     * Get a single attachment for an object.
     *
     * @param string $object
     * @param mixed $key
     * @param string $field
     * @param string|null $txnId
     * @return array
     */
    public function get($object, $key, $field, $txnId = null)
    {
        $params = [
            'type' => $object,
            'pKey' => $key,
            'attribute' => $field,
        ];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $response = $this->http->get('AttachmentService/getAttachment', $params);

        $attachment = $response;
        if (isset($attachment['content'])) {
            $attachment['content'] = base64_decode($attachment['content']);
        }

        return $attachment;
    }

    /**
     * Remove all attachments for an object.
     *
     * @param string $object
     * @param mixed $key
     * @param string $field
     * @param string|null $txnId
     */
    public function removeAll($object, $key, $field, $txnId = null)
    {
        $params = [
            'type' => $object,
            'pKey' => $key,
            'attribute' => $field,
        ];

        if ($txnId !== null) {
            $params['txnId'] = $txnId;
        }

        $this->http->delete('AttachmentService/removeAllAttachments', $params);
    }

    /**
     * Guess the MIME type for the specified file.
     *
     * @param string $name
     * @param string $content
     * @return string
     */
    protected function guessMimeType($name, $content)
    {
        $finfo = new Finfo(FILEINFO_MIME_TYPE);

        return $finfo->buffer($content) ?: 'application/octet-stream';
    }
}
