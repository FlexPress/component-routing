<?php

namespace FlexPress\Components\Routing;

use Symfony\Component\HttpFoundation\Request;

class Route
{

    /**
     * @var array
     */
    protected $callable;

    /**
     * @var \SplQueue
     */
    protected $conditions;

    /**
     * @var \Pimple
     */
    protected $pimple;

    /**
     * @var \Pimple
     */
    protected $request;

    /**
     * @param \SplQueue $queue - The queue that should be used to store the conditions
     * @param \Pimple $pimple
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(\SplQueue $queue, \Pimple $pimple, Request $request)
    {
        $this->pimple = $pimple;
        $this->conditions = $queue;
        $this->request = $request;
    }

    /**
     * Sets the controller and action
     *
     * @param $callableExpression - A string in the format 'controller@action'
     * @throws \InvalidArgumentException
     * @author Tim Perry
     */
    public function setCallable($callableExpression)
    {

        if (strpos("@", $callableExpression) === false) {
            $callableExpression .= "@indexAction";
        }

        list($controllerName, $action) = explode("@", $callableExpression);

        $controller = $this->pimple[$controllerName];

        $callable = array(
            $controller,
            $action
        );

        if (!is_callable($callable)) {

            $message = "The callable you have provided is not callable, if you did not provide a action then ";
            $message .= "indexAction will be called on your given controller";

            throw new \InvalidArgumentException($message);
        }

        $this->callable = $callable;
    }

    /**
     * Adds conditions to the route from the given array
     *
     * @param array $conditions - an array of conditions
     *
     * @throws \InvalidArgumentException
     * @author Tim Perry
     */
    public function addConditionsFromArray(array $conditions)
    {
        foreach ($conditions as $condition) {

            if (!is_callable($condition)) {

                $message = "One or more of the conditions you provided are not callable, ";
                $message .= "please pass an array of callable functions.";

                throw new \InvalidArgumentException($message);
            }

            $this->conditions->enqueue($condition);

        }
    }

    /**
     * Runs through all the callable functions provided, if all are met call the given callable
     * @author Tim Perry
     */
    public function run()
    {
        if (!isset($this->conditions)
            || !isset($this->callable)
        ) {

            $message = "You have called the run function but have not provided both a array of conditions";
            $message .= " and a callable function, which will be called if all the given conditions are met.";

            throw new \RuntimeException($message);
        }

        $this->conditions->rewind();
        while ($this->conditions->valid()) {

            $condition = $this->conditions->current();

            if (!call_user_func($condition)) {
                return false;
            }

            $this->conditions->next();
        }

        call_user_func($this->callable, $this->request);
        return true;

    }

    /**
     *
     * Required to do a deep copy of the object
     *
     * @author Tim Perry
     *
     */
    public function __clone()
    {
        $this->callable = null;

        // deep copy the conditions queue
        $this->conditions = clone $this->conditions;
    }
}
