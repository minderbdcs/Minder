<?php

namespace MinderNG\Collection;

use Countable;
use IteratorAggregate;
use MinderNG\Collection\Event\Sort;
use MinderNG\Events;

/**
 * Class Collection
 * @package MinderNG\Collection
 *
 * @method \Iterator pluck($attribute)
 * @method \Iterator where(array $properties, $strict = true)
 * @method \Iterator filter($callback)
 * @method ModelAggregateInterface|null findWhere(array $properties, $strict = true)
 * @method \Iterator map($callback)
 */
class Collection implements IteratorAggregate, Countable, Events\PublisherAggregateInterface, Events\SubscriberAggregateInterface {
    /**
     * @var ModelAggregateInterface[]
     */
    protected $_aggregates;
    protected $_idAttribute;
    protected $_byId = array();

    /**
     * @var Events\PublisherInterface
     */
    private $_publisher;

    /**
     * @var Events\SubscriberInterface
     */
    private $_subscriber;

    /**
     * @var ModelAggregateFactoryInterface
     */
    private $_aggregateFactory;

    public function __call($name, $arguments)
    {
        array_unshift($arguments, $this);
        return call_user_func_array(array($this->_getHelper(), $name), $arguments);
    }

    private $_helper;

    public function init(array $models = null, AddOptions $options = null, ModelAggregateFactoryInterface $factory = null) {
        $options = is_null($options) ? new AddOptions() : $options;
        $this->_setAggregateFactory($factory);
        $this->_idAttribute = $this->_getAggregateFactory()->getIdAttribute();

        $this->_reset();
        $this->_initialize($models, $options);

        if (!empty($models)) {
            $this->reset($models, $options);
        }
    }

    public function reset(array $models = null, AddOptions $addOptions = null) {
        $addOptions = is_null($addOptions) ? new AddOptions() : $addOptions;
        $models = is_null($models) ? array() : $models;

        foreach ($this->_aggregates as $model) { $this->_removeReference($model); }

        $previousModels = $this->_aggregates;
        $this->_reset();

        $models = $this->add($models, $addOptions);

        if (!$addOptions->silent()) {$this->getPublisher()->trigger(new Event\CollectionReset($this, $previousModels));}

        return $models;
    }

    /**
     * @param array|ModelAggregateInterface $modelData
     * @param AddOptions $options
     * @return Model
     */
    public function addOne(array $modelData, AddOptions $options = null) {
        $models = $this->add(array($modelData), $options);
        return array_shift($models);
    }

    /**
     * @param array[]|ModelAggregateInterface[] $models
     * @param AddOptions $options
     * @return Model[]
     */
    public function add(array $models, AddOptions $options = null) {
        $options = is_null($options) ? new AddOptions() : $options;
        return $this->set($models, $options);
    }

    /**
     * @param array[]|ModelAggregateInterface[] $models
     * @param SetOptionsInterface $options
     * @return ModelAggregateInterface[]
     */
    public function set(array $models, SetOptionsInterface $options = null) {
        $options = is_null($options) ? new SetOptions() : $options;

        if ($options->parse()) {$models = $this->_parse($models);}

        list($addedModels, $toKeep) = $this->_addOrUpdateModels($models, $options);

        if ($options->remove()) {
            $this->_removeRedundantModels($toKeep);
        }

        $comparator = $this->_getComparator();

        if (!is_null($comparator)) {
            $this->sort(true);
        }

        if (!$options->silent()) {
            $this->_fireAddEvents($addedModels);
            $this->getPublisher()->trigger(new Sort($this));
        }

        return $models;
    }

    protected function _removeReference(ModelAggregateInterface $aggregate) {
        if ($aggregate->getModel()->getCollection() === $this) {$aggregate->getModel()->setCollection(null);}
        $this->getSubscriber()->stopSubscriptionTo($aggregate->getModel());
    }

    /**
     * By default do nothing
     *
     * @param $models
     * @param AddOptions $addOptions
     * @internal param Options $options
     */
    protected function _initialize($models, AddOptions $addOptions) {}


    /**
     * @return ModelAggregateInterface[]|\ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_aggregates);
    }

    private function _reset() {
        $this->_aggregates = array();
        $this->_byId = array();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_aggregates);
    }

    /**
     * @param $models
     * @return mixed
     */
    protected function _parse($models)
    {
        return $models;
    }

