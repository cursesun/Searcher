<?php
namespace Phalcon\Searcher;

use Phalcon\Searcher\Factories\ExceptionFactory;

/**
 * Searcher daemon class
 * @package Phalcon\Searcher
 * @since PHP >=5.5.12
 * @version 1.0
 * @author Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanislav WEB
 */
class Searcher
{

    /**
     * Query value for DB
     * @var array
     */
    public $query = [];

    /**
     * Strict flag
     * @var boolean
     */
    public $exact = false;

    /**
     * Validator
     * @var \Phalcon\Searcher\Validator
     */
    private $_validator;

    /**
     * Initialize class
     * @uses Phalcon\Searcher\Validator
     * @return null
     */
    public function __construct()
    {
        $this->_validator = new Validator();
    }

    /**
     * Set minimum value for the search
     *
     * @param int $min value
     * @return Searcher
     */
    public function setMin($min)
    {
        $this->_validator->setMin($min);
        return $this;
    }

    /**
     * Set maximum value for the search
     *
     * @param int $max value
     * @return Searcher
     */
    public function setMax($max)
    {
        $this->_validator->setMax($max);
        return $this;
    }

    /**
     * Use Strict mode ?
     *
     * @param boolean $flag
     * @example <code>
     *          $s->setExact(true) // false
     *          </code>
     * @return Searcher
     */
    public function setExact($flag)
    {
        $this->exact = $flag;
        return $this;
    }

    /**
     * Prepare models to participate in search
     *
     * @param array $models
     * @example <code>
     *          $s->setFields([
     *            'Model/Table1'    =>    [
     *                'title',
     *                'text'
     *            ],
     *                'Model/Table2'    =>    [
     *                'name',
     *                'mark'
     *            ]....
     *          ])
     *          </code>
     * @throws ExceptionFactory {$error}
     * @return Searcher
     */
    public function setFields(array $models)
    {

        // need to return << true
        $this->_validator->verify($models, [
            'isArray', 'isNotEmpty', 'isExists'
        ], 'where');

        return $this;
    }

    /**
     * Order results
     *
     * @param array $order
     * @example <code>
     *          $s->setOrder(['Model/Table1' => ['id' => 'DESC']])
     *          $s->setOrder([
     *            'Model/Table1' => ['id' => 'DESC']
     *            'Model/Table2' => ['title' =>  'ASC']
     *          ])
     *          </code>
     * @throws ExceptionFactory {$error}
     * @return Searcher
     */
    public function setOrder(array $order)
    {

        // need to return << true
        $this->_validator->verify($order, [
            'isArray', 'isNotEmpty', 'isOrdered'
        ], 'order');
        return $this;
    }


    /**
     * Group results
     *
     * @param array $group
     * @example <code>
     *          $s->setGroup(['Model/Table1' => ['id']])
     *          $s->setGroup([
     *            'Model/Table1' => ['id', 'title']
     *            'Model/Table2' => ['id', 'description']
     *          ])
     *          </code>
     * @throws ExceptionFactory {$error}
     * @return Searcher
     */
    public function setGroup(array $group)
    {

        // need to return << true
        $this->_validator->verify($group, [
            'isArray', 'isNotEmpty', 'isExists'
        ], 'group');

        return $this;
    }


    /**
     * Setup offset, limit threshold
     *
     * @param mixed $threshold
     * @example <code>
     *          $s->setThreshold(100)        //    limit
     *          $s->setThreshold([0,100])    //    offset, limit
     *          </code>
     * @throws ExceptionFactory {$error}
     * @return Searcher
     */
    public function setThreshold($threshold)
    {

        // need to return << true
        if (is_array($threshold) === true)
            $threshold = array_map('intval', array_splice($threshold, 0, 2));
        else
            $threshold = intval($threshold);
        $this->_validator->verify($threshold, [], 'threshold');
        return $this;
    }

    /**
     * Prepare query value
     * @param string|null $query
     * @example <code>
     *          $s->setQuery('what i want to find')
     *          </code>
     * @throws ExceptionFactory {$error}
     * @return Searcher
     */
    public function setQuery($query = null)
    {

        // need to return << true
        $this->_validator->verify($query, ['isNotNull', 'isNotFew', 'isNotMuch']);

        if (false === $this->exact)
            $this->query = ['query' => '%' . $query . '%'];
        else
            $this->query = ['query' => $query];
        return $this;
    }

    /**
     * Get qualified valid tables & fields
     * @return array
     */
    public function getFields()
    {
        return $this->_validator->fields;
    }

    /**
     * Search procedure started
     *
     * @throws ExceptionFactory {$error}
     * @return Builder|null
     */
    final public function run()
    {

        try {

            $builder = (new Builder($this))->loop();
            return $builder;
        } catch (ExceptionFactory $e) {
            echo $e->getMessage();
        }

    }
}
