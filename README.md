# mod_uemsinfotutoria

Plugin Moodle para exibir, dentro da disciplina, a equipe de tutoria presencial e mediação pedagógica vinculada aos polos.

A experiência principal é inline, no estilo Label: a informação aparece diretamente na página da disciplina, sem exigir clique do estudante.

## Requisitos

- Moodle 4.5.
- Plugin instalado em `mod/uemsinfotutoria`.
- Papéis institucionais existentes no Moodle:
  - `mod_tutor` — Tutor Presencial.
  - `mod_medpdg` — Mediador Pedagógico.
- Polos representados por grupos do curso cujo nome contém `polo`.

## Instalação

A pasta do plugin dentro do Moodle deve se chamar `uemsinfotutoria`.

Exemplo usando Git:

```bash
cd /caminho/do/moodle/mod
git clone -b MOODLE_405_STABLE https://github.com/UEMS-Virtual/moodle-mod_uems_info_tutoria.git uemsinfotutoria
cd /caminho/do/moodle
php admin/cli/upgrade.php
php admin/cli/purge_caches.php
```

No ambiente Docker local deste projeto:

```bash
docker exec moodle45-app php /var/www/html/admin/cli/upgrade.php --non-interactive
docker exec moodle45-app php /var/www/html/admin/cli/purge_caches.php
```

## Como funciona

O plugin não cadastra tutores, mediadores ou polos. Ele lê dados já existentes no Moodle:

- usuários ativos matriculados na disciplina;
- papéis atribuídos no contexto da disciplina;
- grupos do curso usados como polos;
- foto de perfil do usuário;
- recurso nativo de mensagens do Moodle.

## Regras de domínio

### Tutor Presencial

Usuário ativo da disciplina com papel `mod_tutor`.

### Mediador Pedagógico

Usuário ativo da disciplina com papel `mod_medpdg`.

### Polo

Grupo da disciplina cujo nome contém `polo`, sem diferenciar maiúsculas/minúsculas.

### Reoferta

No modo automático, o plugin detecta reofertas pelo `shortname` da disciplina quando há token isolado `REO` ou `REO2`.

Exemplos detectados:

- `CISOL_23_2S_SA_(REO)_cb9e8`
- `ABC_REO_2026`
- `ABC-(REO2)-2026`
- `ABC_REO2_x`

Exemplos não detectados:

- `TEOREOLOGIA_2026`
- `PREOFERTA_2026`
- `COREO_ABC`

## Configurações da atividade

Ao adicionar a atividade na disciplina, é possível configurar:

- nome da atividade;
- descrição;
- título do painel do estudante;
- se Tutor Presencial é esperado: Automático / Sim / Não;
- se Mediador Pedagógico é esperado: Automático / Sim / Não.

Regras do modo automático:

- Tutor Presencial é esperado.
- Mediador Pedagógico é esperado em disciplinas comuns.
- Mediador Pedagógico não é esperado em reofertas detectadas por `REO`/`REO2` no `shortname`.

## Visualização do estudante

O estudante vê primeiro a aba **Meu polo**, com:

- nome do seu polo;
- Tutor(es) Presencial(is) vinculados ao seu polo;
- Mediador(es) Pedagógico(s) vinculados ao seu polo;
- opção de alternar para **Lista completa**.

Se o estudante não estiver em nenhum polo, a aba **Meu polo** não usa a Lista completa como fallback.

## Visualização de professor/admin

Usuários com perfil de gestão da disciplina veem a **Lista completa**, com a equipe vinculada à disciplina inteira.

## Estados vazios

Quando uma função é esperada, mas não há pessoa vinculada:

- na Lista completa: `não informado para a disciplina`;
- em Meu polo: `não informado para seu polo`.

Quando uma função não é esperada, sua seção não aparece.

Se nenhuma função for esperada:

- estudantes não veem conteúdo;
- usuários com permissão de gerenciar atividades veem aviso operacional mínimo.

## Testes e validação

Validação final realizada:

```text
PHP lint: OK
PHPUnit: 10 tests, 24 assertions
Behat: 3 scenarios, 40 steps
Grunt AMD: OK
Upgrade Moodle: OK
```

Comandos úteis no ambiente Docker local:

```bash
# PHP lint do plugin.
find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l

# PHPUnit.
docker exec moodle45-app bash -lc 'cd /var/www/html && vendor/bin/phpunit mod/uemsinfotutoria/tests/team_data_test.php mod/uemsinfotutoria/tests/output_test.php'

# Behat.
docker exec moodle45-app bash -lc 'cd /var/www/html && vendor/bin/behat --config /var/www/behatdata/behatrun/behat/behat.yml --profile=chrome mod/uemsinfotutoria/tests/behat/inline_display.feature'

# Compilar AMD com Node 22 no host.
source ~/.nvm/nvm.sh
nvm use 22.22.3
cd /home/breno/docker/moodle45/moodle
npx grunt amd --root=mod/uemsinfotutoria
```

## Documentação complementar

- Norte do projeto: `docs/NORTE_DO_PROJETO.html`
- Explicação sobre Behat: `docs/BEHAT_NO_PROJETO.html`
- Glossário de domínio: `CONTEXT.md`
- Protótipos visuais: `docs/prototipos-visuais/`

## Branch Moodle

A branch estável para Moodle 4.5 é:

```text
MOODLE_405_STABLE
```
