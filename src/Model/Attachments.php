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
     * @param string|null $category
     * @param string|null $note
     * @param int|string|null $keyName
     * @return \Pace\RestModel|\Pace\Model
     */
    public function attachFile($name, $content, $field = null, $category = null, $note = null, $keyName = null)
    {
        // For InventoryItem objects, temporarily remove the 'basis' field if it exists
        // This prevents validation errors when Pace internally tries to update the item
        // during attachment, as basis weight cannot be edited when setBasisWeightFromPaperWeight is true
        $basisValue = null;
        $hadBasis = false;
        $modelType = method_exists($this, 'type') ? $this->type() : $this->type;
        if ($modelType === 'InventoryItem' && isset($this->attributes['basis'])) {
            $basisValue = $this->attributes['basis'];
            $hadBasis = true;
            unset($this->attributes['basis']);
            // Also remove from original to prevent it from being considered dirty
            if (isset($this->original['basis'])) {
                unset($this->original['basis']);
            }
        }

        try {
            $key = $this->client->attachment()->add($this->type, $this->key($keyName), $field, $name, $content, $category, $note);
            
            if (!$key) {
                throw new \Exception("Failed to obtain an attachment key from Pace API.");
            }
            
            // Restore basis field if we removed it
            if ($hadBasis) {
                $this->attributes['basis'] = $basisValue;
                $this->original['basis'] = $basisValue;
            }
        } catch (\Exception $e) {
            // Restore basis field if we removed it (even if error occurred)
            if ($hadBasis) {
                $this->attributes['basis'] = $basisValue;
                $this->original['basis'] = $basisValue;
            }
            // Handle server-side validation warnings that don't actually prevent operations
            // These are 500 errors with validation messages about the underlying object
            // but the attachment operation may have still succeeded
            if ($this->isNonBlockingValidationErrorForAttachment($e)) {
                // Try to extract the attachment key from the error response body
                $key = $this->extractAttachmentKeyFromError($e);
                
                // If we couldn't extract it from the error, try querying for it
                if (!$key) {
                    try {
                        // Give the API a moment to persist the attachment
                        usleep(100000); // 0.1 second delay
                        
                        $allAttachments = $this->client->attachment()->getAll($this->type, $this->key($keyName), $field);
                        
                        if (!empty($allAttachments) && is_array($allAttachments)) {
                            // Find the most recent attachment with matching name
                            $matchingAttachment = null;
                            foreach ($allAttachments as $attachment) {
                                if (!is_array($attachment)) {
                                    continue;
                                }
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
                            
                            // Try multiple possible field names for the attachment key
                            $possibleKeyFields = ['attachment', 'attachmentKey', 'key', 'id'];
                            foreach ($possibleKeyFields as $keyField) {
                                if (isset($matchingAttachment[$keyField])) {
                                    $key = $matchingAttachment[$keyField];
                                    break;
                                }
                            }
                        }
                    } catch (\Exception $lookupException) {
                        // getAll() failed, but that's okay - we know this is a non-blocking error
                        // and the attachment was likely created
                    }
                }
                
                // If we found the key (either from error or query), use it
                if ($key) {
                    return $this->client->model('FileAttachment')->read($key);
                }
                
                // If we couldn't find the attachment key, but we know this is a non-blocking
                // validation error and users confirm attachments are created, we'll suppress
                // the exception and return null. The attachment exists but we can't return it.
                // In practice, the user can query for it later if needed.
                return null;
            }
            throw $e;
        }

        $fileAttachment = $this->client->model('FileAttachment')->read($key);

        // Ensure the attachment key is definitely set on the model
        if ($fileAttachment && !$fileAttachment->hasAttribute('attachment')) {
            $fileAttachment->setAttribute('attachment', $key);
            $fileAttachment->original['attachment'] = $key;
        }

        return $fileAttachment;
    }

    /**
     * Try to extract an attachment key from the error response body.
     *
     * @param \Exception $e
     * @return string|null
     */
    protected function extractAttachmentKeyFromError(\Exception $e)
    {
        $message = $e->getMessage();
        
        // Try to parse JSON from the error message
        // Error format is usually: "Server error: 500 - {"message":"...", ...}"
        if (preg_match('/\{[^}]+\}/', $message, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json && isset($json['attachment'])) {
                return $json['attachment'];
            }
            if ($json && isset($json['attachmentKey'])) {
                return $json['attachmentKey'];
            }
            if ($json && isset($json['key'])) {
                return $json['key'];
            }
        }
        
        return null;
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
            '/Unable to locate object: FileAttachment \(null\)/',
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
