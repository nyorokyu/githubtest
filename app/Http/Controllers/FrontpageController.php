<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\QuoteRequestMakeRelationTable;
use App\Blog;
use App\Movie;
use App\Ma;

class FrontpageController extends Controller
{
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct(QuoteRequestMakeRelationTable $quoteRelation, Blog $blog, Movie $movie, Ma $ma)
  {
    $this->quoteRelation = $quoteRelation;
    $this->blog = $blog;
    $this->movie = $movie;
    $this->ma = $ma;
  }

  public function index()
  {
    $quote = $this->quoteRelation->where('quote_status', 4)->where('is_deleted', 0)->latest('id')->limit(5)->get();
    $quoteData = [];
    foreach($quote as $data) {
      if($data->quoteRequestTables->self_quote_amount != 0) {
        $maker = $data->quoteRequestTables->maker;
        $carModel = $data->quoteRequestTables->car_model;
        $selfQuoteAmount = $data->quoteRequestTables->self_quote_amount;
        $amount = $data->quoteMakeTables->wage + $data->quoteMakeTables->parts_price + $data->quoteMakeTables->painting_wage + $data->quoteMakeTables->painting_parts_price;

        $array = [
          'maker' => $maker,
          'car_model' => $carModel,
          'self_quote_amount' => number_format($selfQuoteAmount),
          'amount' => number_format($amount),
          'ratio' => round(($amount * 100 / $selfQuoteAmount) - 100)
        ];
        array_push($quoteData, $array);
      }
    }

    $news = $this->blog->where('is_display', 1)->where('is_deleted', 0)->latest('displayed_at')->limit(3)->get();
    $movie = $this->movie->where('is_display', 1)->where('is_deleted', 0)->latest('displayed_at')->limit(3)->get();
    $info = $this->ma->where('is_display', 1)->where('is_deleted', 0)->latest('displayed_at')->limit(4)->get();
    return view('frontpage', compact('quoteData', 'news', 'movie', 'info'));
  }
}
