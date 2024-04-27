<?php
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

function paginate( $size, $page, $total, $data ) {
    $size = (int)$size;
    $page = (int)$page;
    $starting_point = ($page * $size) - $size;
    $data = array_slice($data, $starting_point, $size, true);
    $res = new Paginator($data, $total, $size, $page, [
        'path' => request()->url(),
        'query' => request()->query(),
    ]);
    return $res;
}