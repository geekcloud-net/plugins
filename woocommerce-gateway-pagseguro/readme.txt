=== WooCommerce PagSeguro (Brazil) Gateway Module ===
A payment extension for PagSeguro.


== Important Note ==
Please set your origin country to Brazil and currency to BRL


== INSTRUCTIONS TO USE ==
This plugin NOT works on API 2.0 from PagSeguro, this case, the documentation
overview is https://pagseguro.uol.com.br/v2/guia-de-integracao/visao-geral.html

Is necessary do some configurations in your PagSeguro account:

	1 - Generate a Token Key https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml
		Without this Token key, this gateway no works.

	2 - Activate the API 2.0 https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml
		When this os active the plugin PagSeguro Gateway can comunicate with PagSeguro automatically

	3 - Activate Dynamic redirect https://pagseguro.uol.com.br/integracao/pagina-de-redirecionamento.jhtml
		Inform in input field "transaction_id" (without quotes), choose Activate and click save

	4 - Add your email and Token Key in your PagSeguro Gateway. Save changes and ready.


if you want receive payments of credit card and mobile (Oi Paggo), you need visit this
page and activate thats https://pagseguro.uol.com.br/preferences/receiving.jhtml

== ERRORS AND PROBLEMS ==
Any errors can happen, this is a page of explication thats
https://pagseguro.uol.com.br/v2/guia-de-integracao/codigos-de-erro.html
