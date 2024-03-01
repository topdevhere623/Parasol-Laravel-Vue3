<?php

namespace ParasolCRMV2\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use ParasolCRMV2\CRUD;
use ParasolCRMV2\ResourceQuery;
use ParasolCRMV2\ResourceScheme;
use ParasolCRMV2\Services\CRM\Facades\PrslV2;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends BaseController
{
    use CRUD;

    protected ResourceScheme $resource;

    protected ResourceQuery $resourceQuery;

    protected ?string $id;

    public function __construct()
    {
        if (app()->runningInConsole()) {
            return;
        }

        $resource = PrslV2::getResource();

        abort_if(!$resource, Response::HTTP_NOT_FOUND);

        $this->id = request()->route('id');
        $this->resource = $resource;
        $this->resourceQuery = new ResourceQuery($this->resource->getModel());

        if (method_exists($this->resource, 'query')) {
            $this->middleware(function ($request, $next) {
                $this->resource->query($this->resourceQuery->getQueryBuilder());
                return $next($request);
            });
        }
    }
}
