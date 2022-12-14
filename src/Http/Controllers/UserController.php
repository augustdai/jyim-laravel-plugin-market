<?php
namespace Jyim\LaravelPluginMarket\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Jyim\LaravelPluginMarket\Enums\PluginVersionStatus;
use Jyim\LaravelPluginMarket\Http\Resources\PluginResource;
use Jyim\LaravelPluginMarket\Http\Resources\UserResource;
use Jyim\LaravelPluginMarket\Models\MarketPlugin;
use Jyim\LaravelPluginMarket\Models\MarketPluginVersion;
use Jyim\LaravelPluginMarket\Models\MarketUser;

class UserController extends Controller
{

    /**
     * @return AnonymousResourceCollection
     */
    public function index():AnonymousResourceCollection
    {
        return UserResource::collection(MarketUser::query()->latest()->paginate());
    }
    /**
     * @return UserResource
     */
    public function getUserInfo(): UserResource
    {
        return new UserResource(Auth::user());
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function getPlugins(Request $request): AnonymousResourceCollection
    {
        $status = $request->input('status');
        $mps = MarketPlugin::query()
            ->with(['versions', 'author'])
            ->where('author_id', Auth::id())
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
}