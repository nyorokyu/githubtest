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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

/***** admin *****/
Route::name('admin.')->group(function() {
  Route::get('admin/home', 'HomeController@index')->name('home.index');

  Route::get('admin/excellent_comparison', 'CheckController@index')->name('excellent_comparison.index');
  Route::post('admin/excellent_comparison', 'CsvImportController@store')->name('excellent_comparison.store');
  Route::get('admin/export', 'ExcelExportController@export')->name('excellent_comparison.export');

  Route::get('admin/list_excellent', 'ListExcellentController@index')->name('list_excellent.index');
  Route::delete('admin/list_excellent/{id}', 'ListExcellentController@destroy')->name('list_excellent.destroy');

  // 倒産データ
  Route::get('admin/insolvency_master_import', 'CsvImportInsolvencyController@index')->name('insolvency_master_import.index');
  Route::post('admin/insolvency_master_import', 'CsvImportInsolvencyController@store')->name('insolvency_master_import.store');
  Route::get('admin/export_insolvency', 'ExcelExportInsolvencyController@export')->name('export_insolvency.export');
  Route::get('admin/list_insolvency_master', 'ListInsolvencyController@index')->name('list_insolvency_master.index');
  Route::delete('admin/list_insolvency_master', 'ListInsolvencyController@destroy')->name('list_insolvency_master.destroy');
  Route::get('admin/insolvency_analysis', 'InsolvencyAnalysisController@index')->name('insolvency_analysis.index');

  // 動画投稿
  Route::get('admin/list_movie', 'ListMovieController@index')->name('list_movie.index');
  Route::get('admin/movie', 'MovieController@index')->name('movie.index');
  Route::get('admin/movie_cat', 'MovieCatController@index')->name('movie_cat.index');

  // 見積
  Route::get('admin/list_quote', 'ListQuoteController@index')->name('list_quote.index');
  Route::get('admin/request_for_quote', 'RequestForQuoteController@index')->name('request_for_quote.index');
  Route::get('admin/detail_quote', 'DetailQuoteController@index')->name('detail_quote.index');
  Route::get('admin/make_quote', 'MakeQuoteController@index')->name('make_quote.index');

});