    /**
     * @param string|ModelAggregateInterface|null $idOrAggregate
     * @return ModelAggregateInterface|null
     */
    public function get($idOrAggregate) {
        if (is_null($idOrAggregate)) {
            return null;
        } elseif (is_string($idOrAggregate)) {
            return isset($this->_byId[$idOrAggregate]) ? $this->_byId[$idOrAggregate] : null;
        } elseif (is_array($idOrAggregate)) {
            return $this->get($this->newModelInstance($idOrAggregate));
        } elseif ($idOrAggregate instanceof ModelAggregateInterface) {
            if (isset($this->_byId[$idOrAggregate->getModel()->getId()])) {
                return $this->_byId[$idOrAggregate->getModel()->getId()];
            }

            return isset($this->_byId[$idOrAggregate->getModel()->getCid()]) ? $this->_byId[$idOrAggregate->getModel()->getCid()] : null;
        }

        return null;
    }

    /**
     * @param array $aggregateData
     * @param null $collection
     * @param bool|true $parse
     * @param bool|true $silent
     * @return ModelAggregateInterface
     */
    public function newModelInstance(array $aggregateData = array(), $collection = null, $parse = true, $silent = true) {
        return $this->_getAggregateFactory()->newInstance($aggregateData, $collection, $parse, $silent);
    }

    /**
     * @param Events\EventInterface $event
     * @param ModelAggregateInterface $aggregate
     * @param mixed $collection
     */
    public function onModelEvent(Events\EventInterface $event, ModelAggregateInterface $aggregate = null, /** @noinspection PhpUnusedParameterInspection */
                                 $collection = null) {
        $eventName = $event->getName();
        if (($event instanceof Event\ModelAdd || $event instanceof Event\ModelRemove) && $aggregate->getModel()->getCollection() !== $this) {
            return;
        }

        if ($event instanceof Event\ModelDestroy) {
            $this->remove(array($aggregate), $event->silent());
        }

        if (!is_null($aggregate) && $eventName == Event\ModelFieldChange::eventName($this->_idAttribute)) {
            $model = $aggregate->getModel();
            if (isset($this->_byId[$model->previousId()])) {
                unset($this->_byId[$model->previousId()]);
            }
            $newId = $model->getId();
            if (!empty($newId)) $this->_byId[$newId] = $aggregate;
        }

        $this->getPublisher()->trigger($event);
    }

    /**
     * @param array|ModelAggregateInterface $aggregateData
     * @param SetOptionsInterface $options
     * @return Model
     */
    protected function _prepareModel($aggregateData, SetOptionsInterface $options) {
        return $aggregateData instanceof ModelAggregateInterface
            ? $aggregateData
            : $this->_getAggregateFactory()->newInstance($aggregateData, $this, $options->parse(), $options->silent());
    }

    /**
     * @param ModelAggregateInterface $aggregate
     */
    protected function _addReference(ModelAggregateInterface $aggregate) {

        $model = $aggregate->getModel();
        $this->_aggregates[] = $aggregate;
        $this->_byId[$model->getCid()] = $aggregate;
        $id = $model->getId();
        if (!empty($id)) {$this->_byId[$id] = $aggregate;}
        if (!$model->isInCollection()) {$model->setCollection($this);}
        $this->getSubscriber()->subscribeToAllEvents($aggregate, 'onModelEvent');
    }

    /**
     * @param ModelAggregateInterface[] $modelAggregates
     * @param bool $silent
     * @return ModelAggregateInterface[]
     */
    public function remove(array $modelAggregates, $silent = false)
    {
        foreach ($modelAggregates as &$aggregate) {
            $aggregate = $this->get($aggregate);

            if (is_null($aggregate)) {continue;}

            $model = $aggregate->getModel();

            if (isset($this->_byId[$model->getId()])) {unset($this->_byId[$model->getId()]);}
            if (isset($this->_byId[$model->getCid()])) {unset($this->_byId[$model->getCid()]);}

            $index = array_search($aggregate, $this->_aggregates);

            if (false !== $index) {
                unset($this->_aggregates[$index]);
            }

            if (!$silent) {
                $aggregate->getPublisher()->trigger(new Event\ModelRemove($aggregate, $this, $index));
            }

            $this->_removeReference($aggregate);
        }

        return $modelAggregates;
    }

    /**
     * @param ModelAggregateInterface[] $addedAggregates
     */
    protected function _fireAddEvents($addedAggregates)
    {
        foreach ($addedAggregates as $aggregate) {
            $aggregate->getPublisher()->trigger(new Event\ModelAdd($aggregate, $this));
        }
    }

