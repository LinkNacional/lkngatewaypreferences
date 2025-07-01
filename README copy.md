# whmcs-gateway-preferences

## Requisitos e suporte
- PHP: 8.1+
- WHMCS: 8.1+

## Modo de instalação
- Baixar o .zip do módulo.
- É recomendado que exclua a versão anterior do módulo do seu WHMCS.
- Descompacte e envie os o conteúdo extraído à raiz de instalação do seu WHMCS.

## Configuração
1. Vá à página de addons, ativo o módulo `Preferências de gateways`.
2. Clique em `Configurar`.
3. Na configuração `Controle de Acesso", conceda acesso aos grupos de administradores que poderão acessar a página do módulo.

## Modo de uso
1. No menu do WHMCS, no topo da página, no item `Addons`, acesse `Preferências de gateways`.
2. Na página do módulo, há dois menus: um para definição de gateways por país e outro para visualização de preferências por cliente.
3. Para definir preferências por cliente, basta acessar o perfil de um cliente, na aba `Resumo`, na seção `Outras Ações`, clique em `Editar preferências de gateway`.

## Funcionalidades
- Definir gateways permitidos globalmente, para todos os países
- Definir gateways permitidos por país
- Definir gateways permitodos por cliente
- Exibição ou não exibição automática dos gateways na fatura e no checkout
- Verificação e troca automática para gateway permitido a cliente na criação da fatura


## Notas de desenvolvimento

### Tabelas do módulo no banco de dados

#### mod_lkngatewaypreferences_by_client
Colunas:
- `client_id`: chave estrangeira para a coluna id da tablea `tblclients`.
- `gateways`: string em formato json com os códigos dos gateways permitidos: `[lknbbpix, lkncielocreditcard, ...]`.


#### mod_lkngatewaypreferences_by_country
Colunas:
- `country`: sigla do país na ISO 3166-2 (BR, US). Caso seja para todos os países, o valor da coluna é `**`.
- `gateways`: string em formato json com os códigos dos gateways permitidos: `[lknbbpix, lkncielocreditcard, ...]`.

#### mod_lkngatewaypreferences_settings
Colunas:
- `id`
- `setting`: nome da configuração.
- `gateways`: string em formato json do valor da configuração.
