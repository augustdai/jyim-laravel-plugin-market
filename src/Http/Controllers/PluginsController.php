<?php
namespace Jyim\LaravelPluginMarket\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Jyim\LaravelPluginMarket\DTOs\CreatePluginData;
use Jyim\LaravelPluginMarket\Enums\PluginVersionStatus;
use Jyim\LaravelPluginMarket\Exceptions\ApiRequestException;
use Jyim\LaravelPluginMarket\Models\MarketPlugin;
use Jyim\LaravelPluginMarket\Http\Resources\PluginResource;
use Jyim\LaravelPluginMarket\Models\MarketPluginVersion;
use Jyim\LaravelPluginMarket\Services\Plugins\Create;
use Jyim\LaravelPluginMarket\Services\Plugins\Download;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PluginsController extends Controller
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $status = $request->input('status');
        $mps = MarketPlugin::query()
            ->with(['versions', 'author'])
            ->when($status, fn(Builder $builder) =>
               $builder->whereHas('versions', fn(Builder $builder) => call_user_func([$builder, $status]))
            )
            ->latest()
            ->paginate();
        if ($status) {
            $mps->through(function(MarketPlugin $plugin) use ($status) {
                $plugin->versions = $plugin->versions->filter(fn(MarketPluginVersion $version) =>
                $status === "release" ? $version->status === PluginVersionStatus::ACTIVE : $version->status !== PluginVersionStatus::ACTIVE
                );
                return $plugin;
            });
        }

        return PluginResource::collection($mps);
    }

    /**
     * @param  Request  $request
     * @param  Create  $create
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Request $request, Create $create): JsonResponse
    {
        try {
            $create->execute(CreatePluginData::fromRequest($request));
            return Response::json([]);
        } catch (\Exception $exception) {
            throw new ApiRequestException($exception->getMessage());
        }
    }

    /**
     * @param  int  $versionId
     * @param  Download  $install
     * @return StreamedResponse
     */
    public function download(int $versionId, Download $install): StreamedResponse
    {
        return $install->execute($versionId, Auth::id());
    }

    /**
     * @return JsonResponse
     */
    public function count(): JsonResponse
    {
        return Response::json(['count' => MarketPlugin::query()->whereHas('versions', fn(Builder $builder) => $builder->release())->count()]);
    }
}