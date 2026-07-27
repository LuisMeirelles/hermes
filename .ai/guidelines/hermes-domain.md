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
- `now()` neste projeto retorna `Carbon\CarbonImmutable`, não `Carbon\Carbon`
  mutável. Encadear `$now->subX(...)->timestamp` seguido de
  `$now->addY(...)->timestamp` NÃO acumula — cada chamada parte do mesmo
  instante original, já que nenhuma reatribui `$now`. Atenção redobrada em
  qualquer cálculo de janela de tempo (ex: `exp`/`iat` de JWT).
- Rotas tipo webhook (sem formulário/CSRF token, chamadas servidor-a-servidor)
  usam `$middleware->validateCsrfTokens(except: [...])` em `bootstrap/app.php`
  em vez de um grupo `api:` novo — o app não tem `routes/api.php` registrado.
- Eventos de ciclo de vida da instalação do GitHub App (`installation`,
  `installation_repositories`) são entregues automaticamente pra qualquer
  webhook ativo do App — não aparecem em "Subscribe to events" e não precisam
  (nem podem) ser assinados explicitamente.
- Agrupamentos de UI que não existem como status real (ex: "Pendente" no
  dashboard e no filtro de `/testes` = `nao_iniciado` + `em_andamento`) ficam
  como método estático no enum (`TesteStatus::pendentes()`) ou mapa local na
  página — nunca como case sintético do enum, pra não quebrar `cases()`/
  `from()`/matches exaustivos como o `Record<Status, ...>` do `StatusBadge`.

## Roadmap de construção

1. ✅ **Feito.** Auth via GitHub OAuth + conexão do GitHub App (JWT/installation
   token, webhook endpoint). Login via Socialite
   (`app/Http/Controllers/Auth/GithubController.php`); JWT + installation
   token via `app/Services/GithubApp.php`; conexão da instalação via Setup URL
   em Settings > GitHub (`GithubInstallationController`); webhook com
   verificação de assinatura (`VerifyGithubWebhookSignature` middleware) e
   processamento assíncrono (`ProcessGithubWebhook` job, tabela
   `github_installations`). Verificado contra a API e webhook reais do GitHub,
   não só mocks.
2. ✅ **Feito.** Biblioteca de Casos de Teste + CRUD de Teste/Cenário + fluxo
   de status + cálculo agregado. CRUD de Casos de Teste em Gherkin
   (`CasoDeTesteController`, editor drag-and-drop `GherkinStepEditor`); Testes
   vinculados a issues reais do GitHub, um repo+issue pode ter vários ciclos
   de Teste (sem unique constraint) (`TesteController`); Cenários instanciados
   de Casos de Teste com snapshot de passos e máquina de estados de status
   (`CenarioController`, `CenarioStatus::allowedNextStatuses()`); cálculo
   agregado isolado em serviço puro e testável
   (`TesteAggregateCalculator`/`TesteAggregateResult`/`TesteAggregateRecalculator`).
3. ✅ **Feito.** Dashboard com visão geral dos Testes: cards de contagem por
   status agregado (Sucesso/Falha/Parcial/Pendente, cada um linkando para a
   listagem de Testes já filtrada via `?status=`), distribuição percentual
   entre eles, cenários bloqueantes em aberto (severidade Bloqueante/Crítica
   ainda não `passou`, incluindo `bloqueado` — que já conta como falha
   efetiva pela regra de cálculo agregado), testes recentes e totais da
   biblioteca de Casos de Teste (total + não utilizados em nenhum Cenário)
   (`DashboardController`). Verificado com testes Pest e manualmente no
   browser, com dados de teste criados pela própria UI (não apenas
   factories/tinker).
4. Timer + Evidências + Impacto/Tags. **(próximo passo)**
5. Gate de Projects v2 (leitura) + comentários automáticos (escrita).
6. Criação de bug issues (seleção manual / todos os falhados).
