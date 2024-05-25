<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'App\Http\Controllers\IndexController@Index');
//Route::get('Scopus', 'App\Http\Controllers\ScopusController@search');