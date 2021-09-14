<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'FrontpageController@index')->name('frontpage');
Route::get('/news/list', 'FrontNewsController@list')->name('news.list');
Route::get('/news/{id}', 'FrontNewsController@detail')->name('news');
Route::get('/movie/list', 'FrontMovieController@list')->name('movie.list');
Route::get('/info/list', 'FrontInfoController@list')->name('info.list');
Route::get('/info/{id}', 'FrontInfoController@detail')->name('info');
Route::get('/company', 'FrontCompanyController@index')->name('company.index');
Route::get('/contact/{title?}', 'FrontContactController@index')->name('contact.index');
Route::post('/contact', 'FrontContactController@sendmail')->name('contact.sendmail');

// Auth::routes();
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');

/*
|-------------------------------------------------------------------------
| 管理者以上で操作
|-------------------------------------------------------------------------
 */
Route::group(['middleware' => ['auth', 'can:SYSTEM_ADMIN']], function () {
  //ユーザー登録
  Route::get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
  Route::post('register', 'Auth\RegisterController@register');
});

// Auth::routes();

/***** admin *****/
Route::name('admin.')->group(function() {
  Route::get('admin/home', 'HomeController@index')->name('home.index');

  // 優良顧客比較
  Route::get('admin/excellent_comparison', 'CheckController@index')->name('excellent_comparison.index');
  Route::post('admin/excellent_comparison', 'CsvImportController@store')->name('excellent_comparison.store');
  Route::get('admin/export', 'ExcelExportController@export')->name('excellent_comparison.export');
  Route::get('admin/list_excellent', 'ListExcellentController@index')->name('list_excellent.index');
  Route::delete('admin/list_excellent/{id}', 'ListExcellentController@destroy')->name('list_excellent.destroy');

  // 倒産データ
  Route::get('admin/insolvency_master_import', 'CsvImportInsolvencyController@index')->name('insolvency_master_import.index');
  Route::post('admin/insolvency_master_import', 'CsvImportInsolvencyController@store')->name('insolvency_master_import.store');
  Route::get('admin/export_insolvency', 'ExcelExportInsolvencyController@export')->name('export_insolvency.export');
  Route::get('admin/export_insolvency_master', 'ExcelExportInsolvencyMasterController@export')->name('export_insolvency_master.export');
  Route::get('admin/list_insolvency_master', 'ListInsolvencyController@index')->name('list_insolvency_master.index');
  Route::post('admin/list_insolvency_master', 'ListInsolvencyController@store')->name('list_insolvency_master.store');
  Route::delete('admin/list_insolvency_master', 'ListInsolvencyController@destroy')->name('list_insolvency_master.destroy');
  Route::get('admin/insolvency_analysis/{id}', 'InsolvencyAnalysisController@index')->name('insolvency_analysis.index');
  Route::post('admin/insolvency_analysis', 'InsolvencyAnalysisController@store')->name('insolvency_analysis.store');

  // 動画投稿
  Route::get('admin/list_movie', 'ListMovieController@index')->name('list_movie.index');
  Route::delete('admin/list_movie/{id}', 'ListMovieController@destroy')->name('list_movie.destroy');
  Route::get('admin/movie/{id?}', 'MovieController@index')->name('movie.index');
  Route::post('admin/movie', 'MovieController@store')->name('movie.store');
  Route::put('admin/movie/{id}', 'MovieController@update')->name('movie.update');
  Route::get('admin/movie_cat', 'MovieCatController@index')->name('movie_cat.index');
  Route::post('admin/movie_cat', 'MovieCatController@store')->name('movie_cat.store');

  // 見積
  Route::get('admin/list_quote', 'ListQuoteController@index')->name('list_quote.index');
  Route::put('admin/list_quote/{id?}', 'ListQuoteController@update')->name('list_quote.update');
  Route::get('admin/request_for_quote', 'RequestForQuoteController@index')->name('request_for_quote.index');
  Route::get('admin/detail_quote/{type}/{id?}', 'DetailQuoteController@index')->name('detail_quote.index');
  Route::post('admin/detail_quote', 'DetailQuoteController@store')->name('detail_quote.store');
  Route::get('admin/make_quote/{id?}', 'MakeQuoteController@index')->name('make_quote.index');
  Route::post('admin/make_quote', 'MakeQuoteController@store')->name('make_quote.store');

  // ブログ投稿
  Route::get('admin/list_blog', 'ListBlogController@index')->name('list_blog.index');
  Route::delete('admin/list_blog/{id}', 'ListBlogController@destroy')->name('list_blog.destroy');
  Route::get('admin/blog/{id?}', 'BlogController@index')->name('blog.index');
  Route::post('admin/blog', 'BlogController@store')->name('blog.store');
  Route::put('admin/blog/{id}', 'BlogController@update')->name('blog.update');
  Route::get('admin/blog_cat', 'BlogCatController@index')->name('blog_cat.index');
  Route::post('admin/blog_cat', 'BlogCatController@store')->name('blog_cat.store');

  // M＆A投稿
  Route::get('admin/list_ma', 'ListMaController@index')->name('list_ma.index');
  Route::delete('admin/list_ma/{id}', 'ListMaController@destroy')->name('list_ma.destroy');
  Route::get('admin/ma/{id?}', 'MaController@index')->name('ma.index');
  Route::post('admin/ma', 'MaController@store')->name('ma.store');
  Route::put('admin/ma/{id}', 'MaController@update')->name('ma.update');

  // 会員情報一覧
  Route::get('admin/list_member_info', 'ListMemberInfoController@index')->name('list_member_info.index');
  Route::get('admin/member_info/edit/{id}', 'ListMemberInfoController@edit')->name('member_info.edit');
  Route::put('admin/member_info/{id}', 'ListMemberInfoController@update')->name('member_info.update');
  Route::delete('admin/member_info/{id}', 'ListMemberInfoController@destroy')->name('member_info.destroy');

});
