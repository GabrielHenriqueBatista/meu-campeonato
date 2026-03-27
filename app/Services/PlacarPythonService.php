<?php

namespace App\Services;

use RuntimeException;

class PlacarPythonService
{
    public function obterPlacar(): array
    {
        $scriptPath = base_path('teste.py');

        $descritores = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $processo = proc_open("python3 {$scriptPath}", $descritores, $pipes);

        if (!is_resource($processo)) {
            throw new RuntimeException('Falha ao executar o script Python.');
        }

        $output = stream_get_contents($pipes[1]);
        $erro   = stream_get_contents($pipes[2]);

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $codigoSaida = proc_close($processo);

        if ($codigoSaida !== 0) {
            throw new RuntimeException("Erro ao executar teste.py: {$erro}");
        }

        $linhas = array_filter(explode("\n", trim($output)));
        $linhas = array_values($linhas);

        if (count($linhas) !== 2) {
            throw new RuntimeException("Output inesperado do script Python: {$output}");
        }

        return [
            'gols_mandante'  => (int) $linhas[0],
            'gols_visitante' => (int) $linhas[1],
        ];
    }
}
