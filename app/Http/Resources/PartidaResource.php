<?php

namespace App\Http\Resources;

use App\Models\Partida;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Partida */
class PartidaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'fase'           => $this->fase,
            'time_mandante'  => new TimeResource($this->whenLoaded('timeMandante')),
            'time_visitante' => new TimeResource($this->whenLoaded('timeVisitante')),
            'time_vencedor'  => new TimeResource($this->whenLoaded('timeVencedor')),
            'gols_mandante'  => $this->gols_mandante,
            'gols_visitante' => $this->gols_visitante,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
