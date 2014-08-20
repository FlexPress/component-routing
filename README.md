# FlexPress Route component

## Install with Pimple
The Route component uses two classes:
- Route, which lets you set various option on to create a route, however this is done by the router and we just pass the router a single route as a dependency, that is used as a prototype object.
- Router, which does the actual routing.

Lets create a pimple config for both of these

```
$pimple["route"] = function () {
  return new Route();
};

$pimple['router'] = function ($c) {
    return new RouteHelper($c['queue'], $c, $c['route']));
};
```
- Note the dependency $c['queue']  is a SPLQueue

## Adding routes
Now that we have the configuration setup we probably want to add some routes. Lets start by adding a very simple route for the frontpage the FrontPageController

```
$router = $pimple['router'];
$router->addRoute(
    'frontPageController',
    function () {
        return is_front_page();
    }
);
$router->route();
```

So what's going on here? First we are getting the router from pimple. Next we are calling the addRoute method, the first paramater is what controller you want to target, this should match the config name in pimple. After that we have a function that is returning is_front_page(), this function allows you to return a boolean yes no answer so whether the router should use this route. Finally we have the call to the route method, as the name suggests, this kicks off the routing and works though each route until a possitive conditional is met. Note that the router uses a queue so it will procces the routes in the order you have added them.

So what are saying is if it is the frontpage we want to use the frontPageController from our DIC, that part that is miss is the @indexAction, this can be omiited when you want to target the indexAction but to be explicit this is what it would look like:

```
$router = $pimple['router'];
$router->addRoute(
    'frontPageController@indexAction',
    function () {
        return is_front_page();
    }
);
$router->route();
```

## Filters

Filters are can be reused on multiple routers to keep your code DRY. Lets create a simple filter for is_search():

```
$router = $pimple['router'];
$router->addFilter('isSearch', function(){
  return is_search();
});
```
That's it, dead simple eh! So what have we done here? The first paramater of addFilter lets you speficy a name for it, the second param is the function, the same as the one you used in the route.

Now how do we add that to a route? Instead of passing a single function as the second paramater you can pass an array, of which can either include multiple functions/filters or both. Lets create an example that has both a function and a filter:

```
$router = $pimple['router'];

$router->addFilter('isSearch', function(){
  return is_search();
});

$router->addRoute(
    'searchController',
    array(
      'isSearch',
      function () {
        return is_user_logged_in();
      }
    )
);

$router->route();
```

This example, will route to the searchControllers index action when is a user is loggedin and when it is_search().

## Public methods - Route
- setCallable($callableExpression) - Set the callable using a string with the format 'controller@action'.
- addConditionsFromArray(array $expressions) - Adds conditions to the route from the given array.
- run() - Runs through all the callable functions provided, if all are met call the given callable.

## Public methods - Router
- route() - Runs through all the routes and runs them.
- addRoute($callable, $conditions) - Used to add a route to the router for a given callable and conditions.
- addFilter($name, $function) - Used to add a filter to the router for a given name and function.

## Protected methods - Router
- replaceFilterFunctions(array $conditions) - Replaces the string conditions(filters) with the filter callable.
