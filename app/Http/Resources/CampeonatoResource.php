<?php

namespace App\Http\Resources;

use App\Models\Campeonato;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Campeonato */
class CampeonatoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'nome'       => $this->nome,
            'status'     => $this->status,
            'times'      => $this->whenLoaded('times'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
