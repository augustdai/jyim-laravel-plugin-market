<?php
namespace Jyim\LaravelPluginMarket\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Jyim\LaravelPluginMarket\DTOs\UpdatePluginVersionData;
use Jyim\LaravelPluginMarket\Exceptions\ApiRequestException;
use Jyim\LaravelPluginMarket\Http\Resources\VersionDetailResource;
use Jyim\LaravelPluginMarket\Models\MarketPluginVersion;
use Jyim\LaravelPluginMarket\Services\PluginVersions\Update;

class PluginsVersionsController extends Controller
{
    /**
     * @param  int  $versionId
     * @return VersionDetailResource
     */
    public function show(int $versionId):VersionDetailResource
    {
        return new VersionDetailResource(MarketPluginVersion::query()->findOrFail($versionId));
    }

    /**
     * @param  int  $versionId
     * @param  Request  $request
     * @param  Update  $update
     * @return JsonResponse
     */
    public function update(int $versionId, Request $request, Update $update): JsonResponse
    {
        try {
            $isAdmin = Auth::user()->is_admin;
            $update->execute($versionId, Auth::id(), $isAdmin, UpdatePluginVersionData::fromRequest($request));
            return Response::json([]);
        } catch (\Exception $exception) {
            throw new ApiRequestException($exception->getMessage());
        }
    }
}