    /**
     * @param $toKeep
     * @internal param $toRemove
     */
    protected function _removeRedundantModels($toKeep)
    {
        $toRemove = array();
        foreach ($this->_aggregates as $aggregate) {
            $model = $aggregate->getModel();

            if (!isset($toKeep[$model->getCid()]) || !$toKeep[$model->getCid()]) {
                $toRemove[] = $aggregate;
            }
        }
        if (count($toRemove) > 0) {
            $this->remove($toRemove);
        }
    }

    /**
     * @param array $aggregates
     * @param SetOptionsInterface $options
     * @return array (array $addedModels, array $toKeep)
     */
    protected function _addOrUpdateModels(array &$aggregates, SetOptionsInterface $options)
    {
        $toKeep = array();
        $addedModels = array();

        foreach ($aggregates as &$aggregateData) {
            $model = null;

            if ($aggregateData instanceof Model) {
                $id = $model = $aggregateData;
            } else {
                $id = $this->_getAggregateFactory()->calculateId($aggregateData);
            }

            $existing = $this->get($id);

            if (!empty($existing)) {
                if ($options->remove()) {
                    $toKeep[$existing->getModel()->getCid()] = true;
                }
                if ($options->merge()) {
                    $attributes = ($aggregateData instanceof ModelAggregateInterface) ? $aggregateData->getModel()->getAttributes() : $aggregateData;
                    if ($options->parse()) {
                        $attributes = $existing->parse($attributes);
                    }
                    $existing->getModel()->set($attributes, $options->silent(), false);
                }

                $aggregateData = $existing;
            } elseif ($options->add()) {
                $model = $aggregateData = $this->_prepareModel($aggregateData, $options);
                if (is_null($model)) {
                    continue;
                }
                $addedModels[] = $model;
                $this->_addReference($model);
            }

            $model = is_null($existing) ? $model : $existing;
            if ($model instanceof Model) {
                $toKeep[$model->getCid()] = true;
            }
        }

        return array($addedModels, $toKeep);
    }

    protected function _getDefaultModelClassName() {
        return __NAMESPACE__ . '\\Model';
    }

    /**
     * @return Events\PublisherInterface
     */
    public function getPublisher()
    {
        if (empty($this->_publisher)) {
            $this->_publisher = new Events\Publisher();
        }

        return $this->_publisher;
    }

    /**
     * @return Events\SubscriberInterface
     */
    public function getSubscriber()
    {
        if (empty($this->_subscriber)) {
            $this->_subscriber = new Events\Subscriber($this);
        }

        return $this->_subscriber;
    }

    /**
     * @return ModelAggregateFactoryInterface
     */
    protected function _getAggregateFactory()
    {
        if (empty($this->_aggregateFactory)) {
            $this->_aggregateFactory = new ModelAggregateFactory($this->_getDefaultModelClassName());
        }

        return $this->_aggregateFactory;
    }

    /**
     * @param ModelAggregateFactoryInterface $aggregateFactory
     * @return $this
     */
    protected function _setAggregateFactory($aggregateFactory)
    {
        $this->_aggregateFactory = $aggregateFactory;
        return $this;
    }

    /**
     * @return ModelAggregateInterface|null
     */
    public function first() {
        if (count($this->_aggregates) < 1) {
            return null;
        }

        $iterator = $this->getIterator();
        $iterator->seek(0);
        return $iterator->current();

    }

    public function sort($silent = false) {
        $comparator = $this->_getComparator();

        if (is_null($comparator)) {
            throw new \Exception('Cannot sort without comparator.');
        }

        if (is_string($comparator)) {
            $comparator = $this->_sortBy($comparator);
        }

        usort($this->_aggregates, $comparator);

        if (!$silent) {
            $this->getPublisher()->trigger(new Sort($this));
        }
    }

    protected function _sortBy($field) {
        return function(ModelAggregateInterface $a, ModelAggregateInterface $b) use($field) {
            if ($a->getModel()->offsetGet($field) < $b->getModel()->offsetGet($field)) {
                return -1;
            } elseif ($a->getModel()->offsetGet($field) > $b->getModel()->offsetGet($field)) {
                return 1;
            }

            return 0;
        };
    }

    protected function _getComparator() {
        return null;
    }

    /**
     * @param bool $full
     * @return \Iterator
     */
    public function getArrayCopy($full = false) {
        return $this->_getHelper()->map(
            $this,
            function(ModelAggregateInterface $aggregate)use($full){return $aggregate->getModel()->getArrayCopy($full);}
        );
    }

    private function _getHelper() {
        if (empty($this->_helper)) {
            $this->_helper = new Helper\Helper();
        }

        return $this->_helper;
    }


}