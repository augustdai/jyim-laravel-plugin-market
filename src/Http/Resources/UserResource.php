<?php
namespace Jyim\LaravelPluginMarket\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'is_admin' => $this->is_admin,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}