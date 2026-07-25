# Hermes — contexto de produto

Hermes é uma ferramenta de gestão de testes para o GitHub — o equivalente ao que o
Zephyr Scale faz para o Jira. MVP interno, uma única organização, com integração
profunda via GitHub App (login, webhooks, GitHub Projects v2, comentários e
criação de issues).

## Hierarquia de domínio

Caso de Teste (biblioteca reutilizável) → Teste (ciclo/execução, vinculado a 1
Issue e/ou 1 PR do GitHub) → Cenário (instância de um Caso de Teste dentro de um
Teste específico).

## Regras de negócio já decididas

- **Status de Cenário**: `a_fazer` → `em_andamento` → `passou` / `falhou` /
  `bloqueado`. Cenários em status final podem ser reabertos para `em_andamento`
  (reteste).
- **Severidade do Cenário** (em português, mesmo soando parecido com o status
  "Bloqueado" — o label de contexto já desambigua): `Bloqueante`, `Crítica`,
  `Maior`, `Menor`. `Bloqueante`/`Crítica` = bloqueante para o agregado;
  `Maior`/`Menor` = não-bloqueante. `Impacto` e `Tags` são campos independentes.
- **Cálculo agregado do Teste** (recalculado sempre que um Cenário muda):
  cenários com status `bloqueado` são neutros — não contam como falha nem
  sucesso, e são descontados do denominador do percentual de conclusão.

  ```
  efetivos = cenarios - bloqueados
  percent_complete = terminal(efetivos) / total(efetivos) * 100  (ou 100% se não sobrar nenhum efetivo)
  status = passou                        se nenhum falhou entre os efetivos
         = falhou                        se algum falhou com severidade Bloqueante/Crítica
         = parcial                       se algum falhou mas só com severidade Maior/Menor
         = em_andamento/nao_iniciado     se ainda há efetivos não terminados
  ```

  Ideia futura (não construir ainda, só não travar o schema): agrupar cenários
  `Parcial` em um novo Teste, ou criar bug automaticamente — por isso vale manter
  um gancho de linhagem (ex: `cloned_from_cenario_id`) quando o schema de
  cenários for criado.
- **Timer**: cronômetro start/stop por cenário, tempo somado no total do Teste.
- **Gate de criação do Teste**: só pode ser criado/iniciado quando o campo
  "Status" da Issue/PR vinculada, num GitHub Project (v2), bate com um valor
  configurável pelo admin (não hardcoded).
- **Escrita de volta pro GitHub**: comentário automático na Issue/PR só quando o
  status *agregado do Teste* muda (não a cada cenário). Criação de bug issue tem
  duas ações: QA seleciona cenários falhos específicos, ou um botão único cria
  issue para todos os falhados de uma vez.

## Stack e decisões de arquitetura já tomadas

- Laravel + Inertia + React (starter kit oficial), autenticação de usuário via
  GitHub OAuth (Socialite, usando as credenciais do próprio GitHub App — não
  precisa de OAuth App separado).
- Integração com GitHub via GitHub App (não OAuth App solto, não PAT) — cobre
  login, webhooks, e chamadas servidor-a-servidor (JWT + installation tokens).
- Projects v2 é GraphQL-only (sem REST) — Issues/comentários/criação de issue via
  REST.
- Fila: `database` no MVP (evolui pra Redis/Horizon depois, sem mudar código).
- Evidências: `Storage` (disco local no MVP, trocar por S3 depois é config, não
  lógica de negócio).
- Docker local: `compose.yml` na raiz, espelhando o padrão do projeto irmão
  `laravel-whatsapp` (serviços `db`, `app`, `migrate`, `mailhog`, `queues`,
  `laravel-boost` sob o perfil `ai`), com um adicional: serviço `vite`
  (mesma imagem `app`, que já tem PHP + Node — necessário porque o plugin
  Wayfinder do Vite chama `php artisan` internamente). **Ambiente 100% Docker
  por decisão explícita do usuário** — nada de PHP/Composer/Node local. Todo
  comando (`composer`, `artisan`, `npm`) deve rodar via
  `docker compose exec <serviço> ...` (containers já de pé) ou
  `docker compose run --rm <serviço> ...` (container avulso).

## Roadmap de construção (ainda NÃO implementado — referência para quando essa
## etapa começar)

1. Auth via GitHub OAuth + conexão do GitHub App (JWT/installation token,
   webhook endpoint).
2. Biblioteca de Casos de Teste + CRUD de Teste/Cenário + fluxo de status +
   cálculo agregado.
3. Timer + Evidências + Severidade/Impacto/Tags.
4. Gate de Projects v2 (leitura) + comentários automáticos (escrita).
5. Criação de bug issues (seleção manual / todos os falhados).
