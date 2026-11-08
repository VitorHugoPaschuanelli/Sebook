# Escopo e decisões do MVP

## Objetivo

Permitir que um sebo organize seu acervo localmente, cadastre livros e acompanhe entradas e saídas. A interface usa PHP renderizado no servidor, HTML, CSS, JavaScript e Bootstrap. A persistência usa PDO e SQLite; Composer fornece o autoload PSR-4.

## Regras

- Um cadastro representa um grupo de exemplares da mesma edição, conservação e preço. Cadastros diferentes podem compartilhar ISBN.
- Título, autor, conservação e preço são obrigatórios. Preço zero é permitido.
- Quantidades são inteiras entre 0 e 1.000.000. Movimentações exigem quantidade maior que zero.
- Saldo igual ou inferior ao mínimo aparece como estoque baixo. Estoque zero também gera alerta.
- A quantidade inicial gera uma entrada; saldo inicial zero não gera movimentação.
- Alterar cadastro não altera quantidade. Movimentações e saldo são gravados juntos em transação, com atualização condicional que impede saldo negativo.
- Movimentações não são apagadas nem editadas. Erros são corrigidos por uma nova movimentação inversa, com justificativa.
- Arquivamento só é permitido com saldo zero. O cadastro e seu histórico são preservados e podem ser reativados.
- Preço é um inteiro em centavos. O painel não representa receita, lucro ou valor de aquisição.
- As datas são registradas no fuso de São Paulo.

## Dados

`books` guarda dados do livro, preço, saldo atual, estoque mínimo, situação e datas. `movements` guarda livro, tipo, quantidade, saldo após, motivo, observação e data. A chave estrangeira mantém a relação e há índice para o histórico por livro. `PRAGMA user_version` identifica a versão do esquema.

O banco é criado automaticamente e fica fora da raiz pública. `SEBOOK_DB_PATH` pode indicar um caminho alternativo, usado pelos testes. Sem essa variável, o arquivo é `database/sebook.sqlite`.

## Proteções implementadas

Consultas parametrizadas, escape de saída HTML, validação no servidor, token CSRF nos formulários, redirecionamento após POST e cookies de sessão HttpOnly/SameSite. O JavaScript desabilita o botão durante o envio para evitar duplo clique. A aplicação é local, sem autenticação e sem controle de múltiplos usuários.

## Fora desta versão

Clientes, fornecedores, contas de acesso, caixa, emissão fiscal, relatórios financeiros, leitura automática de código de barras, importação, fotos/capas, sincronização na nuvem e hospedagem. A baixa com motivo Venda é uma movimentação de estoque, não uma venda com pagamento ou comprovante.

## Testes

As verificações de regras cobrem cadastro, valores, busca, entrada, saída, estoque mínimo, arquivamento, reativação, rollback e preservação do saldo na edição. Os testes HTTP cobrem formulários, redirecionamentos, persistência, validação, CSRF, escape HTML, filtros e proteção dos caminhos privados. Os dados de teste são isolados do acervo real.
