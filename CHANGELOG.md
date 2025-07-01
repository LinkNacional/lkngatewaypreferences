# 1.6.2 01/07/24
- Remoção de funções de licença.

# 1.6.1 06/05/24
- Adicionar compatibilidade com checkout custom da Link Nacional

# 1.6.0 26/11/24
- #40 Adicionado a parte de esconder o gateway no checkout personalizado
- #42 Correção nos caminhos dinâmicos do módulo
- #39 Correção ao gerar logs
- #38 Correção de erro ao não encontrar arquivo
- #37 Correção de erro ao reativar o módulo

# 1.5.0 02/01/24
- #27 Adicionada nova aba para preferências para fraude
- #15 Adicionado aviso de novas versões
- #26 Melhorias de verificação de licença
- #29 Melhorada internacionalização de logs

# 1.4.0 - 07/12/23
- #19 Removido link vazio da logo no banner
- #20 Modificado link de compra para o endereço correto
- #23 Adicionada feature de adicionar notas aos clientes com pedido alterado
- Modificado comportamento da feature de alterar pedidos Fraude para Pendente apenas se Fraude for detectada enquanto a opção estiver selecionada

# 1.3.1 - 05/12/23
- #22 Adicionar licença individual e geral.

# 1.3.0 - 30/11/23
- #11 Ajuste para definição de gateways para pedidos fraudulentos somente estar disponível para usuários pro
- #11 Ajustado para limite do plano gratuito ser contemplado pelo plugin
- #11 Adicionada explicação de como adicionar preferências de gateway para clientes
- #14 Adicionada opção de inserir preferências de cliente diretamente pela tela de preferências por cliente
- #14 Adicionado aviso de que opção de inserir preferência diretamente na tela do cliente é apenas para usuários pro
- #16 Adicionada opção de definir pedidos fraudulentos como pendentes automaticamente
- Atualizados termos

# 1.2.1
- Ajustes para não exposição do código-fonte de hooks.php

# 1.2.0
- Implementar licença
- Implementar suporte para inglês, português do Brasil e português de Portugal

# 1.1.1 - 19/07/23
- Adicionar feedback ao salvar preferências de gateway
- Aprimorar configuração de gateways para pedidos fraudulen

# 1.1.0 - 06/07/23
- Simplificar estrutura do módulo
- Aprimorar lógica para definir quais gateways estão permitidos para um cliente
- Implementar verificação de gateway na página viewinvoice.php
- Implementar evento para atualizar faturas em estado pendente quando preferência específica para cliente é editada
- Corrigir bug no helper Config

# 1.0.0 - 29/05/23
- Definir gateways permitidos globalmente, para todos os países
- Definir gateways permitidos por país
- Definir gateways permitodos por cliente
- Exibição ou não exibição automática dos gateways na fatura e no checkout
- Verificação e troca automática para gateway permitido a cliente na criação da fatura
