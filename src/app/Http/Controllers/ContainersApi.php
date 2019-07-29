<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder as QueryBuilder;

class ContainersApi extends Controller
{
    const DEFAULT_CONTAINERS_LIMIT = 100;

    /**
     * Operation createContainers
     * Create a container.
     *
     * @return Http response
     */
    public function createContainers(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer|unique:containers',
            'name' => 'required',
            "products" => 'required|array',
            "products.*.id" => 'required|integer',
            "products.*.name" => 'required',
        ]);

        $container = app('db')->table('containers')->insert($request->only('id', 'name', 'products'));

        return $this->showContainerById($request->id)->setStatusCode(201);
    }

    /**
     * Operation listContainers
     * List all containers.
     *
     * @return Http response
     */
    public function listContainers(Request $request)
    {
        $page = $request->input('page', 1) - 1;

        $containers = app('db')->table('containers')->select('*')
        ->take(self::DEFAULT_CONTAINERS_LIMIT)
        ->skip($page*self::DEFAULT_CONTAINERS_LIMIT)
        ->get()->map(function($item) {
            unset($item['_id']);
            return $item;
        });

        return response()->json($containers);
    }

    /**
     * Operation showContainerById
     * Info for a specific container.
     *
     * @param string $containerId The id of the container to retrieve (required)
     * @return Http response
     */
    public function showContainerById($containerId)
    {
        $container = app('db')->table('containers')
        ->where('id', (int)$containerId)
        ->get()->map(function($item) {
            unset($item['_id']);
            return $item;
        })->first();

        return response()->json($container);
    }
}
