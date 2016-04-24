<?php

require './Route.php';
require './RouteFacade.php';

//bool class_alias ( string $original , string $alias [, bool $autoload = TRUE ] )

class_alias('\Jelly\RouteFacade', 'Route');

Route::test1();
Route::test2(array(1,5,7));
Route::test2(array(2,3,7));
