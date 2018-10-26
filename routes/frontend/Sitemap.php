<?php

Route::get('sitemap', 'SEOSitemapController@index')->name('sitemap');
Route::get('sitemap/{type}/{page}', 'SEOSitemapController@single')->name('sitemap.single');