# Informações de Tutoria

Contexto do plugin Moodle que exibe, dentro de uma disciplina, contatos de tutoria presencial e mediação pedagógica vinculados aos polos.

## Language

**Disciplina**:
Curso Moodle onde a atividade de informações de tutoria está inserida.
_Avoid_: Sala, turma, course

**Polo**:
Grupo da Disciplina cujo nome contém a palavra “polo” e representa uma unidade/local de apoio do estudante.
_Avoid_: Unidade, grupo comum

**Tutor Presencial**:
Usuário ativo da Disciplina com papel institucional `mod_tutor`.
_Avoid_: Tutor, tutor da sala

**Mediador Pedagógico**:
Usuário ativo da Disciplina com papel institucional `mod_medpdg`.
_Avoid_: Mediador, professor mediador

**Equipe de Tutoria e Mediação**:
Conjunto de Tutores Presenciais e Mediadores Pedagógicos ativos vinculados à Disciplina.
_Avoid_: Informações de tutoria, Equipe de tutoria e mediação pedagógica

**Tutoria esperada**:
Configuração da Disciplina que indica, separadamente, se a presença de Tutor Presencial e se a presença de Mediador Pedagógico são esperadas para aquela atividade; cada função pode estar em modo Automático, Sim ou Não.
_Avoid_: Sala deve ter tutor, bloco obrigatório

**Marcador de reoferta**:
Token isolado presente no shortname da Disciplina, como `REO` ou `REO2`, usado para reconhecer uma Reoferta de Disciplina.
_Avoid_: Sufixo REO

**Reoferta de Disciplina**:
Oferta em que pode haver Tutor Presencial esperado para ações de polo, como aplicação de prova, sem Mediador Pedagógico esperado para acompanhamento contínuo do estudante.
_Avoid_: Disciplina sem tutoria

**Lista completa**:
Visualização que mostra a Equipe de Tutoria e Mediação da Disciplina inteira.
_Avoid_: Visão institucional, todos

**Meu polo**:
Visualização do estudante que mostra apenas Tutores Presenciais e Mediadores Pedagógicos vinculados ao Polo do estudante.
_Avoid_: Minha sala, meu grupo

## Relationships

- Uma **Disciplina** possui zero ou mais **Polos**.
- Um estudante deve pertencer a no máximo um **Polo** por **Disciplina**.
- Uma **Disciplina** possui zero ou mais **Tutores Presenciais**.
- Uma **Disciplina** possui zero ou mais **Mediadores Pedagógicos**.
- Uma **Disciplina** possui duas configurações independentes de **Tutoria esperada**: uma para Tutor Presencial e uma para Mediador Pedagógico.
- Em modo Automático, Tutor Presencial é esperado por padrão.
- Em modo Automático, Mediador Pedagógico não é esperado quando a Disciplina possui **Marcador de reoferta**; nos demais casos, é esperado.
- O **Marcador de reoferta** deve ser detectado como token isolado, aceitando separadores como parênteses, underscore e hífen; não deve casar trechos internos de outras palavras.
- O formulário da atividade oferece Automático, Sim e Não para Tutor Presencial esperado e Mediador Pedagógico esperado; o modo Automático é explicado em textos de ajuda, sem necessidade inicial de mostrar o valor resolvido no formulário.
- Um **Polo** pode ter zero ou mais **Tutores Presenciais** e zero ou mais **Mediadores Pedagógicos** vinculados via participação em grupo.
- A **Equipe de Tutoria e Mediação** pertence a uma **Disciplina**.
- A **Lista completa** é calculada a partir da **Disciplina** inteira.
- **Meu polo** é calculado a partir do **Polo** do estudante.
- Se o estudante não pertence a nenhum **Polo**, **Meu polo** não deve mostrar a equipe da Disciplina como fallback.
- Se o estudante pertence a mais de um **Polo** na mesma **Disciplina**, isso é inconsistência operacional, mas a interface usa o primeiro Polo encontrado sem bloquear o estudante.
- Uma função não esperada pela configuração de **Tutoria esperada** não aparece na interface.
- Se nenhuma função é esperada, estudantes não veem conteúdo; professores/administradores veem um aviso operacional mínimo.
- A experiência principal da atividade é inline na página da Disciplina; a página própria da atividade existe apenas como fallback técnico simples.

## Example dialogue

> **Dev:** “Quando não há Tutor Presencial na Disciplina, mostramos uma mensagem global ou escondemos a seção?”
> **Domain expert:** “Precisamos diferenciar ausência na Disciplina de ausência no Polo do estudante, porque são situações diferentes.”

## Flagged ambiguities

- “Sala” foi usado para se referir à **Disciplina**. Resolvido: a documentação deve usar **Disciplina**.
- “Disciplina sem tutoria” pode significar ausência total de equipe ou apenas ausência de Mediador Pedagógico em uma **Reoferta de Disciplina**. Resolvido: usar **Tutoria esperada** por função.
- “Sufixo REO” foi usado para o padrão do shortname, mas `REO` pode aparecer como marcador interno, por exemplo `_(REO)_`. Resolvido: usar **Marcador de reoferta**.
- Um estudante em dois Polos na mesma Disciplina foi tratado como possibilidade técnica, mas no domínio é erro operacional. Resolvido: estudante deve ter no máximo um Polo por Disciplina; se houver mais de um, a interface usa o primeiro Polo encontrado.
