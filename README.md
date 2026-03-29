# Teste-Irroba 🏆

Aplicação para simular campeonatos de futebol eliminatórios, desenvolvida com Laravel 13.

## Tecnologias

- **PHP 8.5** via Laravel Sail
- **Laravel 13.2**
- **MySQL 8.4**
- **Python 3.12** (geração de placares)
- **Redis** (cache)
- **Pest** (testes)
- **Larastan** nível 8 (análise estática)
- **Laravel Pint** PSR-12 (formatação)
- **Husky** + **Commitlint** (hooks de commit)

---

## Pré-requisitos

- Docker
- Git

---

## Instalação

**1. Clonar o repositório:**
```bash
git clone git@github.com:GabrielHenriqueBatista/teste-irroba.git
cd teste-irroba
```

**2. Copiar o arquivo de ambiente:**
```bash
cp .env.example .env
```

**3. Instalar dependências sem precisar do PHP local:**
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php85-composer:latest \
    composer install --ignore-platform-reqs
```

**4. Subir os containers:**
```bash
./vendor/bin/sail up -d
```

**5. Gerar a chave da aplicação:**
```bash
./vendor/bin/sail artisan key:generate
```

**6. Rodar as migrations:**
```bash
./vendor/bin/sail artisan migrate
```

---

## Rodar os testes

**Todos os testes:**
```bash
./vendor/bin/sail artisan test
```

**Apenas testes de integração da API:**
```bash
./vendor/bin/sail artisan test --filter=CampeonatoApiTest
```

**Apenas testes de desempate:**
```bash
./vendor/bin/sail artisan test --filter=SimulacaoPartidaServiceTest
```

**Apenas testes de chaveamento:**
```bash
./vendor/bin/sail artisan test --filter=ChaveamentoCampeonatoServiceTest
```

**Apenas testes do script Python:**
```bash
./vendor/bin/sail artisan test --filter=PlacarPythonServiceTest
```

---

## Como funciona o campeonato

O campeonato é eliminatório e começa nas quartas de final com 8 times:
```
Quartas de final (4 jogos) → Semifinais (2 jogos) → Final + Disputa do 3º lugar
```

### Regras de desempate

Em caso de empate no placar, o vencedor é definido pela seguinte ordem:

1. **Gols na partida** — time com mais gols vence
2. **Pontuação acumulada** — vence quem tem maior pontuação total no campeonato (+1 por gol marcado, -1 por gol sofrido)
3. **Gols fora de casa** ⭐ *(diferencial implementado)* — vence quem marcou mais gols atuando como visitante ao longo do campeonato
4. **Ordem de inscrição** — vence quem se inscreveu primeiro no campeonato

### Geração de placares

Os placares são gerados pelo script Python `teste.py` na raiz do projeto, simulando uma chamada a um modelo de machine learning externo:
```python
import random
print(random.randrange(0, 8, 1))
print(random.randrange(0, 8, 1))
```

---

## Endpoints da API

Base URL: `http://localhost`

> Sempre envie o header `Accept: application/json` nas requisições.

---

### Criar campeonato
```http
POST /api/campeonatos
```

**Body:**
```json
{
  "nome": "Campeonato do Bairro"
}
```

**Response 201:**
```json
{
  "data": {
    "id": 1,
    "nome": "Campeonato do Bairro",
    "status": "pendente",
    "created_at": "2026-03-28T17:57:02.000000Z",
    "updated_at": "2026-03-28T17:57:02.000000Z"
  }
}
```

---

### Inscrever time
```http
POST /api/campeonatos/{id}/times
```

**Body:**
```json
{
  "nome": "Flamengo"
}
```

**Response 201:**
```json
{
  "data": {
    "id": 1,
    "nome": "Flamengo",
    "ordem_inscricao": 1,
    "pontuacao_total": 0,
    "created_at": "2026-03-28T18:58:34.000000Z",
    "updated_at": "2026-03-28T18:58:34.000000Z"
  }
}
```

**Erros:**
- `422` — campeonato já possui 8 times inscritos
- `422` — campeonato não está com status `pendente`

---

### Simular campeonato
```http
POST /api/campeonatos/{id}/simular
```

**Response 200:**
```json
{
  "message": "Campeonato simulado com sucesso.",
  "campeonato": {
    "id": 1,
    "nome": "Campeonato do Bairro",
    "status": "finalizado",
    "times": [],
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Erros:**
- `422` — campeonato não possui exatamente 8 times
- `422` — campeonato já foi simulado

---

### Listar campeonatos
```http
GET /api/campeonatos
GET /api/campeonatos?status=pendente
GET /api/campeonatos?status=em_andamento
GET /api/campeonatos?status=finalizado
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "nome": "Campeonato do Bairro",
      "status": "finalizado",
      "times_count": 8,
      "created_at": "...",
      "updated_at": "..."
    }
  ],
  "links": {},
  "meta": {}
}
```

---

### Detalhar campeonato
```http
GET /api/campeonatos/{id}
```

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "nome": "Campeonato do Bairro",
    "status": "finalizado",
    "times": [],
    "partidas": {
      "quartas_de_final": [],
      "semifinal": [],
      "terceiro_lugar": [],
      "final": []
    },
    "classificacao": [
      {
        "posicao": 1,
        "time": { "id": 1, "nome": "Flamengo" },
        "pontuacao_total": 9,
        "gols_fora_de_casa": 5
      }
    ],
    "created_at": "...",
    "updated_at": "..."
  }
}
```

---

## Padrão de commits

Este projeto segue o padrão **Conventional Commits**:
```
feat(escopo): [CARD-XX] descrição
fix(escopo): [CARD-XX] descrição
chore(escopo): descrição
test: [CARD-XX] descrição
docs: descrição
```

**Tipos permitidos:** `feat`, `fix`, `chore`, `docs`, `test`, `refactor`, `style`, `ci`

---

## Collection Postman/Insomnia

A collection com todos os endpoints está disponível em `/docs/collection.json`.

Importe o arquivo no Insomnia ou Postman e configure a variável de ambiente `base_url` como `http://localhost`.
