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
