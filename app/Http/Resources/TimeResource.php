<?php

namespace App\Http\Resources;

use App\Models\CampeonatoTimePivot;
use App\Models\Time;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Time */
class TimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nome'            => $this->nome,
            'ordem_inscricao' => $this->whenPivotLoaded('campeonato_time', function () {
                /** @var CampeonatoTimePivot $pivot */
                $pivot = $this->resource->pivot;

                return $pivot->ordem_inscricao;
            }),
            'pontuacao_total' => $this->whenPivotLoaded('campeonato_time', function () {
                /** @var CampeonatoTimePivot $pivot */
                $pivot = $this->resource->pivot;

                return $pivot->pontuacao_total;
            }),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
