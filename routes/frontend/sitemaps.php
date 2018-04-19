<?php
/**
 * Sitemap routes
 */

Route::get('sitemaps', 'SitemapsController@index')->name('index');

Route::get('sitemaps/daily', 'SitemapsController@daily')->name('daily');
Route::get('{lang}/sitemaps/daily', 'SitemapsController@daily')->name('daily.lang');

Route::get('sitemaps/categories', 'SitemapsController@categories')->name('categories');
Route::get('{lang}/sitemaps/categories', 'SitemapsController@categories')->name('categories.lang');

Route::get('sitemaps/categories/{postTypeSlug}', 'SitemapsController@categories')->name('categoriesByPostType');
Route::get('{lang}/sitemaps/categories/{postTypeSlug}', 'SitemapsController@categories')->name('categoriesByPostType.lang');

Route::get('sitemaps/category/{categorySlug}', 'SitemapsController@category')->name('category');
Route::get('{lang}/sitemaps/category/{categorySlug}', 'SitemapsController@category')->name('category.lang');

Route::get('sitemaps/category/{categorySlug}/{year}/{month}', 'SitemapsController@posts')->name('category.posts');
Route::get('{lang}/sitemaps/category/{categorySlug}/{year}/{month}', 'SitemapsController@posts')->name('category.posts.lang');

Route::get('sitemaps/tags', 'SitemapsController@tags')->name('tags');

Route::get('sitemaps/authors', 'SitemapsController@authors')->name('authors');

Route::get('sitemaps/{postTypeSlug}', 'SitemapsController@postType')->name('postType');
Route::get('{lang}/sitemaps/{postTypeSlug}', 'SitemapsController@postType')->name('postType.lang');

Route::get('sitemaps/{postTypeSlug}/{year}/{month}', 'SitemapsController@postsByPostType')->name('postType.posts');
Route::get('{lang}/sitemaps/{postTypeSlug}/{year}/{month}', 'SitemapsController@postsByPostType')->name('postType.posts.lang');