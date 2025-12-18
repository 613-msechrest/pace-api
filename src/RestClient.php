<?php

namespace Pace;

use Closure;
use InvalidArgumentException;
use Pace\Rest\Factory as RestFactory;
use Pace\Contracts\Rest\Factory as RestFactoryContract;

class RestClient
{
    /**
     * The primary key field.
     */
    const PRIMARY_KEY = 'primaryKey';

    /**
     * Previously loaded services.
     *
     * @var array
     */
    protected $services = [];

    /**
     * The REST client factory.
     *
     * @var RestFactoryContract
     */
    protected $restFactory;

    /**
     * The Pace services URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Create a new instance.
     *
     * @param RestFactoryContract $restFactory
     * @param string $host
     * @param string $login
     * @param string $password
     * @param string $scheme
     */
    public function __construct(RestFactoryContract $restFactory, $host, $login, $password, $scheme = 'https')
    {
        $restFactory->setOptions([
            'auth' => [$login, $password],
        ]);
        $this->restFactory = $restFactory;

        $this->url = sprintf('%s://%s/rpc/rest/services/', $scheme, $host);
    }

    /**
     * Prepare the instance for serialization.
     *
     * @return string[]
     */
    public function __sleep()
    {
        return ['restFactory', 'url'];
    }

    /**
     * Get the debug information for the instance.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'url' => $this->url,
            'services' => array_keys($this->services),
        ];
    }

    /**
     * Dynamically retrieve the specified model.
     *
     * @param string $name
     * @return RestModel
     */
    public function __get($name)
    {
        return $this->model(Type::modelify($name));
    }

    /**
     * Get an instance of the attachment service.
     *
     * @return \Pace\RestServices\AttachmentService
     */
    public function attachment()
    {
        return $this->service('AttachmentService');
    }

    /**
     * Get an instance of the customization service.
     *
     * @return \Pace\RestServices\CustomizationService
     */
    public function customization()
    {
        return $this->service('CustomizationService');
    }

    /**
     * Get an instance of the geolocation service.
     *
     * @return \Pace\RestServices\GeoLocate
     */
    public function geoLocate()
    {
        return $this->service('GeoLocate');
    }

    /**
     * Get an instance of the system inspector service.
     *
     * @return \Pace\RestServices\SystemInspector
     */
    public function systemInspector()
    {
        return $this->service('SystemInspector');
    }

    /**
     * Get an instance of the mobile authentication service.
     *
     * @return \Pace\RestServices\MobileAuthentication
     */
    public function mobileAuthentication()
    {
        return $this->service('MobileAuthentication');
    }

    /**
     * Get an instance of the mobile general service.
     *
     * @return \Pace\RestServices\MobileGeneral
     */
    public function mobileGeneral()
    {
        return $this->service('MobileGeneral');
    }

    /**
     * Get an instance of the mobile todo items service.
     *
     * @return \Pace\RestServices\MobileTodoItems
     */
    public function mobileTodoItems()
    {
        return $this->service('MobileTodoItems');
    }

    /**
     * Clone an object.
     *
     * @param string $object
     * @param array $attributes
     * @return array
     */
    public function cloneObject($object, $attributes)
    {
        return $this->service('CloneObject')->clone($object, $attributes);
    }

    /**
     * Create an object.
     *
     * @param string $object
     * @param array $attributes
     * @return array
     */
    public function createObject($object, $attributes)
    {
        return $this->service('CreateObject')->create($object, $attributes);
    }

    /**
     * Delete an object.
     *
     * @param string $object
     * @param int|string $key
     * @return array
     */
    public function deleteObject($object, $key)
    {
        return $this->service('DeleteObject')->delete($object, $key);
    }

    /**
     * Find objects matching the specified criteria.
     *
     * @param string $object
     * @param string $filter
     * @param array $options
     * @return array
     */
    public function findObjects($object, $filter, $options = [])
    {
        return $this->service('FindObjects')->find($object, $filter);
    }

    /**
     * Find and sort objects matching the specified criteria.
     *
     * @param string $object
     * @param string $filter
     * @param array $sort
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public function findAndSortObjects($object, $filter, array $sort, $limit = null, $offset = null)
    {
        return $this->service('FindObjects')->findAndSort($object, $filter, $sort, $limit, $offset);
    }

    /**
     * Invoke an action.
     *
     * @param string $action
     * @param array $parameters
     * @return mixed
     */
    public function invokeAction($action, $parameters = [])
    {
        return $this->service('InvokeAction')->invoke($action, $parameters);
    }

    /**
     * Invoke a Pace Connect process.
     *
     * @param string $process
     * @param array $parameters
     * @return mixed
     */
    public function invokePaceConnect($process, $parameters = [])
    {
        return $this->service('InvokePaceConnect')->invoke($process, $parameters);
    }

    /**
     * Invoke a process.
     *
     * @param string $process
     * @param array $parameters
     * @return mixed
     */
    public function invokeProcess($process, $parameters = [])
    {
        return $this->service('InvokeProcess')->invoke($process, $parameters);
    }

    /**
     * Read an object.
     *
     * @param string $object
     * @param int|string $key
     * @return array|null
     */
    public function readObject($object, $key)
    {
        return $this->service('ReadObject')->read($object, $key);
    }

    /**
     * Get a new report builder instance.
     *
     * @return \Pace\RestReport\Builder
     */
    public function report()
    {
        $reportService = $this->service('ReportService');
        $report = $this->model('Report');
        
        return new \Pace\RestReport\Builder($reportService, $report);
    }

    /**
     * Start a new transaction.
     *
     * @return string
     */
    public function startTransaction()
    {
        return $this->service('TransactionService')->start();
    }

    /**
     * Commit the current transaction.
     */
    public function commitTransaction()
    {
        $this->service('TransactionService')->commit();
    }

    /**
     * Rollback the current transaction.
     */
    public function rollbackTransaction()
    {
        $this->service('TransactionService')->rollback();
    }

    /**
     * Update an object.
     *
     * @param string $object
     * @param array $attributes
     * @return array
     */
    public function updateObject($object, $attributes)
    {
        return $this->service('UpdateObject')->update($object, $attributes);
    }

    /**
     * Determine the version of Pace running on the server.
     *
     * @return array
     */
    public function version()
    {
        return $this->service('Version')->get();
    }

    /**
     * Create a new instance of the specified service.
     *
     * @param string $service
     * @return mixed
     */
    protected function makeService($service)
    {
        $class = 'Pace\\RestServices\\' . $service;

        if (!class_exists($class)) {
            throw new InvalidArgumentException('Service [$service] is not implemented');
        }

        $http = $this->restFactory->make($this->url);

        return new $class($http);
    }

    /**
     * Get an instance of the specified service.
     *
     * @param string $service
     * @return mixed
     */
    public function service($service)
    {
        if (!isset($this->services[$service])) {
            $this->services[$service] = $this->makeService($service);
        }

        return $this->services[$service];
    }

    /**
     * Create a new model instance.
     *
     * @param string $type
     * @return \Pace\RestModel
     */
    public function model($type)
    {
        return new \Pace\RestModel($this, $type);
    }
}
