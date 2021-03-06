<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collecction;
use App\Models\Expense;
use App\Models\Gallery;
use RakibDevs\Covid19\Covid19;
use DB, stdClass, Cache;

class PagesController extends Controller
{
    public function index()
    {

        $d =  new Covid19;
        $covid_summary = Cache::remember('covid_summary',600, function () use ($d) {
            return $d->getSummary();
        });

        //dd($covid_summary);


    	$collections = Collecction::select(
                            DB::raw('sum(amount) as amount'),
                            DB::raw('CAST(created_at AS DATE) as date')
                        )
                        ->groupBy(DB::raw('CAST(created_at AS DATE)'))
                        ->orderBy('id','DESC')
                        ->take(6)
                        ->pluck('amount','date')
                        ->toArray();

        $expenses = Expense::select(
                        DB::raw('sum(amount) as amount'),
                        'exp_date'
                    )
                    ->groupBy('exp_date')
                    ->orderBy('id','DESC')
                    ->take(6)
                    ->pluck('amount','exp_date')
                    ->toArray();

        $combined = array_merge($collections,$expenses);
        ksort($combined);
        $graph = new stdClass();
        $graph->label = [];
        $graph->collect = [];
        $graph->expense = [];

        foreach ($combined as $key => $single) {
        	$graph->label[]   = $key;
        	$graph->collect[] = $collections[$key]??0;
        	$graph->expense[] = $expenses[$key]??0;
        }

        $gallery = Gallery::orderBy('id','DESC')->take(6)->get();

        return view('index', compact('graph','gallery','covid_summary'));
    }
}
