<?php
namespace Jyim\LaravelPluginMarket\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Jyim\LaravelPluginMarket\Exceptions\ApiRequestException;
use Spatie\DataTransferObject\DataTransferObject;

class CreatePluginData extends DataTransferObject
{
    public string $pluginName;
    public int $authorId;
    public array $pluginMetadata;
    public string $content;
    public string $contentType;

    public static function fromRequest(Request $request): self
    {
        $pluginInfo = json_decode($request->header('plugin-info'), true) ?? [];

        $validator = Validator::make($pluginInfo, [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new ApiRequestException($validator->errors()->all()[0]);
        }

        return new self([
            'pluginName' => data_get($pluginInfo,'name'),
            'authorId' => Auth::user()->id,
            'content' => $request->getContent(),
            'pluginMetadata' => $pluginInfo,
            'contentType' => $request->header('content-type')
        ]);
    }
}