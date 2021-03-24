<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| Card API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your card. These routes
| are loaded by the ServiceProvider of your card. You're free to add
| as many additional routes to this file as your card may require.
|
*/
//

Route::post('/fetch', function () {
    $resource = request()->json('resource');

    if(!class_exists($resource)) {
        return response()->json(['error' => true, 'message' => "Invalid class $resource"], 422);
    }

    $orderBy = request()->json('orderBy', 'created_at');
    $order = request()->json('order', 'desc');
    $limit = request()->json('limit', 10);
    $resourceTitleColumn = request()->json('resourceTitleColumn', 'title');
    $column = request()->json('column');
    $value = request()->json('value');
    $addRow = request()->json('addRow');
    $query = $resource::orderBy($orderBy, $order);
    $belongsTo = request()->json('belongsTo');

    if($column!=''){
       $query = $query->where($column, $value);
    }
    if(isset($belongsTo) && $belongsTo!=""){
        $query = $query->with($belongsTo);
    }

    $items = $query->with('customer')->take($limit)->get();
    


    $items->map(function ($item) use ($resourceTitleColumn, $orderBy, $addRow, $belongsTo) {
        $item->makeVisible('id');
        $item->title = $item->{$resourceTitleColumn};
        if(isset($belongsTo) && $belongsTo!="" && $addRow!=""){
            $item->addRow = $item->{$belongsTo}->{$addRow};
        }
        else if($addRow!=""){
            $item->addRow = $item->{$addRow};
        }

        
        $item->ordered_column = request()->json('readableDate', false) ? \Carbon\Carbon::parse($item->{$orderBy})->diffForHumans() : $item->{$orderBy};
    });

    return response()->json($items, 200);
});

//Route::post('/fetch' , \Radwanic\ResourceListing\Http\Controllers\ResourceListingController::class.'@fetch');