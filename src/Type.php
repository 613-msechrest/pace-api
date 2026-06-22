<?php

namespace Pace;

use Doctrine\Inflector\InflectorFactory;

class Type
{
    /**
     * Object types with adjacent uppercase letters.
     *
     * @var array
     */
    protected static $irregularNames = [
        'apSetup' => 'APSetup',
        'arSetup' => 'ARSetup',
        'crmSetup' => 'CRMSetup',
        'crmStatus' => 'CRMStatus',
        'crmUser' => 'CRMUser',
        'csr' => 'CSR',
        'dsfMediaSize' => 'DSFMediaSize',
        'dsfOrderStatus' => 'DSFOrderStatus',
        'faSetup' => 'FASetup',
        'glAccount' => 'GLAccount',
        'glAccountBalance' => 'GLAccountBalance',
        'glAccountBalanceSummary' => 'GLAccountBalanceSummary',
        'glAccountBudget' => 'GLAccountBudget',
        'glAccountingPeriod' => 'GLAccountingPeriod',
        'glBatch' => 'GLBatch',
        'glDepartment' => 'GLDepartment',
        'glDepartmentLocation' => 'GLDepartmentLocation',
        'glJournalEntry' => 'GLJournalEntry',
        'glJournalEntryAudit' => 'GLJournalEntryAudit',
        'glLocation' => 'GLLocation',
        'glRegisterNumber' => 'GLRegisterNumber',
        'glSchedule' => 'GLSchedule',
        'glScheduleLine' => 'GLScheduleLine',
        'glSetup' => 'GLSetup',
        'glSplit' => 'GLSplit',
        'glSummaryName' => 'GLSummaryName',
        'jmfReceivedMessage' => 'JMFReceivedMessage',
        'jmfReceivedMessagePartition' => 'JMFReceivedMessagePartition',
        'jmfReceivedMessageTransaction' => 'JMFReceivedMessageTransaction',
        'jmfReceivedMessageTransactionPartition' => 'JMFReceivedMessageTransactionPartition',
        'poSetup' => 'POSetup',
        'poStatus' => 'POStatus',
        'rssChannel' => 'RSSChannel',
        'uom' => 'UOM',
        'uomDimension' => 'UOMDimension',
        'uomRange' => 'UOMRange',
        'uomSetup' => 'UOMSetup',
        'uomType' => 'UOMType',
        'wipCategory' => 'WIPCategory',
    ];

    /**
     * Object types with irregular primary keys.
     *
     * @var array
     */
    protected static $irregularKeys = [
        'FileAttachment' => 'attachment',
    ];

    /**
     * The Doctrine Inflector instance.
     *
     * @var \Doctrine\Inflector\Inflector|null
     */
    protected static $inflector;

    /**
     * Convert a name to camel case.
     *
     * @param string $name
     * @return string
     */
    public static function camelize($name)
    {
        return array_search($name, static::$irregularNames) ?: lcfirst($name);
    }

    /**
     * Convert a name to model case.
     *
     * @param string $name
     * @return string
     */
    public static function modelify($name)
    {
        return array_search($name, array_flip(static::$irregularNames)) ?: ucfirst($name);
    }

    /**
     * Get the singular form of a name.
     *
     * @param string $name
     * @return string
     */
    public static function singularize($name)
    {
        if (is_null(static::$inflector)) {
            static::$inflector = InflectorFactory::create()->build();
        }

        return static::$inflector->singularize($name);
    }

    /**
     * Get the primary key for the specified type.
     *
     * @param string $type
     * @return string|null
     */
    public static function keyName($type)
    {
        if (array_key_exists($type, static::$irregularKeys)) {
            return static::$irregularKeys[$type];
        }

        return null;
    }

    /**
     * Resolve the primary key value from object attributes.
     *
     * @param string $type
     * @param array $attributes
     * @return mixed
     */
    public static function resolveKeyValue($type, array $attributes)
    {
        $attributeKey = static::attributeKeyName($type, $attributes);

        if (!empty($attributes[$attributeKey])) {
            return $attributes[$attributeKey];
        }

        foreach ([RestClient::PRIMARY_KEY, 'id', static::keyName($type) ?: static::camelize($type)] as $fallback) {
            if ($fallback !== $attributeKey && !empty($attributes[$fallback])) {
                return $attributes[$fallback];
            }
        }

        return null;
    }

    /**
     * Get the attribute name Pace expects on update/create payloads.
     *
     * @param string $type
     * @param array $attributes
     * @return string
     */
    public static function attributeKeyName($type, array $attributes = [])
    {
        if ($keyName = static::keyName($type)) {
            return $keyName;
        }

        if (array_key_exists('id', $attributes)) {
            return 'id';
        }

        if (!empty($attributes[RestClient::PRIMARY_KEY])) {
            return RestClient::PRIMARY_KEY;
        }

        if (array_key_exists(RestClient::PRIMARY_KEY, $attributes)) {
            return RestClient::PRIMARY_KEY;
        }

        return static::camelize($type);
    }
}
