<?php

namespace App\Http\Resources;

use App\Models\Campeonato;
use App\Models\Time;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Campeonato
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Time> $times
 */
class CampeonatoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'nome'          => $this->nome,
            'status'        => $this->status,
            'times_count'   => $this->whenNotNull($this->times_count),
            'times'         => TimeResource::collection($this->whenLoaded('times')),
            'partidas'      => $this->whenLoaded('partidas', function () {
                return $this->partidas
                    ->groupBy('fase')
                    ->map(fn ($partidas) => PartidaResource::collection($partidas));
            }),
            'classificacao' => $this->when($this->isFinalizado() && $this->relationLoaded('times'), function () {
                return $this->times
                    ->sortByDesc(fn (Time $time) => $time->pivot->pontuacao_total)
                    ->values()
                    ->map(fn (Time $time, int $index) => [
                        'posicao'           => $index + 1,
                        'time'              => new TimeResource($time),
                        'pontuacao_total'   => $time->pivot->pontuacao_total,
                        'gols_fora_de_casa' => $time->pivot->gols_fora_de_casa,
                    ]);
            }),
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
