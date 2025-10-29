<?php

namespace Pace\Model;

use BadMethodCallException;

trait Attachments
{
    /**
     * Attach a file to the model.
     *
     * @param string $name
     * @param string $content
     * @param string|null $field
     * @param int|string|null $keyName
     * @return \Pace\RestModel|\Pace\Model
     */
    public function attachFile($name, $content, $field = null, $keyName = null)
    {
        try {
            $key = $this->client->attachment()->add($this->type, $this->key($keyName), $field, $name, $content);
        } catch (\Exception $e) {
            // Handle server-side validation warnings that don't actually prevent operations
            // These are 500 errors with validation messages about the underlying object
            // but the attachment operation may have still succeeded
            if ($this->isNonBlockingValidationErrorForAttachment($e)) {
                // The attachment was likely created despite the validation error
                // Try to find the attachment by querying all attachments for this object
                // and matching by name (which should be unique for recent uploads)
                try {
                    $allAttachments = $this->client->attachment()->getAll($this->type, $this->key($keyName), $field);
                    
                    if (!empty($allAttachments)) {
                        // Find the most recent attachment with matching name
                        // Sort by creation/modification time if available, or just take the last one
                        $matchingAttachment = null;
                        foreach ($allAttachments as $attachment) {
                            // Check if name matches (or if this is the most recent one)
                            if (isset($attachment['name']) && $attachment['name'] === $name) {
                                $matchingAttachment = $attachment;
                                break;
                            }
                        }
                        
                        // If no exact name match, use the most recent attachment
                        if ($matchingAttachment === null) {
                            $matchingAttachment = end($allAttachments);
                        }
                        
                        if (isset($matchingAttachment['attachment'])) {
                            $key = $matchingAttachment['attachment'];
                            // Successfully found the attachment, continue silently
                            // The validation error was about the object, not the attachment
                            return $this->client->model('FileAttachment')->read($key);
                        }
                    }
                } catch (\Exception $lookupException) {
                    // Couldn't verify, re-throw original error
                    throw $e;
                }
                // If we couldn't find it, re-throw the original error
                throw $e;
            }
            throw $e;
        }

        return $this->client->model('FileAttachment')->read($key);
    }

    /**
     * Check if an exception is a non-blocking validation error for attachment operations.
     *
     * @param \Exception $e
     * @return bool
     */
    protected function isNonBlockingValidationErrorForAttachment(\Exception $e)
    {
        // Check if it's a 500 server error
        if ($e->getCode() !== 500) {
            return false;
        }

        $message = $e->getMessage();

        // Validation warnings about the underlying object that don't prevent attachment creation
        $nonBlockingPatterns = [
            '/Editing basis weight is not permitted/',
            '/is not permitted when.*is true/',
        ];

        foreach ($nonBlockingPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The file attachments relationship.
     *
     * @return \Pace\XPath\Builder
     */
    public function fileAttachments()
    {
        return $this->morphMany('FileAttachment');
    }

    /**
     * Get the file attachment content.
     *
     * @return string
     */
    public function getContent()
    {
        if ($this->type !== 'FileAttachment') {
            throw new BadMethodCallException('Call to method which only exists on FileAttachment');
        }

        return $this->client->attachment()->getByKey($this->attachment)['content'];
    }
}
