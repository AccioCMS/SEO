<?php
/**
 * Routes of this plugin
 */

/**
 * GET
 */
Route::get('/', 'SEOController@index')->name('index');
Route::get('/get-all', 'SEOController@getAll')->name('getAll');
Route::get('/details/{postID}/{post_type}', 'SEOController@details')->name('details');
Route::get('/{component}', 'SEOController@index')->name('index');
Route::get('/{component}/{subComponent}', 'SEOController@index')->name('index');

/**
 * POST
 */
Route::post('/store', 'SEOController@store')->name('store');
